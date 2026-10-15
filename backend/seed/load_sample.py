"""Nạp dữ liệu mẫu (đã ẩn danh hóa) vào database phát triển.

Chạy từ thư mục backend:
    python -m seed.load_sample

DATABASE_URL lấy từ biến môi trường hoặc file .env (xem app/core/config.py).
Idempotent: chạy lại nhiều lần vẫn an toàn — bản ghi đã tồn tại sẽ được bỏ qua.
"""
import json
from datetime import date
from pathlib import Path

from sqlalchemy import select

import app.models  # noqa: F401 — đăng ký models với Base
from app.db.base import Base
from app.db.session import SessionLocal, engine
from app.models.employee import (
    Department,
    Employee,
    JobTitle,
    MaternityLeave,
    MaternityType,
    Position,
    Province,
)

DATA = json.loads(Path(__file__).with_name("sample_data.json").read_text(encoding="utf-8"))


def _parse_date(s):
    if not s:
        return None
    try:
        return date.fromisoformat(s)
    except ValueError:
        return None


def _load_catalog(db, model, items):
    """Nạp danh mục, trả về map old_id -> new id. Đã tồn tại (theo tên) thì bỏ qua."""
    mapping = {}
    for it in items:
        row = db.scalar(select(model).where(model.name == it["name"]))
        if row is None:
            row = model(name=it["name"])
            db.add(row)
            db.flush()
        mapping[it["old_id"]] = row.id
    return mapping


def main():
    Base.metadata.create_all(engine)
    db = SessionLocal()
    try:
        dep = _load_catalog(db, Department, DATA["departments"])
        pos = _load_catalog(db, Position, DATA["positions"])
        job = _load_catalog(db, JobTitle, DATA["job_titles"])
        _load_catalog(db, Province, DATA["provinces"])
        mtype = _load_catalog(db, MaternityType, DATA["maternity_types"])

        n_emp = 0
        code_to_id = {}
        for e in DATA["employees"]:
            row = db.scalar(
                select(Employee).where(Employee.employee_code == e["employee_code"])
            )
            if row is None:
                row = Employee(
                    employee_code=e["employee_code"],
                    card_id=e["card_id"],
                    first_name=e["first_name"],
                    last_name=e["last_name"],
                    gender=e["gender"],
                    birth_date=_parse_date(e["birth_date"]),
                    department_id=dep.get(e["department_old_id"]),
                    position_id=pos.get(e["position_old_id"]),
                    job_title_id=job.get(e["job_title_old_id"]),
                    join_date=_parse_date(e["join_date"]),
                    probation_end_date=_parse_date(e["probation_end_date"]),
                    salary_coefficient=e["salary_coefficient"],
                    is_active=e["is_active"],
                    terminated_date=_parse_date(e["terminated_date"]),
                )
                db.add(row)
                db.flush()
                n_emp += 1
            code_to_id[e["employee_code"]] = row.id

        n_leave = 0
        for m in DATA["maternity_leaves"]:
            emp_id = code_to_id.get(m["employee_code"])
            type_id = mtype.get(m["maternity_type_old_id"])
            if emp_id is None or type_id is None:
                continue
            exists = db.scalar(
                select(MaternityLeave).where(
                    MaternityLeave.employee_id == emp_id,
                    MaternityLeave.maternity_type_id == type_id,
                    MaternityLeave.start_date == _parse_date(m["start_date"]),
                )
            )
            if exists:
                continue
            db.add(
                MaternityLeave(
                    employee_id=emp_id,
                    maternity_type_id=type_id,
                    start_date=_parse_date(m["start_date"]),
                    end_date=_parse_date(m["end_date"]),
                    leave_days=m["leave_days"] or 0,
                    notes=m.get("notes") or None,
                )
            )
            n_leave += 1

        db.commit()
        print(f"seed xong: +{n_emp} nhân viên, +{n_leave} hồ sơ thai sản (đã tồn tại thì bỏ qua)")
    finally:
        db.close()


if __name__ == "__main__":
    main()
