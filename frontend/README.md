# Frontend — NSDT NextGen

React 19 + Vite + Astryx (@astryxdesign/core). Khung giả định cho UI quản lý
chấm công / tính lương — hiện có Dashboard và Quản lý nhân viên (dữ liệu
mock, xem `src/lib/mock-data.js`).

## Chạy dev

```bash
cd frontend
npm install
npm run dev        # http://localhost:5173 (proxy /api về backend :8000)
```

## Build

```bash
npm run build      # ra thư mục dist/
```

## Kết nối backend thật

Copy `.env.example` thành `.env` và sửa `VITE_API_BASE_URL`:

```
VITE_API_BASE_URL=http://localhost:8000
```

Client API ở `src/lib/api.js` (sẵn các hàm employees). Trang nhân viên hiện
dùng mock — TODO: chuyển sang `employeesApi` khi backend chạy ổn định.

## Quy ước UI (theo dự án)

- Heading trang: `Heading level={4}` in đậm.
- Tiêu đề dialog: component dùng chung `src/components/dialog-title.jsx`.
- Bảng: `Table` data-driven, cột dùng `pixel()` / `proportional()`.
- Không dùng thẻ `<div>`/`<span>` thô — xem `AGENTS.md` (quy tắc Astryx).
