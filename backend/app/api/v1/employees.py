"""CRUD nhân viên — thiết kế cho quy mô ~10.000 nhân viên.

- List dùng phân trang limit/offset + tìm kiếm theo mã/tên + lọc theo
  đơn vị/trạng thái, tránh trả toàn bộ danh sách một lúc.
- Xóa là "nghỉ việc" (is_active=False + terminated_date), không xóa cứng
  để giữ lịch sử chấm công/lương.
"""
from datetime import date

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy import func, or_, select
from sqlalchemy.orm import Session, selectinload

from app.api.deps import get_db
from app.models.employee import Employee
from app.schemas.employee import (
    EmployeeCreate,
    EmployeeRead,
    EmployeeUpdate,
    PaginatedEmployees,
)

router = APIRouter(prefix="/employees", tags=["employees"])


def _get_or_404(db: Session, employee_id: int) -> Employee:
    emp = db.get(Employee, employee_id)
    if emp is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy nhân viên")
    return emp


@router.get("", response_model=PaginatedEmployees)
def list_employees(
    db: Session = Depends(get_db),
    page: int = Query(1, ge=1),
    page_size: int = Query(50, ge=1, le=500),
    q: str | None = Query(None, description="Tìm theo mã NV hoặc họ tên"),
    department_id: int | None = None,
    is_active: bool | None = None,
):
    stmt = select(Employee).options(
        selectinload(Employee.department),
        selectinload(Employee.position),
        selectinload(Employee.job_title),
    )
    if q:
        like = f"%{q}%"
        stmt = stmt.where(
            or_(
                Employee.employee_code.like(like),
                Employee.first_name.like(like),
                Employee.last_name.like(like),
            )
        )
    if department_id is not None:
        stmt = stmt.where(Employee.department_id == department_id)
    if is_active is not None:
        stmt = stmt.where(Employee.is_active == is_active)

    total = db.scalar(select(func.count()).select_from(stmt.subquery()))
    items = db.scalars(
        stmt.order_by(Employee.employee_code).offset((page - 1) * page_size).limit(page_size)
    ).all()
    return PaginatedEmployees(items=items, total=total, page=page, page_size=page_size)


@router.get("/{employee_id}", response_model=EmployeeRead)
def get_employee(employee_id: int, db: Session = Depends(get_db)):
    return _get_or_404(db, employee_id)


@router.post("", response_model=EmployeeRead, status_code=status.HTTP_201_CREATED)
def create_employee(payload: EmployeeCreate, db: Session = Depends(get_db)):
    exists = db.scalar(
        select(Employee).where(Employee.employee_code == payload.employee_code)
    )
    if exists:
        raise HTTPException(status_code=409, detail="Mã nhân viên đã tồn tại")
    emp = Employee(**payload.model_dump())
    db.add(emp)
    db.commit()
    db.refresh(emp)
    return emp


@router.patch("/{employee_id}", response_model=EmployeeRead)
def update_employee(employee_id: int, payload: EmployeeUpdate, db: Session = Depends(get_db)):
    emp = _get_or_404(db, employee_id)
    for field, value in payload.model_dump(exclude_unset=True).items():
        setattr(emp, field, value)
    db.commit()
    db.refresh(emp)
    return emp


@router.delete("/{employee_id}", response_model=EmployeeRead)
def deactivate_employee(
    employee_id: int,
    terminated_date: date | None = None,
    db: Session = Depends(get_db),
):
    """Cho nghỉ việc (xóa mềm)."""
    emp = _get_or_404(db, employee_id)
    emp.is_active = False
    emp.terminated_date = terminated_date or date.today()
    db.commit()
    db.refresh(emp)
    return emp
