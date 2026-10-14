# Kiến trúc nsdt-nextgen

Tài liệu quyết định kiến trúc — chốt ngày 2026-10-14.

## Bối cảnh

Tái tạo và nâng cấp hệ thống tính lương công nhân từ dự án cũ chạy PHP
(phiên bản cũ) + MySQL 5.1.73. Hệ thống đọc file dữ liệu thời gian từ các
máy chấm công, tính giờ làm việc và tính lương.

Database cũ: `nskl_section_b` — 71 tables, 37 functions, 6 procedures
(xem `docs/database/schema-nskl_section_b.md`).

## Quyết định

| Thành phần | Lựa chọn | Lý do |
|---|---|---|
| Backend | Python + FastAPI | Xử lý dữ liệu theo lô (đọc file chấm công, tính lương, xuất Excel) là sở trường của Python; dễ viết, dễ test, dễ bảo trì dài hạn |
| Database | MySQL 8 (cài mới trên server nội bộ) | Giữ MySQL để tận dụng cấu trúc và câu SQL cũ, giảm công chuyển đổi; chỉ nâng version |
| Frontend | React + Astryx | Tái dùng kinh nghiệm và component từ dự án ahri-apps |
| Deploy | Docker Compose trên server nội bộ | Một file compose chạy cả app + MySQL 8; backup/nâng cấp gọn |
| Ngôn ngữ loại bỏ | Rust, PHP cũ | Rust overkill cho bài toán tính lương theo lô; PHP cũ là thứ cần thay thế |

## Nguyên tắc triển khai

1. **Logic nghiệp vụ đưa lên tầng Python.** 37 functions + 6 procedures hiện
   nằm trong MySQL sẽ được viết lại thành code Python có unit test, dễ đọc
   dễ sửa. Database chỉ giữ vai trò lưu trữ.
2. **Module đọc file chấm công tách riêng.** Mỗi định dạng file máy chấm công
   có một parser riêng, tất cả đưa về một cấu trúc dữ liệu chung trước khi
   tính toán — thêm máy mới chỉ cần thêm parser.
3. **Chạy nội bộ hoàn toàn.** Không phụ thuộc dịch vụ cloud; backup database
   định kỳ trên server nội bộ.
4. **Người bảo trì duy nhất** là tác giả dự án — ưu tiên code rõ ràng, tài
   liệu tiếng Việt, hơn là tối ưu hiệu năng.

## Yêu cầu quy mô

- Khoảng **10.000 công nhân** cần quản lý.
- Mỗi công nhân mỗi ngày có thể phát sinh **nhiều bản ghi thời gian**
  (nhiều lần chấm công vào/ra, tăng ca...) → ước tính hàng chục nghìn
  bản ghi/ngày, vài triệu bản ghi/tháng.

Hệ quả cho thiết kế — ưu tiên thuật toán và giải pháp tối ưu ngay từ đầu:

1. **Import file chấm công theo lô** (bulk insert), tuyệt đối không insert
   từng dòng.
2. **Bảng bản ghi thời gian**: index theo (worker_id, thời điểm chấm);
   cân nhắc partition theo tháng để query và dọn dữ liệu cũ nhanh.
3. **Tính lương chạy batch theo tháng**, idempotent (chạy lại vẫn đúng),
   có thể chia nhỏ theo đơn vị/phân xưởng để chạy song song khi cần.
4. **Tránh N+1 query và vòng lặp từng công nhân** trong code Python;
   tính toán theo tập hợp (set-based / vector hóa) rồi mới ghi kết quả.
5. **Lưu trữ lạnh** dữ liệu các năm cũ, giữ database vận hành gọn nhẹ.

## Cấu trúc repo dự kiến

```
nsdt-nextgen/
├── docs/                  # tài liệu: schema DB cũ, kiến trúc, worklog
│   └── database/
├── backend/               # Python + FastAPI
├── frontend/              # React + Astryx
└── docker-compose.yml     # app + MySQL 8 cho server nội bộ
```

## Việc tiếp theo

- Phân tích sâu schema DB cũ để thiết kế lại data model cho MySQL 8.
- Dựng khung backend (FastAPI) và docker-compose.
- Dựng khung frontend (React + Astryx).
