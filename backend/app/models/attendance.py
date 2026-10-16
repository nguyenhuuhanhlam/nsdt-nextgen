"""Chấm công: file đã nạp + lượt quẹt thẻ.

Thiết kế cho quy mô ~10.000 nhân viên x nhiều lượt quẹt/ngày:

- ``attendance_files``: vết từng đợt nạp file (hash SHA256 chống nạp trùng,
  trạng thái pending_fix khi còn thẻ chưa khớp nhân viên).
- ``attendance_taps``: mỗi dòng = 1 lượt quẹt. Partition theo tháng trên MySQL
  (xem ``seed/partition_attendance_taps.sql`` — ``create_all`` không tạo
  partition, chỉ dùng cho dev/test).
"""

from datetime import datetime

from sqlalchemy import (
    JSON,
    BigInteger,
    Date,
    DateTime,
    ForeignKey,
    Index,
    String,
    Time,
    UniqueConstraint,
    func,
)
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.db.base import Base
from app.models.employee import MYSQL_TABLE_ARGS

# Trạng thái file
FILE_IMPORTED = "imported"  # nạp xong, không còn thẻ lạ
FILE_PENDING_FIX = "pending_fix"  # còn thẻ chưa khớp NV, phải gán rồi nạp lại


class AttendanceFile(Base):
    """Một đợt nạp file chấm công."""

    __tablename__ = "attendance_files"
    __table_args__ = MYSQL_TABLE_ARGS

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    filename: Mapped[str] = mapped_column(String(255), nullable=False)
    file_hash: Mapped[str] = mapped_column(String(64), nullable=False, unique=True)
    storage_path: Mapped[str] = mapped_column(String(500), nullable=False)
    work_dates: Mapped[list] = mapped_column(JSON, nullable=False, default=list)
    confirmed_dates: Mapped[list] = mapped_column(JSON, nullable=False, default=list)
    total_rows: Mapped[int] = mapped_column(nullable=False, default=0)
    imported_rows: Mapped[int] = mapped_column(nullable=False, default=0)
    skipped_rows: Mapped[int] = mapped_column(nullable=False, default=0)
    deduped_count: Mapped[int] = mapped_column(nullable=False, default=0)
    unmatched_cards: Mapped[list] = mapped_column(JSON, nullable=False, default=list)
    status: Mapped[str] = mapped_column(String(20), nullable=False, default=FILE_IMPORTED)
    imported_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )
    imported_by: Mapped[str | None] = mapped_column(String(100), nullable=True)

    taps: Mapped[list["AttendanceTap"]] = relationship(back_populates="file")


class AttendanceTap(Base):
    """Một lượt quẹt thẻ đã qua kiểm tra và khớp nhân viên."""

    __tablename__ = "attendance_taps"
    __table_args__ = (
        # Unique gồm tap_date để tương thích partition theo tháng trên MySQL
        # (partition key bắt buộc nằm trong mọi unique key).
        UniqueConstraint("card_id", "tap_date", "tap_time", name="uq_taps_card_datetime"),
        Index("ix_taps_employee_date", "employee_id", "tap_date"),
        Index("ix_taps_date_card", "tap_date", "card_id"),
        Index("ix_taps_file", "file_id"),
        MYSQL_TABLE_ARGS,
    )

    id: Mapped[int] = mapped_column(primary_key=True, autoincrement=True)
    card_id: Mapped[int] = mapped_column(BigInteger, nullable=False)
    employee_id: Mapped[int] = mapped_column(
        ForeignKey("employees.id"), nullable=False
    )
    tap_date: Mapped[datetime] = mapped_column(Date, nullable=False)
    tap_time: Mapped[datetime] = mapped_column(Time, nullable=False)
    tap_datetime: Mapped[datetime] = mapped_column(DateTime, nullable=False)
    # Cờ cảnh báo W4/W5/W6: ["single_tap", "excess_taps", "odd_hours"]
    flags: Mapped[list | None] = mapped_column(JSON, nullable=True)
    file_id: Mapped[int] = mapped_column(
        ForeignKey("attendance_files.id"), nullable=False
    )
    created_at: Mapped[datetime] = mapped_column(
        DateTime, server_default=func.now(), nullable=False
    )

    file: Mapped["AttendanceFile"] = relationship(back_populates="taps")
