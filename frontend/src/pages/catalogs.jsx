// Quản lý các bảng danh mục: đơn vị, chức vụ, công việc, tỉnh thành, loại thai sản.
// Một component dùng chung, cấu hình theo từng loại qua CATALOGS. Dữ liệu qua API.
import { AlertDialog } from '@astryxdesign/core/AlertDialog';
import { Button } from '@astryxdesign/core/Button';
import { ContextMenu } from '@astryxdesign/core/ContextMenu';
import { Dialog } from '@astryxdesign/core/Dialog';
import { DropdownMenu } from '@astryxdesign/core/DropdownMenu';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { IconButton } from '@astryxdesign/core/IconButton';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useEffect, useMemo, useRef, useState } from 'react';
import { Navigate, useParams } from 'react-router-dom';

import DialogTitle from '../components/dialog-title.jsx';
import { EditIcon, PlusIcon, TrashIcon } from '../components/icons.jsx';
import { catalogsApi } from '../lib/api.js';

const CATALOGS = {
  departments: { title: 'Đơn vị', itemLabel: 'đơn vị' },
  positions: { title: 'Chức vụ', itemLabel: 'chức vụ' },
  'job-titles': { title: 'Công việc', itemLabel: 'công việc' },
  provinces: { title: 'Tỉnh thành', itemLabel: 'tỉnh thành' },
  'maternity-types': { title: 'Loại thai sản', itemLabel: 'loại thai sản' },
};

function catalogMenuItems(cfg, item, onEdit, onDelete) {
  return [
    { label: 'Sửa thông tin', icon: <EditIcon />, onClick: () => onEdit(item) },
    { type: 'divider' },
    {
      label: `Xóa ${cfg.itemLabel}`,
      icon: <TrashIcon />,
      variant: 'destructive',
      onClick: () => onDelete(item),
    },
  ];
}

function CatalogManager({ kind }) {
  const cfg = CATALOGS[kind];
  const api = catalogsApi[kind];
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [search, setSearch] = useState('');
  const [dialog, setDialog] = useState(null); // null | { mode: 'add' } | { mode: 'edit', item }
  const [deleteTarget, setDeleteTarget] = useState(null);
  const [deleteError, setDeleteError] = useState('');
  const [name, setName] = useState('');
  const [nameError, setNameError] = useState('');
  const [saving, setSaving] = useState(false);
  const [ctxItem, setCtxItem] = useState(null);
  const tableWrapRef = useRef(null);

  const reload = async () => {
    setLoading(true);
    try {
      setItems(await api.list());
      setError('');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    reload();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [kind]);

  const filtered = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return items;
    return items.filter((it) => it.name.toLowerCase().includes(q));
  }, [items, search]);

  // Chuột phải lên hàng nào thì mở context menu cho hàng đó
  useEffect(() => {
    const el = tableWrapRef.current;
    if (!el) return;
    const onContextMenu = (e) => {
      const tr = e.target.closest('tbody tr.sectionRowIndex');
      if (!tr) return;
      const id = Number(tr.dataset.id ?? tr.getAttribute('data-id'));
      const found = items.find((it) => it.id === id);
      if (found) setCtxItem(found);
    };
    el.addEventListener('contextmenu', onContextMenu);
    return () => el.removeEventListener('contextmenu', onContextMenu);
  }, [items]);

  const openAdd = () => {
    setName('');
    setNameError('');
    setDialog({ mode: 'add' });
  };

  const openEdit = (item) => {
    setName(item.name);
    setNameError('');
    setDialog({ mode: 'edit', item });
  };

  const save = async () => {
    const value = name.trim();
    if (!value) {
      setNameError(`Tên ${cfg.itemLabel} không được để trống.`);
      return;
    }
    const dup = items.some(
      (it) =>
        it.name.trim().toLowerCase() === value.toLowerCase() &&
        (dialog.mode === 'add' || it.id !== dialog.item.id),
    );
    if (dup) {
      setNameError(`${cfg.title} "${value}" đã tồn tại.`);
      return;
    }
    setSaving(true);
    try {
      if (dialog.mode === 'add') {
        await api.create(value);
      } else {
        await api.update(dialog.item.id, value);
      }
      setDialog(null);
      await reload();
    } catch (e) {
      setNameError(e.message);
    } finally {
      setSaving(false);
    }
  };

  const confirmDelete = async () => {
    setDeleteError('');
    try {
      await api.remove(deleteTarget.id);
      setDeleteTarget(null);
      await reload();
    } catch (e) {
      // 409 khi mục đang được nhân viên dùng — giữ dialog mở để hiện lý do.
      setDeleteError(e.message);
    }
  };

  const columns = useMemo(
    () => [
      { key: 'id', header: 'Mã', width: pixel(80) },
      { key: 'name', header: `Tên ${cfg.itemLabel}`, width: proportional(1) },
      {
        key: '__actions',
        header: 'Thao tác',
        width: pixel(110),
        align: 'center',
        renderCell: (row) => (
          <HStack gap={1} justify="center">
            <DropdownMenu
              label={`Tác vụ của ${row.name}`}
              items={catalogMenuItems(cfg, row, openEdit, setDeleteTarget)}
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
              label={`Xóa ${cfg.itemLabel}`}
              tooltip="Xóa"
              variant="ghost"
              size="sm"
              onClick={() => { setDeleteError(''); setDeleteTarget(row); }}
            />
          </HStack>
        ),
      },
    ],
    [cfg],
  );

  return (
    <VStack gap={4}>
      <HStack justify="between" align="center" width="100%">
        <Heading level={4} weight="bold">
          Quản lý {cfg.itemLabel}
        </Heading>
        <Button variant="primary" label={`Thêm ${cfg.itemLabel}`} icon={<PlusIcon />} onClick={openAdd}>
          Thêm {cfg.itemLabel}
        </Button>
      </HStack>

      <HStack gap={3} align="end">
        <TextInput
          label="Tìm kiếm"
          isLabelHidden
          placeholder={`Tìm theo tên ${cfg.itemLabel}...`}
          value={search}
          onChange={setSearch}
          startIcon="search"
          width={320}
        />
        <Text type="supporting">
          {loading ? 'Đang tải...' : `${filtered.length} ${cfg.itemLabel}`}
        </Text>
      </HStack>

      {error && items.length === 0 ? (
        <VStack gap={3} align="center" padding={8}>
          <Text>Không kết nối được tới backend: {error}</Text>
          <Button variant="secondary" label="Thử lại" onClick={reload}>
            Thử lại
          </Button>
        </VStack>
      ) : (
        <VStack ref={tableWrapRef} gap={3} paddingBlockStart={4}>
          <ContextMenu
            isOpen={ctxItem != null}
            onOpenChange={(open) => {
              if (!open) setCtxItem(null);
            }}
            items={ctxItem ? catalogMenuItems(cfg, ctxItem, openEdit, (it) => { setDeleteError(''); setDeleteTarget(it); }) : []}
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
      )}

      <Dialog
        isOpen={dialog != null}
        onOpenChange={(open) => {
          if (!open) setDialog(null);
        }}
        width={440}
      >
        <DialogTitle onClose={() => setDialog(null)}>
          {dialog?.mode === 'edit' ? `Sửa ${cfg.itemLabel}` : `Thêm ${cfg.itemLabel}`}
        </DialogTitle>
        <VStack gap={3} padding={4}>
          <TextInput
            label={`Tên ${cfg.itemLabel}`}
            value={name}
            onChange={(v) => {
              setName(v);
              if (nameError) setNameError('');
            }}
            placeholder={`Nhập tên ${cfg.itemLabel}`}
            description={nameError || undefined}
            status={nameError ? 'error' : undefined}
            hasAutoFocus
            width="100%"
          />
        </VStack>
        <HStack gap={2} justify="end" padding={4}>
          <Button variant="ghost" label="Hủy" onClick={() => setDialog(null)} isDisabled={saving}>
            Hủy
          </Button>
          <Button variant="primary" label="Lưu" onClick={save} isDisabled={saving}>
            Lưu
          </Button>
        </HStack>
      </Dialog>

      <AlertDialog
        isOpen={deleteTarget != null}
        onOpenChange={(open) => {
          if (!open) {
            setDeleteTarget(null);
            setDeleteError('');
          }
        }}
        title={`Xóa ${cfg.itemLabel}`}
        description={deleteError || `Bạn có chắc muốn xóa "${deleteTarget?.name}"? Thao tác này không thể hoàn tác.`}
        cancelLabel="Hủy"
        actionLabel="Xóa"
        actionVariant="destructive"
        onAction={confirmDelete}
      />
    </VStack>
  );
}

export default function Catalogs() {
  const { kind } = useParams();
  if (!CATALOGS[kind]) return <Navigate to="/" replace />;
  return <CatalogManager key={kind} kind={kind} />;
}
