"""Schemas API cho nạp file chấm công."""

from datetime import date, datetime

from pydantic import BaseModel, Field


class RowErrorRead(BaseModel):
    code: str
    line_no: int
    content: str
    message: str


class DateBreakdown(BaseModel):
    date: date
    rows: int


class UnmatchedCardRead(BaseModel):
    card_id: int
    taps: int
    deduped: int = 0  # số lượt đã gộp trùng (W1) — phản ánh máy đọc thẻ có vấn đề
    dates: list[date]
    # ngày ISO -> các giờ quẹt trong ngày (sau gộp trùng)
    times: dict[str, list[str]] = Field(default_factory=dict)


class DedupLineRead(BaseModel):
    """Một dòng trong nhóm trùng: dòng nào, quẹt lúc nào, có phải dòng được giữ."""

    line_no: int
    tap_datetime: str
    kept: bool


class DedupCardGroup(BaseModel):
    """Các lượt quẹt gần nhau của một mã thẻ: click vào để xem từng dòng."""

    card_id: int
    merged_count: int
    lines: list[DedupLineRead] = Field(default_factory=list)


class ParseReport(BaseModel):
    """Báo cáo kiểm tra file — bước 2 của luồng nạp, chưa ghi DB."""

    upload_id: str
    filename: str
    file_hash: str
    total_rows: int
    valid_rows: int
    error_rows: int
    error_counts: dict[str, int]
    error_samples: list[RowErrorRead] = Field(default_factory=list)
    dates: list[DateBreakdown] = Field(default_factory=list)
    deduped_count: int = 0
    # Gộp trùng nhóm theo mã thẻ (sắp xếp theo số lượt bị gộp giảm dần)
    dedup_by_card: list[DedupCardGroup] = Field(default_factory=list)
    unmatched_cards: list[UnmatchedCardRead] = Field(default_factory=list)
    flag_counts: dict[str, int] = Field(default_factory=dict)
    importable_rows: int = 0
    # W7: file đã từng nạp
    already_imported: bool = False
    existing_file_id: int | None = None
    existing_status: str | None = None


class ImportRequest(BaseModel):
    upload_id: str
    # W2: user phải xác nhận từng ngày có trong file mới được nạp
    confirmed_dates: list[date] = Field(default_factory=list)
    # W7: nạp lại thay thế đợt đã nạp trước đó (trùng hash)
    replace_existing: bool = False


class ImportResult(BaseModel):
    file_id: int
    filename: str
    imported_rows: int
    skipped_unmatched: int
    deduped_count: int
    status: str
    unmatched_cards: list[UnmatchedCardRead] = Field(default_factory=list)


class AttendanceFileRead(BaseModel):
    id: int
    filename: str
    work_dates: list[date]
    total_rows: int
    imported_rows: int
    skipped_rows: int
    deduped_count: int
    unmatched_count: int = 0
    status: str
    imported_at: datetime

    model_config = {"from_attributes": True}


class PaginatedFiles(BaseModel):
    items: list[AttendanceFileRead]
    total: int
    page: int
    page_size: int


class UnmatchedFileCards(BaseModel):
    file_id: int
    filename: str
    cards: list[UnmatchedCardRead]


class AssignCardRequest(BaseModel):
    card_id: int
    employee_id: int


class TapRead(BaseModel):
    id: int
    card_id: int
    employee_id: int
    employee_code: str = ""
    employee_name: str = ""
    tap_date: date
    tap_time: str
    flags: list[str] = Field(default_factory=list)
    file_id: int


class PaginatedTaps(BaseModel):
    items: list[TapRead]
    total: int
    page: int
    page_size: int
