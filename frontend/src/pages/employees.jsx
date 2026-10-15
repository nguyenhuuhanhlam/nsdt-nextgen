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
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

import DialogTitle from '../components/dialog-title.jsx';
import { EditIcon, PlusIcon } from '../components/icons.jsx';
import { catalogsApi, employeesApi } from '../lib/api.js';
import { formatDate, parseDateInput } from '../lib/format.js';

const PAGE_SIZE = 15;

function useDebouncedValue(value, delay) {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const t = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(t);
  }, [value, delay]);
  return debounced;
}

function todayISO() {
  return new Date().toISOString().slice(0, 10);
}

// Form lưu id danh mục (string, '' = chưa chọn); khi lưu mới đổi sang number/null.
function buildPayload(form) {
  const toId = (v) => (v === '' ? null : Number(v));
  const text = (v) => (v.trim() === '' ? null : v.trim());
  return {
    first_name: form.first_name.trim(),
    last_name: form.last_name.trim(),
    card_id: form.card_id.trim() === '' ? null : Number(form.card_id),
    gender: form.gender,
    birth_date: form.birth_date || null,
    birth_place_id: toId(form.birth_place_id),
    nationality_id: toId(form.nationality_id),
    ethnicity_id: toId(form.ethnicity_id),
    id_number: text(form.id_number),
    id_issue_date: form.id_issue_date || null,
    id_issue_place_id: toId(form.id_issue_place_id),
    hometown: text(form.hometown),
    permanent_address: text(form.permanent_address),
    current_address: text(form.current_address),
    education_id: toId(form.education_id),
    marital_status_id: toId(form.marital_status_id),
    department_id: toId(form.department_id),
    position_id: toId(form.position_id),
    job_title_id: toId(form.job_title_id),
    join_date: form.join_date || null,
    probation_end_date: form.probation_end_date || null,
    salary_coefficient: form.salary_coefficient === '' ? null : Number(form.salary_coefficient),
    contract_type_id: toId(form.contract_type_id),
    btkt: form.btkt,
    is_active: form.is_active,
    terminated_date: form.is_active ? null : form.terminated_date || null,
  };
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

// Dialog nhập/sửa nhân viên theo form cũ (nhansu.form.php / xuly_nhansu.form.php).
// Tab "Thông tin chung" | "Lý lịch" | "Công việc" (+ "Nghỉ việc" khi sửa).
// Khi sửa: khóa mã NV, mã thẻ, đơn vị, chức vụ, hệ số lương — đúng quy tắc form cũ.
function EmployeeDialog({ employee, catalogs, onClose, onSave }) {
  const isEdit = Boolean(employee);
  const {
    departments = [],
    positions = [],
    jobTitles = [],
    provinces = [],
    nationalities = [],
    ethnicities = [],
    educations = [],
    maritalStatuses = [],
    contractTypes = [],
  } = catalogs ?? {};
  // Tab đang mở. Sau này thêm tác vụ mới = thêm Tab + panel tương ứng ở đây.
  const [tab, setTab] = useState('info');
  const id = (rel) => (rel?.id != null ? String(rel.id) : '');
  const [form, setForm] = useState(() => ({
    employee_code: employee?.employee_code ?? '',
    card_id: employee?.card_id != null ? String(employee.card_id) : '',
    first_name: employee?.first_name ?? '',
    last_name: employee?.last_name ?? '',
    gender: employee?.gender ?? 'Nam',
    birth_date: employee?.birth_date ?? '',
    birth_place_id: id(employee?.birth_place),
    nationality_id: id(employee?.nationality),
    ethnicity_id: id(employee?.ethnicity),
    id_number: employee?.id_number ?? '',
    id_issue_date: employee?.id_issue_date ?? '',
    id_issue_place_id: id(employee?.id_issue_place),
    hometown: employee?.hometown ?? '',
    permanent_address: employee?.permanent_address ?? '',
    current_address: employee?.current_address ?? '',
    education_id: id(employee?.education),
    marital_status_id: id(employee?.marital_status),
    department_id: id(employee?.department),
    position_id: id(employee?.position),
    job_title_id: id(employee?.job_title),
    join_date: employee?.join_date ?? '',
    probation_end_date: employee?.probation_end_date ?? '',
    salary_coefficient: employee?.salary_coefficient != null ? String(employee.salary_coefficient) : '1.124',
    contract_type_id: id(employee?.contract_type),
    btkt: employee?.btkt ?? false,
    is_active: employee?.is_active ?? true,
    terminated_date: employee?.terminated_date ?? '',
  }));
  const [saving, setSaving] = useState(false);
  const [saveError, setSaveError] = useState('');
  const set = (k) => (v) => setForm((f) => {
    const next = { ...f, [k]: v };
    // Tắt "đang làm việc" mà chưa có ngày nghỉ → mặc định hôm nay.
    if (k === 'is_active' && v === false && !next.terminated_date) {
      next.terminated_date = todayISO();
    }
    return next;
  });

  const handleSave = async () => {
    setSaving(true);
    setSaveError('');
    try {
      await onSave(form);
      onClose();
    } catch (e) {
      setSaveError(e.message);
      setSaving(false);
    }
  };

  const catalogOptions = (list) => list.map((c) => ({ value: String(c.id), label: c.name }));
  const dateInput = (key, label, extraProps) => (
    <TextInput
      label={label}
      value={formatDate(form[key])}
      onChange={(v) => set(key)(parseDateInput(v))}
      placeholder="dd-mm-yyyy"
      width="100%"
      {...extraProps}
    />
  );

  return (
    <Dialog isOpen onOpenChange={(open) => !open && onClose()} width={640} maxHeight="85dvh">
      <DialogTitle onClose={onClose}>
        {isEdit ? 'Sửa thông tin nhân viên' : 'Thêm nhân viên'}
      </DialogTitle>
      <TabList value={tab} onChange={setTab} hasDivider>
        <Tab value="info" label="Thông tin chung" />
        <Tab value="profile" label="Lý lịch" />
        <Tab value="job" label="Công việc" />
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
              {dateInput('birth_date', 'Ngày sinh')}
              <Selector label="Nơi sinh" value={form.birth_place_id} onChange={set('birth_place_id')} options={catalogOptions(provinces)} hasSearch hasClear width="100%" />
            </HStack>
            <HStack gap={3}>
              <Selector label="Quốc tịch" value={form.nationality_id} onChange={set('nationality_id')} options={catalogOptions(nationalities)} hasSearch hasClear width="100%" />
              <Selector label="Dân tộc" value={form.ethnicity_id} onChange={set('ethnicity_id')} options={catalogOptions(ethnicities)} hasSearch hasClear width="100%" />
            </HStack>
            <TextInput label="Mã thẻ (thẻ từ)" value={form.card_id} onChange={set('card_id')} placeholder="Số thẻ chấm công" isDisabled={isEdit} width="100%" />
          </>
        )}
        {tab === 'profile' && (
          <>
            <HStack gap={3}>
              <TextInput label="Số CMND/CCCD" value={form.id_number} onChange={set('id_number')} width="100%" />
              {dateInput('id_issue_date', 'Ngày cấp')}
            </HStack>
            <HStack gap={3}>
              <Selector label="Nơi cấp" value={form.id_issue_place_id} onChange={set('id_issue_place_id')} options={catalogOptions(provinces)} hasSearch hasClear width="100%" />
              <TextInput label="Nguyên quán" value={form.hometown} onChange={set('hometown')} width="100%" />
            </HStack>
            <TextInput label="Địa chỉ thường trú" value={form.permanent_address} onChange={set('permanent_address')} width="100%" />
            <TextInput label="Địa chỉ cư ngụ" value={form.current_address} onChange={set('current_address')} width="100%" />
            <HStack gap={3}>
              <Selector label="Học vấn" value={form.education_id} onChange={set('education_id')} options={catalogOptions(educations)} hasSearch hasClear width="100%" />
              <Selector label="Hôn nhân" value={form.marital_status_id} onChange={set('marital_status_id')} options={catalogOptions(maritalStatuses)} hasSearch hasClear width="100%" />
            </HStack>
          </>
        )}
        {tab === 'job' && (
          <>
            <HStack gap={3}>
              {dateInput('join_date', 'Ngày vào làm')}
              {dateInput('probation_end_date', 'Ngày hết thử việc')}
            </HStack>
            <HStack gap={3}>
              <Selector label="Đơn vị" value={form.department_id} onChange={set('department_id')} options={catalogOptions(departments)} hasSearch hasClear isDisabled={isEdit} width="100%" />
              <Selector label="Chức vụ" value={form.position_id} onChange={set('position_id')} options={catalogOptions(positions)} hasSearch hasClear isDisabled={isEdit} width="100%" />
            </HStack>
            <HStack gap={3}>
              <Selector label="Công việc" value={form.job_title_id} onChange={set('job_title_id')} options={catalogOptions(jobTitles)} hasSearch hasClear width="100%" />
              <TextInput label="Hệ số lương" value={form.salary_coefficient} onChange={set('salary_coefficient')} isDisabled={isEdit} width="100%" />
            </HStack>
            <HStack gap={3}>
              <Selector label="Loại HĐLĐ" value={form.contract_type_id} onChange={set('contract_type_id')} options={catalogOptions(contractTypes)} hasSearch hasClear width="100%" />
              <VStack width="100%" justify="end">
                <Switch
                  label="BT/KT"
                  value={form.btkt}
                  onChange={set('btkt')}
                  description="Cờ BT/KT của hệ cũ (bật = loại khỏi một số danh sách)."
                />
              </VStack>
            </HStack>
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
        {saveError && <Text type="supporting">{saveError}</Text>}
      </VStack>
      <HStack gap={2} justify="end" padding={4}>
        <Button variant="ghost" label="Hủy" onClick={onClose} isDisabled={saving}>Hủy</Button>
        <Button variant="primary" label="Lưu" onClick={handleSave} isDisabled={saving}>Lưu</Button>
      </HStack>
    </Dialog>
  );
}

export default function Employees() {
  const [rows, setRows] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [q, setQ] = useState('');
  const [deptId, setDeptId] = useState('');
  const [catalogs, setCatalogs] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [dialog, setDialog] = useState(null); // null | {mode:'add'} | {mode:'edit', employee}
  // Nhân viên đang được mở context menu bằng chuột phải lên row.
  const [ctxEmployee, setCtxEmployee] = useState(null);
  const tableWrapRef = useRef(null);
  const debouncedQ = useDebouncedValue(q, 350);

  // Nạp danh mục một lần.
  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const [d, p, j, prov, nat, eth, edu, mar, ct] = await Promise.all([
          employeesApi.listDepartments(),
          employeesApi.listPositions(),
          employeesApi.listJobTitles(),
          catalogsApi.provinces.list(),
          catalogsApi.nationalities.list(),
          catalogsApi.ethnicities.list(),
          catalogsApi.educations.list(),
          catalogsApi['marital-statuses'].list(),
          catalogsApi['contract-types'].list(),
        ]);
        if (!cancelled) {
          setCatalogs({
            departments: d,
            positions: p,
            jobTitles: j,
            provinces: prov,
            nationalities: nat,
            ethnicities: eth,
            educations: edu,
            maritalStatuses: mar,
            contractTypes: ct,
          });
        }
      } catch (e) {
        if (!cancelled) setError(e.message);
      }
    })();
    return () => { cancelled = true; };
  }, []);

  const reload = useCallback(async (pageNow, qNow, deptNow) => {
    setLoading(true);
    try {
      const data = await employeesApi.list({
        page: pageNow,
        pageSize: PAGE_SIZE,
        q: qNow.trim(),
        departmentId: deptNow === '' ? null : deptNow,
      });
      setRows(data.items);
      setTotal(data.total);
      setError('');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    reload(page, debouncedQ, deptId);
  }, [page, debouncedQ, deptId, reload]);

  const pageCount = Math.max(1, Math.ceil(total / PAGE_SIZE));
  const pageRows = useMemo(
    () => rows.map((e) => ({
      ...e,
      department_name: e.department?.name ?? '',
      position_name: e.position?.name ?? '',
      job_title_name: e.job_title?.name ?? '',
    })),
    [rows],
  );

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

  const saveEmployee = async (form) => {
    if (dialog?.mode === 'add') {
      await employeesApi.create({
        employee_code: form.employee_code.trim(),
        ...buildPayload(form),
      });
      setPage(1);
      await reload(1, debouncedQ, deptId);
    } else if (dialog?.mode === 'edit') {
      await employeesApi.update(dialog.employee.id, buildPayload(form));
      await reload(page, debouncedQ, deptId);
    }
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
          placeholder="Tìm theo mã NV, mã thẻ hoặc họ tên..."
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
          value={deptId}
          onChange={(v) => { setDeptId(v ?? ''); setPage(1); }}
          options={(catalogs?.departments ?? []).map((d) => ({ value: String(d.id), label: d.name }))}
          hasSearch
          hasClear
          width={240}
        />
        <Text type="supporting">{loading ? 'Đang tải...' : `${total} nhân viên`}</Text>
      </HStack>

      {error && rows.length === 0 ? (
        <VStack gap={3} align="center" padding={8}>
          <Text>Không kết nối được tới backend: {error}</Text>
          <Button variant="secondary" label="Thử lại" onClick={() => reload(page, debouncedQ, deptId)}>
            Thử lại
          </Button>
        </VStack>
      ) : (
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
              rowCount={total}
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
      )}

      {dialog && (
        <EmployeeDialog
          employee={dialog.mode === 'edit' ? dialog.employee : null}
          catalogs={catalogs}
          onClose={closeDialog}
          onSave={saveEmployee}
        />
      )}
    </VStack>
  );
}
