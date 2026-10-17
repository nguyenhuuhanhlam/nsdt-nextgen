"""Nạp file chấm công — luồng 3 bước: parse -> báo cáo kiểm tra -> xác nhận nạp.

- POST /attendance/parse: upload file, trả báo cáo E1..E5 / W1..W7, chưa ghi DB.
- POST /attendance/import: xác nhận nạp (kèm ngày đã kiểm tra W2).
- POST /attendance/files/{id}/reload: nạp lại file pending_fix sau khi gán thẻ.
"""

from __future__ import annotations

import json
import os
import uuid
from datetime import date
from pathlib import Path

from fastapi import APIRouter, Depends, File, HTTPException, Query, UploadFile, status
from sqlalchemy import delete, func, select, tuple_
from sqlalchemy.orm import Session

from app.api.deps import get_db
from app.models.attendance import (
    FILE_IMPORTED,
    FILE_PENDING_FIX,
    AttendanceFile,
    AttendanceTap,
)
from app.models.employee import Employee
from app.parsers.mcc_txt import ParseResult, TapRow, parse_mcc_txt
from app.schemas.attendance import (
    AssignCardRequest,
    AttendanceFileRead,
    DateBreakdown,
    DedupDetailRead,
    ImportRequest,
    ImportResult,
    PaginatedFiles,
    PaginatedTaps,
    ParseReport,
    RowErrorRead,
    TapRead,
    UnmatchedCardRead,
    UnmatchedFileCards,
)

router = APIRouter(prefix="/attendance", tags=["attendance"])

UPLOAD_DIR = Path(
    os.environ.get(
        "ATTENDANCE_UPLOAD_DIR",
        str(Path(__file__).resolve().parent.parent.parent / "uploads" / "attendance"),
    )
)
UPLOAD_DIR.mkdir(parents=True, exist_ok=True)

MAX_FILE_SIZE = 20 * 1024 * 1024  # 20MB
INSERT_CHUNK = 1000
ERROR_SAMPLES = 20  # số dòng lỗi mẫu trong báo cáo
DEDUP_SAMPLES = 50  # số lượt gộp trùng mẫu trong báo cáo


def _parsed_path(upload_id: str) -> Path:
    return UPLOAD_DIR / f"{upload_id}.parsed.json"


def _raw_path(upload_id: str) -> Path:
    return UPLOAD_DIR / f"{upload_id}.txt"


def _card_employee_map(db: Session, card_ids: set[int]) -> dict[int, int]:
    """card_id -> employee_id. Thẻ trùng ở nhiều NV: ưu tiên NV đang làm."""
    if not card_ids:
        return {}
    rows = db.execute(
        select(Employee.id, Employee.card_id)
        .where(Employee.card_id.in_(card_ids))
        .order_by(Employee.is_active.desc(), Employee.id)
    ).all()
    mapping: dict[int, int] = {}
    for emp_id, card_id in rows:
        mapping.setdefault(card_id, emp_id)
    return mapping


def _unmatched_cards(valid_rows: list[TapRow], emp_map: dict[int, int],
                      deduped_per_card: dict[int, int]):
    """Nhóm các dòng có thẻ chưa khớp NV: [{card_id, taps, deduped, dates, times}]."""
    groups: dict[int, dict] = {}
    for r in valid_rows:
        if r.card_id in emp_map:
            continue
        g = groups.setdefault(r.card_id, {"taps": 0, "dates": set(), "times": {}})
        g["taps"] += 1
        date_iso = r.tap_date.isoformat()
        g["dates"].add(date_iso)
        # valid_rows đã sort theo (card, datetime) nên giờ trong ngày tăng dần
        g["times"].setdefault(date_iso, []).append(r.tap_time.strftime("%H:%M:%S"))
    return [
        {
            "card_id": cid,
            "taps": g["taps"],
            "deduped": deduped_per_card.get(cid, 0),
            "dates": sorted(g["dates"]),
            "times": {d: g["times"][d] for d in sorted(g["times"])},
        }
        for cid, g in sorted(groups.items())
    ]


def _build_report(
    db: Session, upload_id: str, filename: str, parsed
) -> ParseReport:
    card_ids = {r.card_id for r in parsed.valid_rows}
    emp_map = _card_employee_map(db, card_ids)
    unmatched = _unmatched_cards(parsed.valid_rows, emp_map, parsed.deduped_per_card)
    unmatched_set = {u["card_id"] for u in unmatched}

    existing = db.scalar(
        select(AttendanceFile).where(AttendanceFile.file_hash == parsed.file_hash)
    )

    return ParseReport(
        upload_id=upload_id,
        filename=filename,
        file_hash=parsed.file_hash,
        total_rows=parsed.total_rows,
        valid_rows=len(parsed.valid_rows),
        error_rows=len(parsed.errors),
        error_counts=parsed.error_counts,
        error_samples=[
            RowErrorRead(**e.to_dict()) for e in parsed.errors[:ERROR_SAMPLES]
        ],
        dates=[
            DateBreakdown(date=date.fromisoformat(d), rows=n)
            for d, n in parsed.dates.items()
        ],
        deduped_count=parsed.deduped_count,
        dedup_samples=[
            DedupDetailRead(
                line_no=d.line_no,
                card_id=d.card_id,
                tap_datetime=d.tap_datetime.strftime("%d-%m-%Y %H:%M:%S"),
                kept_line_no=d.kept_line_no,
                kept_datetime=d.kept_datetime.strftime("%d-%m-%Y %H:%M:%S"),
            )
            for d in parsed.dedup_details[:DEDUP_SAMPLES]
        ],
        unmatched_cards=[UnmatchedCardRead(**u) for u in unmatched],
        flag_counts=parsed.flag_counts,
        importable_rows=sum(1 for r in parsed.valid_rows if r.card_id not in unmatched_set),
        already_imported=existing is not None,
        existing_file_id=existing.id if existing else None,
        existing_status=existing.status if existing else None,
    )


def _insert_new_taps(
    db: Session,
    file_id: int,
    rows: list[tuple[TapRow, int, list[str]]],
) -> int:
    """Insert bulk, bỏ qua dòng đã tồn tại (unique card+date+time). Trả về số dòng đã thêm."""
    inserted = 0
    for i in range(0, len(rows), INSERT_CHUNK):
        chunk = rows[i : i + INSERT_CHUNK]
        keys = [(r.tap_date, r.tap_time, r.card_id) for r, _, _ in chunk]
        existing = set(
            db.execute(
                select(AttendanceTap.tap_date, AttendanceTap.tap_time, AttendanceTap.card_id)
                .where(
                    tuple_(
                        AttendanceTap.tap_date,
                        AttendanceTap.tap_time,
                        AttendanceTap.card_id,
                    ).in_(keys)
                )
            ).all()
        )
        mappings = [
            {
                "card_id": r.card_id,
                "employee_id": emp_id,
                "tap_date": r.tap_date,
                "tap_time": r.tap_time,
                "tap_datetime": r.tap_datetime,
                "flags": flags or None,
                "file_id": file_id,
            }
            for r, emp_id, flags in chunk
            if (r.tap_date, r.tap_time, r.card_id) not in existing
        ]
        if mappings:
            db.bulk_insert_mappings(AttendanceTap, mappings)
            inserted += len(mappings)
    return inserted


def _do_import(
    db: Session,
    parsed: ParseResult,
    confirmed_dates: set[date],
    storage_path: str,
    file_row: AttendanceFile | None = None,
) -> ImportResult:
    """Lõi nạp: chỉ nạp dòng đã xác nhận ngày + khớp NV. Idempotent."""
    emp_map = _card_employee_map(db, {r.card_id for r in parsed.valid_rows})
    flags = {int(k): v for k, v in parsed.flags.items()}

    to_insert: list[tuple[TapRow, int, list[str]]] = []
    skipped_unmatched = 0
    for idx, r in enumerate(parsed.valid_rows):
        if r.tap_date not in confirmed_dates:
            continue
        emp_id = emp_map.get(r.card_id)
        if emp_id is None:
            skipped_unmatched += 1
            continue
        to_insert.append((r, emp_id, flags.get(idx, [])))

    if file_row is None:
        file_row = AttendanceFile(
            filename=parsed.filename,
            file_hash=parsed.file_hash,
            storage_path=storage_path,
            work_dates=sorted(parsed.dates.keys()),
            confirmed_dates=sorted(d.isoformat() for d in confirmed_dates),
            total_rows=parsed.total_rows,
            skipped_rows=len(parsed.errors),
            deduped_count=parsed.deduped_count,
        )
        db.add(file_row)
        db.flush()  # lấy id

    inserted = _insert_new_taps(db, file_row.id, to_insert)

    unmatched = _unmatched_cards(
        [r for r in parsed.valid_rows if r.tap_date in confirmed_dates],
        emp_map,
        parsed.deduped_per_card,
    )
    file_row.imported_rows = (file_row.imported_rows or 0) + inserted
    file_row.unmatched_cards = unmatched
    file_row.status = FILE_PENDING_FIX if unmatched else FILE_IMPORTED
    db.commit()
    db.refresh(file_row)

    return ImportResult(
        file_id=file_row.id,
        filename=file_row.filename,
        imported_rows=inserted,
        skipped_unmatched=skipped_unmatched,
        deduped_count=parsed.deduped_count,
        status=file_row.status,
        unmatched_cards=[UnmatchedCardRead(**u) for u in unmatched],
    )


@router.post("/parse", response_model=ParseReport)
def parse_file(file: UploadFile = File(...), db: Session = Depends(get_db)):
    content = file.file.read()
    if len(content) > MAX_FILE_SIZE:
        raise HTTPException(status_code=413, detail="File quá lớn (tối đa 20MB)")
    if not content.strip():
        raise HTTPException(status_code=422, detail="File rỗng")
    try:
        parsed = parse_mcc_txt(content, file.filename or "upload.txt")
    except ValueError as e:
        raise HTTPException(status_code=422, detail=str(e))

    upload_id = uuid.uuid4().hex
    _raw_path(upload_id).write_bytes(content)
    _parsed_path(upload_id).write_text(
        json.dumps(parsed.to_dict()), encoding="utf-8"
    )
    return _build_report(db, upload_id, file.filename or "upload.txt", parsed)


@router.post("/import", response_model=ImportResult)
def import_file(payload: ImportRequest, db: Session = Depends(get_db)):
    parsed_file = _parsed_path(payload.upload_id)
    if not parsed_file.exists():
        raise HTTPException(status_code=404, detail="Phiên parse đã hết hạn, hãy upload lại")
    parsed = ParseResult.from_dict(json.loads(parsed_file.read_text(encoding="utf-8")))

    # W7: file đã nạp trước đó
    existing = db.scalar(
        select(AttendanceFile).where(AttendanceFile.file_hash == parsed.file_hash)
    )
    if existing is not None:
        if not payload.replace_existing:
            raise HTTPException(
                status_code=409,
                detail=f"File đã nạp trước đó (đợt #{existing.id}). "
                "Chọn 'nạp lại thay thế' nếu muốn ghi đè.",
            )
        # Nạp lại thay thế: xóa đợt cũ (cả lượt quẹt), nạp mới hoàn toàn
        db.execute(delete(AttendanceTap).where(AttendanceTap.file_id == existing.id))
        db.delete(existing)
        db.commit()

    # W2: phải xác nhận đủ các ngày trong file
    file_dates = {date.fromisoformat(d) for d in parsed.dates.keys()}
    if not file_dates <= set(payload.confirmed_dates):
        missing = sorted(d.isoformat() for d in file_dates - set(payload.confirmed_dates))
        raise HTTPException(
            status_code=422,
            detail=f"Chưa xác nhận các ngày: {', '.join(missing)}",
        )

    upload_id = payload.upload_id
    return _do_import(
        db,
        parsed,
        set(payload.confirmed_dates),
        storage_path=str(_raw_path(upload_id)),
    )


@router.post("/files/{file_id}/reload", response_model=ImportResult)
def reload_file(file_id: int, db: Session = Depends(get_db)):
    """Nạp lại file pending_fix sau khi đã gán thẻ cho NV (W3)."""
    file_row = db.get(AttendanceFile, file_id)
    if file_row is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy đợt nạp")
    if file_row.status != FILE_PENDING_FIX:
        raise HTTPException(
            status_code=409, detail="File không ở trạng thái chờ xử lý"
        )
    raw = Path(file_row.storage_path)
    if not raw.exists():
        raise HTTPException(status_code=410, detail="File gốc không còn trên server")
    parsed = parse_mcc_txt(raw.read_bytes(), file_row.filename)
    confirmed = {date.fromisoformat(d) for d in file_row.confirmed_dates}
    return _do_import(
        db, parsed, confirmed,
        storage_path=file_row.storage_path, file_row=file_row,
    )


@router.get("/files", response_model=PaginatedFiles)
def list_files(
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    q: str = Query(""),
    status_: str | None = Query(None, alias="status"),
    db: Session = Depends(get_db),
):
    stmt = select(AttendanceFile)
    count_stmt = select(func.count()).select_from(AttendanceFile)
    if q:
        like = f"%{q}%"
        stmt = stmt.where(AttendanceFile.filename.ilike(like))
        count_stmt = count_stmt.where(AttendanceFile.filename.ilike(like))
    if status_:
        stmt = stmt.where(AttendanceFile.status == status_)
        count_stmt = count_stmt.where(AttendanceFile.status == status_)
    total = db.scalar(count_stmt) or 0
    items = db.scalars(
        stmt.order_by(AttendanceFile.id.desc())
        .offset((page - 1) * page_size)
        .limit(page_size)
    ).all()
    return PaginatedFiles(
        items=[
            AttendanceFileRead(
                id=f.id,
                filename=f.filename,
                work_dates=[date.fromisoformat(d) for d in f.work_dates],
                total_rows=f.total_rows,
                imported_rows=f.imported_rows,
                skipped_rows=f.skipped_rows,
                deduped_count=f.deduped_count,
                unmatched_count=len(f.unmatched_cards or []),
                status=f.status,
                imported_at=f.imported_at,
            )
            for f in items
        ],
        total=total,
        page=page,
        page_size=page_size,
    )


@router.get("/unmatched", response_model=list[UnmatchedFileCards])
def list_unmatched(db: Session = Depends(get_db)):
    """Thẻ chưa khớp NV của các file pending_fix (W3) — để gán rồi nạp lại."""
    files = db.scalars(
        select(AttendanceFile).where(AttendanceFile.status == FILE_PENDING_FIX)
    ).all()
    return [
        UnmatchedFileCards(
            file_id=f.id,
            filename=f.filename,
            cards=[UnmatchedCardRead(**u) for u in (f.unmatched_cards or [])],
        )
        for f in files
    ]


@router.post("/unmatched/assign", status_code=status.HTTP_200_OK)
def assign_card(payload: AssignCardRequest, db: Session = Depends(get_db)):
    """Gán mã thẻ cho nhân viên (xử lý W3). Xong thì nạp lại file."""
    emp = db.get(Employee, payload.employee_id)
    if emp is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy nhân viên")
    other = db.scalar(
        select(Employee).where(
            Employee.card_id == payload.card_id,
            Employee.id != payload.employee_id,
        )
    )
    if other is not None:
        raise HTTPException(
            status_code=409,
            detail=f"Thẻ đã gán cho NV {other.employee_code}",
        )
    emp.card_id = payload.card_id
    db.commit()
    return {"ok": True}


@router.get("/taps", response_model=PaginatedTaps)
def list_taps(
    page: int = Query(1, ge=1),
    page_size: int = Query(50, ge=1, le=200),
    tap_date: date | None = Query(None),
    card_id: int | None = Query(None),
    file_id: int | None = Query(None),
    flag: str | None = Query(None, description="single_tap|excess_taps|odd_hours"),
    db: Session = Depends(get_db),
):
    stmt = (
        select(
            AttendanceTap,
            Employee.employee_code,
            Employee.first_name,
            Employee.last_name,
        )
        .join(Employee, Employee.id == AttendanceTap.employee_id)
        .order_by(AttendanceTap.tap_datetime.desc())
    )
    count_stmt = select(func.count()).select_from(AttendanceTap)
    if tap_date:
        stmt = stmt.where(AttendanceTap.tap_date == tap_date)
        count_stmt = count_stmt.where(AttendanceTap.tap_date == tap_date)
    if card_id:
        stmt = stmt.where(AttendanceTap.card_id == card_id)
        count_stmt = count_stmt.where(AttendanceTap.card_id == card_id)
    if file_id:
        stmt = stmt.where(AttendanceTap.file_id == file_id)
        count_stmt = count_stmt.where(AttendanceTap.file_id == file_id)
    if flag:
        # JSON_CONTAINS trên MySQL; LIKE dự phòng cho SQLite (dev/test)
        like = f'%"flag"%'.replace("flag", flag)
        stmt = stmt.where(AttendanceTap.flags.ilike(like))
        count_stmt = count_stmt.where(AttendanceTap.flags.ilike(like))
    total = db.scalar(count_stmt) or 0
    rows = db.execute(
        stmt.offset((page - 1) * page_size).limit(page_size)
    ).all()
    return PaginatedTaps(
        items=[
            TapRead(
                id=t.id,
                card_id=t.card_id,
                employee_id=t.employee_id,
                employee_code=code,
                employee_name=f"{first} {last}".strip(),
                tap_date=t.tap_date,
                tap_time=t.tap_time.strftime("%H:%M:%S"),
                flags=t.flags or [],
                file_id=t.file_id,
            )
            for t, code, first, last in rows
        ],
        total=total,
        page=page,
        page_size=page_size,
    )
