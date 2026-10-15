-- Migration thủ công cho database dev đã tạo TRƯỚC commit f1f9280.
--
-- Lý do: `Base.metadata.create_all()` chỉ tạo bảng còn thiếu, KHÔNG tự thêm
-- cột mới vào bảng đã tồn tại. Nên DB cũ thiếu các cột/danh mục dưới đây.
--
-- Cách chạy (một lần duy nhất):
--   mysql -u <user> -p <ten_database> < backend/seed/migrate_20261015_employee_fields.sql
-- Nếu câu nào báo lỗi "Duplicate column name" hoặc "Table ... already exists"
-- thì câu đó đã có rồi — bỏ qua, chạy tiếp các câu còn lại.
-- Chạy xong thì nạp danh mục mới:  cd backend && python -m seed.load_sample

-- 1. Các bảng danh mục mới
CREATE TABLE IF NOT EXISTS nationalities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ethnicities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS educations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS marital_statuses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contract_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Các cột mới của bảng employees
ALTER TABLE employees
  ADD COLUMN id_issue_date DATE NULL,
  ADD COLUMN id_issue_place_id INT NULL,
  ADD COLUMN birth_place_id INT NULL,
  ADD COLUMN nationality_id INT NULL,
  ADD COLUMN ethnicity_id INT NULL,
  ADD COLUMN education_id INT NULL,
  ADD COLUMN marital_status_id INT NULL,
  ADD COLUMN contract_type_id INT NULL,
  ADD COLUMN btkt TINYINT(1) NOT NULL DEFAULT 0;

-- 3. Foreign key (không bắt buộc — app vẫn chạy khi thiếu; bỏ qua nếu báo trùng tên)
ALTER TABLE employees
  ADD CONSTRAINT fk_employees_birth_place FOREIGN KEY (birth_place_id) REFERENCES provinces (id),
  ADD CONSTRAINT fk_employees_id_issue_place FOREIGN KEY (id_issue_place_id) REFERENCES provinces (id),
  ADD CONSTRAINT fk_employees_nationality FOREIGN KEY (nationality_id) REFERENCES nationalities (id),
  ADD CONSTRAINT fk_employees_ethnicity FOREIGN KEY (ethnicity_id) REFERENCES ethnicities (id),
  ADD CONSTRAINT fk_employees_education FOREIGN KEY (education_id) REFERENCES educations (id),
  ADD CONSTRAINT fk_employees_marital_status FOREIGN KEY (marital_status_id) REFERENCES marital_statuses (id),
  ADD CONSTRAINT fk_employees_contract_type FOREIGN KEY (contract_type_id) REFERENCES contract_types (id);
