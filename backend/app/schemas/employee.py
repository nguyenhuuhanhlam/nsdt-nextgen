from datetime import date, datetime

from pydantic import BaseModel, ConfigDict, Field


class DepartmentBase(BaseModel):
    name: str = Field(max_length=100)


class DepartmentCreate(DepartmentBase):
    pass


class DepartmentRead(DepartmentBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    created_at: datetime


class PositionBase(BaseModel):
    name: str = Field(max_length=100)


class PositionCreate(PositionBase):
    pass


class PositionRead(PositionBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    created_at: datetime


class JobTitleBase(BaseModel):
    name: str = Field(max_length=150)


class JobTitleCreate(JobTitleBase):
    pass


class JobTitleRead(JobTitleBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    created_at: datetime


class ProvinceBase(BaseModel):
    name: str = Field(max_length=100)


class ProvinceCreate(ProvinceBase):
    pass


class ProvinceRead(ProvinceBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    created_at: datetime


class EmployeeBase(BaseModel):
    employee_code: str = Field(max_length=16)
    card_id: int | None = None
    first_name: str = Field(max_length=100)
    last_name: str = Field(max_length=50)
    id_number: str | None = Field(default=None, max_length=20)
    birth_date: date | None = None
    gender: str | None = Field(default=None, max_length=10)
    birth_place: str | None = Field(default=None, max_length=100)
    hometown: str | None = Field(default=None, max_length=255)
    permanent_address: str | None = Field(default=None, max_length=255)
    current_address: str | None = Field(default=None, max_length=255)
    department_id: int | None = None
    position_id: int | None = None
    job_title_id: int | None = None
    join_date: date | None = None
    probation_end_date: date | None = None
    salary_coefficient: float | None = None
    notes: str | None = None


class EmployeeCreate(EmployeeBase):
    pass


class EmployeeUpdate(BaseModel):
    """Tất cả field optional — PATCH từng phần."""

    card_id: int | None = None
    first_name: str | None = Field(default=None, max_length=100)
    last_name: str | None = Field(default=None, max_length=50)
    id_number: str | None = Field(default=None, max_length=20)
    birth_date: date | None = None
    gender: str | None = Field(default=None, max_length=10)
    birth_place: str | None = Field(default=None, max_length=100)
    hometown: str | None = Field(default=None, max_length=255)
    permanent_address: str | None = Field(default=None, max_length=255)
    current_address: str | None = Field(default=None, max_length=255)
    department_id: int | None = None
    position_id: int | None = None
    job_title_id: int | None = None
    join_date: date | None = None
    probation_end_date: date | None = None
    salary_coefficient: float | None = None
    is_active: bool | None = None
    terminated_date: date | None = None
    notes: str | None = None


class EmployeeRead(EmployeeBase):
    model_config = ConfigDict(from_attributes=True)

    id: int
    is_active: bool
    terminated_date: date | None
    department: DepartmentRead | None = None
    position: PositionRead | None = None
    job_title: JobTitleRead | None = None
    full_name: str
    created_at: datetime
    updated_at: datetime


class PaginatedEmployees(BaseModel):
    items: list[EmployeeRead]
    total: int
    page: int
    page_size: int
