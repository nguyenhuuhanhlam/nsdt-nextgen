// Quản lý hồ sơ nghỉ thai sản của nhân viên (theo ns_xuly_thaisan của hệ cũ).
import { AlertDialog } from '@astryxdesign/core/AlertDialog';
import { Button } from '@astryxdesign/core/Button';
import { ContextMenu } from '@astryxdesign/core/ContextMenu';
import { Dialog } from '@astryxdesign/core/Dialog';
import { DropdownMenu } from '@astryxdesign/core/DropdownMenu';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { IconButton } from '@astryxdesign/core/IconButton';
import { Selector } from '@astryxdesign/core/Selector';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useEffect, useMemo, useRef, useState } from 'react';

import DialogTitle from '../components/dialog-title.jsx';
import { EditIcon, PlusIcon, TrashIcon } from '../components/icons.jsx';
import { formatDate, parseDateInput } from '../lib/format.js';
import { makeMockCatalogs, makeMockEmployees, makeMockMaternityLeaves } from '../lib/mock-data.js';

/** Số ngày nghỉ tính bao gồm cả 2 đầu; null khi ngày không hợp lệ. */
function calcDays(startISO, endISO) {
  if (!startISO || !endISO) return null;
  const s = new Date(`${startISO}T00:00:00`);
  const e = new Date(`${endISO}T00:00:00`);
  if (Number.isNaN(s.getTime()) || Number.isNaN(e.getTime()) || e < s) return null;
  return Math.round((e - s) / 86400000) + 1;
}

function leaveMenuItems(leave, onEdit, onDelete) {
  return [
    { label: 'Sửa thông tin', icon: <EditIcon />, onClick: () => onEdit(leave) },
    { type: 'divider' },
    {
      label: 'Xóa hồ sơ',
      icon: <TrashIcon />,
      variant: 'destructive',
      onClick: () => onDelete(leave),
    },
  ];
}

function LeaveDialog({ leave, employees, types, onClose, onSave }) {
  const isEdit = Boolean(leave);
  const [form, setForm] = useState(() => ({
    employee_id: leave ? String(leave.employee.id) : '',
    type_name: leave?.maternity_type.name ?? types[0]?.name ?? '',
    start_date: leave?.start_date ?? '',
    end_date: leave?.end_date ?? '',
    leave_days: leave ? String(leave.leave_days) : '',
    notes: leave?.notes ?? '',
  }));
  // Người dùng đã tự sửa số ngày nghỉ → không tự tính đè nữa.
  const [daysTouched, setDaysTouched] = useState(isEdit);
  const [error, setError] = useState('');

  const set = (k) => (v) =>
    setForm((f) => {
      const next = { ...f, [k]: v };
      if ((k === 'start_date' || k === 'end_date') && !daysTouched) {
        const d = calcDays(next.start_date, next.end_date);
        next.leave_days = d == null ? '' : String(d);
      }
      return next;
    });

  const employeeOptions = useMemo(
    () =>
      employees.map((e) => ({
        value: String(e.id),
        label: `${e.employee_code} — ${e.full_name}`,
      })),
    [employees],
  );

  const save = () => {
    if (!form.employee_id) return setError('Hãy chọn nhân viên.');
    if (!form.type_name) return setError('Hãy chọn loại thai sản.');
    const days = calcDays(form.start_date, form.end_date);
    if (days == null)
      return setError('Ngày bắt đầu/kết thúc không hợp lệ (ngày kết thúc phải từ ngày bắt đầu trở đi).');
    const emp = employees.find((e) => String(e.id) === form.employee_id);
    const type = types.find((t) => t.name === form.type_name);
    onSave({
      employee: { id: emp.id, employee_code: emp.employee_code, full_name: emp.full_name },
      maternity_type: { id: type.id, name: type.name },
      start_date: form.start_date,
      end_date: form.end_date,
      leave_days: form.leave_days.trim() === '' ? days : Number(form.leave_days),
      notes: form.notes.trim(),
    });
  };

  return (
    <Dialog isOpen onOpenChange={(open) => !open && onClose()} width={560} maxHeight="85dvh">
      <DialogTitle onClose={onClose}>
        {isEdit ? 'Sửa hồ sơ thai sản' : 'Thêm hồ sơ thai sản'}
      </DialogTitle>
      <VStack gap={3} padding={4}>
        <Selector
          label="Nhân viên"
          value={form.employee_id}
          onChange={set('employee_id')}
          options={employeeOptions}
          hasSearch
          isRequired
          placeholder="Chọn nhân viên"
          width="100%"
        />
        <HStack gap={3}>
          <Selector
            label="Loại thai sản"
            value={form.type_name}
            onChange={set('type_name')}
            options={types.map((t) => t.name)}
            hasSearch
            width="100%"
          />
          <TextInput
            label="Số ngày nghỉ"
            value={form.leave_days}
            onChange={(v) => {
              setDaysTouched(true);
              set('leave_days')(v);
            }}
            placeholder="Tự tính từ ngày"
            width="100%"
          />
        </HStack>
        <HStack gap={3}>
          <TextInput
            label="Ngày bắt đầu"
            value={formatDate(form.start_date)}
            onChange={(v) => set('start_date')(parseDateInput(v))}
            placeholder="dd-mm-yyyy"
            width="100%"
          />
          <TextInput
            label="Ngày kết thúc"
            value={formatDate(form.end_date)}
            onChange={(v) => set('end_date')(parseDateInput(v))}
            placeholder="dd-mm-yyyy"
            width="100%"
          />
        </HStack>
        <TextInput
          label="Ghi chú"
          value={form.notes}
          onChange={set('notes')}
          placeholder="Ghi chú thêm (nếu có)"
          width="100%"
        />
        {error ? <Text>{error}</Text> : null}
      </VStack>
      <HStack gap={2} justify="end" padding={4}>
        <Button variant="ghost" label="Hủy" onClick={onClose}>
          Hủy
        </Button>
        <Button variant="primary" label="Lưu" onClick={save}>
          Lưu
        </Button>
      </HStack>
    </Dialog>
  );
}

export default function Maternity() {
  const employees = useMemo(() => makeMockEmployees(60), []);
  const types = useMemo(() => makeMockCatalogs()['maternity-types'], []);
  const [leaves, setLeaves] = useState(() => makeMockMaternityLeaves(employees));
  const [q, setQ] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [dialog, setDialog] = useState(null); // null | {mode:'add'} | {mode:'edit', leave}
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [ctxLeave, setCtxLeave] = useState(null);
  const tableWrapRef = useRef(null);

  const filtered = useMemo(() => {
    const needle = q.trim().toLowerCase();
    return leaves.filter(
      (l) =>
        (!needle ||
          l.employee.employee_code.toLowerCase().includes(needle) ||
          l.employee.full_name.toLowerCase().includes(needle)) &&
        (!typeFilter || l.maternity_type.name === typeFilter),
    );
  }, [leaves, q, typeFilter]);

  useEffect(() => {
    const el = tableWrapRef.current;
    if (!el) return;
    const onContextMenu = (e) => {
      const tr = e.target.closest('tbody tr.sectionRowIndex');
      if (!tr) return;
      const id = Number(tr.dataset.id ?? tr.getAttribute('data-id'));
      const found = filtered.find((l) => l.id === id);
      if (found) setCtxLeave(found);
    };
    el.addEventListener('contextmenu', onContextMenu);
    return () => el.removeEventListener('contextmenu', onContextMenu);
  }, [filtered]);

  const openAdd = () => setDialog({ mode: 'add' });
  const openEdit = (leave) => setDialog({ mode: 'edit', leave });

  const saveLeave = (payload) => {
    if (dialog.mode === 'add') {
      const nextId = leaves.reduce((m, l) => Math.max(m, l.id), 0) + 1;
      setLeaves([{ id: nextId, ...payload }, ...leaves]);
    } else {
      setLeaves(leaves.map((l) => (l.id === dialog.leave.id ? { ...l, ...payload } : l)));
    }
    setDialog(null);
  };

  const confirmDelete = () => {
    setLeaves(leaves.filter((l) => l.id !== deleteTarget.id));
    setDeleteTarget(null);
  };

  const columns = [
    {
      key: 'employee',
      header: 'Nhân viên',
      width: proportional(2),
      renderCell: (row) => `${row.employee.employee_code} — ${row.employee.full_name}`,
    },
    {
      key: 'type',
      header: 'Loại thai sản',
      width: proportional(2),
      renderCell: (row) => row.maternity_type.name,
    },
    {
      key: 'start_date',
      header: 'Ngày bắt đầu',
      width: pixel(120),
      renderCell: (row) => formatDate(row.start_date),
    },
    {
      key: 'end_date',
      header: 'Ngày kết thúc',
      width: pixel(120),
      renderCell: (row) => formatDate(row.end_date),
    },
    {
      key: 'leave_days',
      header: 'Số ngày nghỉ',
      width: pixel(110),
      align: 'center',
    },
    {
      key: 'actions',
      header: 'Thao tác',
      width: pixel(110),
      align: 'center',
      renderCell: (row) => (
        <HStack gap={1} justify="center">
          <DropdownMenu
            label={`Tác vụ của ${row.employee.full_name}`}
            items={leaveMenuItems(row, openEdit, setDeleteTarget)}
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
          <IconButton
            icon={<TrashIcon />}
            label="Xóa hồ sơ"
            tooltip="Xóa"
            variant="ghost"
            size="sm"
            onClick={() => setDeleteTarget(row)}
          />
        </HStack>
      ),
    },
  ];

  return (
    <VStack gap={4}>
      <HStack justify="between" align="center" width="100%">
        <Heading level={4} weight="bold">
          Quản lý thai sản
        </Heading>
        <Button variant="primary" label="Thêm hồ sơ thai sản" icon={<PlusIcon />} onClick={openAdd}>
          Thêm hồ sơ
        </Button>
      </HStack>

      <HStack gap={3} align="end">
        <TextInput
          label="Tìm kiếm"
          isLabelHidden
          placeholder="Tìm theo mã/tên nhân viên..."
          value={q}
          onChange={setQ}
          startIcon="search"
          width={320}
        />
        <Selector
          label="Loại thai sản"
          isLabelHidden
          value={typeFilter}
          onChange={setTypeFilter}
          options={['', ...types.map((t) => t.name)]}
          width={240}
        />
        <Text type="supporting">{filtered.length} hồ sơ</Text>
      </HStack>

      <VStack ref={tableWrapRef} gap={3} paddingBlockStart={4}>
        <ContextMenu
          isOpen={ctxLeave != null}
          onOpenChange={(open) => {
            if (!open) setCtxLeave(null);
          }}
          items={ctxLeave ? leaveMenuItems(ctxLeave, openEdit, setDeleteTarget) : []}
        >
          <Table
            data={filtered}
            columns={columns}
            idKey="id"
            density="balanced"
            textOverflow="truncate"
            verticalAlign="middle"
            hasHover
          />
        </ContextMenu>
      </VStack>

      {dialog && (
        <LeaveDialog
          leave={dialog.mode === 'edit' ? dialog.leave : null}
          employees={employees}
          types={types}
          onClose={() => setDialog(null)}
          onSave={saveLeave}
        />
      )}

      <AlertDialog
        isOpen={deleteTarget != null}
        onOpenChange={(open) => {
          if (!open) setDeleteTarget(null);
        }}
        title="Xóa hồ sơ thai sản"
        description={`Bạn có chắc muốn xóa hồ sơ "${deleteTarget?.maternity_type.name}" của ${deleteTarget?.employee.full_name} (${formatDate(deleteTarget?.start_date)} – ${formatDate(deleteTarget?.end_date)})? Thao tác này không thể hoàn tác.`}
        cancelLabel="Hủy"
        actionLabel="Xóa"
        actionVariant="destructive"
        onAction={confirmDelete}
      />
    </VStack>
  );
}
