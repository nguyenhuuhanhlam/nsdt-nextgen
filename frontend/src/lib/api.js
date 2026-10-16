// API client cho backend FastAPI.
// Base URL lấy từ biến môi trường VITE_API_BASE_URL (xem .env.example).
// Khi chạy `vite dev`, vite.config.js đã proxy /api về localhost:8000.

const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? '';

async function request(path, options = {}) {
  const res = await fetch(`${BASE_URL}${path}`, {
    headers: { 'Content-Type': 'application/json', ...(options.headers ?? {}) },
    ...options,
  });
  if (!res.ok) {
    const body = await res.json().catch(() => ({}));
    throw new Error(body.detail || `Lỗi ${res.status}`);
  }
  if (res.status === 204) return null;
  return res.json();
}

export const api = {
  get: (path) => request(path),
  post: (path, body) => request(path, { method: 'POST', body: JSON.stringify(body) }),
  put: (path, body) => request(path, { method: 'PUT', body: JSON.stringify(body) }),
  patch: (path, body) => request(path, { method: 'PATCH', body: JSON.stringify(body) }),
  del: (path) => request(path, { method: 'DELETE' }),
  // Upload file (multipart) — không set Content-Type để browser tự thêm boundary.
  upload: async (path, formData) => {
    const res = await fetch(`${BASE_URL}${path}`, { method: 'POST', body: formData });
    if (!res.ok) {
      const body = await res.json().catch(() => ({}));
      throw new Error(body.detail || `Lỗi ${res.status}`);
    }
    return res.json();
  },
};

export const employeesApi = {
  list: ({ page = 1, pageSize = 50, q = '', departmentId = null, isActive = null } = {}) => {
    const params = new URLSearchParams({ page, page_size: pageSize });
    if (q) params.set('q', q);
    if (departmentId != null) params.set('department_id', departmentId);
    if (isActive != null) params.set('is_active', isActive);
    return api.get(`/api/v1/employees?${params}`);
  },
  get: (id) => api.get(`/api/v1/employees/${id}`),
  create: (payload) => api.post('/api/v1/employees', payload),
  update: (id, payload) => api.patch(`/api/v1/employees/${id}`, payload),
  deactivate: (id) => api.del(`/api/v1/employees/${id}`),
  listDepartments: () => api.get('/api/v1/departments'),
  listPositions: () => api.get('/api/v1/positions'),
  listJobTitles: () => api.get('/api/v1/job-titles'),
};

// CRUD cho 5 bảng danh mục: departments, positions, job-titles, provinces, maternity-types
const catalogCrud = (path) => ({
  list: () => api.get(`/api/v1/${path}`),
  create: (name) => api.post(`/api/v1/${path}`, { name }),
  update: (id, name) => api.put(`/api/v1/${path}/${id}`, { name }),
  remove: (id) => api.del(`/api/v1/${path}/${id}`),
});

export const catalogsApi = {
  departments: catalogCrud('departments'),
  positions: catalogCrud('positions'),
  'job-titles': catalogCrud('job-titles'),
  provinces: catalogCrud('provinces'),
  'maternity-types': catalogCrud('maternity-types'),
  nationalities: catalogCrud('nationalities'),
  ethnicities: catalogCrud('ethnicities'),
  educations: catalogCrud('educations'),
  'marital-statuses': catalogCrud('marital-statuses'),
  'contract-types': catalogCrud('contract-types'),
};

export const maternityApi = {
  listTypes: () => api.get('/api/v1/maternity-types'),
  list: ({ page = 1, pageSize = 50, q = '', employeeId = null, typeId = null } = {}) => {
    const params = new URLSearchParams({ page, page_size: pageSize });
    if (q) params.set('q', q);
    if (employeeId != null) params.set('employee_id', employeeId);
    if (typeId != null) params.set('maternity_type_id', typeId);
    return api.get(`/api/v1/maternity-leaves?${params}`);
  },
  create: (payload) => api.post('/api/v1/maternity-leaves', payload),
  update: (id, payload) => api.patch(`/api/v1/maternity-leaves/${id}`, payload),
  remove: (id) => api.del(`/api/v1/maternity-leaves/${id}`),
};

// Nạp file chấm công: parse -> báo cáo kiểm tra -> xác nhận nạp.
export const attendanceApi = {
  parse: (file) => {
    const fd = new FormData();
    fd.append('file', file);
    return api.upload('/api/v1/attendance/parse', fd);
  },
  importFile: ({ uploadId, confirmedDates, replaceExisting = false }) =>
    api.post('/api/v1/attendance/import', {
      upload_id: uploadId,
      confirmed_dates: confirmedDates,
      replace_existing: replaceExisting,
    }),
  reloadFile: (id) => api.post(`/api/v1/attendance/files/${id}/reload`),
  files: ({ page = 1, pageSize = 20, q = '', status = '' } = {}) => {
    const params = new URLSearchParams({ page, page_size: pageSize });
    if (q) params.set('q', q);
    if (status) params.set('status', status);
    return api.get(`/api/v1/attendance/files?${params}`);
  },
  unmatched: () => api.get('/api/v1/attendance/unmatched'),
  assignCard: ({ cardId, employeeId }) =>
    api.post('/api/v1/attendance/unmatched/assign', {
      card_id: cardId,
      employee_id: employeeId,
    }),
  taps: ({ page = 1, pageSize = 50, tapDate = '', cardId = '', fileId = '', flag = '' } = {}) => {
    const params = new URLSearchParams({ page, page_size: pageSize });
    if (tapDate) params.set('tap_date', tapDate);
    if (cardId) params.set('card_id', cardId);
    if (fileId) params.set('file_id', fileId);
    if (flag) params.set('flag', flag);
    return api.get(`/api/v1/attendance/taps?${params}`);
  },
};
