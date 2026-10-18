# Bộ quy định giờ công (work rules)

Mục tiêu: mọi hằng số đang cứng trong code/MySQL cũ được đưa vào **một bộ quy định
có hiệu lực theo thời gian**. Đổi quy định (giờ giấc, giờ nghỉ, ngưỡng tính công...)
chỉ cần tạo bộ mới — code tính toán không đổi, và bảng lương tháng cũ vẫn tính lại
đúng vì mỗi đợt tính lương ghi nhận đã dùng bộ quy định nào.

## 1. Hằng số cũ → trường cấu hình

| Hằng số trong code cũ | Nằm ở | Trường cấu hình |
|---|---|---|
| Giờ bắt đầu tính công 07:15 | `mcc.time.month.php` (truyền vào `workingTime`) | `standard_start` |
| Mốc giờ quy định 16:30 (toàn công ty) | `fixTime`, `overTime`, `workingTime` | `standard_end` |
| Giờ nghỉ trưa 11:15–12:15 (trừ 1h, không tính tiền) | `fixTime`, `workinghoursSegment` | `unpaid_breaks` (danh sách — sau này thêm khoảng nghỉ khác được) |
| Ngưỡng tách tăng ca 1.5h (trước/sau) | `mcc.time.month.php` (`lamthemtruoc`/`lamthemsau`) | `overtime_split_hours` |
| Bậc ngày công: ≤2h→0.25, ≤4h→0.5, ≤6h→0.75, >6h→1 | `dayCount` | `day_count_steps` (JSON) |
| Trần giờ HC: thai sản loại 1,2,3 → 7h; thường → 8h | `maternityFixWorkingTime` | `maternity_cap_hours`, `standard_cap_hours` |
| Thai sản loại 2 → tăng ca = 0 | `maternityFixOverTime` | `maternity_no_overtime_types` (JSON, vd `[2]`) |
| HC > 6h → +1 giờ bù thai sản (BCN) | `thaisan_themgio` | `extra_hour_threshold`, `extra_hour_value` |
| Đơn vị 1..7 (văn phòng) không tính tăng ca | `overTime` | cờ `is_office` trên bảng departments (không nhét id cứng vào quy định) |

## 2. Bảng `work_rule_sets`

```sql
CREATE TABLE work_rule_sets (
  id            BIGINT PRIMARY KEY AUTO_INCREMENT,
  name          VARCHAR(100) NOT NULL,          -- vd "Quy định giờ công 2024"
  effective_from DATE NOT NULL,                 -- hiệu lực từ ngày
  effective_to   DATE NULL,                     -- NULL = đang hiệu lực
  standard_start TIME NOT NULL DEFAULT '07:15:00',
  standard_end   TIME NOT NULL DEFAULT '16:30:00',
  unpaid_breaks  JSON NOT NULL,                  -- [{"start":"11:15","end":"12:15"}]
  overtime_split_hours DECIMAL(4,2) NOT NULL DEFAULT 1.50,
  day_count_steps JSON NOT NULL,                -- [[2,0.25],[4,0.5],[6,0.75],[null,1]]
  maternity_cap_hours DECIMAL(4,2) NOT NULL DEFAULT 7.00,
  standard_cap_hours  DECIMAL(4,2) NOT NULL DEFAULT 8.00,
  maternity_no_overtime_types JSON NOT NULL,    -- [2]
  extra_hour_threshold DECIMAL(4,2) NOT NULL DEFAULT 6.00,
  extra_hour_value    DECIMAL(4,2) NOT NULL DEFAULT 1.00,
  is_active     BOOLEAN NOT NULL DEFAULT TRUE,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by    VARCHAR(100) NULL
);
```

Quy tắc chọn bộ khi tính lương tháng M: bộ có `effective_from <= ngày đầu tháng M`
mới nhất (và `effective_to` NULL hoặc >= ngày cuối tháng M). Đợt tính lương lưu
`rule_set_id` đã dùng → tính lại tháng cũ luôn đúng dù quy định đã đổi.

## 3. Cách dùng trong code

`app/payroll/timecalc.py` — các hàm nhận thêm tham số `rules: WorkRules`
(dataclass đọc từ `work_rule_sets`), thay cho hằng số cứng:

```python
def fix_time(t, rules, closing_time): ...
def working_time(h1..h6, rules, closing_time, disable): ...
def over_time(h2, h4, h6, rules, closing_time, is_office, disable): ...
```

`is_office` lấy từ `departments.is_office` (dữ liệu hiện tại: các đơn vị có
`old_id` 1..7 → `is_office = TRUE` khi seed).

## 4. Phân biệt với dữ liệu vận hành

| Loại | Bảng | Ví dụ | Thay đổi |
|---|---|---|---|
| Quy định (policy) | `work_rule_sets` | 16:30, giờ nghỉ trưa, bậc ngày công | hiếm, có hiệu lực theo thời gian |
| Vận hành (operation) | `work_calendar` (ngày, đơn vị) | closingTime từng ngày, holiday, disable | hàng ngày/tuần |

`closingTime` **không** nằm trong bộ quy định — nó là dữ liệu vận hành theo
(ngày, đơn vị), nhập qua form "Sửa Giờ Đóng Chuyền".
