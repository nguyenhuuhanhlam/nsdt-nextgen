# MCP server — NSDT NextGen (giai đoạn 1: chỉ đọc)

## Chạy

```bash
cd backend
pip install fastmcp          # đã có trong requirements.txt
DATABASE_URL=sqlite:////tmp/nsdt.db python -m mcp_server.server          # stdio
DATABASE_URL=... python -m mcp_server.server --http --port 8001          # HTTP nội bộ
```

Trên server nội bộ, `DATABASE_URL` là MySQL như backend
(`mysql+pymysql://nsdt:...@db:3306/nsdt?charset=utf8mb4`).

## Tools (chỉ đọc)

| Tool | Dùng để |
|---|---|
| `tim_nhan_vien` | Tìm NV theo tên / mã NV / mã thẻ / đơn vị |
| `xem_cham_cong` | Lượt quẹt + cờ cảnh báo của 1 NV trong khoảng ngày |
| `xem_thai_san` | Hồ sơ thai sản của NV / trong khoảng ngày |
| `thong_ke_cham_cong` | Thống kê file đã nạp, thẻ chưa khớp, lượt quẹt gắn cờ |
| `truy_van_sql` | SQL SELECT tự do, có giới hạn (mục dưới) |

Chưa có vì bảng chưa tồn tại: `xem_gio_cong_thang`, `xem_lich_lam_viec`,
`xem_quy_dinh` (tạm có resource `nsdt://quy-dinh-hien-hanh` với giá trị mặc định).
Thêm khi phần tính giờ/lương và bảng `work_calendar` / `work_rule_sets` xong.

## Resources

- `nsdt://schema` — từ điển dữ liệu (bảng, cột, quy ước ngày tháng).
- `nsdt://quy-dinh-hien-hanh` — bộ quy định giờ công đang áp dụng.

## Giới hạn của `truy_van_sql`

- Chỉ câu bắt đầu bằng `SELECT`/`WITH`, một câu duy nhất.
- Cấm INSERT/UPDATE/DELETE/DROP/ALTER/CREATE/... (kiểm tra sau khi bỏ chuỗi).
- Chỉ các bảng nghiệp vụ trong allowlist (xem `SQL_BANG_CHO_PHEP`).
- Tự thêm `LIMIT 500` nếu câu không có LIMIT; timeout 10 giây.

## Log

Mọi lần gọi tool ghi log ra stderr (stdout là kênh protocol của stdio).
