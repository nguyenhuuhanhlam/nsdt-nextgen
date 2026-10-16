import { HashRouter, Navigate, Route, Routes } from 'react-router-dom';

import AppShellFrame from './app-shell.jsx';
import AttendanceFiles from './pages/attendance-files.jsx';
import AttendanceImport from './pages/attendance-import.jsx';
import AttendanceReview from './pages/attendance-review.jsx';
import AttendanceUnmatched from './pages/attendance-unmatched.jsx';
import Catalogs from './pages/catalogs.jsx';
import Dashboard from './pages/dashboard.jsx';
import Employees from './pages/employees.jsx';
import Maternity from './pages/maternity.jsx';

export default function App() {
  return (
    <HashRouter>
      <AppShellFrame>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/employees" element={<Employees />} />
          <Route path="/maternity" element={<Maternity />} />
          <Route path="/catalogs/:kind" element={<Catalogs />} />
          <Route path="/attendance/import" element={<AttendanceImport />} />
          <Route path="/attendance/files" element={<AttendanceFiles />} />
          <Route path="/attendance/unmatched" element={<AttendanceUnmatched />} />
          <Route path="/attendance/review" element={<AttendanceReview />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </AppShellFrame>
    </HashRouter>
  );
}
