"""Parser file chấm công định dạng ``mcc_txt`` (máy quẹt thẻ xuất file .txt).

Định dạng (quan sát từ file thật):
    0<TAB>2025/06/25<TAB>06:09:54<TAB>'00347903930483<TAB>00<TAB>0

6 cột TSV, chỉ 3 cột có nghĩa: ngày (yyyy/mm/dd), giờ (hh:mm:ss), mã thẻ
(có dấu ' đầu + số 0 đệm — strip hết để ra số nguyên so với card_id).

Bảng kiểm tra (docs/attendance-import.md):
- Lỗi cứng E1..E5: dòng bị loại khỏi đợt nạp.
- Cảnh báo W1 (gộp trùng), W2 (lẫn ngày), W4/W5/W6 (gắn cờ) được phát hiện ở đây;
  W3 (thẻ chưa khớp NV) và W7 (trùng file) cần DB nên xử lý ở tầng service.
"""

from __future__ import annotations

import hashlib
import re
from dataclasses import dataclass, field
from datetime import date, datetime, time

# Ngưỡng kiểm tra — để ở đây để chỉnh không cần sửa logic
DEDUP_WINDOW_SECONDS = 60  # W1: trùng quẹt cùng thẻ trong 60 giây -> gộp
EXCESS_TAPS_PER_DAY = 6  # W5: > 6 lượt quẹt/ngày là bất thường
DAY_START = time(5, 0, 0)  # W6: khung giờ bình thường
DAY_END = time(21, 0, 0)

_DATE_RE = re.compile(r"^(\d{4})/(\d{2})/(\d{2})$")
_TIME_RE = re.compile(r"^(\d{2}):(\d{2}):(\d{2})$")
_SAMPLE_CHARS = 120  # số ký tự dòng lỗi giữ lại trong báo cáo


@dataclass
class TapRow:
    line_no: int
    card_id: int
    tap_date: date
    tap_time: time

    @property
    def tap_datetime(self) -> datetime:
        return datetime.combine(self.tap_date, self.tap_time)

    def to_dict(self) -> dict:
        return {
            "line_no": self.line_no,
            "card_id": self.card_id,
            "tap_date": self.tap_date.isoformat(),
            "tap_time": self.tap_time.isoformat(),
        }

    @classmethod
    def from_dict(cls, d: dict) -> "TapRow":
        return cls(
            line_no=d["line_no"],
            card_id=d["card_id"],
            tap_date=date.fromisoformat(d["tap_date"]),
            tap_time=time.fromisoformat(d["tap_time"]),
        )


@dataclass
class RowError:
    code: str  # E1..E5
    line_no: int
    content: str
    message: str

    def to_dict(self) -> dict:
        return {
            "code": self.code,
            "line_no": self.line_no,
            "content": self.content,
            "message": self.message,
        }


@dataclass
class ParseResult:
    filename: str
    file_hash: str
    total_rows: int
    valid_rows: list[TapRow] = field(default_factory=list)  # đã gộp trùng W1
    errors: list[RowError] = field(default_factory=list)
    deduped_count: int = 0
    # W1 chi tiết: card_id -> số lượt đã gộp trùng (phản ánh máy đọc thẻ có vấn đề)
    deduped_per_card: dict[int, int] = field(default_factory=dict)
    # flags: index trong valid_rows -> ["single_tap", "excess_taps", "odd_hours"]
    flags: dict[int, list[str]] = field(default_factory=dict)

    @property
    def error_counts(self) -> dict[str, int]:
        counts: dict[str, int] = {}
        for e in self.errors:
            counts[e.code] = counts.get(e.code, 0) + 1
        return counts

    @property
    def dates(self) -> dict[str, int]:
        """Ngày -> số dòng (sau gộp trùng), sắp xếp tăng dần."""
        counts: dict[str, int] = {}
        for r in self.valid_rows:
            k = r.tap_date.isoformat()
            counts[k] = counts.get(k, 0) + 1
        return dict(sorted(counts.items()))

    @property
    def flag_counts(self) -> dict[str, int]:
        counts: dict[str, int] = {}
        for flist in self.flags.values():
            for f in flist:
                counts[f] = counts.get(f, 0) + 1
        return counts

    def to_dict(self) -> dict:
        return {
            "filename": self.filename,
            "file_hash": self.file_hash,
            "total_rows": self.total_rows,
            "valid_rows": [r.to_dict() for r in self.valid_rows],
            "errors": [e.to_dict() for e in self.errors],
            "deduped_count": self.deduped_count,
            "deduped_per_card": {str(k): v for k, v in self.deduped_per_card.items()},
            "flags": {str(k): v for k, v in self.flags.items()},
        }

    @classmethod
    def from_dict(cls, d: dict) -> "ParseResult":
        return cls(
            filename=d["filename"],
            file_hash=d["file_hash"],
            total_rows=d["total_rows"],
            valid_rows=[TapRow.from_dict(r) for r in d["valid_rows"]],
            errors=[RowError(**e) for e in d["errors"]],
            deduped_count=d["deduped_count"],
            deduped_per_card={int(k): v for k, v in d.get("deduped_per_card", {}).items()},
            flags={int(k): v for k, v in d["flags"].items()},
        )


def _strip_card(raw: str) -> int | None:
    """'00347903930483 -> 347903930483. None khi không còn chữ số."""
    s = raw.strip()
    if s.startswith("'"):
        s = s[1:]
    s = s.lstrip("0")
    if not s or not s.isdigit():
        return None
    return int(s)


def _parse_line(line_no: int, raw_line: str, today: date) -> TapRow | RowError:
    content = raw_line[:_SAMPLE_CHARS]
    cols = raw_line.split("\t")
    if len(cols) != 6:
        return RowError("E1", line_no, content,
                        f"Sai số cột: có {len(cols)}, cần 6")
    date_s, time_s, card_s = cols[1].strip(), cols[2].strip(), cols[3].strip()

    m = _DATE_RE.match(date_s)
    tap_date = None
    if m:
        try:
            tap_date = date(int(m.group(1)), int(m.group(2)), int(m.group(3)))
        except ValueError:
            tap_date = None
    if tap_date is None:
        return RowError("E2", line_no, content,
                        f"Ngày không hợp lệ: '{date_s}' (cần yyyy/mm/dd)")

    m = _TIME_RE.match(time_s)
    tap_time = None
    if m:
        try:
            tap_time = time(int(m.group(1)), int(m.group(2)), int(m.group(3)))
        except ValueError:
            tap_time = None
    if tap_time is None:
        return RowError("E3", line_no, content,
                        f"Giờ không hợp lệ: '{time_s}' (cần hh:mm:ss)")

    card_id = _strip_card(card_s)
    if card_id is None:
        return RowError("E4", line_no, content,
                        f"Mã thẻ rỗng hoặc không phải số: '{card_s}'")

    if tap_date > today:
        return RowError("E5", line_no, content,
                        f"Ngày trong tương lai: '{date_s}'")

    return TapRow(line_no=line_no, card_id=card_id,
                  tap_date=tap_date, tap_time=tap_time)


def parse_mcc_txt(content: bytes, filename: str) -> ParseResult:
    """Parse toàn bộ file, chạy E1..E5 + W1/W2/W4/W5/W6. Không cần DB."""
    try:
        text = content.decode("utf-8-sig")
    except UnicodeDecodeError:
        raise ValueError(f"File '{filename}' không đọc được (cần text UTF-8)")

    file_hash = hashlib.sha256(content).hexdigest()
    today = date.today()
    result = ParseResult(filename=filename, file_hash=file_hash, total_rows=0)

    rows: list[TapRow] = []
    for i, raw_line in enumerate(text.splitlines(), start=1):
        line = raw_line.strip()
        if not line:
            continue  # bỏ qua dòng trống, không tính vào total
        result.total_rows += 1
        parsed = _parse_line(i, raw_line.rstrip("\r\n"), today)
        if isinstance(parsed, RowError):
            result.errors.append(parsed)
        else:
            rows.append(parsed)

    # W1: gộp trùng quẹt — cùng thẻ, cách nhau <= 60 giây thì giữ lượt đầu
    rows.sort(key=lambda r: (r.card_id, r.tap_datetime))
    deduped: list[TapRow] = []
    for r in rows:
        if (deduped and deduped[-1].card_id == r.card_id
                and (r.tap_datetime - deduped[-1].tap_datetime).total_seconds()
                <= DEDUP_WINDOW_SECONDS):
            result.deduped_count += 1
            result.deduped_per_card[r.card_id] = \
                result.deduped_per_card.get(r.card_id, 0) + 1
            continue
        deduped.append(r)
    result.valid_rows = deduped

    # W4/W5/W6: gắn cờ theo nhóm (thẻ, ngày)
    groups: dict[tuple[int, date], list[int]] = {}
    for idx, r in enumerate(deduped):
        groups.setdefault((r.card_id, r.tap_date), []).append(idx)
    for idxs in groups.values():
        if len(idxs) == 1:  # W4: chỉ 1 lượt/ngày
            result.flags.setdefault(idxs[0], []).append("single_tap")
        elif len(idxs) > EXCESS_TAPS_PER_DAY:  # W5
            for idx in idxs:
                result.flags.setdefault(idx, []).append("excess_taps")
        for idx in idxs:  # W6: ngoài khung giờ
            t = deduped[idx].tap_time
            if t < DAY_START or t > DAY_END:
                result.flags.setdefault(idx, []).append("odd_hours")

    return result
