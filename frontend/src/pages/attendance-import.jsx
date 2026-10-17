// Nạp file chấm công — luồng 3 bước: chọn file -> báo cáo kiểm tra -> xác nhận nạp.
import { Button } from '@astryxdesign/core/Button';
import { Divider } from '@astryxdesign/core/Divider';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { Switch } from '@astryxdesign/core/Switch';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Text } from '@astryxdesign/core/Text';
import { VStack } from '@astryxdesign/core/VStack';
import { useRef, useState } from 'react';
import { Link } from 'react-router-dom';

import { PlusIcon } from '../components/icons.jsx';
import { attendanceApi } from '../lib/api.js';
import { formatDate } from '../lib/format.js';

const FLAG_LABELS = {
  single_tap: 'Chỉ 1 lượt/ngày',
  excess_taps: 'Trên 6 lượt/ngày',
  odd_hours: 'Ngoài 05:00–21:00',
};

const ERROR_LABELS = {
  E1: 'Sai số cột',
  E2: 'Ngày không hợp lệ',
  E3: 'Giờ không hợp lệ',
  E4: 'Mã thẻ rỗng/không phải số',
  E5: 'Ngày trong tương lai',
};

function Stat({ label, value }) {
  return (
    <VStack gap={1}>
      <Text type="supporting">{label}</Text>
      <Text weight="bold">{value}</Text>
    </VStack>
  );
}

export default function AttendanceImport() {
  const [report, setReport] = useState(null);
  const [confirmed, setConfirmed] = useState([]);
  const [replaceExisting, setReplaceExisting] = useState(false);
  const [showErrors, setShowErrors] = useState(false);
  const [expandedDedupCard, setExpandedDedupCard] = useState(null);
  const [parsing, setParsing] = useState(false);
  const [importing, setImporting] = useState(false);
  const [error, setError] = useState('');
  const [result, setResult] = useState(null);
  const [unmatchedPage, setUnmatchedPage] = useState(1);
  const fileRef = useRef(null);

  const pickFile = () => fileRef.current?.click();

  const onFile = async (e) => {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;
    setParsing(true);
    setError('');
    setResult(null);
    try {
      const rep = await attendanceApi.parse(file);
      setReport(rep);
      setConfirmed([]);
      setReplaceExisting(false);
      setShowErrors(false);
      setExpandedDedupCard(null);
      setUnmatchedPage(1);
    } catch (err) {
      setError(err.message);
      setReport(null);
    } finally {
      setParsing(false);
    }
  };

  const allConfirmed =
    report != null && report.dates.every((d) => confirmed.includes(d.date));
  const canImport =
    report != null &&
    allConfirmed &&
    report.importable_rows > 0 &&
    (!report.already_imported || replaceExisting) &&
    !importing;

  const doImport = async () => {
    setImporting(true);
    setError('');
    try {
      const res = await attendanceApi.importFile({
        uploadId: report.upload_id,
        confirmedDates: confirmed,
        replaceExisting,
      });
      setResult(res);
    } catch (err) {
      setError(err.message);
    } finally {
      setImporting(false);
    }
  };

  const reset = () => {
    setReport(null);
    setResult(null);
    setConfirmed([]);
    setExpandedDedupCard(null);
    setError('');
  };

  const toggleDate = (iso) =>
    setConfirmed((c) => (c.includes(iso) ? c.filter((d) => d !== iso) : [...c, iso]));

  // Thẻ chưa khớp: phân trang phía client, 25 dòng/trang
  const UNMATCHED_PAGE_SIZE = 25;
  const unmatchedTotal = report?.unmatched_cards.length ?? 0;
  const unmatchedPageCount = Math.max(1, Math.ceil(unmatchedTotal / UNMATCHED_PAGE_SIZE));
  const unmatchedRows = (report?.unmatched_cards ?? []).slice(
    (unmatchedPage - 1) * UNMATCHED_PAGE_SIZE,
    unmatchedPage * UNMATCHED_PAGE_SIZE,
  );

  const dateColumns = [
    {
      key: 'date',
      header: 'Ngày trong file',
      width: proportional(1),
      renderCell: (row) => formatDate(row.date),
    },
    {
      key: 'rows',
      header: 'Số dòng',
      width: pixel(120),
      align: 'center',
      renderCell: (row) => row.rows,
    },
    {
      key: 'confirm',
      header: 'Đã kiểm tra',
      width: pixel(250),
      align: 'center',
      renderCell: (row) => (
        <Switch
          label={`Xác nhận ngày ${formatDate(row.date)}`}
          value={confirmed.includes(row.date)}
          onChange={() => toggleDate(row.date)}
        />
      ),
    },
  ];

  const unmatchedColumns = [
    {
      key: 'card',
      header: 'Mã thẻ',
      width: pixel(140),
      renderCell: (row) => row.card_id,
    },
    {
      key: 'taps',
      header: 'Lượt quẹt',
      width: pixel(100),
      align: 'center',
      renderCell: (row) => row.taps,
    },
    {
      key: 'deduped',
      header: 'Gộp trùng',
      width: pixel(100),
      align: 'center',
      renderCell: (row) => (row.deduped > 0 ? row.deduped : '—'),
    },
    {
      key: 'times',
      header: 'Thời gian quẹt',
      width: proportional(2),
      renderCell: (row) => (
        <VStack gap={1}>
          {Object.entries(row.times ?? {}).map(([d, times]) => (
            <Text key={d} type="supporting">
              {formatDate(d)}: {times.join(', ')}
            </Text>
          ))}
        </VStack>
      ),
    },
  ];

  return (
    <VStack gap={4}>
      <HStack justify="between" align="center" width="100%">
        <Heading level={4} weight="bold">
          Nạp file chấm công
        </Heading>
        <input
          ref={fileRef}
          type="file"
          accept=".txt"
          hidden
          onChange={onFile}
        />
        <Button
          variant="primary"
          label="Chọn file chấm công"
          icon={<PlusIcon />}
          onClick={pickFile}
          isDisabled={parsing}
        >
          {parsing ? 'Đang kiểm tra...' : 'Chọn file'}
        </Button>
      </HStack>

      {error ? <Text>{error}</Text> : null}

      {!report && !result && !parsing && (
        <Text type="supporting">
          Chọn file .txt từ máy chấm công. Hệ thống sẽ kiểm tra lỗi định dạng,
          trùng quẹt, thẻ lạ rồi mới cho nạp vào dữ liệu.
        </Text>
      )}

      {report && !result && (
        <VStack gap={4}>
          <HStack gap={6}>
            <Stat label="File" value={report.filename} />
            <Stat label="Tổng dòng" value={report.total_rows} />
            <Stat label="Hợp lệ" value={report.valid_rows} />
            <Stat label="Lỗi" value={report.error_rows} />
            <Stat label="Đã gộp trùng" value={report.deduped_count} />
            <Stat label="Nạp được" value={report.importable_rows} />
          </HStack>

          {report.deduped_count > 0 && (
            <>
              <VStack gap={2}>
                <Text weight="bold">
                  {report.deduped_count} lượt trùng đã gộp (giữ lại lượt quẹt đầu
                  tiên) — bấm vào từng thẻ để xem các lượt gần nhau
                </Text>
                <Table
                  data={report.dedup_by_card}
                  columns={[
                    { key: 'card', header: 'Mã thẻ', width: proportional(1), renderCell: (r) => r.card_id },
                    { key: 'count', header: 'Số lượt bị gộp', width: pixel(140), align: 'center', renderCell: (r) => r.merged_count },
                    {
                      key: 'detail',
                      header: 'Chi tiết',
                      width: pixel(120),
                      align: 'center',
                      renderCell: (r) => (
                        <Button
                          size="sm"
                          variant="ghost"
                          label={expandedDedupCard === r.card_id ? `Ẩn chi tiết thẻ ${r.card_id}` : `Xem chi tiết thẻ ${r.card_id}`}
                          onClick={() => setExpandedDedupCard((c) => (c === r.card_id ? null : r.card_id))}
                        >
                          {expandedDedupCard === r.card_id ? 'Ẩn' : 'Xem'}
                        </Button>
                      ),
                    },
                  ]}
                  idKey="card_id"
                  density="balanced"
                  verticalAlign="middle"
                  hasHover
                />
                {(() => {
                  const g = report.dedup_by_card.find((x) => x.card_id === expandedDedupCard);
                  if (!g) return null;
                  return (
                    <VStack gap={2}>
                      <Text weight="bold">
                        Thẻ {g.card_id}: {g.merged_count} lượt bị gộp
                      </Text>
                      <Table
                        data={g.samples}
                        columns={[
                          { key: 'line', header: 'Dòng bị gộp', width: pixel(110), align: 'center', renderCell: (r) => r.line_no },
                          { key: 'time', header: 'Giờ quẹt', width: pixel(170), renderCell: (r) => r.tap_datetime },
                          { key: 'kept', header: 'Gộp vào dòng', width: pixel(110), align: 'center', renderCell: (r) => r.kept_line_no },
                          { key: 'kepttime', header: 'Giờ được giữ', width: pixel(170), renderCell: (r) => r.kept_datetime },
                        ]}
                        idKey="line_no"
                        density="balanced"
                        verticalAlign="middle"
                      />
                      {g.samples.length < g.merged_count && (
                        <Text type="supporting">
                          Hiện {g.samples.length}/{g.merged_count} lượt mẫu.
                        </Text>
                      )}
                    </VStack>
                  );
                })()}
              </VStack>
              <Divider />
            </>
          )}

          {report.already_imported && (
            <>
              <VStack gap={2}>
                <Text weight="bold">
                  File này đã được nạp trước đó. Mặc định sẽ bỏ qua.
                </Text>
                <Switch
                  label="Nạp lại thay thế đợt cũ"
                  value={replaceExisting}
                  onChange={setReplaceExisting}
                />
              </VStack>
              <Divider />
            </>
          )}

          <VStack gap={2}>
            <Text weight="bold">
              Ngày trong file — hãy kiểm tra rồi bật xác nhận từng ngày
            </Text>
            <Table
              data={report.dates}
              columns={dateColumns}
              idKey="date"
              density="balanced"
              verticalAlign="middle"
            />
          </VStack>

          <Divider />

          {report.error_rows > 0 && (
            <>
              <VStack gap={2}>
                <HStack gap={3} align="center">
                  <Text weight="bold">
                    {report.error_rows} dòng lỗi (bị loại khỏi đợt nạp)
                  </Text>
                  <Button
                    variant="ghost"
                    size="sm"
                    label={showErrors ? 'Ẩn chi tiết lỗi' : 'Xem chi tiết lỗi'}
                    onClick={() => setShowErrors((s) => !s)}
                  >
                    {showErrors ? 'Ẩn' : 'Xem'}
                  </Button>
                </HStack>
                {Object.entries(report.error_counts).map(([code, n]) => (
                  <Text key={code} type="supporting">
                    {code} — {ERROR_LABELS[code] ?? code}: {n} dòng
                  </Text>
                ))}
                {showErrors && (
                  <Table
                    data={report.error_samples}
                    columns={[
                      { key: 'line', header: 'Dòng', width: pixel(80), renderCell: (r) => r.line_no },
                      { key: 'code', header: 'Mã', width: pixel(70), renderCell: (r) => r.code },
                      {
                        key: 'msg',
                        header: 'Lý do',
                        width: proportional(1),
                        renderCell: (r) => r.message,
                      },
                    ]}
                    idKey="line_no"
                    density="balanced"
                    verticalAlign="middle"
                  />
                )}
              </VStack>
              <Divider />
            </>
          )}

          {Object.keys(report.flag_counts).length > 0 && (
            <>
              <VStack gap={1}>
                <Text weight="bold">Cảnh báo (vẫn nạp, gắn cờ để xem lại sau)</Text>
                {Object.entries(report.flag_counts).map(([flag, n]) => (
                  <Text key={flag} type="supporting">
                    {FLAG_LABELS[flag] ?? flag}: {n} lượt
                  </Text>
                ))}
              </VStack>
              <Divider />
            </>
          )}

          {report.unmatched_cards.length > 0 && (
            <>
              <VStack gap={2}>
                <Text weight="bold">
                  {report.unmatched_cards.length} thẻ chưa khớp nhân viên — sẽ bị
                  loại khỏi đợt nạp, phải gán thẻ rồi nạp lại file
                </Text>
                <Table
                  data={unmatchedRows}
                  columns={unmatchedColumns}
                  idKey="card_id"
                  density="balanced"
                  verticalAlign="middle"
                />
                {unmatchedPageCount > 1 && (
                  <HStack gap={3} align="center" justify="end">
                    <Text type="supporting">Trang {unmatchedPage} / {unmatchedPageCount}</Text>
                    <Button
                      size="sm"
                      variant="ghost"
                      label="Trang trước"
                      isDisabled={unmatchedPage <= 1}
                      onClick={() => setUnmatchedPage((p) => p - 1)}
                    >
                      Trước
                    </Button>
                    <Button
                      size="sm"
                      variant="ghost"
                      label="Trang sau"
                      isDisabled={unmatchedPage >= unmatchedPageCount}
                      onClick={() => setUnmatchedPage((p) => p + 1)}
                    >
                      Sau
                    </Button>
                  </HStack>
                )}
              </VStack>
              <Divider />
            </>
          )}

          <HStack gap={2} justify="end">
            <Button variant="ghost" label="Hủy" onClick={reset} isDisabled={importing}>
              Hủy
            </Button>
            <Button
              variant="primary"
              label="Xác nhận nạp file"
              onClick={doImport}
              isDisabled={!canImport}
            >
              {importing ? 'Đang nạp...' : 'Xác nhận nạp'}
            </Button>
          </HStack>
          {!allConfirmed && (
            <Text type="supporting">
              Hãy bật "Đã kiểm tra" cho tất cả các ngày trong file trước khi nạp.
            </Text>
          )}
        </VStack>
      )}

      {result && (
        <VStack gap={3}>
          <Text weight="bold">Nạp xong: {result.imported_rows} lượt quẹt.</Text>
          {result.skipped_unmatched > 0 && (
            <Text>
              {result.skipped_unmatched} lượt của thẻ chưa khớp đã bị loại.
            </Text>
          )}
          {result.status === 'pending_fix' ? (
            <Text>
              File đang ở trạng thái chờ xử lý.{' '}
              <Link to="/attendance/unmatched">Sang gán thẻ cho nhân viên</Link>{' '}
              rồi nạp lại file để hoàn tất.
            </Text>
          ) : (
            <Text>Tất cả thẻ đã khớp nhân viên.</Text>
          )}
          <HStack gap={2} justify="end">
            <Button variant="secondary" label="Nạp file khác" onClick={reset}>
              Nạp file khác
            </Button>
          </HStack>
        </VStack>
      )}
    </VStack>
  );
}
