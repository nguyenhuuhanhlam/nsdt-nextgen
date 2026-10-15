import { Button } from '@astryxdesign/core/Button';
import { ContextMenu } from '@astryxdesign/core/ContextMenu';
import { Dialog } from '@astryxdesign/core/Dialog';
import { DropdownMenu } from '@astryxdesign/core/DropdownMenu';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { IconButton } from '@astryxdesign/core/IconButton';
import { Selector } from '@astryxdesign/core/Selector';
import { StatusDot } from '@astryxdesign/core/StatusDot';
import { Switch } from '@astryxdesign/core/Switch';
import { Tab, TabList } from '@astryxdesign/core/TabList';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useEffect, useMemo, useRef, useState } from 'react';

import DialogTitle from '../components/dialog-title.jsx';
import { EditIcon, PlusIcon } from '../components/icons.jsx';
import { formatDate, parseDateInput } from '../lib/format.js';
import { makeMockCatalogs, makeMockEmployees } from '../lib/mock-data.js';

// TODO: thay mock bằng employeesApi (src/lib/api.js) khi backend chạy.
const ALL = makeMockEmployees(60);
const PAGE_SIZE = 15;

const DEPARTMENTS = [...new Set(ALL.map((e) => e.department.name))].sort();
const JOB_TITLES = makeMockCatalogs()['job-titles'].map((j) => j.name).sort();

function rows(list) {
  return list.map((e) => ({
    ...e,
    department_name: e.department.name,
    position_name: e.position.name,
    job_title_name: e.job_title?.name ?? '',
  }));
}


/**
 * Các tác vụ trên một nhân viên — DÙNG CHUNG cho cả hai nơi:
 * - chuột phải lên row (ContextMenu)
 * - nút menu ở cột Thao tác (DropdownMenu)
 * Sau này thêm tác vụ mới (xem chấm công, bảng lương, ...) chỉ cần thêm vào đây.
 * (Cho nghỉ việc hiện nằm trong tab "Nghỉ việc" của dialog Sửa, không ở menu.)
 */
function employeeMenuItems(emp, { onEdit }) {
  if (!emp) return [];
  return [
    { id: 'edit', label: 'Sửa thông tin', icon: <EditIcon />, onClick: () => onEdit(emp) },
    // TODO: thêm các tác vụ tương lai vào đây
  ];
}

function todayISO() {
  return new Date().toISOString().slice(0, 10);
}

function EmployeeDialog({ employee, onClose, onSave }) {
  const isEdit = Boolean(employee);
  // Tab đang mở. Sau này thêm tác vụ mới = thêm Tab + panel tương ứng ở đây.
  const [tab, setTab] = useState('info');
  const [form, setForm] = useState(() => ({
    employee_code: employee?.employee_code ?? '',
    card_id: employee?.card_id != null ? String(employee.card_id) : '',
    first_name: employee?.first_name ?? '',
    last_name: employee?.last_name ?? '',
    gender: employee?.gender ?? 'Nam',
    birth_date: employee?.birth_date ?? '',
    department_name: employee?.department.name ?? DEPARTMENTS[0],
    position_name: employee?.position.name ?? 'Công nhân',
    job_title_name: employee?.job_title?.name ?? '',
    join_date: employee?.join_date ?? '',
    salary_coefficient: employee?.salary_coefficient ?? '1.00',
    is_active: employee?.is_active ?? true,
    terminated_date: employee?.terminated_date ?? '',
  }));
  const set = (k) => (v) => setForm((f) => {
    const next = { ...f, [k]: v };
    // Tắt "đang làm việc" mà chưa có ngày nghỉ → mặc định hôm nay.
    if (k === 'is_active' && v === false && !next.terminated_date) {
      next.terminated_date = todayISO();
    }
    return next;
  });

  return (
    <Dialog isOpen onOpenChange={(open) => !open && onClose()} width={600} maxHeight="85dvh">
      <DialogTitle onClose={onClose}>
        {isEdit ? 'Sửa thông tin nhân viên' : 'Thêm nhân viên'}
      </DialogTitle>
      <TabList value={tab} onChange={setTab} hasDivider>
        <Tab value="info" label="Thông tin chung" />
        {isEdit && <Tab value="leave" label="Nghỉ việc" />}
        {/* TODO: thêm tab tác vụ tương lai ở đây */}
      </TabList>
      <VStack gap={3} padding={4}>
        {tab === 'info' && (
          <>
            <HStack gap={3}>
              <TextInput label="Mã nhân viên" value={form.employee_code} onChange={set('employee_code')} isDisabled={isEdit} isRequired width="100%" />
              <Selector label="Giới tính" value={form.gender} onChange={set('gender')} options={['Nam', 'Nữ']} width="100%" />
            </HStack>
            <HStack gap={3}>
              <TextInput label="Họ" value={form.first_name} onChange={set('first_name')} isRequired width="100%" />
              <TextInput label="Tên" value={form.last_name} onChange={set('last_name')} isRequired width="100%" />
            </HStack>
            <HStack gap={3}>
              <TextInput label="Ngày sinh" value={formatDate(form.birth_date)} onChange={(v) => set('birth_date')(parseDateInput(v))} placeholder="dd-mm-yyyy" width="100%" />
              <TextInput label="Ngày vào làm" value={formatDate(form.join_date)} onChange={(v) => set('join_date')(parseDateInput(v))} placeholder="dd-mm-yyyy" width="100%" />
            </HStack>
            <HStack gap={3}>
              <Selector label="Đơn vị" value={form.department_name} onChange={set('department_name')} options={DEPARTMENTS} hasSearch width="100%" />
              <TextInput label="Chức vụ" value={form.position_name} onChange={set('position_name')} width="100%" />
            </HStack>
            <HStack gap={3}>
              <TextInput label="Mã thẻ (thẻ từ)" value={form.card_id} onChange={set('card_id')} placeholder="Số thẻ chấm công" width="100%" />
              <Selector label="Công việc" value={form.job_title_name} onChange={set('job_title_name')} options={JOB_TITLES} hasSearch width="100%" />
            </HStack>
            <TextInput label="Hệ số lương" value={form.salary_coefficient} onChange={set('salary_coefficient')} width={160} />
          </>
        )}
        {tab === 'leave' && isEdit && (
          <>
            <HStack gap={2} align="center">
              <StatusDot
                variant={form.is_active ? 'success' : 'error'}
                label={form.is_active ? 'Đang làm việc' : 'Đã nghỉ việc'}
                tooltip={form.is_active ? 'Đang làm việc' : 'Đã nghỉ việc'}
              />
              <Text>{form.is_active ? 'Nhân viên đang làm việc.' : `Nhân viên đã nghỉ việc${form.terminated_date ? ` từ ngày ${formatDate(form.terminated_date)}` : ''}.`}</Text>
            </HStack>
            <Switch
              label="Đang làm việc"
              value={form.is_active}
              onChange={set('is_active')}
              description="Tắt để cho nhân viên nghỉ việc. Có thể bật lại bất cứ lúc nào."
            />
            {!form.is_active && (
              <TextInput
                label="Ngày nghỉ việc"
                value={formatDate(form.terminated_date)}
                onChange={(v) => set('terminated_date')(parseDateInput(v))}
                placeholder="dd-mm-yyyy"
                width={200}
              />
            )}
          </>
        )}
      </VStack>
      <HStack gap={2} justify="end" padding={4}>
        <Button variant="ghost" label="Hủy" onClick={onClose}>Hủy</Button>
        <Button variant="primary" label="Lưu" onClick={() => onSave(form)}>Lưu</Button>
      </HStack>
    </Dialog>
  );
}

export default function Employees() {
  const [data, setData] = useState(ALL);
  const [q, setQ] = useState('');
  const [dept, setDept] = useState('');
  const [page, setPage] = useState(1);
  const [dialog, setDialog] = useState(null); // null | {mode:'add'} | {mode:'edit', employee}
  // Nhân viên đang được mở context menu bằng chuột phải lên row.
  const [ctxEmployee, setCtxEmployee] = useState(null);
  const tableWrapRef = useRef(null);

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase();
    return data.filter(
      (e) =>
        (!needle ||
          e.employee_code.toLowerCase().includes(needle) ||
          e.full_name.toLowerCase().includes(needle) ||
          String(e.card_id ?? '').includes(needle)) &&
        (!dept || e.department.name === dept),
    );
  }, [data, q, dept]);

  const pageCount = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const pageRows = rows(filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE));

  // Bắt row được chuột phải: native listener trên wrapper chạy trước khi
  // ContextMenu mở menu, nên menu luôn hiện đúng tác vụ của row đó.
  const pageRowsRef = useRef(pageRows);
  pageRowsRef.current = pageRows;
  useEffect(() => {
    const el = tableWrapRef.current;
    if (!el) return;
    const handler = (e) => {
      const tr = e.target?.closest?.('tbody tr');
      const rowsNow = pageRowsRef.current;
      if (!tr || !el.contains(tr)) {
        setCtxEmployee(null);
        return;
      }
      const idx = tr.sectionRowIndex;
      setCtxEmployee(idx >= 0 && idx < rowsNow.length ? rowsNow[idx] : null);
    };
    el.addEventListener('contextmenu', handler);
    return () => el.removeEventListener('contextmenu', handler);
  }, []);

  const closeDialog = () => setDialog(null);

  const saveEmployee = (form) => {
    const base = {
      first_name: form.first_name,
      last_name: form.last_name,
      full_name: `${form.first_name} ${form.last_name}`.trim(),
      card_id: form.card_id.trim() === '' ? null : Number(form.card_id),
      gender: form.gender,
      birth_date: form.birth_date || null,
      department: { id: 0, name: form.department_name },
      position: { id: 0, name: form.position_name },
      job_title: form.job_title_name ? { id: 0, name: form.job_title_name } : null,
      join_date: form.join_date || null,
      salary_coefficient: form.salary_coefficient,
      is_active: form.is_active,
      terminated_date: form.is_active ? null : form.terminated_date || null,
    };
    if (dialog?.mode === 'add') {
      const n = Math.max(...data.map((e) => e.id)) + 1;
      setData((d) => [...d, { id: n, employee_code: form.employee_code, ...base }]);
    } else if (dialog?.mode === 'edit') {
      setData((d) =>
        d.map((e) => (e.id === dialog.employee.id ? { ...e, ...base } : e)),
      );
    }
    closeDialog();
    setPage(1);
  };

  const columns = [
    { key: 'employee_code', header: 'Mã NV', width: pixel(100) },
    { key: 'card_id', header: 'Mã thẻ', width: pixel(100) },
    { key: 'full_name', header: 'Họ tên', width: proportional(2) },
    { key: 'gender', header: 'Giới tính', width: pixel(90), align: 'center' },
    { key: 'birth_date', header: 'Ngày sinh', width: pixel(110), renderCell: (row) => formatDate(row.birth_date) },
    { key: 'department_name', header: 'Đơn vị', width: proportional(2) },
    { key: 'position_name', header: 'Chức vụ', width: proportional(1) },
    { key: 'job_title_name', header: 'Công việc', width: proportional(1) },
    { key: 'join_date', header: 'Ngày vào làm', width: pixel(110), renderCell: (row) => formatDate(row.join_date) },
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
      width: pixel(96),
      align: 'center',
      renderCell: (row) => (
        <DropdownMenu
          label={`Tác vụ của ${row.full_name}`}
          items={employeeMenuItems(row, {
            onEdit: (emp) => setDialog({ mode: 'edit', employee: emp }),
          })}
          renderTrigger={(props) => (
            <IconButton
              icon={<EditIcon />}
              label="Mở menu tác vụ"
              tooltip="Tác vụ"
              variant="ghost"
              size="sm"
              {...props}
            />
          )}
        />
      ),
    },
  ];

  return (
    <VStack gap={4}>
      <HStack justify="between" align="center" width="100%">
        <Heading level={4} weight="bold">Quản lý nhân viên</Heading>
        <Button variant="primary" label="Thêm nhân viên" icon={<PlusIcon />} onClick={() => setDialog({ mode: 'add' })}>
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

      <VStack ref={tableWrapRef} gap={3} paddingBlockStart={4}>
        <ContextMenu
          label="Tác vụ nhân viên"
          items={employeeMenuItems(ctxEmployee, {
            onEdit: (emp) => setDialog({ mode: 'edit', employee: emp }),
          })}
        >
          <Table
            data={pageRows}
            columns={columns}
            idKey="id"
            density="balanced"
            textOverflow="truncate"
            verticalAlign="middle"
            hasHover
            rowIndexStart={(page - 1) * PAGE_SIZE + 1}
            rowCount={filtered.length}
          />
        </ContextMenu>

        <HStack gap={3} align="center" justify="end">
          <Text type="supporting">Trang {page} / {pageCount}</Text>
          <Button size="sm" variant="ghost" label="Trang trước" isDisabled={page <= 1} onClick={() => setPage((p) => p - 1)}>
            Trước
          </Button>
          <Button size="sm" variant="ghost" label="Trang sau" isDisabled={page >= pageCount} onClick={() => setPage((p) => p + 1)}>
            Sau
          </Button>
        </HStack>
      </VStack>

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
