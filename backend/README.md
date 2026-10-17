# Backend — NSDT NextGen

Python + FastAPI. Khởi đầu với module quản lý nhân viên (dựng lại từ bảng
`ns_nhansu` của hệ cũ).

## Chạy bằng docker-compose (khuyên dùng)

Từ thư mục gốc repo:

```bash
docker compose up --build
```

- API: http://localhost:8000
- Swagger UI: http://localhost:8000/docs
- Health check: http://localhost:8000/health
- MySQL 8: localhost:3306 (db `nsdt`, user/pass `nsdt`/`nsdt` mặc định —
  đổi qua biến môi trường `MYSQL_PASSWORD` khi triển khai thật)

## Chạy local không qua Docker

```bash
cd backend
python -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env   # sửa DATABASE_URL cho đúng
uvicorn app.main:app --reload
```

## Tạo bảng lần đầu

```bash
python -c "from app.db.base import Base; from app.db.session import engine; import app.models; Base.metadata.create_all(engine)"
```

(về sau sẽ dùng Alembic migration khi schema ổn định)

## Nạp dữ liệu mẫu (seed)

`seed/sample_data.json` chứa toàn bộ hồ sơ nhân viên (~1027) + danh mục liên quan trích từ
hệ cũ, **đã ẩn danh hóa** (tên là dữ liệu giả theo giới tính, không gồm
CMND/địa chỉ) — an toàn để commit. Nạp vào DB phát triển:

```bash
cd backend && python -m seed.load_sample
```

Chạy lại nhiều lần vẫn an toàn (bản ghi đã tồn tại sẽ được bỏ qua).
Muốn trích xuất lại từ server cũ: xem `seed/extract_sample.py` (chỉ đọc).
