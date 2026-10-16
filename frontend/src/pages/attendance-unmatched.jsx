// Gán thẻ chưa khớp cho nhân viên (xử lý W3), xong nạp lại file.
import { Button } from '@astryxdesign/core/Button';
import { Dialog } from '@astryxdesign/core/Dialog';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { VStack } from '@astryxdesign/core/VStack';
import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';

import DialogTitle from '../components/dialog-title.jsx';
import { attendanceApi, employeesApi } from '../lib/api.js';
import { formatDate } from '../lib/format.js';

function AssignDialog({ card, onClose, onAssigned }) {
  const [q, setQ] = useState('');
  const [results, setResults] = useState([]);
  const [searching, setSearching] = useState(false);
  const [assigning, setAssigning] = useState(null);
  const [error, setError] = useState('');

  const search = async () => {
    if (!q.trim()) return;
    setSearching(true);
    setError('');
    try {
      const data = await employeesApi.list({ page: 1, pageSize: 20, q: q.trim() });
      setResults(data.items);
    } catch (e) {
      setError(e.message);
    } finally {
      setSearching(false);
    }
  };

  const assign = async (emp) => {
    setAssigning(emp.id);
    setError('');
    try {
      await attendanceApi.assignCard({ cardId: card.card_id, employeeId: emp.id });
      onAssigned();
      onClose();
    } catch (e) {
      setError(e.message);
    } finally {
      setAssigning(null);
    }
  };

  return (
    <Dialog isOpen onOpenChange={(open) => !open && onClose()} width={640} maxHeight="85dvh">
      <DialogTitle onClose={onClose}>
        Gán thẻ {card.card_id} cho nhân viên
      </DialogTitle>
      <VStack gap={3} padding={4}>
        <HStack gap={3} align="end">
          <TextInput
            label="Tìm nhân viên"
            placeholder="Mã hoặc tên nhân viên..."
            value={q}
            onChange={setQ}
            startIcon="search"
            hasClear
            width="100%"
          />
          <Button variant="secondary" label="Tìm nhân viên" onClick={search} isDisabled={searching}>
            {searching ? 'Đang tìm...' : 'Tìm'}
          </Button>
        </HStack>
        {error ? <Text>{error}</Text> : null}
        {results.length > 0 && (
          <Table
            data={results}
            columns={[
              { key: 'code', header: 'Mã NV', width: pixel(110), renderCell: (r) => r.employee_code },
              {
                key: 'name',
                header: 'Họ tên',
                width: proportional(1),
                renderCell: (r) => r.full_name,
              },
              {
                key: 'dept',
                header: 'Đơn vị',
                width: proportional(1),
                renderCell: (r) => r.department?.name ?? '—',
              },
              {
                key: 'card',
                header: 'Thẻ hiện tại',
                width: pixel(130),
                renderCell: (r) => r.card_id ?? '—',
              },
              {
                key: 'pick',
                header: '',
                width: pixel(100),
                align: 'center',
                renderCell: (r) => (
                  <Button
                    size="sm"
                    variant="primary"
                    label={`Gán thẻ cho ${r.employee_code}`}
                    onClick={() => assign(r)}
                    isDisabled={assigning === r.id}
                  >
                    {assigning === r.id ? '...' : 'Gán'}
                  </Button>
                ),
              },
            ]}
            idKey="id"
            density="balanced"
            verticalAlign="middle"
          />
        )}
      </VStack>
      <HStack gap={2} justify="end" padding={4}>
        <Button variant="ghost" label="Đóng" onClick={onClose}>
          Đóng
        </Button>
      </HStack>
    </Dialog>
  );
}

export default function AttendanceUnmatched() {
  const [groups, setGroups] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [assignCard, setAssignCard] = useState(null);

  const reload = useCallback(async () => {
    setLoading(true);
    try {
      const data = await attendanceApi.unmatched();
      setGroups(data);
      setError('');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    reload();
  }, [reload]);

  const totalCards = groups.reduce((n, g) => n + g.cards.length, 0);

  const columns = [
    {
      key: 'file',
      header: 'File',
      width: proportional(1),
      renderCell: (row) => row.filename,
    },
    {
      key: 'card',
      header: 'Mã thẻ',
      width: pixel(160),
      renderCell: (row) => row.card.card_id,
    },
    {
      key: 'taps',
      header: 'Lượt quẹt',
      width: pixel(110),
      align: 'center',
      renderCell: (row) => row.card.taps,
    },
    {
      key: 'dates',
      header: 'Ngày',
      width: proportional(1),
      renderCell: (row) => row.card.dates.map(formatDate).join(', '),
    },
    {
      key: 'actions',
      header: 'Thao tác',
      width: pixel(130),
      align: 'center',
      renderCell: (row) => (
        <Button
          size="sm"
          variant="secondary"
          label={`Gán thẻ ${row.card.card_id}`}
          onClick={() => setAssignCard({ ...row.card, file_id: row.file_id })}
        >
          Gán thẻ
        </Button>
      ),
    },
  ];

  const rows = groups.flatMap((g) =>
    g.cards.map((card) => ({
      id: `${g.file_id}-${card.card_id}`,
      file_id: g.file_id,
      filename: g.filename,
      card,
    })),
  );

  return (
    <VStack gap={4}>
      <Heading level={4} weight="bold">
        Thẻ chưa khớp nhân viên
      </Heading>

      {error ? <Text>{error}</Text> : null}

      {!loading && totalCards === 0 && !error && (
        <Text type="supporting">Không còn thẻ nào chưa khớp.</Text>
      )}

      {totalCards > 0 && (
        <VStack gap={2}>
          <Text type="supporting">
            {totalCards} thẻ chưa khớp. Gán thẻ cho nhân viên xong, sang{' '}
            <Link to="/attendance/files">Lịch sử nạp file</Link> bấm "Nạp lại"
            để hoàn tất.
          </Text>
          <Table
            data={rows}
            columns={columns}
            idKey="id"
            density="balanced"
            verticalAlign="middle"
            hasHover
          />
        </VStack>
      )}

      {assignCard && (
        <AssignDialog
          card={assignCard}
          onClose={() => setAssignCard(null)}
          onAssigned={reload}
        />
      )}
    </VStack>
  );
}
