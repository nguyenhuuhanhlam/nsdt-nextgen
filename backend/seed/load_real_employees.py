"""Đổ toàn bộ nhân viên THẬT từ database hệ cũ (MySQL 5.1) vào database mới.

- Server cũ: CHỈ ĐỌC (SELECT). Không ghi gì lên server cũ.
- Dữ liệu thật (họ tên, CMND, địa chỉ...) chỉ chạy trên máy của user —
  KHÔNG commit dữ liệu vào repo (repo public), script này chỉ chứa code.
- Ghi vào DB mới theo kiểu upsert theo mã NV: đã có thì CẬP NHẬT TẠI CHỖ
  (giữ nguyên id nên các lượt quẹt thai sản đã nạp không bị đứt liên kết),
  chưa có thì thêm mới. Không xóa nhân viên nào. Chạy lại nhiều lần an toàn.

Chạy trên máy Windows (nơi đặt MySQL mới), từ thư mục backend:
    .\\venv\\Scripts\\python -m seed.load_real_employees --dry-run
    .\\venv\\Scripts\\python -m seed.load_real_employees

Biến môi trường:
    NSDT_OLD_HOST, NSDT_OLD_PORT, NSDT_OLD_USER, NSDT_OLD_DB,
    NSDT_OLD_PASS (không đặt thì script hỏi nhập, không hiện trên màn hình)
    DATABASE_URL (DB mới — như backend, vd mysql+pymysql://nsdt:...@127.0.0.1:3306/nsdt?charset=utf8mb4)
"""
import argparse
import getpass
import os
import re
import sys

import pymysql
from sqlalchemy import select

import app.models  # noqa: F401 — đăng ký models với Base
from app.db.base import Base
from app.db.session import SessionLocal, engine
from app.models.employee import (
    ContractType,
    Department,
    Education,
    Employee,
    Ethnicity,
    JobTitle,
    MaritalStatus,
    Nationality,
    Position,
    Province,
)

_ZERO = re.compile(r"^\d{4}-00|-\d{2}-00$|0000")


def iso_date(v):
    """date/datetime/str -> date ISO hoặc None (ngày zero kiểu '1985-00-00' -> None)."""
    if v is None:
        return None
    if hasattr(v, "isoformat"):
        return v
    s = str(v)
    if _ZERO.search(s):
        return None
    try:
        return s[:10]
    except Exception:
        return None


def parse_date(s):
    if not s:
        return None
    from datetime import date as _date

    if isinstance(s, _date):
        return s
    try:
        return _date.fromisoformat(str(s)[:10])
    except ValueError:
        return None


CATALOGS = [
    ("departments", Department, "SELECT id_donvi AS old_id, donvi AS name FROM ns_donvi ORDER BY id_donvi"),
    ("positions", Position, "SELECT id_chucvu AS old_id, chucvu AS name FROM ns_chucvu ORDER BY id_chucvu"),
    ("job_titles", JobTitle, "SELECT id_congviec AS old_id, congviec AS name FROM ns_congviec ORDER BY id_congviec"),
    ("provinces", Province, "SELECT id_tinhthanh AS old_id, tinhthanh AS name FROM ns_tinhthanh ORDER BY id_tinhthanh"),
    ("nationalities", Nationality, "SELECT id_quoctich AS old_id, quoctich AS name FROM ns_quoctich ORDER BY id_quoctich"),
    ("ethnicities", Ethnicity, "SELECT id_dantoc AS old_id, dantoc AS name FROM ns_dantoc ORDER BY id_dantoc"),
    ("educations", Education, "SELECT id_hocvan AS old_id, hocvan AS name FROM ns_hocvan ORDER BY id_hocvan"),
    ("marital_statuses", MaritalStatus, "SELECT id_honnhan AS old_id, honnhan AS name FROM ns_honnhan ORDER BY id_honnhan"),
    ("contract_types", ContractType, "SELECT id_hdld AS old_id, loaihopdong AS name FROM ns_hdld ORDER BY id_hdld"),
]

EMP_SQL = """SELECT ma_nhanvien, ma_the, ho, ten, socmnd, ngaysinh, gioitinh,
    id_quoctich, id_dantoc, ngaycapcmnd, noicapcmnd, noisinh, id_hocvan, id_honnhan,
    nguyenquan, diachithuongtru, diachicungu, id_donvi, id_chucvu, id_congviec,
    ngayvaolam, ngayhetthuviec, hesoluong, id_hdld, id_nghidaihan, ngay_nghiviec, btkt
    FROM ns_nhansu ORDER BY ma_nhanvien"""


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true",
                    help="chỉ trích xuất + so sánh, không ghi DB mới")
    args = ap.parse_args()

    old_pass = os.environ.get("NSDT_OLD_PASS") or getpass.getpass("Mat khau MySQL cu (root): ")
    old = pymysql.connect(
        host=os.environ.get("NSDT_OLD_HOST", "113.161.213.22"),
        port=int(os.environ.get("NSDT_OLD_PORT", "43306")),
        user=os.environ.get("NSDT_OLD_USER", "root"),
        password=old_pass,
        database=os.environ.get("NSDT_OLD_DB", "nskl_section_b"),
        charset="utf8", connect_timeout=90, read_timeout=300)
    cur = old.cursor()

    def rows(sql, params=None):
        cur.execute(sql, params or ())
        cols = [d[0] for d in cur.description]
        return [dict(zip(cols, r)) for r in cur.fetchall()]

    catalogs = {key: rows(sql) for key, _, sql in CATALOGS}
    dm_nghidaihan = {r["id_nghidaihan"]: r["nghidaihan"]
                     for r in rows("SELECT id_nghidaihan, nghidaihan FROM dm_nghidaihan")}
    emps = rows(EMP_SQL)
    old.close()
    print(f"Server cu: {len(emps)} nhan vien")
    for key, items in catalogs.items():
        print(f"  {key}: {len(items)} muc")
    from collections import Counter
    null_cards = sum(1 for e in emps if not e["ma_the"])
    dup_cards = sum(1 for c, n in Counter(e["ma_the"] for e in emps if e["ma_the"]).items() if n > 1)
    print(f"  ma the trong: {null_cards}, ma the trung o nhieu NV: {dup_cards}")

    # --- DB mới: map danh mục theo TÊN (như load_sample.py) ---
    Base.metadata.create_all(engine)
    db = SessionLocal()
    try:
        maps = {}
        for key, model, _ in CATALOGS:
            name_to_id = {r.name: r.id for r in db.scalars(select(model)).all()}
            mapping = {}
            for it in catalogs[key]:
                nid = name_to_id.get(it["name"])
                if nid is None:
                    if args.dry_run:
                        print(f"  [dry-run] danh muc {key} thieu: {it['name']!r} (se them moi)")
                        continue
                    row = model(name=it["name"])
                    db.add(row)
                    db.flush()
                    nid = row.id
                    name_to_id[it["name"]] = nid
                mapping[it["old_id"]] = nid
            maps[key] = mapping

        def map_fk(table, old_id):
            if old_id is None:
                return None
            return maps[table].get(old_id)

        existing = {e.employee_code: e for e in db.scalars(select(Employee)).all()}
        n_new = n_upd = n_same = 0
        for e in emps:
            vals = {
                "card_id": e["ma_the"] or None,
                "first_name": (e["ho"] or "").strip(),
                "last_name": (e["ten"] or "").strip(),
                "id_number": str(e["socmnd"]) if e["socmnd"] else None,
                "id_issue_date": parse_date(iso_date(e["ngaycapcmnd"])),
                "id_issue_place_id": map_fk("provinces", e["noicapcmnd"]),
                "birth_date": parse_date(iso_date(e["ngaysinh"])),
                "gender": (e["gioitinh"] or "").strip() or None,
                "birth_place_id": map_fk("provinces", e["noisinh"]),
                "nationality_id": map_fk("nationalities", e["id_quoctich"]),
                "ethnicity_id": map_fk("ethnicities", e["id_dantoc"]),
                "education_id": map_fk("educations", e["id_hocvan"]),
                "marital_status_id": map_fk("marital_statuses", e["id_honnhan"]),
                "hometown": e["nguyenquan"] or None,
                "permanent_address": e["diachithuongtru"] or None,
                "current_address": e["diachicungu"] or None,
                "department_id": map_fk("departments", e["id_donvi"]),
                "position_id": map_fk("positions", e["id_chucvu"]),
                "job_title_id": map_fk("job_titles", e["id_congviec"]),
                "join_date": parse_date(iso_date(e["ngayvaolam"])),
                "probation_end_date": parse_date(iso_date(e["ngayhetthuviec"])),
                "salary_coefficient": float(e["hesoluong"]) if e["hesoluong"] is not None else None,
                "contract_type_id": map_fk("contract_types", e["id_hdld"]),
                "btkt": bool(e["btkt"]),
                "is_active": e["ngay_nghiviec"] is None,
                "terminated_date": parse_date(iso_date(e["ngay_nghiviec"])),
            }
            note_ldh = None
            if e["id_nghidaihan"] is not None:
                note_ldh = f"Nghỉ dài hạn: {dm_nghidaihan.get(e['id_nghidaihan'], e['id_nghidaihan'])}"
            row = existing.get(e["ma_nhanvien"])
            if row is None:
                if args.dry_run:
                    n_new += 1
                    continue
                row = Employee(employee_code=e["ma_nhanvien"], **vals)
                if note_ldh:
                    row.notes = note_ldh
                db.add(row)
                db.flush()
                n_new += 1
            else:
                changed = any(getattr(row, k) != v for k, v in vals.items())
                if args.dry_run:
                    n_upd += changed
                    n_same += (not changed)
                    continue
                for k, v in vals.items():
                    setattr(row, k, v)
                if note_ldh and not (row.notes or ""):
                    row.notes = note_ldh
                n_upd += changed
                n_same += (not changed)
        if args.dry_run:
            print(f"[dry-run] se THEM MOI {n_new} NV, CAP NHAT {n_upd} NV, {n_same} NV khong doi")
            print(f"[dry-run] DB moi hien co {len(existing)} NV (ma khong co trong server cu se giu nguyen)")
        else:
            db.commit()
            print(f"XONG: +{n_new} NV moi, cap nhat {n_upd} NV, {n_same} NV khong doi "
                  f"(tong DB moi: {len(existing) + n_new} NV)")
    finally:
        db.close()


if __name__ == "__main__":
    main()
