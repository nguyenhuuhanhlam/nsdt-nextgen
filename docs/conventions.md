# Quy ước dự án nsdt-nextgen

Các quy ước thống nhất cho toàn dự án. Ai làm (người hay AI) đều tuân theo.

## Ngày tháng

- **Hiển thị trên UI: luôn `dd-mm-yyyy`** (ví dụ: 14-10-2026).
- **API / database: `yyyy-mm-dd` (ISO 8601)** — frontend tự format khi hiển thị
  và parse khi nhập liệu (xem `frontend/src/lib/format.js`).
- Không dùng định dạng khác (mm/dd/yyyy, yyyy/mm/dd...) ở bất cứ đâu người dùng nhìn thấy.

## Font chữ

- **Toàn bộ app dùng font Cascadia Mono** (kể cả tiêu đề).
- Font được bundle qua gói npm `@fontsource/cascadia-mono` (weights 400/500/600/700)
  để app chạy offline trên server nội bộ, không phụ thuộc Google Fonts lúc runtime.
- Áp dụng bằng cách override 3 biến `--font-family-body/heading/code` của theme Astryx
  trong `frontend/src/app-fonts.css` (dùng `@scope ([data-astryx-theme])`, không đặt trong
  `@layer` để thắng layer `astryx-theme` của theme).
