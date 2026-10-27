# MCP server cho NSDT NextGen

Mục tiêu: truy vấn dữ liệu hệ thống bằng câu hỏi tự nhiên (ưu tiên tiếng Việt)
từ bất kỳ AI client nào hỗ trợ MCP (Claude Desktop, Muse...). MCP server chỉ
cung cấp công cụ truy vấn an toàn, có mô tả tiếng Việt — việc hiểu câu hỏi và
chọn tool là của AI client.

## Quyết định đã chốt (2026-10-27)

1. **Giai đoạn 1 chỉ đọc.** Mọi thao tác ghi (gán thẻ, sửa H1..H6, sửa quy định...)
   làm ở giai đoạn 2, và **mỗi lần ghi đều phải hỏi xác nhận người dùng trước**
   (AI client hỏi; server yêu cầu tham số `xac_nhan=true` thì mới thực thi).
2. **Chỉ người nội bộ dùng.** Server chạy trên server nội bộ, không mở port ra
   ngoài. Dùng chung tài khoản DB với backend.
3. **Tool chuyên biệt + SQL chỉ-đọc có giới hạn** (xem mục 4).
4. **Ngôn ngữ:** mô tả tool/param bằng tiếng Việt có dấu; tên tool và tên tham số
   tiếng Việt không dấu (vd `tu_ngay`, `ma_nhanvien`).

## 1. Kiến trúc

- Server Python riêng trong repo (`mcp_server/`), dùng **FastMCP** (SDK chuẩn).
- Dùng chung `app/models` (SQLAlchemy) và `DATABASE_URL` với backend — không
  viết lại mapping bảng.
- Chạy cùng Docker Compose với backend (service `mcp`), transport **stdio**
  khi chạy local trên server; chuyển sang Streamable HTTP nội bộ nếu nhiều
  máy/client cùng dùng.
- Ghi log mọi lần gọi tool (tên tool, tham số, thời gian) để kiểm tra sau.

## 2. Tool giai đoạn 1 (chỉ đọc)

✅ Đã triển khai (`backend/mcp_server/server.py`, test qua stdio OK):

| Tool | Mô tả |
|---|---|
| `tim_nhan_vien` | Tìm NV theo mã NV / tên / mã thẻ / đơn vị. Trả mã NV, tên, đơn vị, chức vụ, mã thẻ, trạng thái. |
| `xem_cham_cong` | Lượt quẹt + H1..H6 của một NV (hoặc một đơn vị) trong khoảng ngày. |
| `xem_thai_san` | Hồ sơ thai sản của NV / trong khoảng ngày. |
| `thong_ke_cham_cong` | Thống kê: thẻ chưa khớp, lượt quẹt cần xem lại, số lượt quẹt theo ngày. |
| `truy_van_sql` | SQL SELECT tự do, có giới hạn (mục 4). |

⏳ Chưa có (chờ bảng tương ứng — phần tính giờ/lương):

| Tool | Chờ |
|---|---|
| `xem_gio_cong_thang` | bảng kết quả tính giờ/lương tháng |
| `xem_lich_lam_viec` | bảng `work_calendar` (giờ đóng chuyền, lễ, disable) |
| `xem_quy_dinh` | bảng `work_rule_sets` (tạm có resource `nsdt://quy-dinh-hien-hanh` với giá trị mặc định) |

## 3. Resource (từ điển dữ liệu)

- `nsdt://schema` — mô tả các bảng, ý nghĩa từng cột, quy ước: ngày hiển thị
  `dd-mm-yyyy`, API/DB dùng ISO; mã NV, mã thẻ; đơn vị/chức vụ là danh mục.
- `nsdt://quy-dinh-hien-hanh` — bộ quy định đang áp dụng.

## 4. Tool `truy_van_sql` — giới hạn an toàn

- Chỉ câu bắt đầu bằng `SELECT`/`WITH` (không INSERT/UPDATE/DELETE/DROP...).
- Một câu duy nhất (không có `;` nối nhiều câu).
- Chỉ đọc các bảng nghiệp vụ (allowlist); cấm bảng hệ thống/tài khoản.
- `LIMIT` tối đa 500 dòng nếu câu không có LIMIT; timeout 10 giây.
- Trả kết quả dạng JSON + ghi log.

## 5. Tool giai đoạn 2 (ghi — cần xác nhận)

- `gan_the_cho_nhan_vien`, `sua_gio_vao_ra` (H1..H6), `cap_nhat_quy_dinh`...
- Mỗi tool ghi có tham số bắt buộc `xac_nhan: true`. AI client **phải hỏi người
  dùng và nhận được đồng ý** trước khi gọi với `xac_nhan=true`; gọi không có
  tham số này chỉ trả về bản xem trước (dry-run) của thay đổi.
- Chỉ thực thi khi kết nối từ mạng nội bộ.

## 6. Lộ trình

1. Thiết kế này (xong).
2. ✅ Triển khai server MCP giai đoạn 1 (chỉ đọc) — `backend/mcp_server/`, chạy
   stdio hoặc HTTP (`--http --port 8001`); service `mcp` trong docker-compose;
   test qua stdio trên DB seed + file chấm công thật 20250625.txt: 5 tool +
   2 resource đều trả đúng, `truy_van_sql` chặn được DELETE và bảng cấm.
3. Kiểm tra độ chính xác trả lời trên các câu hỏi mẫu tiếng Việt (dùng thử
   với Claude Desktop / Muse qua connector).
4. Giai đoạn 2: tool ghi + xác nhận, sau khi phần tính giờ/lương chạy ổn định.
