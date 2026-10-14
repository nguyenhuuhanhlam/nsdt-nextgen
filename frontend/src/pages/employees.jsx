import { Button } from '@astryxdesign/core/Button';
import { Dialog } from '@astryxdesign/core/Dialog';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { Selector } from '@astryxdesign/core/Selector';
import { StatusDot } from '@astryxdesign/core/StatusDot';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useMemo, useState } from 'react';

import DialogTitle from '../components/dialog-title.jsx';
import { makeMockEmployees } from '../lib/mock-data.js';

// TODO: thay mock bằng employeesApi (src/lib/api.js) khi backend chạy.
const ALL = makeMockEmployees(60);
const PAGE_SIZE = 15;

const DEPARTMENTS = [...new Set(ALL.map((e) => e.department.name))].sort();

function rows(list) {
  return list.map((e) => ({
    ...e,
    department_name: e.department.name,
    position_name: e.position.name,
  }));
}

function EmployeeDialog({ employee, onClose, onSave }) {
  const [form, setForm] = useState(() => ({
    employee_code: employee?.employee_code ?? '',
    first_name: employee?.first_name ?? '',
    last_name: employee?.last_name ?? '',
    gender: employee?.gender ?? 'Nam',
    birth_date: employee?.birth_date ?? '',
    department_name: employee?.department.name ?? DEPARTMENTS[0],
    position_name: employee?.position.name ?? 'Công nhân',
    join_date: employee?.join_date ?? '',
    salary_coefficient: employee?.salary_coefficient ?? '1.00',
  }));
  const set = (k) => (v) => setForm((f) => ({ ...f, [k]: v }));
  const isEdit = Boolean(employee);

  return (
    <Dialog isOpen onOpenChange={(open) => !open && onClose()} width={560} maxHeight="85dvh">
      <DialogTitle onClose={onClose}>
        {isEdit ? 'Sửa thông tin nhân viên' : 'Thêm nhân viên'}
      </DialogTitle>
      <VStack gap={3} padding={4}>
        <HStack gap={3}>
          <TextInput label="Mã nhân viên" value={form.employee_code} onChange={set('employee_code')} isDisabled={isEdit} isRequired width="100%" />
          <Selector label="Giới tính" value={form.gender} onChange={set('gender')} options={['Nam', 'Nữ']} width="100%" />
        </HStack>
        <HStack gap={3}>
          <TextInput label="Họ" value={form.first_name} onChange={set('first_name')} isRequired width="100%" />
          <TextInput label="Tên" value={form.last_name} onChange={set('last_name')} isRequired width="100%" />
        </HStack>
        <HStack gap={3}>
          <TextInput label="Ngày sinh" value={form.birth_date} onChange={set('birth_date')} placeholder="yyyy-mm-dd" width="100%" />
          <TextInput label="Ngày vào làm" value={form.join_date} onChange={set('join_date')} placeholder="yyyy-mm-dd" width="100%" />
        </HStack>
        <HStack gap={3}>
          <Selector label="Đơn vị" value={form.department_name} onChange={set('department_name')} options={DEPARTMENTS} hasSearch width="100%" />
          <TextInput label="Chức vụ" value={form.position_name} onChange={set('position_name')} width="100%" />
        </HStack>
        <TextInput label="Hệ số lương" value={form.salary_coefficient} onChange={set('salary_coefficient')} width={160} />
        <HStack gap={2} justify="end">
          <Button variant="ghost" onClick={onClose}>Hủy</Button>
          <Button variant="primary" onClick={() => onSave(form)}>Lưu</Button>
        </HStack>
      </VStack>
    </Dialog>
  );
}

export default function Employees() {
  const [data, setData] = useState(ALL);
  const [q, setQ] = useState('');
  const [dept, setDept] = useState('');
  const [page, setPage] = useState(1);
  const [dialog, setDialog] = useState(null); // null | {mode:'add'} | {mode:'edit', employee}

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase();
    return data.filter(
      (e) =>
        (!needle ||
          e.employee_code.toLowerCase().includes(needle) ||
          e.full_name.toLowerCase().includes(needle)) &&
        (!dept || e.department.name === dept),
    );
  }, [data, q, dept]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const pageRows = rows(filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE));

  const closeDialog = () => setDialog(null);

  const saveEmployee = (form) => {
    if (dialog?.mode === 'add') {
      const n = Math.max(...data.map((e) => e.id)) + 1;
      setData((d) => [
        ...d,
        {
          id: n,
          ...form,
          full_name: `${form.first_name} ${form.last_name}`.trim(),
          card_id: null,
          department: { id: 0, name: form.department_name },
          position: { id: 0, name: form.position_name },
          job_title: null,
          is_active: true,
          terminated_date: null,
        },
      ]);
    } else if (dialog?.mode === 'edit') {
      setData((d) =>
        d.map((e) =>
          e.id === dialog.employee.id
            ? {
                ...e,
                ...form,
                full_name: `${form.first_name} ${form.last_name}`.trim(),
                department: { ...e.department, name: form.department_name },
                position: { ...e.position, name: form.position_name },
              }
            : e,
        ),
      );
    }
    closeDialog();
    setPage(1);
  };

  const deactivate = (employee) => {
    if (!window.confirm(`Cho nhân viên ${employee.full_name} (${employee.employee_code}) nghỉ việc?`)) return;
    setData((d) => d.map((e) => (e.id === employee.id ? { ...e, is_active: false } : e)));
  };

  const columns = [
    { key: 'employee_code', header: 'Mã NV', width: pixel(100) },
    { key: 'full_name', header: 'Họ tên', width: proportional(2) },
    { key: 'gender', header: 'Giới tính', width: pixel(90), align: 'center' },
    { key: 'birth_date', header: 'Ngày sinh', width: pixel(110) },
    { key: 'department_name', header: 'Đơn vị', width: proportional(2) },
    { key: 'position_name', header: 'Chức vụ', width: proportional(1) },
    { key: 'join_date', header: 'Ngày vào làm', width: pixel(110) },
    { key: 'salary_coefficient', header: 'HSL', width: pixel(70), align: 'center' },
    {
      key: 'is_active',
      header: 'Trạng thái',
      width: pixel(120),
      align: 'center',
      renderCell: (row) => (
        <StatusDot
          variant={row.is_active ? 'success' : 'error'}
          label={row.is_active ? 'Đang làm việc' : 'Đã nghỉ việc'}
          tooltip={row.is_active ? 'Đang làm việc' : 'Đã nghỉ việc'}
        />
      ),
    },
    {
      key: 'actions',
      header: 'Thao tác',
      width: pixel(170),
      renderCell: (row) => (
        <HStack gap={2}>
          <Button size="sm" variant="ghost" onClick={() => setDialog({ mode: 'edit', employee: row })}>
            Sửa
          </Button>
          {row.is_active && (
            <Button size="sm" variant="ghost" onClick={() => deactivate(row)}>
              Nghỉ việc
            </Button>
          )}
        </HStack>
      ),
    },
  ];

  return (
    <VStack gap={4}>
      <HStack justify="space-between" align="center">
        <Heading level={4} weight="bold">Quản lý nhân viên</Heading>
        <Button variant="primary" onClick={() => setDialog({ mode: 'add' })}>
          Thêm nhân viên
        </Button>
      </HStack>

      <HStack gap={3} align="end">
        <TextInput
          label="Tìm kiếm"
          isLabelHidden
          placeholder="Tìm theo mã NV hoặc họ tên..."
          value={q}
          onChange={(v) => { setQ(v); setPage(1); }}
          startIcon="search"
          hasClear
          width={320}
        />
        <Selector
          label="Đơn vị"
          isLabelHidden
          placeholder="Tất cả đơn vị"
          value={dept}
          onChange={(v) => { setDept(v ?? ''); setPage(1); }}
          options={DEPARTMENTS}
          hasSearch
          hasClear
          width={240}
        />
        <Text type="supporting">{filtered.length} nhân viên</Text>
      </HStack>

      <Table
        data={pageRows}
        columns={columns}
        idKey="id"
        density="balanced"
        textOverflow="truncate"
        rowIndexStart={(page - 1) * PAGE_SIZE + 1}
        rowCount={filtered.length}
      />

      <HStack gap={3} align="center" justify="end">
        <Text type="supporting">Trang {page} / {pageCount}</Text>
        <Button size="sm" variant="ghost" isDisabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
          Trước
        </Button>
        <Button size="sm" variant="ghost" isDisabled={page >= pageCount} onClick={() => setPage((p) => p + 1)}>
          Sau
        </Button>
      </HStack>

      {dialog && (
        <EmployeeDialog
          employee={dialog.mode === 'edit' ? dialog.employee : null}
          onClose={closeDialog}
          onSave={saveEmployee}
        />
      )}
    </VStack>
  );
}
