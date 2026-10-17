// Nạp file chấm công — luồng 3 bước: chọn file -> báo cáo kiểm tra -> xác nhận nạp.
import { Button } from '@astryxdesign/core/Button';
import { Banner } from '@astryxdesign/core/Banner';
import { Dialog } from '@astryxdesign/core/Dialog';
import { Divider } from '@astryxdesign/core/Divider';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { IconButton } from '@astryxdesign/core/IconButton';
import { Selector } from '@astryxdesign/core/Selector';
import { Switch } from '@astryxdesign/core/Switch';
import { Table, pixel, proportional } from '@astryxdesign/core/Table';
import { Tab, TabList } from '@astryxdesign/core/TabList';
import { Text } from '@astryxdesign/core/Text';
import { VStack } from '@astryxdesign/core/VStack';
import { useRef, useState } from 'react';
import { Link } from 'react-router-dom';

import DialogTitle from '../components/dialog-title.jsx';
import { EyeIcon, PlusIcon } from '../components/icons.jsx';
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
  const [dedupDialog, setDedupDialog] = useState(null); // group dedup đang xem chi tiết
  const [reportTab, setReportTab] = useState('overview');
  const [parsing, setParsing] = useState(false);
  const [importing, setImporting] = useState(false);
  const [error, setError] = useState('');
  const [result, setResult] = useState(null);
  const [unmatchedPage, setUnmatchedPage] = useState(1);
  const [dedupPage, setDedupPage] = useState(1);
  const [tablePageSize, setTablePageSize] = useState(25);
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
      setDedupDialog(null);
      setReportTab('overview');
      setUnmatchedPage(1);
      setDedupPage(1);
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
    setReportTab('overview');
    setUnmatchedPage(1);
    setDedupPage(1);
    setError('');
  };

  const toggleDate = (iso) =>
    setConfirmed((c) => (c.includes(iso) ? c.filter((d) => d !== iso) : [...c, iso]));

  // Thẻ chưa khớp: phân trang phía client theo cỡ bảng đã chọn
  const unmatchedTotal = report?.unmatched_cards.length ?? 0;
  const unmatchedPageCount = Math.max(1, Math.ceil(unmatchedTotal / tablePageSize));
  const unmatchedRows = (report?.unmatched_cards ?? []).slice(
    (unmatchedPage - 1) * tablePageSize,
    unmatchedPage * tablePageSize,
  );

  // Gộp trùng theo thẻ: phân trang phía client theo cỡ bảng đã chọn
  const dedupGroups = report?.dedup_by_card ?? [];
  const dedupPageCount = Math.max(1, Math.ceil(dedupGroups.length / tablePageSize));
  const dedupRows = dedupGroups.slice(
    (dedupPage - 1) * tablePageSize,
    dedupPage * tablePageSize,
  );

  const changeTablePageSize = (v) => {
    setTablePageSize(Number(v));
    setUnmatchedPage(1);
    setDedupPage(1);
  };

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
      width: pixel(300),
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
        <HStack gap={3} align="end">
          <Selector
            label="Số dòng mỗi bảng"
            isLabelHidden
            value={String(tablePageSize)}
            onChange={changeTablePageSize}
            options={[
              { value: '25', label: '25 dòng/bảng' },
              { value: '50', label: '50 dòng/bảng' },
              { value: '100', label: '100 dòng/bảng' },
            ]}
            width={150}
          />
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
          <TabList value={reportTab} onChange={setReportTab} hasDivider>
            <Tab value="overview" label="Tổng quan" />
            <Tab value="dates" label="Ngày trong file" />
            <Tab
              value="unmatched"
              label={`Thẻ chưa khớp (${report.unmatched_cards.length})`}
            />
          </TabList>

          {reportTab === 'overview' && (
            <VStack gap={4}>
              <HStack gap={6}>
                <Stat label="File" value={report.filename} />
                <Stat label="Tổng dòng" value={report.total_rows} />
                <Stat label="Hợp lệ" value={report.valid_rows} />
                <Stat label="Lỗi" value={report.error_rows} />
                <Stat label="Đã gộp trùng" value={report.deduped_count} />
                <Stat label="Nạp được" value={report.importable_rows} />
              </HStack>

              {report.already_imported && (
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
              )}

              {report.error_rows > 0 && (
                <VStack gap={2}>
                  <Banner
                    status="error"
                    title={`${report.error_rows} dòng lỗi — bị loại khỏi đợt nạp`}
                    description={Object.entries(report.error_counts)
                      .map(([code, n]) => `${code} (${ERROR_LABELS[code] ?? code}): ${n} dòng`)
                      .join('; ')}
                    endContent={
                      <Button
                        variant="ghost"
                        size="sm"
                        label={showErrors ? 'Ẩn chi tiết lỗi' : 'Xem chi tiết lỗi'}
                        onClick={() => setShowErrors((s) => !s)}
                      >
                        {showErrors ? 'Ẩn' : 'Xem'}
                      </Button>
                    }
                  />
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
              )}

              {Object.keys(report.flag_counts).length > 0 && (
                <Banner
                  status="warning"
                  title="Cảnh báo — vẫn nạp, gắn cờ để xem lại sau"
                  description={Object.entries(report.flag_counts)
                    .map(([flag, n]) => `${FLAG_LABELS[flag] ?? flag}: ${n} lượt`)
                    .join('; ')}
                />
              )}

              {report.deduped_count > 0 && (
                <VStack gap={2}>
                  <Text weight="bold">
                    {report.deduped_count} lượt trùng đã gộp (giữ lại lượt quẹt đầu
                    tiên) — bấm vào từng thẻ để xem các lượt gần nhau
                  </Text>
                  <Table
                    data={dedupRows}
                    columns={[
                      { key: 'card', header: 'Mã thẻ', width: proportional(1), renderCell: (r) => r.card_id },
                      { key: 'count', header: 'Số lượt bị gộp', width: pixel(140), align: 'center', renderCell: (r) => r.merged_count },
                      {
                        key: 'detail',
                        header: 'Chi tiết',
                        width: pixel(80),
                        align: 'center',
                        renderCell: (r) => (
                          <IconButton
                            icon={<EyeIcon />}
                            label={`Xem các lượt quẹt gần nhau của thẻ ${r.card_id}`}
                            tooltip="Xem chi tiết"
                            variant="ghost"
                            size="sm"
                            onClick={() => setDedupDialog(r)}
                          />
                        ),
                      },
                    ]}
                    idKey="card_id"
                    density="balanced"
                    verticalAlign="middle"
                    hasHover
                  />
                  {dedupPageCount > 1 && (
                    <HStack gap={3} align="center" justify="end">
                      <Text type="supporting">Trang {dedupPage} / {dedupPageCount}</Text>
                      <Button
                        size="sm"
                        variant="ghost"
                        label="Trang trước"
                        isDisabled={dedupPage <= 1}
                        onClick={() => setDedupPage((p) => p - 1)}
                      >
                        Trước
                      </Button>
                      <Button
                        size="sm"
                        variant="ghost"
                        label="Trang sau"
                        isDisabled={dedupPage >= dedupPageCount}
                        onClick={() => setDedupPage((p) => p + 1)}
                      >
                        Sau
                      </Button>
                    </HStack>
                  )}
                </VStack>
              )}

            </VStack>
          )}

          {reportTab === 'dates' && (
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
              {!allConfirmed && (
                <Text type="supporting">
                  Hãy bật "Đã kiểm tra" cho tất cả các ngày trong file trước khi nạp.
                </Text>
              )}
            </VStack>
          )}

          {reportTab === 'unmatched' && (
            <VStack gap={2}>
              {report.unmatched_cards.length === 0 ? (
                <Text type="supporting">Không có thẻ nào chưa khớp nhân viên.</Text>
              ) : (
                <>
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
                </>
              )}
            </VStack>
          )}

          <Divider />

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

      {dedupDialog && (() => {
        const shownDropped = dedupDialog.lines.filter((l) => !l.kept).length;
        return (
          <Dialog
            isOpen
            onOpenChange={(open) => !open && setDedupDialog(null)}
            width={560}
            maxHeight="85dvh"
          >
            <DialogTitle onClose={() => setDedupDialog(null)}>
              Thẻ {dedupDialog.card_id}: các lượt quẹt gần nhau
            </DialogTitle>
            <VStack gap={2} padding={4}>
              <Text type="supporting">
                {dedupDialog.merged_count} lượt bị gộp — dòng được giữ lại tô màu
                nổi bật.
              </Text>
              <VStack gap={1}>
                {dedupDialog.lines.map((l) => (
                  <Text
                    key={l.line_no}
                    color={l.kept ? 'accent' : undefined}
                    weight={l.kept ? 'bold' : undefined}
                  >
                    Dòng {l.line_no} — {l.tap_datetime}
                    {l.kept ? ' (giữ lại)' : ''}
                  </Text>
                ))}
              </VStack>
              {shownDropped < dedupDialog.merged_count && (
                <Text type="supporting">
                  Hiện một phần ({shownDropped}/{dedupDialog.merged_count} lượt bị gộp).
                </Text>
              )}
            </VStack>
            <HStack gap={2} justify="end" padding={4}>
              <Button variant="ghost" label="Đóng" onClick={() => setDedupDialog(null)}>
                Đóng
              </Button>
            </HStack>
          </Dialog>
        );
      })()}
    </VStack>
  );
}
