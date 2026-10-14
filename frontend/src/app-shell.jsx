import { AppShell } from '@astryxdesign/core/AppShell';
import { Button } from '@astryxdesign/core/Button';
import { DropdownMenu } from '@astryxdesign/core/DropdownMenu';
import { DropdownMenuItem } from '@astryxdesign/core/DropdownMenu';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { SideNav } from '@astryxdesign/core/SideNav';
import { SideNavItem } from '@astryxdesign/core/SideNav';
import { SideNavSection } from '@astryxdesign/core/SideNav';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { TopNav } from '@astryxdesign/core/TopNav';
import { useState } from 'react';
import { useLocation } from 'react-router-dom';

const NAV = [
  {
    section: 'Tổng quan',
    items: [{ label: 'Dashboard', href: '#/' }],
  },
  {
    section: 'Nhân sự',
    items: [{ label: 'Quản lý nhân viên', href: '#/employees' }],
  },
  {
    section: 'Danh mục',
    items: [
      { label: 'Đơn vị', href: '#/catalogs/departments' },
      { label: 'Chức vụ', href: '#/catalogs/positions' },
      { label: 'Công việc', href: '#/catalogs/job-titles' },
      { label: 'Tỉnh thành', href: '#/catalogs/provinces' },
    ],
  },
  {
    section: 'Chấm công',
    items: [{ label: 'Dữ liệu chấm công', href: '#/attendance', disabled: true, note: 'Sắp có' }],
  },
  {
    section: 'Lương',
    items: [{ label: 'Bảng lương tháng', href: '#/payroll', disabled: true, note: 'Sắp có' }],
  },
];

function TopBar() {
  const [search, setSearch] = useState('');
  return (
    <TopNav
      label="Thanh điều hướng chính"
      heading={<Heading level={4}>NSDT NextGen</Heading>}
      endContent={
        <HStack gap={2} align="center">
          {/* TODO: mở command palette (Ctrl+K) khi có nhiều màn hình */}
          <TextInput
            label="Tìm kiếm"
            isLabelHidden
            placeholder="Tìm kiếm (Ctrl+K)"
            value={search}
            onChange={setSearch}
            startIcon="search"
            width={240}
          />
          <DropdownMenu
            trigger={<Button variant="ghost" label="Tài khoản">Tài khoản</Button>}
          >
            <DropdownMenuItem label="Quản trị viên" description="admin@nsdt.local" />
            <DropdownMenuItem label="Đăng xuất" onClick={() => {}} />
          </DropdownMenu>
        </HStack>
      }
    />
  );
}

function Rail() {
  const { hash } = useLocation();
  const path = hash.replace(/^#/, '') || '/';
  return (
    <SideNav
      label="Điều hướng chính"
      resizable={{ defaultWidth: 240, minWidth: 180, maxWidth: 400 }}
    >
      {NAV.map((group) => (
        <SideNavSection key={group.section} title={group.section}>
          {group.items.map((item) => (
            <SideNavItem
              key={item.label}
              label={item.label}
              href={item.disabled ? undefined : item.href}
              isSelected={!item.disabled && item.href === `#${path}`}
              isDisabled={item.disabled}
              endContent={
                item.note ? <Text type="supporting" size="xsm">{item.note}</Text> : null
              }
            />
          ))}
        </SideNavSection>
      ))}
    </SideNav>
  );
}

export default function AppShellFrame({ children }) {
  return (
    <AppShell
      height="fill"
      contentPadding={4}
      topNav={<TopBar />}
      sideNav={<Rail />}
    >
      {children}
    </AppShell>
  );
}
