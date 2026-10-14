# Quy ước dự án nsdt-nextgen

Các quy ước thống nhất cho toàn dự án. Ai làm (người hay AI) đều tuân theo.

## Ngày tháng

- **Hiển thị trên UI: luôn `dd-mm-yyyy`** (ví dụ: 14-10-2026).
- **API / database: `yyyy-mm-dd` (ISO 8601)** — frontend tự format khi hiển thị
  và parse khi nhập liệu (xem `frontend/src/lib/format.js`).
- Không dùng định dạng khác (mm/dd/yyyy, yyyy/mm/dd...) ở bất cứ đâu người dùng nhìn thấy.
