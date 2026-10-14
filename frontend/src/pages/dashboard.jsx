import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { Text } from '@astryxdesign/core/Text';
import { VStack } from '@astryxdesign/core/VStack';

// Khung giả định — số liệu mock. TODO: lấy từ API khi backend có endpoint thống kê.
const STATS = [
  { label: 'Tổng nhân viên', value: '9.842' },
  { label: 'Đang làm việc', value: '9.105' },
  { label: 'Đơn vị / phân xưởng', value: '24' },
  { label: 'Bản ghi chấm công hôm nay', value: '31.208' },
];

function Stat({ label, value }) {
  return (
    <VStack gap={1}>
      <Text type="supporting">{label}</Text>
      <Heading level={4}>{value}</Heading>
    </VStack>
  );
}

export default function Dashboard() {
  return (
    <VStack gap={4}>
      <Heading level={4} weight="bold">Dashboard</Heading>
      <HStack gap={8}>
        {STATS.map((s) => (
          <Stat key={s.label} label={s.label} value={s.value} />
        ))}
      </HStack>
      <Text type="supporting">
        Khung giả định — module Chấm công và Tính lương đang được phát triển,
        sẽ xuất hiện ở menu bên trái khi sẵn sàng.
      </Text>
    </VStack>
  );
}
