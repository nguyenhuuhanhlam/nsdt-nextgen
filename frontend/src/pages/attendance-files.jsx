// Lịch sử các đợt nạp file chấm công + nạp lại file chờ xử lý.
import { Button } from '@astryxdesign/core/Button';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { Selector } from '@astryxdesign/core/Selector';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useCallback, useEffect, useState } from 'react';

import { attendanceApi } from '../lib/api.js';
import { formatDate, formatDateTime } from '../lib/format.js';

const PAGE_SIZE = 20;

const STATUS_LABELS = {
  imported: 'Đã nạp',
  pending_fix: 'Chờ xử lý thẻ lạ',
};

function useDebouncedValue(value, delay) {
  const [debounced, setDebounced] = useState(value);
  useEffect(() => {
    const t = setTimeout(() => setDebounced(value), delay);
    return () => clearTimeout(t);
  }, [value, delay]);
  return debounced;
}

export default function AttendanceFiles() {
  const [files, setFiles] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [q, setQ] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [reloadingId, setReloadingId] = useState(null);
  const debouncedQ = useDebouncedValue(q, 350);

  const reload = useCallback(async (pageNow, qNow, statusNow) => {
    setLoading(true);
    try {
      const data = await attendanceApi.files({
        page: pageNow,
        pageSize: PAGE_SIZE,
        q: qNow.trim(),
        status: statusNow,
      });
      setFiles(data.items);
      setTotal(data.total);
      setError('');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    reload(page, debouncedQ, statusFilter);
  }, [page, debouncedQ, statusFilter, reload]);

  const pageCount = Math.max(1, Math.ceil(total / PAGE_SIZE));

  const doReload = async (file) => {
    setReloadingId(file.id);
    setError('');
    try {
      await attendanceApi.reloadFile(file.id);
      await reload(page, debouncedQ, statusFilter);
    } catch (e) {
      setError(e.message);
    } finally {
      setReloadingId(null);
    }
  };

  const columns = [
    {
      key: 'filename',
      header: 'File',
      width: proportional(2),
      renderCell: (row) => row.filename,
    },
    {
      key: 'dates',
      header: 'Ngày',
      width: proportional(1),
      renderCell: (row) => row.work_dates.map(formatDate).join(', '),
    },
    {
      key: 'rows',
      header: 'Dòng/Tổng',
      width: pixel(110),
      align: 'center',
      renderCell: (row) => `${row.imported_rows}/${row.total_rows}`,
    },
    {
      key: 'unmatched',
      header: 'Thẻ lạ',
      width: pixel(80),
      align: 'center',
      renderCell: (row) => (row.unmatched_count > 0 ? row.unmatched_count : '—'),
    },
    {
      key: 'status',
      header: 'Trạng thái',
      width: pixel(150),
      renderCell: (row) => STATUS_LABELS[row.status] ?? row.status,
    },
    {
      key: 'imported_at',
      header: 'Nạp lúc',
      width: pixel(150),
      renderCell: (row) => formatDateTime(row.imported_at),
    },
    {
      key: 'actions',
      header: 'Thao tác',
      width: pixel(130),
      align: 'center',
      renderCell: (row) =>
        row.status === 'pending_fix' ? (
          <Button
            size="sm"
            variant="secondary"
            label={`Nạp lại ${row.filename}`}
            onClick={() => doReload(row)}
            isDisabled={reloadingId === row.id}
          >
            {reloadingId === row.id ? 'Đang nạp...' : 'Nạp lại'}
          </Button>
        ) : null,
    },
  ];

  return (
    <VStack gap={4}>
      <Heading level={4} weight="bold">
        Lịch sử nạp file chấm công
      </Heading>

      <HStack gap={3} align="end">
        <TextInput
          label="Tìm kiếm"
          isLabelHidden
          placeholder="Tìm theo tên file..."
          value={q}
          onChange={(v) => { setQ(v); setPage(1); }}
          startIcon="search"
          hasClear
          width={320}
        />
        <Selector
          label="Trạng thái"
          isLabelHidden
          value={statusFilter}
          onChange={(v) => { setStatusFilter(v ?? ''); setPage(1); }}
          options={[
            { value: '', label: 'Tất cả' },
            { value: 'imported', label: 'Đã nạp' },
            { value: 'pending_fix', label: 'Chờ xử lý thẻ lạ' },
          ]}
          width={220}
        />
        <Text type="supporting">{loading ? 'Đang tải...' : `${total} đợt nạp`}</Text>
      </HStack>

      {error ? <Text>{error}</Text> : null}

      <Table
        data={files}
        columns={columns}
        idKey="id"
        density="balanced"
        textOverflow="truncate"
        verticalAlign="middle"
        hasHover
      />

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
  );
}
