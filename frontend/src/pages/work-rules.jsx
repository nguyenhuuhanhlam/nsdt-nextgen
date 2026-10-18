// BẢN MẪU GIAO DIỆN — Quy định giờ công (work rules).
// Dữ liệu giả hoàn toàn (chưa có backend). Dùng để chốt thiết kế UI trước.
import { Button } from '@astryxdesign/core/Button';
import { ContextMenu } from '@astryxdesign/core/ContextMenu';
import { Dialog } from '@astryxdesign/core/Dialog';
import { Divider } from '@astryxdesign/core/Divider';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { IconButton } from '@astryxdesign/core/IconButton';
import { Tab, TabList } from '@astryxdesign/core/TabList';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useEffect, useRef, useState } from 'react';

import DialogTitle from '../components/dialog-title.jsx';
import { EditIcon, TrashIcon } from '../components/icons.jsx';
import { formatDate, parseDateInput } from '../lib/format.js';

const TIME_RE = /^([01]?\d|2[0-3]):[0-5]\d$/;
const isTime = (s) => TIME_RE.test((s ?? '').trim());

const DEFAULT_FORM = () => ({
  name: '',
  effective_from: '',
  effective_to: '',
  standard_start: '07:15',
  standard_end: '16:30',
  overtime_split_hours: '1.5',
  unpaid_breaks: [{ start: '11:15', end: '12:15' }],
  day_count_steps: [
    { max_hours: '2', day: '0.25' },
    { max_hours: '4', day: '0.5' },
    { max_hours: '6', day: '0.75' },
    { max_hours: '', day: '1' },
  ],
  maternity_cap_hours: '7',
  standard_cap_hours: '8',
  maternity_no_overtime_types: '2',
  extra_hour_threshold: '6',
  extra_hour_value: '1',
});

const MOCK_RULES = [
  {
    id: 1,
    name: 'Quy định giờ công 2024',
    effective_from: '2024-01-01',
    effective_to: null,
    in_use: true, // đã được đợt tính lương sử dụng → không cho xóa
    ...DEFAULT_FORM(),
    name: 'Quy định giờ công 2024',
    effective_from: '2024-01-01',
    effective_to: '',
  },
  {
    id: 2,
    name: 'Quy định giờ công 2020',
    effective_from: '2020-01-01',
    effective_to: '2023-12-31',
    in_use: false,
    ...DEFAULT_FORM(),
    name: 'Quy định giờ công 2020',
    effective_from: '2020-01-01',
    effective_to: '2023-12-31',
    standard_end: '16:00',
  },
];

const ruleToForm = (r) => ({
  name: r.name,
  effective_from: r.effective_from ?? '',
  effective_to: r.effective_to ?? '',
  standard_start: r.standard_start,
  standard_end: r.standard_end,
  overtime_split_hours: r.overtime_split_hours,
  unpaid_breaks: r.unpaid_breaks.map((b) => ({ ...b })),
  day_count_steps: r.day_count_steps.map((s) => ({ ...s })),
  maternity_cap_hours: r.maternity_cap_hours,
  standard_cap_hours: r.standard_cap_hours,
  maternity_no_overtime_types: r.maternity_no_overtime_types,
  extra_hour_threshold: r.extra_hour_threshold,
  extra_hour_value: r.extra_hour_value,
});

// Hai khoảng [from, to] có giao nhau không (to rỗng = vô hạn)
function rangesOverlap(aFrom, aTo, bFrom, bTo) {
  const aEnd = aTo || '9999-12-31';
  const bEnd = bTo || '9999-12-31';
  return aFrom <= bEnd && bFrom <= aEnd;
}

function RuleDialog({ rule, onClose, onSave, otherRules }) {
  const isEdit = Boolean(rule);
  const [tab, setTab] = useState('general');
  const [form, setForm] = useState(() => (rule ? ruleToForm(rule) : DEFAULT_FORM()));
  const [error, setError] = useState('');

  const set = (k) => (v) => setForm((f) => ({ ...f, [k]: v }));
  const setBreak = (i, k) => (v) =>
    setForm((f) => ({
      ...f,
      unpaid_breaks: f.unpaid_breaks.map((b, j) => (j === i ? { ...b, [k]: v } : b)),
    }));
  const setStep = (i, k) => (v) =>
    setForm((f) => ({
      ...f,
      day_count_steps: f.day_count_steps.map((s, j) => (j === i ? { ...s, [k]: v } : s)),
    }));

  const save = () => {
    const f = form;
    if (!f.name.trim()) return setError('Tên bộ quy định không được để trống.');
    if (!parseDateInput(f.effective_from))
      return setError('Hiệu lực từ ngày chưa đúng (dd-mm-yyyy).');
    const fromISO = parseDateInput(f.effective_from);
    let toISO = '';
    if (f.effective_to.trim()) {
      if (!parseDateInput(f.effective_to)) return setError('Đến ngày chưa đúng (dd-mm-yyyy).');
      toISO = parseDateInput(f.effective_to);
      if (toISO < fromISO) return setError('Đến ngày phải từ ngày hiệu lực trở đi.');
    }
    const clash = otherRules.some((r) =>
      rangesOverlap(fromISO, toISO, r.effective_from, r.effective_to ?? ''),
    );
    if (clash) return setError('Khoảng hiệu lực giao với một bộ quy định khác.');
    for (const b of f.unpaid_breaks) {
      if (!isTime(b.start) || !isTime(b.end))
        return setError('Khoảng nghỉ phải đúng dạng HH:MM.');
      if (b.start >= b.end) return setError('Giờ bắt đầu nghỉ phải trước giờ kết thúc nghỉ.');
    }
    if (!isTime(f.standard_start) || !isTime(f.standard_end))
      return setError('Giờ chuẩn phải đúng dạng HH:MM.');
    onSave({ ...f, effective_from: fromISO, effective_to: toISO });
  };

  return (
    <Dialog isOpen onOpenChange={(o) => !o && onClose()} width={640} maxHeight="85dvh">
      <DialogTitle onClose={onClose}>
        {isEdit ? `Sửa: ${rule.name}` : 'Thêm bộ quy định'}
      </DialogTitle>
      <VStack gap={0}>
        <TabList value={tab} onChange={setTab} hasDivider>
          <Tab value="general" label="Chung" />
          <Tab value="hours" label="Giờ chuẩn" />
          <Tab value="breaks" label="Khoảng nghỉ" />
          <Tab value="leave" label="Ngày công & thai sản" />
        </TabList>

        <VStack gap={3} padding={4}>
          {tab === 'general' && (
            <VStack gap={3}>
              <TextInput label="Tên bộ quy định" value={form.name} onChange={set('name')} width="100%" />
              <HStack gap={3}>
                <TextInput
                  label="Hiệu lực từ ngày"
                  value={formatDate(form.effective_from)}
                  onChange={(v) => set('effective_from')(v)}
                  placeholder="dd-mm-yyyy"
                  width="100%"
                />
                <TextInput
                  label="Đến ngày (bỏ trống = đang áp dụng)"
                  value={formatDate(form.effective_to)}
                  onChange={(v) => set('effective_to')(v)}
                  placeholder="dd-mm-yyyy"
                  width="100%"
                />
              </HStack>
              <Text type="supporting">
                Không được có 2 bộ quy định cùng hiệu lực tại một thời điểm.
              </Text>
            </VStack>
          )}

          {tab === 'hours' && (
            <VStack gap={3}>
              <HStack gap={3}>
                <TextInput
                  label="Giờ bắt đầu tính công"
                  value={form.standard_start}
                  onChange={set('standard_start')}
                  placeholder="HH:MM"
                  width="100%"
                />
                <TextInput
                  label="Mốc giờ quy định (toàn công ty)"
                  value={form.standard_end}
                  onChange={set('standard_end')}
                  placeholder="HH:MM"
                  width="100%"
                />
              </HStack>
              <TextInput
                label="Ngưỡng tách tăng ca trước/sau (giờ)"
                value={form.overtime_split_hours}
                onChange={set('overtime_split_hours')}
                width="100%"
              />
              <Text type="supporting">
                Tăng ca sau mốc giờ quy định đến ngưỡng này tính "làm thêm trước", phần dư tính
                "làm thêm sau".
              </Text>
            </VStack>
          )}

          {tab === 'breaks' && (
            <VStack gap={2}>
              <Text type="supporting">
                Các khoảng nghỉ không tính tiền trong ngày. Thêm dòng mới khi có thêm giờ nghỉ.
              </Text>
              {form.unpaid_breaks.map((b, i) => (
                <HStack key={i} gap={3} align="end">
                  <TextInput
                    label={i === 0 ? 'Từ giờ' : undefined}
                    value={b.start}
                    onChange={setBreak(i, 'start')}
                    placeholder="HH:MM"
                    width="100%"
                  />
                  <TextInput
                    label={i === 0 ? 'Đến giờ' : undefined}
                    value={b.end}
                    onChange={setBreak(i, 'end')}
                    placeholder="HH:MM"
                    width="100%"
                  />
                  <IconButton
                    icon={<TrashIcon />}
                    label={`Xóa khoảng nghỉ ${b.start}–${b.end}`}
                    tooltip="Xóa khoảng nghỉ"
                    variant="ghost"
                    size="sm"
                    isDisabled={form.unpaid_breaks.length <= 1}
                    onClick={() =>
                      setForm((f) => ({
                        ...f,
                        unpaid_breaks: f.unpaid_breaks.filter((_, j) => j !== i),
                      }))
                    }
                  />
                </HStack>
              ))}
              <Button
                variant="ghost"
                size="sm"
                label="Thêm khoảng nghỉ"
                onClick={() =>
                  setForm((f) => ({
                    ...f,
                    unpaid_breaks: [...f.unpaid_breaks, { start: '', end: '' }],
                  }))
                }
              >
                + Thêm khoảng nghỉ
              </Button>
            </VStack>
          )}

          {tab === 'leave' && (
            <VStack gap={3}>
              <Text weight="bold">Bậc ngày công theo giờ hành chính</Text>
              {form.day_count_steps.map((s, i) => (
                <HStack key={i} gap={3} align="end">
                  <TextInput
                    label={i === 0 ? 'Đến (giờ, trống = còn lại)' : undefined}
                    value={s.max_hours}
                    onChange={setStep(i, 'max_hours')}
                    placeholder="vd 2"
                    width="100%"
                  />
                  <TextInput
                    label={i === 0 ? 'Ngày công' : undefined}
                    value={s.day}
                    onChange={setStep(i, 'day')}
                    placeholder="vd 0.25"
                    width="100%"
                  />
                  <IconButton
                    icon={<TrashIcon />}
                    label="Xóa bậc"
                    tooltip="Xóa bậc"
                    variant="ghost"
                    size="sm"
                    isDisabled={form.day_count_steps.length <= 1}
                    onClick={() =>
                      setForm((f) => ({
                        ...f,
                        day_count_steps: f.day_count_steps.filter((_, j) => j !== i),
                      }))
                    }
                  />
                </HStack>
              ))}
              <Button
                variant="ghost"
                size="sm"
                label="Thêm bậc ngày công"
                onClick={() =>
                  setForm((f) => ({
                    ...f,
                    day_count_steps: [...f.day_count_steps, { max_hours: '', day: '' }],
                  }))
                }
              >
                + Thêm bậc
              </Button>
              <Divider />
              <Text weight="bold">Thai sản</Text>
              <HStack gap={3}>
                <TextInput
                  label="Trần giờ HC thai sản"
                  value={form.maternity_cap_hours}
                  onChange={set('maternity_cap_hours')}
                  width="100%"
                />
                <TextInput
                  label="Trần giờ HC thường"
                  value={form.standard_cap_hours}
                  onChange={set('standard_cap_hours')}
                  width="100%"
                />
              </HStack>
              <HStack gap={3}>
                <TextInput
                  label="Loại TS → tăng ca = 0 (cách nhau dấu phẩy)"
                  value={form.maternity_no_overtime_types}
                  onChange={set('maternity_no_overtime_types')}
                  placeholder="vd 2"
                  width="100%"
                />
                <TextInput
                  label="HC trên (giờ) → +giờ bù"
                  value={form.extra_hour_threshold}
                  onChange={set('extra_hour_threshold')}
                  width="100%"
                />
              </HStack>
            </VStack>
          )}

          {error ? <Text color="accent">{error}</Text> : null}
        </VStack>
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

export default function WorkRules() {
  const [rules, setRules] = useState(MOCK_RULES);
  const [dialog, setDialog] = useState(null); // { mode: 'add'|'edit'|'clone', rule? }
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [ctxItem, setCtxItem] = useState(null);
  const tableWrapRef = useRef(null);
  const rulesRef = useRef(rules);
  rulesRef.current = rules;

  useEffect(() => {
    const el = tableWrapRef.current;
    if (!el) return;
    const onContextMenu = (e) => {
      const tr = e.target?.closest?.('tbody tr');
      const rowsNow = rulesRef.current;
      if (!tr || !el.contains(tr)) {
        setCtxItem(null);
        return;
      }
      const idx = tr.sectionRowIndex;
      setCtxItem(idx >= 0 && idx < rowsNow.length ? rowsNow[idx] : null);
    };
    el.addEventListener('contextmenu', onContextMenu);
    return () => el.removeEventListener('contextmenu', onContextMenu);
  }, []);

  const activeRule = rules.find((r) => !r.effective_to);

  const openAdd = () => setDialog({ mode: 'add' });
  const openClone = () => {
    if (!activeRule) return;
    setDialog({ mode: 'clone', rule: activeRule });
  };
  const openEdit = (rule) => setDialog({ mode: 'edit', rule });

  const handleSave = (form) => {
    if (dialog.mode === 'edit') {
      setRules((rs) => rs.map((r) => (r.id === dialog.rule.id ? { ...r, ...form } : r)));
    } else {
      const id = Math.max(...rules.map((r) => r.id)) + 1;
      setRules((rs) => [
        ...rs,
        { id, in_use: false, ...form },
      ]);
    }
    setDialog(null);
  };

  const askDelete = (rule) => {
    if (rule.in_use) return; // menu đã ẩn nút này
    setDeleteTarget(rule);
  };
  const doDelete = () => {
    setRules((rs) => rs.filter((r) => r.id !== deleteTarget.id));
    setDeleteTarget(null);
  };

  const columns = [
    { key: 'name', header: 'Tên bộ quy định', width: proportional(2), renderCell: (r) => r.name },
    {
      key: 'from',
      header: 'Hiệu lực từ',
      width: pixel(140),
      renderCell: (r) => formatDate(r.effective_from),
    },
    {
      key: 'to',
      header: 'Đến ngày',
      width: pixel(140),
      renderCell: (r) => (r.effective_to ? formatDate(r.effective_to) : '—'),
    },
    {
      key: 'status',
      header: 'Trạng thái',
      width: pixel(150),
      renderCell: (r) =>
        r.effective_to ? (
          <Text color="secondary">Hết hiệu lực</Text>
        ) : (
          <Text color="accent">Đang áp dụng</Text>
        ),
    },
    {
      key: 'actions',
      header: '',
      width: pixel(64),
      align: 'center',
      renderCell: (r) => (
        <IconButton
          icon={<EditIcon />}
          label={`Sửa ${r.name}`}
          tooltip="Sửa"
          variant="ghost"
          size="sm"
          onClick={() => openEdit(r)}
        />
      ),
    },
  ];

  return (
    <VStack gap={4}>
      <HStack justify="between" align="center" width="100%">
        <HStack gap={3} align="center">
          <Heading level={4} weight="bold">
            Quy định giờ công
          </Heading>
          <Text type="supporting">Bản mẫu giao diện — dữ liệu giả</Text>
        </HStack>
        <HStack gap={2}>
          <Button
            variant="secondary"
            label="Tạo bộ mới từ bộ đang áp dụng"
            onClick={openClone}
            isDisabled={!activeRule}
          >
            Tạo từ bộ hiện tại
          </Button>
          <Button variant="primary" label="Thêm bộ quy định" onClick={openAdd}>
            Thêm bộ mới
          </Button>
        </HStack>
      </HStack>

      <VStack ref={tableWrapRef} gap={3}>
        <ContextMenu
          label="Tác vụ quy định"
          items={
            ctxItem
              ? [
                  {
                    label: 'Sửa',
                    icon: <EditIcon />,
                    onClick: () => openEdit(ctxItem),
                  },
                  ...(ctxItem.in_use
                    ? []
                    : [
                        {
                          label: 'Xóa',
                          icon: <TrashIcon />,
                          variant: 'destructive',
                          onClick: () => askDelete(ctxItem),
                        },
                      ]),
                ]
              : []
          }
        >
          <Table
            data={rules}
            columns={columns}
            idKey="id"
            density="balanced"
            verticalAlign="middle"
            hasHover
          />
        </ContextMenu>
      </VStack>

      {dialog && (
        <RuleDialog
          rule={dialog.mode === 'add' ? null : dialog.rule}
          otherRules={rules.filter((r) => !dialog.rule || r.id !== dialog.rule.id)}
          onClose={() => setDialog(null)}
          onSave={handleSave}
        />
      )}

      {deleteTarget && (
        <Dialog
          isOpen
          onOpenChange={(o) => !o && setDeleteTarget(null)}
          width={440}
          maxHeight="85dvh"
        >
          <DialogTitle onClose={() => setDeleteTarget(null)}>Xóa bộ quy định</DialogTitle>
          <VStack gap={3} padding={4}>
            <Text>
              Xóa bộ <Text weight="bold">“{deleteTarget.name}”</Text>? Thao tác này không thể
              hoàn tác.
            </Text>
          </VStack>
          <HStack gap={2} justify="end" padding={4}>
            <Button variant="ghost" label="Hủy" onClick={() => setDeleteTarget(null)}>
              Hủy
            </Button>
            <Button variant="destructive" label="Xóa" onClick={doDelete}>
              Xóa
            </Button>
          </HStack>
        </Dialog>
      )}
    </VStack>
  );
}
