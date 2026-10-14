import { AppShell } from '@astryxdesign/core/AppShell';
import { Button } from '@astryxdesign/core/Button';
import { CommandPalette } from '@astryxdesign/core/CommandPalette';
import { DropdownMenu } from '@astryxdesign/core/DropdownMenu';
import { DropdownMenuItem } from '@astryxdesign/core/DropdownMenu';
import { Heading } from '@astryxdesign/core/Heading';
import { HStack } from '@astryxdesign/core/HStack';
import { SideNav } from '@astryxdesign/core/SideNav';
import { SideNavItem } from '@astryxdesign/core/SideNav';
import { SideNavSection } from '@astryxdesign/core/SideNav';
import { Stack } from '@astryxdesign/core/Stack';
import { Text } from '@astryxdesign/core/Text';
import { TextInput } from '@astryxdesign/core/TextInput';
import { TopNav } from '@astryxdesign/core/TopNav';
import { createStaticSource } from '@astryxdesign/core/Typeahead';
import { useEffect, useMemo, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';

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

// Lệnh điều hướng trong command palette (Ctrl+K) — keywords không dấu để tìm dễ hơn
const PALETTE_COMMANDS = [
  { id: 'dashboard', label: 'Dashboard', auxiliaryData: { path: '/', keywords: ['tong quan'] } },
  { id: 'employees', label: 'Quản lý nhân viên', auxiliaryData: { path: '/employees', keywords: ['nhan vien', 'nhan su'] } },
  { id: 'departments', label: 'Đơn vị', auxiliaryData: { path: '/catalogs/departments', keywords: ['don vi', 'danh muc'] } },
  { id: 'positions', label: 'Chức vụ', auxiliaryData: { path: '/catalogs/positions', keywords: ['chuc vu', 'danh muc'] } },
  { id: 'job-titles', label: 'Công việc', auxiliaryData: { path: '/catalogs/job-titles', keywords: ['cong viec', 'danh muc'] } },
  { id: 'provinces', label: 'Tỉnh thành', auxiliaryData: { path: '/catalogs/provinces', keywords: ['tinh thanh', 'danh muc'] } },
];

function TopBar({ onOpenPalette }) {
  return (
    <TopNav
      label="Thanh điều hướng chính"
      heading={<Heading level={4}>NSDT NextGen</Heading>}
      endContent={
        <HStack gap={2} align="center">
          <Stack onClick={onOpenPalette}>
            <TextInput
              label="Tìm kiếm"
              isLabelHidden
              placeholder="Tìm kiếm (Ctrl+K)"
              value=""
              onChange={() => {}}
              isReadOnly
              startIcon="search"
              width={240}
            />
          </Stack>
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
  const [isPaletteOpen, setIsPaletteOpen] = useState(false);
  const navigate = useNavigate();
  const searchSource = useMemo(
    () =>
      createStaticSource(PALETTE_COMMANDS, {
        keywords: (item) => item.auxiliaryData?.keywords ?? [],
      }),
    [],
  );

  useEffect(() => {
    const onKeyDown = (e) => {
      if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        setIsPaletteOpen(true);
      }
    };
    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, []);

  const handleSelect = (id) => {
    const cmd = PALETTE_COMMANDS.find((c) => c.id === id);
    if (cmd) navigate(cmd.auxiliaryData.path);
    setIsPaletteOpen(false);
  };

  return (
    <>
      <AppShell
        height="fill"
        contentPadding={4}
        topNav={<TopBar onOpenPalette={() => setIsPaletteOpen(true)} />}
        sideNav={<Rail />}
      >
        {children}
      </AppShell>
      <CommandPalette
        isOpen={isPaletteOpen}
        onOpenChange={setIsPaletteOpen}
        searchSource={searchSource}
        label="Điều hướng nhanh"
        emptySearchText="Không tìm thấy màn hình nào"
        onValueChange={handleSelect}
      />
    </>
  );
}
