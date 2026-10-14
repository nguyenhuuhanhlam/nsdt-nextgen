import { HashRouter, Navigate, Route, Routes } from 'react-router-dom';

import AppShellFrame from './app-shell.jsx';
import Catalogs from './pages/catalogs.jsx';
import Dashboard from './pages/dashboard.jsx';
import Employees from './pages/employees.jsx';

export default function App() {
  return (
    <HashRouter>
      <AppShellFrame>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/employees" element={<Employees />} />
          <Route path="/catalogs/:kind" element={<Catalogs />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </AppShellFrame>
    </HashRouter>
  );
}
