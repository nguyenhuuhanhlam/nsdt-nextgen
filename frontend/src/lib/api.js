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
  patch: (path, body) => request(path, { method: 'PATCH', body: JSON.stringify(body) }),
  del: (path) => request(path, { method: 'DELETE' }),
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
