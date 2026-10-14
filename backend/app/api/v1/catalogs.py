from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import select
from sqlalchemy.orm import Session

from app.api.deps import get_db
from app.models.employee import Department, JobTitle, Position
from app.schemas.employee import (
    DepartmentCreate,
    DepartmentRead,
    JobTitleCreate,
    JobTitleRead,
    PositionCreate,
    PositionRead,
)

router = APIRouter(tags=["catalogs"])


@router.get("/departments", response_model=list[DepartmentRead])
def list_departments(db: Session = Depends(get_db)):
    return db.scalars(select(Department).order_by(Department.name)).all()


@router.post("/departments", response_model=DepartmentRead, status_code=status.HTTP_201_CREATED)
def create_department(payload: DepartmentCreate, db: Session = Depends(get_db)):
    if db.scalar(select(Department).where(Department.name == payload.name)):
        raise HTTPException(status_code=409, detail="Đơn vị đã tồn tại")
    dep = Department(**payload.model_dump())
    db.add(dep)
    db.commit()
    db.refresh(dep)
    return dep


@router.get("/positions", response_model=list[PositionRead])
def list_positions(db: Session = Depends(get_db)):
    return db.scalars(select(Position).order_by(Position.name)).all()


@router.post("/positions", response_model=PositionRead, status_code=status.HTTP_201_CREATED)
def create_position(payload: PositionCreate, db: Session = Depends(get_db)):
    if db.scalar(select(Position).where(Position.name == payload.name)):
        raise HTTPException(status_code=409, detail="Chức vụ đã tồn tại")
    pos = Position(**payload.model_dump())
    db.add(pos)
    db.commit()
    db.refresh(pos)
    return pos


@router.get("/job-titles", response_model=list[JobTitleRead])
def list_job_titles(db: Session = Depends(get_db)):
    return db.scalars(select(JobTitle).order_by(JobTitle.name)).all()


@router.post("/job-titles", response_model=JobTitleRead, status_code=status.HTTP_201_CREATED)
def create_job_title(payload: JobTitleCreate, db: Session = Depends(get_db)):
    if db.scalar(select(JobTitle).where(JobTitle.name == payload.name)):
        raise HTTPException(status_code=409, detail="Công việc đã tồn tại")
    job = JobTitle(**payload.model_dump())
    db.add(job)
    db.commit()
    db.refresh(job)
    return job
