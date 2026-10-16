// Quy ước dự án: ngày tháng HIỂN THỊ luôn ở dạng dd-mm-yyyy.
// API/backend và database trao đổi/lưu trữ dạng ISO yyyy-mm-dd.

/** yyyy-mm-dd (hoặc Date) -> dd-mm-yyyy để hiển thị. */
export function formatDate(value) {
  if (!value) return '';
  if (value instanceof Date) {
    const dd = String(value.getDate()).padStart(2, '0');
    const mm = String(value.getMonth() + 1).padStart(2, '0');
    return `${dd}-${mm}-${value.getFullYear()}`;
  }
  const m = /^(\d{4})-(\d{1,2})-(\d{1,2})/.exec(String(value));
  if (!m) return String(value);
  return `${m[3].padStart(2, '0')}-${m[2].padStart(2, '0')}-${m[1]}`;
}

/** dd-mm-yyyy (nhập liệu) -> yyyy-mm-dd (gửi API). Trả nguyên chuỗi nếu sai định dạng. */
export function parseDateInput(str) {
  const m = /^(\d{1,2})-(\d{1,2})-(\d{4})$/.exec(String(str).trim());
  if (!m) return str;
  return `${m[3]}-${m[2].padStart(2, '0')}-${m[1].padStart(2, '0')}`;
}

/** ISO datetime -> dd-mm-yyyy hh:mm để hiển thị. */
export function formatDateTime(value) {
  if (!value) return '';
  const m = /^(\d{4})-(\d{1,2})-(\d{1,2})[T ](\d{1,2}):(\d{1,2})/.exec(String(value));
  if (!m) return String(value);
  return `${m[3].padStart(2, '0')}-${m[2].padStart(2, '0')}-${m[1]} ${m[4].padStart(2, '0')}:${m[5].padStart(2, '0')}`;
}
