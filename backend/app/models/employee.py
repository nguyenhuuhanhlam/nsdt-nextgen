"""Data model nhân viên — tái thiết kế từ bảng `ns_nhansu` của hệ cũ.

Thay đổi chính so với bản cũ:
- Tên bảng/cột tiếng Anh, rõ nghĩa; charset utf8mb4.
- Foreign key đầy đủ (bản cũ chỉ có KEY, không có FK thật).
- Thêm created_at/updated_at cho mọi bảng.
- Trạng thái làm việc qua is_active + terminated_date (thay cho chỉ có
  ngay_nghiviec).
- card_id (mã thẻ chấm công) giữ lại và đánh index: là cầu nối giữa hồ sơ
  nhân viên và file dữ liệu máy chấm công.
"""
from datetime import date, datetime

from sqlalchemy import (
    BigInteger,
    Boolean,
    Date,
    DateTime,
    Float,
    ForeignKey,
    Index,
    Integer,
    String,
    Text,
    func,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base

MYSQL_TABLE_ARGS = {"mysql_charset": "utf8mb4", "mysql_collate": "utf8mb4_unicode_ci"}


class Department(Base):
    """Đơn vị / phân xưởng (từ `ns_donvi`)."""

    __tablename__ = "departments"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="department")


class Position(Base):
    """Chức vụ (từ `ns_chucvu`)."""

    __tablename__ = "positions"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="position")


class JobTitle(Base):
    """Công việc (từ `ns_congviec`)."""

    __tablename__ = "job_titles"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(150), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="job_title")


class Province(Base):
    """Tỉnh / thành phố (từ `ns_tinhthanh`). Danh mục độc lập."""

    __tablename__ = "provinces"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )


class MaternityType(Base):
    """Loại thai sản (từ `ns_thaisan`). Danh mục độc lập."""

    __tablename__ = "maternity_types"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )


class Nationality(Base):
    """Quốc tịch (từ `ns_quoctich`)."""

    __tablename__ = "nationalities"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="nationality")


class Ethnicity(Base):
    """Dân tộc (từ `ns_dantoc`)."""

    __tablename__ = "ethnicities"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="ethnicity")


class Education(Base):
    """Trình độ học vấn (từ `ns_hocvan`)."""

    __tablename__ = "educations"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="education")


class MaritalStatus(Base):
    """Tình trạng hôn nhân (từ `ns_honnhan`)."""

    __tablename__ = "marital_statuses"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="marital_status")


class ContractType(Base):
    """Loại hợp đồng lao động (từ `ns_hdld`)."""

    __tablename__ = "contract_types"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    name: Mapped[str] = mapped_column(String(100), unique=True, nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    employees: Mapped[list["Employee"]] = relationship(back_populates="contract_type")


class MaternityLeave(Base):
    """Hồ sơ nghỉ thai sản của nhân viên (từ `ns_xuly_thaisan`).

    leave_days (số ngày nghỉ) được tự tính từ khoảng ngày khi tạo/sửa nếu
    không truyền vào, nhưng vẫn cho phép ghi đè thủ công theo chính sách.
    """

    __tablename__ = "maternity_leaves"
    __table_args__ = (
        Index("ix_maternity_leaves_employee", "employee_id"),
        Index("ix_maternity_leaves_type", "maternity_type_id"),
        MYSQL_TABLE_ARGS,
    )

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)
    employee_id: Mapped[int] = mapped_column(
        ForeignKey("employees.id"), nullable=False
    )
    maternity_type_id: Mapped[int] = mapped_column(
        ForeignKey("maternity_types.id"), nullable=False
    )
    start_date: Mapped[date] = mapped_column(Date, nullable=False)  # ngay_batdau
    end_date: Mapped[date] = mapped_column(Date, nullable=False)  # ngay_ketthuc
    leave_days: Mapped[int] = mapped_column(Integer, nullable=False, default=0)  # songaynghi
    notes: Mapped[str | None] = mapped_column(Text)

    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now(), nullable=False
    )

    employee: Mapped["Employee"] = relationship()
    maternity_type: Mapped["MaternityType"] = relationship()


class Employee(Base):
    """Hồ sơ nhân viên (từ `ns_nhansu`)."""

    __tablename__ = "employees"
    __table_args__ = (
        Index("ix_employees_department", "department_id"),
        Index("ix_employees_card", "card_id"),
        Index("ix_employees_name", "last_name", "first_name"),
        MYSQL_TABLE_ARGS,
    )

    id: Mapped[int] = mapped_column(Integer, primary_key=True, autoincrement=True)

    # mã nhân viên (ma_nhanvien) — unique, dùng làm khóa nghiệp vụ
    employee_code: Mapped[str] = mapped_column(String(16), unique=True, nullable=False)
    # mã thẻ chấm công (ma_the) — map với file máy chấm công
    card_id: Mapped[int | None] = mapped_column(BigInteger, nullable=True)

    first_name: Mapped[str] = mapped_column(String(100), nullable=False)  # họ
    last_name: Mapped[str] = mapped_column(String(50), nullable=False)  # tên

    id_number: Mapped[str | None] = mapped_column(String(20))  # CCCD/CMND (socmnd)
    id_issue_date: Mapped[date | None] = mapped_column(Date)  # ngày cấp CMND (ngaycapcmnd)
    id_issue_place_id: Mapped[int | None] = mapped_column(
        ForeignKey("provinces.id"), nullable=True
    )  # nơi cấp CMND (noicapcmnd)
    birth_date: Mapped[date | None] = mapped_column(Date)
    gender: Mapped[str | None] = mapped_column(String(10))
    birth_place_id: Mapped[int | None] = mapped_column(
        ForeignKey("provinces.id"), nullable=True
    )  # nơi sinh (noisinh)
    nationality_id: Mapped[int | None] = mapped_column(
        ForeignKey("nationalities.id"), nullable=True
    )  # quốc tịch (id_quoctich)
    ethnicity_id: Mapped[int | None] = mapped_column(
        ForeignKey("ethnicities.id"), nullable=True
    )  # dân tộc (id_dantoc)
    education_id: Mapped[int | None] = mapped_column(
        ForeignKey("educations.id"), nullable=True
    )  # học vấn (id_hocvan)
    marital_status_id: Mapped[int | None] = mapped_column(
        ForeignKey("marital_statuses.id"), nullable=True
    )  # hôn nhân (id_honnhan)
    hometown: Mapped[str | None] = mapped_column(String(255))  # nguyên quán
    permanent_address: Mapped[str | None] = mapped_column(String(255))  # hộ khẩu
    current_address: Mapped[str | None] = mapped_column(String(255))  # chỗ ở hiện tại

    department_id: Mapped[int | None] = mapped_column(
        ForeignKey("departments.id"), nullable=True
    )
    position_id: Mapped[int | None] = mapped_column(
        ForeignKey("positions.id"), nullable=True
    )
    job_title_id: Mapped[int | None] = mapped_column(
        ForeignKey("job_titles.id"), nullable=True
    )

    join_date: Mapped[date | None] = mapped_column(Date)  # ngày vào làm
    probation_end_date: Mapped[date | None] = mapped_column(Date)  # hết thử việc
    salary_coefficient: Mapped[float | None] = mapped_column(Float)  # hệ số lương
    contract_type_id: Mapped[int | None] = mapped_column(
        ForeignKey("contract_types.id"), nullable=True
    )  # loại HĐLĐ (id_hdld)
    # Cờ BT/KT của hệ cũ (0/1): nhân viên có btkt=1 bị loại khỏi một số danh sách.
    btkt: Mapped[bool] = mapped_column(Boolean, default=False, nullable=False)

    is_active: Mapped[bool] = mapped_column(Boolean, default=True, nullable=False)
    terminated_date: Mapped[date | None] = mapped_column(Date)  # ngày nghỉ việc

    notes: Mapped[str | None] = mapped_column(Text)

    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )
    updated_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), onupdate=func.now(), nullable=False
    )

    department: Mapped[Department | None] = relationship(back_populates="employees")
    position: Mapped[Position | None] = relationship(back_populates="employees")
    job_title: Mapped[JobTitle | None] = relationship(back_populates="employees")
    birth_place: Mapped["Province | None"] = relationship(foreign_keys=[birth_place_id])
    id_issue_place: Mapped["Province | None"] = relationship(foreign_keys=[id_issue_place_id])
    nationality: Mapped["Nationality | None"] = relationship(back_populates="employees")
    ethnicity: Mapped["Ethnicity | None"] = relationship(back_populates="employees")
    education: Mapped["Education | None"] = relationship(back_populates="employees")
    marital_status: Mapped["MaritalStatus | None"] = relationship(back_populates="employees")
    contract_type: Mapped["ContractType | None"] = relationship(back_populates="employees")

    @property
    def full_name(self) -> str:
        return f"{self.first_name} {self.last_name}".strip()
