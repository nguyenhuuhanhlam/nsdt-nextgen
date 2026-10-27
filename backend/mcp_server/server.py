"""MCP server cho NSDT NextGen — giai đoạn 1: CHỈ ĐỌC.

Chạy:
    # stdio (mặc định — cho Claude Desktop / client local)
    cd backend && python -m mcp_server.server
    # HTTP nội bộ (Streamable HTTP)
    cd backend && python -m mcp_server.server --http --port 8001

Biến môi trường: DATABASE_URL (giống backend). Mặc định dùng sqlite dev.
"""
import argparse
import json
import logging
import re
import sys
from concurrent.futures import ThreadPoolExecutor, TimeoutError as FuturesTimeout
from datetime import date, datetime
from typing import Optional

from fastmcp import FastMCP
from sqlalchemy import func, select, text

import app.models  # noqa: F401 — đăng ký models với Base
from app.db.session import SessionLocal
from app.models.attendance import AttendanceFile, AttendanceTap
from app.models.employee import (
    Department,
    Employee,
    MaternityLeave,
    MaternityType,
    Position,
)

# Log ra stderr — stdout là kênh protocol của stdio, không được ghi gì ra đó.
logging.basicConfig(stream=sys.stderr, level=logging.INFO)
log = logging.getLogger("nsdt-mcp")

mcp = FastMCP("nsdt")

# ---------------------------------------------------------------------------
# Tiện ích
# ---------------------------------------------------------------------------

def _parse_date(s: str, name: str) -> date:
    try:
        return date.fromisoformat(s)
    except (TypeError, ValueError):
        raise ValueError(f"{name} phải đúng dạng yyyy-mm-dd (vd 2026-06-25), nhận được: {s!r}")


def _emp_dict(db, e: Employee) -> dict:
    dept = db.get(Department, e.department_id) if e.department_id else None
    pos = db.get(Position, e.position_id) if e.position_id else None
    return {
        "ma_nhanvien": e.employee_code,
        "ho_ten": f"{e.first_name} {e.last_name}".strip(),
        "gioi_tinh": e.gender,
        "don_vi": dept.name if dept else None,
        "chuc_vu": pos.name if pos else None,
        "ma_the": e.card_id,
        "ngay_vao_lam": e.join_date.isoformat() if e.join_date else None,
        "dang_lam": e.is_active,
        "ngay_nghi_viec": e.terminated_date.isoformat() if e.terminated_date else None,
    }


def _resolve_employee(db, ma_nhanvien: Optional[str], ma_the: Optional[int]) -> Employee:
    if ma_nhanvien:
        e = db.scalar(select(Employee).where(Employee.employee_code == ma_nhanvien))
        if not e:
            raise ValueError(f"Không tìm thấy nhân viên mã {ma_nhanvien}")
        return e
    if ma_the is not None:
        e = db.scalar(select(Employee).where(Employee.card_id == ma_the))
        if not e:
            raise ValueError(f"Không tìm thấy nhân viên có mã thẻ {ma_the}")
        return e
    raise ValueError("Cần cho ma_nhanvien hoặc ma_the")


# ---------------------------------------------------------------------------
# Tools — nhân viên
# ---------------------------------------------------------------------------

@mcp.tool
def tim_nhan_vien(
    q: Optional[str] = None,
    ma_nhanvien: Optional[str] = None,
    ma_the: Optional[int] = None,
    don_vi_id: Optional[int] = None,
    chi_dang_lam: bool = True,
    gioi_han: int = 20,
) -> dict:
    """Tìm nhân viên theo tên, mã nhân viên, mã thẻ hoặc đơn vị.

    Trả về danh sách nhân viên khớp (tối đa gioi_han): mã NV, họ tên, đơn vị,
    chức vụ, mã thẻ, ngày vào làm, trạng thái đang làm/đã nghỉ.
    """
    log.info("tim_nhan_vien q=%r ma=%r the=%r dv=%r", q, ma_nhanvien, ma_the, don_vi_id)
    db = SessionLocal()
    try:
        stmt = select(Employee)
        if ma_nhanvien:
            stmt = stmt.where(Employee.employee_code == ma_nhanvien)
        if ma_the is not None:
            stmt = stmt.where(Employee.card_id == ma_the)
        if don_vi_id is not None:
            stmt = stmt.where(Employee.department_id == don_vi_id)
        if q:
            like = f"%{q.strip()}%"
            stmt = stmt.where(
                (Employee.first_name.like(like))
                | (Employee.last_name.like(like))
                | (Employee.employee_code.like(like))
            )
        if chi_dang_lam:
            stmt = stmt.where(Employee.is_active.is_(True))
        stmt = stmt.order_by(Employee.employee_code).limit(max(1, min(gioi_han, 100)))
        rows = list(db.scalars(stmt))
        return {"so_luong": len(rows), "nhan_vien": [_emp_dict(db, e) for e in rows]}
    finally:
        db.close()


# ---------------------------------------------------------------------------
# Tools — chấm công
# ---------------------------------------------------------------------------

@mcp.tool
def xem_cham_cong(
    tu_ngay: str,
    den_ngay: str,
    ma_nhanvien: Optional[str] = None,
    ma_the: Optional[int] = None,
    chi_co_gan_co: bool = False,
) -> dict:
    """Xem lượt quẹt thẻ của một nhân viên trong khoảng ngày.

    Cần ma_nhanvien hoặc ma_the. Trả về từng ngày: danh sách giờ quẹt
    (HH:MM:SS) và cờ cảnh báo của ngày đó (single_tap: chỉ 1 lượt/ngày,
    excess_taps: quá 6 lượt/ngày, outside_hours: quẹt ngoài 05:00–21:00).
    """
    log.info("xem_cham_cong %s..%s ma=%r the=%r", tu_ngay, den_ngay, ma_nhanvien, ma_the)
    d1, d2 = _parse_date(tu_ngay, "tu_ngay"), _parse_date(den_ngay, "den_ngay")
    if d2 < d1:
        raise ValueError("den_ngay phải từ tu_ngay trở đi")
    db = SessionLocal()
    try:
        e = _resolve_employee(db, ma_nhanvien, ma_the)
        stmt = (
            select(AttendanceTap)
            .where(AttendanceTap.employee_id == e.id)
            .where(AttendanceTap.tap_date >= d1)
            .where(AttendanceTap.tap_date <= d2)
            .order_by(AttendanceTap.tap_datetime)
        )
        if chi_co_gan_co:
            stmt = stmt.where(AttendanceTap.flags.is_not(None))
        taps = list(db.scalars(stmt))
        by_day: dict[str, dict] = {}
        for t in taps:
            day = t.tap_date.isoformat()
            d = by_day.setdefault(day, {"ngay": day, "gio_quet": [], "co": set()})
            d["gio_quet"].append(t.tap_time.strftime("%H:%M:%S"))
            for f in t.flags or []:
                d["co"].add(f)
        days = [{**d, "co": sorted(d["co"])} for d in by_day.values()]
        return {
            "nhan_vien": _emp_dict(db, e),
            "tu_ngay": tu_ngay,
            "den_ngay": den_ngay,
            "so_ngay_co_quẹt": len(days),
            "tong_luot_quẹt": len(taps),
            "theo_ngay": days,
        }
    finally:
        db.close()


@mcp.tool
def xem_thai_san(
    ma_nhanvien: Optional[str] = None,
    tu_ngay: Optional[str] = None,
    den_ngay: Optional[str] = None,
) -> dict:
    """Xem hồ sơ thai sản của một nhân viên, hoặc toàn bộ hồ sơ trong khoảng ngày.

    Trả về loại thai sản, ngày bắt đầu/kết thúc, số ngày nghỉ.
    """
    log.info("xem_thai_san ma=%r %s..%s", ma_nhanvien, tu_ngay, den_ngay)
    db = SessionLocal()
    try:
        stmt = select(MaternityLeave).join(Employee, MaternityLeave.employee_id == Employee.id)
        emp = None
        if ma_nhanvien:
            emp = db.scalar(select(Employee).where(Employee.employee_code == ma_nhanvien))
            if not emp:
                raise ValueError(f"Không tìm thấy nhân viên mã {ma_nhanvien}")
            stmt = stmt.where(MaternityLeave.employee_id == emp.id)
        if tu_ngay:
            stmt = stmt.where(MaternityLeave.end_date >= _parse_date(tu_ngay, "tu_ngay"))
        if den_ngay:
            stmt = stmt.where(MaternityLeave.start_date <= _parse_date(den_ngay, "den_ngay"))
        stmt = stmt.order_by(MaternityLeave.start_date.desc()).limit(200)
        out = []
        for lv in db.scalars(stmt):
            t = db.get(MaternityType, lv.maternity_type_id)
            e = db.get(Employee, lv.employee_id)
            out.append({
                "ma_nhanvien": e.employee_code if e else None,
                "ho_ten": f"{e.first_name} {e.last_name}".strip() if e else None,
                "loai_thai_san": t.name if t else None,
                "ngay_bat_dau": lv.start_date.isoformat(),
                "ngay_ket_thuc": lv.end_date.isoformat(),
                "so_ngay_nghi": lv.leave_days,
            })
        return {"so_luong": len(out), "ho_so": out}
    finally:
        db.close()


@mcp.tool
def thong_ke_cham_cong(tu_ngay: str, den_ngay: str) -> dict:
    """Thống kê chấm công trong khoảng ngày: số file đã nạp, số lượt quẹt,
    số file còn thẻ chưa khớp, số lượt quẹt bị gắn cờ theo từng loại cờ."""
    log.info("thong_ke_cham_cong %s..%s", tu_ngay, den_ngay)
    d1, d2 = _parse_date(tu_ngay, "tu_ngay"), _parse_date(den_ngay, "den_ngay")
    if d2 < d1:
        raise ValueError("den_ngay phải từ tu_ngay trở đi")
    db = SessionLocal()
    try:
        files = list(
            db.scalars(
                select(AttendanceFile)
                .where(AttendanceFile.imported_at >= datetime(d1.year, d1.month, d1.day))
                .where(AttendanceFile.imported_at < datetime(d2.year, d2.month, d2.day, 23, 59, 59))
            )
        )
        pending = [f for f in files if f.status == "pending_fix"]
        tap_count = db.scalar(
            select(func.count())
            .select_from(AttendanceTap)
            .where(AttendanceTap.tap_date >= d1)
            .where(AttendanceTap.tap_date <= d2)
        )
        co_counts = {}
        for flag in ("single_tap", "excess_taps", "outside_hours"):
            co_counts[flag] = db.scalar(
                select(func.count())
                .select_from(AttendanceTap)
                .where(AttendanceTap.tap_date >= d1)
                .where(AttendanceTap.tap_date <= d2)
                .where(AttendanceTap.flags.like(f'%"{flag}"%'))
            )
        return {
            "tu_ngay": tu_ngay,
            "den_ngay": den_ngay,
            "so_file_da_nap": len(files),
            "so_file_con_the_chua_khop": len(pending),
            "file_con_the_chua_khop": [
                {"ten_file": f.filename, "so_the_la": len(f.unmatched_cards or [])}
                for f in pending
            ],
            "tong_luot_quet": tap_count,
            "luot_quet_gan_co": co_counts,
        }
    finally:
        db.close()


# ---------------------------------------------------------------------------
# Tool — SQL chỉ đọc, có giới hạn
# ---------------------------------------------------------------------------

SQL_BANG_CHO_PHEP = {
    "employees", "departments", "positions", "job_titles", "provinces",
    "nationalities", "ethnicities", "educations", "marital_statuses",
    "contract_types", "maternity_leaves", "maternity_types",
    "attendance_files", "attendance_taps",
}
SQL_TU_CAM = re.compile(
    r"\b(INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE|GRANT|REVOKE|"
    r"REPLACE|INTO|CALL|EXEC|PRAGMA|ATTACH|DETACH|VACUUM)\b",
    re.IGNORECASE,
)
SQL_BANG_RE = re.compile(r"\b(?:FROM|JOIN)\s+`?([a-z_][a-z0-9_]*)`?", re.IGNORECASE)
SQL_LIMIT_RE = re.compile(r"\blimit\s+\d+", re.IGNORECASE)

SQL_GIOI_HAN_DONG = 500


def _kiem_tra_sql(cau_lenh: str) -> str:
    q = (cau_lenh or "").strip()
    if not q:
        raise ValueError("Câu lệnh trống")
    if q.endswith(";"):
        q = q[:-1].strip()
    if ";" in q:
        raise ValueError("Chỉ cho một câu lệnh duy nhất (không dùng dấu ; nối câu)")
    if not re.match(r"^(SELECT|WITH)\b", q, re.IGNORECASE):
        raise ValueError("Chỉ cho câu SELECT (hoặc WITH ... SELECT)")
    # Bỏ chuỗi trong ngoặc đơn trước khi kiểm tra từ khóa cấm
    q_khong_chuoi = re.sub(r"'([^']|'')*'", "''", q)
    m = SQL_TU_CAM.search(q_khong_chuoi)
    if m:
        raise ValueError(f"Từ khóa không được phép: {m.group(1)}")
    bangs = {b.lower() for b in SQL_BANG_RE.findall(q)}
    la = bangs - SQL_BANG_CHO_PHEP
    if la:
        raise ValueError(f"Bảng không được phép đọc: {', '.join(sorted(la))}")
    if not SQL_LIMIT_RE.search(q):
        q = f"{q} LIMIT {SQL_GIOI_HAN_DONG}"
    return q


def _chay_sql(q: str):
    db = SessionLocal()
    try:
        rows = db.execute(text(q)).mappings().all()
        return [dict(r) for r in rows]
    finally:
        db.close()


@mcp.tool
def truy_van_sql(cau_lenh: str) -> dict:
    """Chạy một câu SQL SELECT tự do trên dữ liệu NSDT (chỉ đọc).

    Giới hạn an toàn: chỉ SELECT, một câu duy nhất, chỉ các bảng nghiệp vụ
    (nhân viên, danh mục, thai sản, chấm công), tối đa 500 dòng nếu câu
    không có LIMIT. Ngày trong DB dùng ISO yyyy-mm-dd.
    """
    log.info("truy_van_sql: %s", (cau_lenh or "")[:200])
    q = _kiem_tra_sql(cau_lenh)
    try:
        with ThreadPoolExecutor(max_workers=1) as ex:
            rows = ex.submit(_chay_sql, q).result(timeout=10)
    except FuturesTimeout:
        raise ValueError("Câu lệnh chạy quá 10 giây — thu hẹp lại rồi thử lại")
    return {"so_dong": len(rows), "du_lieu": rows}


# ---------------------------------------------------------------------------
# Resources
# ---------------------------------------------------------------------------

SCHEMA_TEXT = """Từ điển dữ liệu NSDT NextGen (ngày trong DB dùng ISO yyyy-mm-dd; hiển thị dd-mm-yyyy).

- employees (nhân viên): employee_code (mã NV, vd NV000123), card_id (mã thẻ chấm công),
  first_name (họ), last_name (tên), gender, birth_date, department_id, position_id,
  job_title_id, join_date, probation_end_date, salary_coefficient (hệ số lương),
  is_active (đang làm), terminated_date (ngày nghỉ việc).
- departments (đơn vị), positions (chức vụ), job_titles (công việc),
  provinces (tỉnh thành), nationalities, ethnicities, educations,
  marital_statuses, contract_types — đều có id + name.
- maternity_leaves (hồ sơ thai sản): employee_id, maternity_type_id, start_date,
  end_date, leave_days. maternity_types: id + name (loại thai sản).
- attendance_files (đợt nạp file): filename, file_hash, work_dates, total_rows,
  imported_rows, skipped_rows, deduped_count, unmatched_cards, status
  (imported | pending_fix — pending_fix nghĩa là còn thẻ chưa khớp nhân viên),
  imported_at.
- attendance_taps (lượt quẹt thẻ): card_id, employee_id, tap_date, tap_time,
  tap_datetime, flags (JSON: single_tap = chỉ 1 lượt/ngày, excess_taps = quá 6
  lượt/ngày, outside_hours = quẹt ngoài 05:00–21:00), file_id.
"""

QUY_DINH_TEXT = """Bộ quy định giờ công đang áp dụng (giá trị mặc định hiện tại — bảng
work_rule_sets chưa có nên chưa chỉnh được qua hệ thống; xem docs/work-rules.md):
- Giờ bắt đầu tính công 07:15; mốc giờ quy định toàn công ty 16:30.
- Khoảng nghỉ không tính tiền: 11:15–12:15 (1 giờ).
- Tăng ca tách ở 1.5 giờ (trước/sau). Đơn vị văn phòng không tính tăng ca.
- Ngày công theo giờ hành chính: <=2h → 0.25, <=4h → 0.5, <=6h → 0.75, >6h → 1.
- Thai sản: trần giờ hành chính 7h (loại 1,2,3) / 8h (thường); loại 2 → tăng ca 0;
  giờ hành chính > 6h → +1 giờ bù.
- Giờ đóng chuyền thay đổi theo từng ngày + đơn vị (bảng work_calendar, sắp có).
"""


@mcp.resource("nsdt://schema")
def resource_schema() -> str:
    """Từ điển dữ liệu NSDT: tên bảng, ý nghĩa các cột, quy ước ngày tháng."""
    return SCHEMA_TEXT


@mcp.resource("nsdt://quy-dinh-hien-hanh")
def resource_quy_dinh() -> str:
    """Bộ quy định giờ công đang áp dụng (giờ chuẩn, giờ nghỉ, ngưỡng tính công...)."""
    return QUY_DINH_TEXT


# ---------------------------------------------------------------------------
# Chạy server
# ---------------------------------------------------------------------------

def main() -> None:
    ap = argparse.ArgumentParser(description="MCP server NSDT NextGen (chỉ đọc)")
    ap.add_argument("--http", action="store_true", help="Chạy Streamable HTTP thay vì stdio")
    ap.add_argument("--host", default="127.0.0.1")
    ap.add_argument("--port", type=int, default=8001)
    args = ap.parse_args()
    if args.http:
        log.info("MCP HTTP tại http://%s:%d/mcp", args.host, args.port)
        mcp.run(transport="http", host=args.host, port=args.port)
    else:
        log.info("MCP stdio — NSDT read-only")
        mcp.run()


if __name__ == "__main__":
    main()
