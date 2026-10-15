"""CRUD hồ sơ nghỉ thai sản của nhân viên (từ `ns_xuly_thaisan` của hệ cũ).

- List phân trang + lọc theo nhân viên / loại thai sản.
- leave_days tự tính từ khoảng ngày (bao gồm cả 2 đầu) khi không truyền vào;
  vẫn cho phép ghi đè thủ công theo chính sách nghỉ của công ty.
"""
from datetime import date

from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy import func, select
from sqlalchemy.orm import Session, selectinload

from app.api.deps import get_db
from app.models.employee import Employee, MaternityLeave, MaternityType
from app.schemas.employee import (
    MaternityLeaveCreate,
    MaternityLeaveRead,
    MaternityLeaveUpdate,
    PaginatedMaternityLeaves,
)

router = APIRouter(prefix="/maternity-leaves", tags=["maternity"])


def _leave_days(start: date, end: date) -> int:
    if end < start:
        raise HTTPException(
            status_code=400, detail="Ngày kết thúc phải từ ngày bắt đầu trở đi"
        )
    return (end - start).days + 1


def _get_or_404(db: Session, leave_id: int) -> MaternityLeave:
    leave = db.get(MaternityLeave, leave_id)
    if leave is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy hồ sơ thai sản")
    return leave


@router.get("", response_model=PaginatedMaternityLeaves)
def list_leaves(
    db: Session = Depends(get_db),
    page: int = Query(1, ge=1),
    page_size: int = Query(50, ge=1, le=500),
    employee_id: int | None = None,
    maternity_type_id: int | None = None,
):
    stmt = select(MaternityLeave).options(
        selectinload(MaternityLeave.employee),
        selectinload(MaternityLeave.maternity_type),
    )
    if employee_id is not None:
        stmt = stmt.where(MaternityLeave.employee_id == employee_id)
    if maternity_type_id is not None:
        stmt = stmt.where(MaternityLeave.maternity_type_id == maternity_type_id)

    total = db.scalar(select(func.count()).select_from(stmt.subquery()))
    items = db.scalars(
        stmt.order_by(MaternityLeave.start_date.desc())
        .offset((page - 1) * page_size)
        .limit(page_size)
    ).all()
    return PaginatedMaternityLeaves(items=items, total=total, page=page, page_size=page_size)


@router.get("/{leave_id}", response_model=MaternityLeaveRead)
def get_leave(leave_id: int, db: Session = Depends(get_db)):
    return _get_or_404(db, leave_id)


@router.post("", response_model=MaternityLeaveRead, status_code=status.HTTP_201_CREATED)
def create_leave(payload: MaternityLeaveCreate, db: Session = Depends(get_db)):
    if db.get(Employee, payload.employee_id) is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy nhân viên")
    if db.get(MaternityType, payload.maternity_type_id) is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy loại thai sản")
    days = payload.leave_days
    if days is None:
        days = _leave_days(payload.start_date, payload.end_date)
    else:
        _leave_days(payload.start_date, payload.end_date)  # chỉ kiểm tra thứ tự ngày
    leave = MaternityLeave(**{**payload.model_dump(), "leave_days": days})
    db.add(leave)
    db.commit()
    db.refresh(leave)
    return leave


@router.patch("/{leave_id}", response_model=MaternityLeaveRead)
def update_leave(
    leave_id: int, payload: MaternityLeaveUpdate, db: Session = Depends(get_db)
):
    leave = _get_or_404(db, leave_id)
    data = payload.model_dump(exclude_unset=True)
    if data.get("maternity_type_id") is not None and db.get(
        MaternityType, data["maternity_type_id"]
    ) is None:
        raise HTTPException(status_code=404, detail="Không tìm thấy loại thai sản")

    start = data.get("start_date", leave.start_date)
    end = data.get("end_date", leave.end_date)
    # Đổi ngày mà không đưa leave_days → tính lại cho khớp.
    if ("start_date" in data or "end_date" in data) and "leave_days" not in data:
        data["leave_days"] = _leave_days(start, end)
    elif "start_date" in data or "end_date" in data:
        _leave_days(start, end)

    for field, value in data.items():
        setattr(leave, field, value)
    db.commit()
    db.refresh(leave)
    return leave


@router.delete("/{leave_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_leave(leave_id: int, db: Session = Depends(get_db)):
    leave = _get_or_404(db, leave_id)
    db.delete(leave)
    db.commit()
