from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy import func, select
from sqlalchemy.orm import Session

from app.api.deps import get_db
from app.models.employee import (
    ContractType,
    Department,
    Education,
    Employee,
    Ethnicity,
    JobTitle,
    MaritalStatus,
    MaternityLeave,
    MaternityType,
    Nationality,
    Position,
    Province,
)
from app.schemas.employee import (
    ContractTypeCreate,
    ContractTypeRead,
    DepartmentCreate,
    DepartmentRead,
    EducationCreate,
    EducationRead,
    EthnicityCreate,
    EthnicityRead,
    JobTitleCreate,
    JobTitleRead,
    MaritalStatusCreate,
    MaritalStatusRead,
    MaternityTypeCreate,
    MaternityTypeRead,
    NationalityCreate,
    NationalityRead,
    PositionCreate,
    PositionRead,
    ProvinceCreate,
    ProvinceRead,
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


def _get_or_404(db: Session, model, item_id: int, label: str):
    item = db.get(model, item_id)
    if item is None:
        raise HTTPException(status_code=404, detail=f"{label} không tồn tại")
    return item


def _check_duplicate_name(db: Session, model, name: str, label: str, exclude_id: int | None = None):
    q = select(model).where(model.name == name)
    if exclude_id is not None:
        q = q.where(model.id != exclude_id)
    if db.scalar(q) is not None:
        raise HTTPException(status_code=409, detail=f"{label} đã tồn tại")


def _rename(db: Session, model, item_id: int, name: str, label: str):
    item = _get_or_404(db, model, item_id, label)
    _check_duplicate_name(db, model, name, label, exclude_id=item_id)
    item.name = name
    db.commit()
    db.refresh(item)
    return item


def _delete(db: Session, model, item_id: int, label: str, references: list | None = None):
    """Xóa một mục danh mục.

    references: [(Model tham chiếu, cột FK, mô tả tiếng Việt), ...] —
    nếu còn bản ghi tham chiếu tới thì chặn với 409.
    """
    item = _get_or_404(db, model, item_id, label)
    for ref_model, ref_column, ref_label in references or []:
        in_use = db.scalar(
            select(func.count()).select_from(ref_model).where(ref_column == item_id)
        )
        if in_use:
            raise HTTPException(
                status_code=409,
                detail=f"{label} đang được dùng bởi {in_use} {ref_label}, không thể xóa",
            )
    db.delete(item)
    db.commit()


# --- Đơn vị ---
@router.put("/departments/{item_id}", response_model=DepartmentRead)
def update_department(item_id: int, payload: DepartmentCreate, db: Session = Depends(get_db)):
    return _rename(db, Department, item_id, payload.name, "Đơn vị")


@router.delete("/departments/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_department(item_id: int, db: Session = Depends(get_db)):
    _delete(db, Department, item_id, "Đơn vị", [(Employee, Employee.department_id, "nhân viên")])


# --- Chức vụ ---
@router.put("/positions/{item_id}", response_model=PositionRead)
def update_position(item_id: int, payload: PositionCreate, db: Session = Depends(get_db)):
    return _rename(db, Position, item_id, payload.name, "Chức vụ")


@router.delete("/positions/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_position(item_id: int, db: Session = Depends(get_db)):
    _delete(db, Position, item_id, "Chức vụ", [(Employee, Employee.position_id, "nhân viên")])


# --- Công việc ---
@router.put("/job-titles/{item_id}", response_model=JobTitleRead)
def update_job_title(item_id: int, payload: JobTitleCreate, db: Session = Depends(get_db)):
    return _rename(db, JobTitle, item_id, payload.name, "Công việc")


@router.delete("/job-titles/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_job_title(item_id: int, db: Session = Depends(get_db)):
    _delete(db, JobTitle, item_id, "Công việc", [(Employee, Employee.job_title_id, "nhân viên")])


# --- Tỉnh thành ---
@router.get("/provinces", response_model=list[ProvinceRead])
def list_provinces(db: Session = Depends(get_db)):
    return db.scalars(select(Province).order_by(Province.name)).all()


@router.post("/provinces", response_model=ProvinceRead, status_code=status.HTTP_201_CREATED)
def create_province(payload: ProvinceCreate, db: Session = Depends(get_db)):
    if db.scalar(select(Province).where(Province.name == payload.name)):
        raise HTTPException(status_code=409, detail="Tỉnh thành đã tồn tại")
    prov = Province(**payload.model_dump())
    db.add(prov)
    db.commit()
    db.refresh(prov)
    return prov


@router.put("/provinces/{item_id}", response_model=ProvinceRead)
def update_province(item_id: int, payload: ProvinceCreate, db: Session = Depends(get_db)):
    return _rename(db, Province, item_id, payload.name, "Tỉnh thành")


@router.delete("/provinces/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_province(item_id: int, db: Session = Depends(get_db)):
    _delete(db, Province, item_id, "Tỉnh thành")


# --- Loại thai sản ---
@router.get("/maternity-types", response_model=list[MaternityTypeRead])
def list_maternity_types(db: Session = Depends(get_db)):
    return db.scalars(select(MaternityType).order_by(MaternityType.name)).all()


@router.post("/maternity-types", response_model=MaternityTypeRead, status_code=status.HTTP_201_CREATED)
def create_maternity_type(payload: MaternityTypeCreate, db: Session = Depends(get_db)):
    if db.scalar(select(MaternityType).where(MaternityType.name == payload.name)):
        raise HTTPException(status_code=409, detail="Loại thai sản đã tồn tại")
    mt = MaternityType(**payload.model_dump())
    db.add(mt)
    db.commit()
    db.refresh(mt)
    return mt


@router.put("/maternity-types/{item_id}", response_model=MaternityTypeRead)
def update_maternity_type(item_id: int, payload: MaternityTypeCreate, db: Session = Depends(get_db)):
    return _rename(db, MaternityType, item_id, payload.name, "Loại thai sản")


@router.delete("/maternity-types/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_maternity_type(item_id: int, db: Session = Depends(get_db)):
    _delete(
        db,
        MaternityType,
        item_id,
        "Loại thai sản",
        [(MaternityLeave, MaternityLeave.maternity_type_id, "hồ sơ thai sản")],
    )


# --- Quốc tịch (ns_quoctich) ---
@router.get("/nationalities", response_model=list[NationalityRead])
def list_nationalities(db: Session = Depends(get_db)):
    return db.scalars(select(Nationality).order_by(Nationality.name)).all()


@router.post("/nationalities", response_model=NationalityRead, status_code=status.HTTP_201_CREATED)
def create_nationality(payload: NationalityCreate, db: Session = Depends(get_db)):
    if db.scalar(select(Nationality).where(Nationality.name == payload.name)):
        raise HTTPException(status_code=409, detail="Quốc tịch đã tồn tại")
    item = Nationality(**payload.model_dump())
    db.add(item)
    db.commit()
    db.refresh(item)
    return item


@router.put("/nationalities/{item_id}", response_model=NationalityRead)
def update_nationality(item_id: int, payload: NationalityCreate, db: Session = Depends(get_db)):
    return _rename(db, Nationality, item_id, payload.name, "Quốc tịch")


@router.delete("/nationalities/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_nationality(item_id: int, db: Session = Depends(get_db)):
    _delete(db, Nationality, item_id, "Quốc tịch", [(Employee, Employee.nationality_id, "nhân viên")])


# --- Dân tộc (ns_dantoc) ---
@router.get("/ethnicities", response_model=list[EthnicityRead])
def list_ethnicities(db: Session = Depends(get_db)):
    return db.scalars(select(Ethnicity).order_by(Ethnicity.name)).all()


@router.post("/ethnicities", response_model=EthnicityRead, status_code=status.HTTP_201_CREATED)
def create_ethnicity(payload: EthnicityCreate, db: Session = Depends(get_db)):
    if db.scalar(select(Ethnicity).where(Ethnicity.name == payload.name)):
        raise HTTPException(status_code=409, detail="Dân tộc đã tồn tại")
    item = Ethnicity(**payload.model_dump())
    db.add(item)
    db.commit()
    db.refresh(item)
    return item


@router.put("/ethnicities/{item_id}", response_model=EthnicityRead)
def update_ethnicity(item_id: int, payload: EthnicityCreate, db: Session = Depends(get_db)):
    return _rename(db, Ethnicity, item_id, payload.name, "Dân tộc")


@router.delete("/ethnicities/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_ethnicity(item_id: int, db: Session = Depends(get_db)):
    _delete(db, Ethnicity, item_id, "Dân tộc", [(Employee, Employee.ethnicity_id, "nhân viên")])


# --- Học vấn (ns_hocvan) ---
@router.get("/educations", response_model=list[EducationRead])
def list_educations(db: Session = Depends(get_db)):
    return db.scalars(select(Education).order_by(Education.name)).all()


@router.post("/educations", response_model=EducationRead, status_code=status.HTTP_201_CREATED)
def create_education(payload: EducationCreate, db: Session = Depends(get_db)):
    if db.scalar(select(Education).where(Education.name == payload.name)):
        raise HTTPException(status_code=409, detail="Học vấn đã tồn tại")
    item = Education(**payload.model_dump())
    db.add(item)
    db.commit()
    db.refresh(item)
    return item


@router.put("/educations/{item_id}", response_model=EducationRead)
def update_education(item_id: int, payload: EducationCreate, db: Session = Depends(get_db)):
    return _rename(db, Education, item_id, payload.name, "Học vấn")


@router.delete("/educations/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_education(item_id: int, db: Session = Depends(get_db)):
    _delete(db, Education, item_id, "Học vấn", [(Employee, Employee.education_id, "nhân viên")])


# --- Hôn nhân (ns_honnhan) ---
@router.get("/marital-statuses", response_model=list[MaritalStatusRead])
def list_marital_statuses(db: Session = Depends(get_db)):
    return db.scalars(select(MaritalStatus).order_by(MaritalStatus.name)).all()


@router.post("/marital-statuses", response_model=MaritalStatusRead, status_code=status.HTTP_201_CREATED)
def create_marital_status(payload: MaritalStatusCreate, db: Session = Depends(get_db)):
    if db.scalar(select(MaritalStatus).where(MaritalStatus.name == payload.name)):
        raise HTTPException(status_code=409, detail="Tình trạng hôn nhân đã tồn tại")
    item = MaritalStatus(**payload.model_dump())
    db.add(item)
    db.commit()
    db.refresh(item)
    return item


@router.put("/marital-statuses/{item_id}", response_model=MaritalStatusRead)
def update_marital_status(item_id: int, payload: MaritalStatusCreate, db: Session = Depends(get_db)):
    return _rename(db, MaritalStatus, item_id, payload.name, "Hôn nhân")


@router.delete("/marital-statuses/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_marital_status(item_id: int, db: Session = Depends(get_db)):
    _delete(db, MaritalStatus, item_id, "Hôn nhân", [(Employee, Employee.marital_status_id, "nhân viên")])


# --- Loại HĐLĐ (ns_hdld) ---
@router.get("/contract-types", response_model=list[ContractTypeRead])
def list_contract_types(db: Session = Depends(get_db)):
    return db.scalars(select(ContractType).order_by(ContractType.name)).all()


@router.post("/contract-types", response_model=ContractTypeRead, status_code=status.HTTP_201_CREATED)
def create_contract_type(payload: ContractTypeCreate, db: Session = Depends(get_db)):
    if db.scalar(select(ContractType).where(ContractType.name == payload.name)):
        raise HTTPException(status_code=409, detail="Loại hợp đồng đã tồn tại")
    item = ContractType(**payload.model_dump())
    db.add(item)
    db.commit()
    db.refresh(item)
    return item


@router.put("/contract-types/{item_id}", response_model=ContractTypeRead)
def update_contract_type(item_id: int, payload: ContractTypeCreate, db: Session = Depends(get_db)):
    return _rename(db, ContractType, item_id, payload.name, "Loại HĐLĐ")


@router.delete("/contract-types/{item_id}", status_code=status.HTTP_204_NO_CONTENT)
def delete_contract_type(item_id: int, db: Session = Depends(get_db)):
    _delete(db, ContractType, item_id, "Loại HĐLĐ", [(Employee, Employee.contract_type_id, "nhân viên")])
