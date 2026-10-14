// Dữ liệu giả cho khung UI — TODO: thay bằng gọi API backend khi backend chạy.
// Hình dáng record bám theo model Employee của backend (app/models/employee.py).

const HO = [
  'Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng',
  'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý',
];
const TEN = [
  'An', 'Bình', 'Chi', 'Dũng', 'Hà', 'Hùng', 'Lan', 'Linh', 'Long', 'Minh',
  'Nam', 'Ngọc', 'Phúc', 'Quân', 'Thảo', 'Thu', 'Trang', 'Tuấn', 'Tú', 'Yến',
];
const DON_VI = [
  'Phân xưởng May 1', 'Phân xưởng May 2', 'Phân xưởng Cắt', 'Phân xưởng Hoàn thiện',
  'Kho nguyên liệu', 'Phòng HCNS', 'Phòng Kế toán',
];
const CHUC_VU = ['Công nhân', 'Tổ trưởng', 'Chuyền trưởng', 'Quản đốc', 'Nhân viên', 'Kế toán'];

function seededRandom(seed) {
  let s = seed;
  return () => {
    s = (s * 1103515245 + 12345) % 2147483648;
    return s / 2147483648;
  };
}

export function makeMockEmployees(count = 60) {
  const rand = seededRandom(42);
  const pick = (arr) => arr[Math.floor(rand() * arr.length)];
  return Array.from({ length: count }, (_, i) => {
    const n = i + 1;
    const firstName = pick(HO);
    const lastName = pick(TEN);
    const active = rand() > 0.08;
    return {
      id: n,
      employee_code: `NV${String(n).padStart(5, '0')}`,
      card_id: 100000 + n,
      first_name: firstName,
      last_name: lastName,
      full_name: `${firstName} ${lastName}`,
      gender: rand() > 0.4 ? 'Nữ' : 'Nam',
      birth_date: `19${85 + Math.floor(rand() * 15)}-0${1 + Math.floor(rand() * 9)}-1${Math.floor(rand() * 9)}`,
      department: { id: (n % DON_VI.length) + 1, name: DON_VI[n % DON_VI.length] },
      position: { id: (n % CHUC_VU.length) + 1, name: CHUC_VU[n % CHUC_VU.length] },
      job_title: null,
      join_date: `202${Math.floor(rand() * 4)}-0${1 + Math.floor(rand() * 9)}-15`,
      salary_coefficient: (1.5 + rand() * 2).toFixed(2),
      is_active: active,
      terminated_date: active ? null : '2024-06-30',
    };
  });
}
