# Thiết kế: Nạp file chấm công (máy quẹt thẻ)

Tài liệu này mô tả menu nhập liệu file chấm công: kiểm tra lỗi từ file nguồn,
báo cáo bất thường, rồi mới nạp vào DB. Bản nháp — chờ duyệt trước khi làm.

## 1. Định dạng file nguồn (`mcc_txt`)

Quan sát từ 5 file mẫu `oldcodes/mcc_data/*.txt` (bản cũ: máy chấm công xuất 1 file/ngày):

```
0<TAB>2025/06/25<TAB>06:09:54<TAB>'00347903930483<TAB>00<TAB>0
```

- 6 cột TSV, chỉ 3 cột có nghĩa: **ngày** (`yyyy/mm/dd`), **giờ** (`hh:mm:ss`), **mã thẻ**
  (có `'` đầu + số 0 đệm — strip hết để ra số so với `card_id`).
- ~1.800 dòng/ngày, ~900 thẻ/ngày. Phân bố lượt quẹt/thẻ/ngày (mẫu 25/06):
  1 lượt: 32 thẻ · 2 lượt: 824 · 3 lượt: 52 · 4 lượt: 9 · 5 lượt: 2 (max).
- Trùng quẹt cách nhau vài giây có thật (vd `06:09:54` và `06:09:57` cùng thẻ).
- **File có thể lẫn ngày khác**: `20250630.txt` chứa 1 dòng ngày 28/06 + 1297 dòng ngày 30/06
  → không tin tên file, chỉ tin ngày trong nội dung.
- Khung giờ quẹt quan sát: ~05:38–19:52. Không có dòng sai định dạng trong 5 file mẫu,
  nhưng kiểm tra vẫn phải có vì file do máy/con người copy tay.

## 2. Luồng nhập liệu (3 bước)

```
[1] Chọn/Upload file  →  [2] Báo cáo kiểm tra (chưa ghi DB)  →  [3] Xác nhận nạp
```

- **Bước 1**: kéo-thả hoặc chọn file `.txt`. Hỗ trợ nạp nhiều file một lúc.
- **Bước 2**: backend parse toàn bộ file, chạy bảng kiểm tra (§3), trả về báo cáo:
  tổng dòng, dòng hợp lệ, dòng lỗi (kèm lý do + mẫu), cảnh báo (kèm số lượng),
  số lượt trùng đã gộp, thẻ chưa khớp nhân viên. Frontend hiện báo cáo cho người dùng xem.
- **Bước 3**: người dùng bấm **Xác nhận nạp** → bulk insert. Có thể **Hủy** không mất gì
  vì bước 2 chưa ghi DB.

## 3. Bảng kiểm tra file

### 3.1 Lỗi cứng — dòng bị loại khỏi đợt nạp, liệt kê trong báo cáo

| Mã | Nội dung | Ghi chú |
|----|----------|---------|
| E1 | Sai số cột (≠ 6) | File không đúng định dạng máy |
| E2 | Ngày sai định dạng hoặc không phải ngày hợp lệ | vd tháng 13, ngày 32 |
| E3 | Giờ sai định dạng hoặc không hợp lệ | vd `25:61:00` |
| E4 | Mã thẻ rỗng / không còn chữ số sau khi strip | |
| E5 | Ngày trong dòng > hôm nay | Dữ liệu tương lai, chắc chắn sai |

### 3.2 Cảnh báo — vẫn nạp, đánh dấu để xem lại sau

| Mã | Nội dung | Xử lý đề xuất |
|----|----------|---------------|
| W1 | Trùng quẹt: cùng thẻ cách nhau ≤ 60 giây | Tự gộp, giữ lượt đầu tiên; báo số lượt đã gộp |
| W2 | File chứa nhiều ngày (thường là hôm trước + hôm tạo file) | Báo cáo liệt kê rõ từng ngày + số dòng; người dùng **xác nhận từng ngày** mới được nạp; nạp tách riêng theo ngày |
| W3 | Thẻ chưa khớp nhân viên nào | **Loại khỏi đợt nạp** (không ghi vào `attendance_taps`); file bị đánh dấu `pending_fix`, lưu danh sách thẻ lạ; phải gán thẻ cho NV rồi **nạp lại file** mới xong |
| W4 | Thẻ chỉ có 1 lượt quẹt trong ngày | Nạp bình thường nhưng gắn cờ `single_tap`; có màn hình lọc lại để xử lý sau |
| W5 | Thẻ có > 6 lượt quẹt/ngày | Nạp bình thường nhưng gắn cờ `excess_taps`; có màn hình lọc lại để xử lý sau |
| W6 | Giờ quẹt ngoài 05:00–21:00 | Nạp bình thường nhưng gắn cờ `odd_hours`; có màn hình lọc lại để xử lý sau |
| W7 | File đã nạp trước đó (trùng SHA256) | Mặc định **bỏ qua**; có tùy chọn "nạp lại thay thế" (xóa đợt cũ, nạp mới) |

Ngưỡng (60 giây, 6 lượt, 05:00–21:00) để trong cấu hình, chỉnh được không cần sửa code.

## 4. Bảng dữ liệu mới

```sql
attendance_files (
  id, filename, file_hash CHAR(64) UNIQUE,   -- SHA256: chống nạp trùng
  storage_path,                               -- file gốc lưu trên disk để nạp lại
  work_dates JSON,                            -- các ngày có trong file (W2)
  confirmed_dates JSON,                       -- các ngày user đã xác nhận (W2)
  total_rows, imported_rows, skipped_rows, deduped_count,
  unmatched_cards JSON,                      -- danh sách thẻ lạ (W3)
  status,                                    -- 'imported' | 'pending_fix' | 'replaced'
  imported_at, imported_by
);

attendance_taps (
  id,
  card_id BIGINT NOT NULL,                    -- mã thẻ đã strip
  employee_id BIGINT NOT NULL,                 -- FK nhân viên (W3 đã loại từ trước)
  tap_date DATE NOT NULL,
  tap_time TIME NOT NULL,
  tap_datetime DATETIME NOT NULL,
  flags JSON NULL,                             -- ['single_tap'|'excess_taps'|'odd_hours'] (W4/W5/W6)
  file_id BIGINT NOT NULL,                     -- truy vết về file nguồn
  created_at,
  UNIQUE (card_id, tap_datetime),              -- chống trùng ở tầng DB
  INDEX (employee_id, tap_date),
  INDEX (tap_date, card_id)
)
PARTITION BY RANGE (YEAR(tap_date)*100 + MONTH(tap_date));  -- 1 partition/tháng
```

- Partition theo tháng: tính lương tháng nào chỉ quét 1 partition (~1 triệu dòng).
- Vòng đời dữ liệu (đã chốt): lượt quẹt thô giữ nóng vài tháng → archive file lạnh sau khi
  chốt lương; xem `docs/architecture.md`.

## 5. API (backend)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/v1/attendance/parse` | Upload file → trả báo cáo kiểm tra (§3), chưa ghi DB |
| POST | `/api/v1/attendance/import` | Xác nhận nạp từ kết quả parse (kèm `upload_id` + `confirmed_dates`); thẻ lạ (W3) bị loại, file đánh dấu `pending_fix` |
| POST | `/api/v1/attendance/files/{id}/reload` | Nạp lại file `pending_fix` sau khi đã gán thẻ cho NV (idempotent: unique key bỏ qua dòng đã nạp) |
| GET  | `/api/v1/attendance/files` | Lịch sử file đã nạp (phân trang, tìm theo ngày/tên file, lọc `pending_fix`) |
| GET  | `/api/v1/attendance/unmatched` | Thẻ chưa khớp NV theo từng file (W3) — gán thẻ cho NV tại đây |
| GET  | `/api/v1/attendance/taps` | Tra cứu lượt quẹt, lọc theo cờ `single_tap` / `excess_taps` / `odd_hours` (W4/W5/W6) |

Parser tách module riêng theo định dạng máy (`app/parsers/mcc_txt.py`) — đúng như
`docs/architecture.md`: mỗi loại máy một module parser.

## 6. Frontend — trang "Nạp file chấm công"

- Vị trí: sidebar Nhân sự → mục Chấm công (folder mới) → "Nạp file chấm công".
- Bước 1: vùng kéo-thả file + nút chọn file.
- Bước 2: thẻ tóm tắt (tổng dòng / hợp lệ / lỗi / cảnh báo / đã gộp trùng / thẻ lạ),
  **bảng phân rã theo ngày** (ngày nào, bao nhiêu dòng — bấm xác nhận từng ngày, W2),
  danh sách chi tiết từng mã lỗi/cảnh báo dạng expandable (hiện ~20 dòng mẫu + tổng số).
  Nút **Xác nhận nạp** chỉ bật khi đã xác nhận hết các ngày trong file.
- Bước 3: nút **Xác nhận nạp** (primary) / **Hủy** (ghost). Nạp xong hiện kết quả:
  nếu có thẻ lạ → file vào trạng thái "chờ xử lý", hiện link sang màn hình gán thẻ.
- Trang phụ: "Lịch sử nạp file" (xem lại các đợt nạp, lọc file chờ xử lý, nút **Nạp lại**),
  "Thẻ chưa khớp" (liệt kê thẻ lạ theo file → chọn nhân viên để gán → xong quay lại nạp lại file),
  "Lượt quẹt cần xem lại" (lọc theo cờ W4/W5/W6).

## 7. Quyết định đã chốt (2026-10-16)

1. Ngưỡng gộp trùng: 60 giây.
2. Thẻ chưa khớp NV: **loại khỏi đợt nạp**, file đánh dấu `pending_fix`, gán thẻ xong nạp lại file.
3. File lẫn nhiều ngày: báo cáo phân rã theo ngày, **user xác nhận từng ngày** mới được nạp.
4. File trùng hash: mặc định bỏ qua, có tùy chọn "nạp lại thay thế".
5. W4/W5/W6: nạp bình thường + gắn cờ `flags`, có màn hình lọc lại để xử lý sau.
