// Xem lại lượt quẹt: lọc theo cờ cảnh báo W4/W5/W6, tra cứu theo ngày/thẻ.
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
import { formatDate, parseDateInput } from '../lib/format.js';

const PAGE_SIZE = 50;

const FLAG_LABELS = {
  single_tap: 'Chỉ 1 lượt/ngày',
  excess_taps: 'Trên 6 lượt/ngày',
  odd_hours: 'Ngoài 05:00–21:00',
};

export default function AttendanceReview() {
  const [taps, setTaps] = useState([]);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  const [tapDate, setTapDate] = useState('');
  const [dateInput, setDateInput] = useState('');
  const [cardId, setCardId] = useState('');
  const [flag, setFlag] = useState('');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const reload = useCallback(async (pageNow, dateNow, cardNow, flagNow) => {
    setLoading(true);
    try {
      const data = await attendanceApi.taps({
        page: pageNow,
        pageSize: PAGE_SIZE,
        tapDate: dateNow,
        cardId: cardNow.trim(),
        flag: flagNow,
      });
      setTaps(data.items);
      setTotal(data.total);
      setError('');
    } catch (e) {
      setError(e.message);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    reload(page, tapDate, /^\d+$/.test(cardId.trim()) ? cardId.trim() : '', flag);
  }, [page, tapDate, cardId, flag, reload]);

  const pageCount = Math.max(1, Math.ceil(total / PAGE_SIZE));

  const columns = [
    {
      key: 'datetime',
      header: 'Ngày giờ quẹt',
      width: pixel(170),
      renderCell: (row) => `${formatDate(row.tap_date)} ${row.tap_time}`,
    },
    {
      key: 'card',
      header: 'Mã thẻ',
      width: pixel(150),
      renderCell: (row) => row.card_id,
    },
    {
      key: 'employee',
      header: 'Nhân viên',
      width: proportional(2),
      renderCell: (row) => `${row.employee_code} — ${row.employee_name}`,
    },
    {
      key: 'flags',
      header: 'Cờ cảnh báo',
      width: proportional(1),
      renderCell: (row) =>
        row.flags.length > 0
          ? row.flags.map((f) => FLAG_LABELS[f] ?? f).join(', ')
          : '—',
    },
  ];

  return (
    <VStack gap={4}>
      <Heading level={4} weight="bold">
        Lượt quẹt cần xem lại
      </Heading>

      <HStack gap={3} align="end">
        <TextInput
          label="Ngày"
          placeholder="dd-mm-yyyy"
          value={dateInput}
          onChange={(v) => {
            setDateInput(v);
            const iso = parseDateInput(v);
            // Chỉ lọc khi nhập đủ ngày hợp lệ (hoặc xóa trắng)
            if (iso === '' || /^\d{4}-\d{2}-\d{2}$/.test(iso)) {
              setTapDate(iso);
              setPage(1);
            }
          }}
          hasClear
          width={170}
        />
        <TextInput
          label="Mã thẻ"
          isLabelHidden
          placeholder="Mã thẻ..."
          value={cardId}
          onChange={(v) => { setCardId(v); setPage(1); }}
          hasClear
          width={200}
        />
        <Selector
          label="Cờ cảnh báo"
          isLabelHidden
          value={flag}
          onChange={(v) => { setFlag(v ?? ''); setPage(1); }}
          options={[
            { value: '', label: 'Tất cả' },
            { value: 'single_tap', label: 'Chỉ 1 lượt/ngày' },
            { value: 'excess_taps', label: 'Trên 6 lượt/ngày' },
            { value: 'odd_hours', label: 'Ngoài 05:00–21:00' },
          ]}
          width={220}
        />
        <Text type="supporting">{loading ? 'Đang tải...' : `${total} lượt`}</Text>
      </HStack>

      {error ? <Text>{error}</Text> : null}

      <Table
        data={taps}
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
