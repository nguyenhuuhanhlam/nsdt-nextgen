# Database schema — `nskl_section_b`

- Trích xuất: 2026-10-14 19:36 (giờ máy)
- MySQL version: `5.1.73-community`
- Database charset: `utf8` / collation: `utf8_general_ci`
- Phạm vi: chỉ đọc cấu trúc (SHOW / INFORMATION_SCHEMA), KHÔNG đọc dữ liệu

## Tổng quan

- Tables: 71
- Views: 0

## Tables

### `___mcc_analysis`

```sql
CREATE TABLE `___mcc_analysis` (
  `ma_the` bigint(20) DEFAULT NULL,
  `ngaythangnam` date DEFAULT NULL,
  `gio_trongngay` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_f`

```sql
CREATE TABLE `___mcc_f` (
  `fname` date NOT NULL,
  PRIMARY KEY (`fname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_log`

```sql
CREATE TABLE `___mcc_log` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_log_chitiet`

```sql
CREATE TABLE `___mcc_log_chitiet` (
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngaythangnam` date NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_log_month`

```sql
CREATE TABLE `___mcc_log_month` (
  `ma_nhanvien` varchar(16) DEFAULT NULL,
  `ngaythangnam` date NOT NULL,
  `h1` time NOT NULL,
  `h2` time NOT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_log_thang`

```sql
CREATE TABLE `___mcc_log_thang` (
  `ma_nhanvien` varchar(16) DEFAULT NULL,
  `ngaythangnam` date NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_log_thang_ao`

```sql
CREATE TABLE `___mcc_log_thang_ao` (
  `ma_nhanvien` varchar(16) DEFAULT NULL,
  `ngaythangnam` date NOT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL,
  `giohanhchanh` time DEFAULT NULL,
  `giolamthem` time DEFAULT NULL,
  `giochunhat` time DEFAULT NULL,
  `tongthoigian` time DEFAULT NULL,
  `tongngay` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_log_thang_copy`

```sql
CREATE TABLE `___mcc_log_thang_copy` (
  `ma_nhanvien` varchar(16) DEFAULT NULL,
  `ngaythangnam` date NOT NULL,
  `giovao` time DEFAULT NULL,
  `giora` time DEFAULT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `___mcc_working_day`

```sql
CREATE TABLE `___mcc_working_day` (
  `day` date NOT NULL,
  `holiday` tinyint(4) DEFAULT '0',
  `disable` tinyint(4) DEFAULT '0',
  `closingTime` time DEFAULT NULL,
  PRIMARY KEY (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `_timerecord`

```sql
CREATE TABLE `_timerecord` (
  `Event` char(2) NOT NULL,
  `Date` char(10) NOT NULL,
  `Time` time NOT NULL,
  `IDCode` char(16) NOT NULL,
  `Shift` char(2) DEFAULT NULL,
  `MachineNumber` char(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `baohiemxahoi`

```sql
CREATE TABLE `baohiemxahoi` (
  `id_baohiemxahoi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngay_batdau` date NOT NULL,
  `muc_bhxh` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_baohiemxahoi`),
  UNIQUE KEY `ngay_batdau` (`ngay_batdau`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `baohiemyte`

```sql
CREATE TABLE `baohiemyte` (
  `id_baohiemyte` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngay_batdau` date NOT NULL,
  `muc_bhyt` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_baohiemyte`),
  UNIQUE KEY `ngay_batdau` (`ngay_batdau`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `calendar`

```sql
CREATE TABLE `calendar` (
  `dt` date NOT NULL,
  `y` smallint(6) DEFAULT NULL,
  `m` tinyint(4) DEFAULT NULL,
  `d` tinyint(4) DEFAULT NULL,
  `dn` varchar(2) DEFAULT NULL,
  `isHoliday` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `chamcong_chitiet_ngay`

```sql
CREATE TABLE `chamcong_chitiet_ngay` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
```

- Engine: `MEMORY` | Collation: `utf8_general_ci` | Created: `2026-08-02 08:41:32`

### `diaban`

```sql
CREATE TABLE `diaban` (
  `id_tp` int(10) unsigned NOT NULL,
  `ten_tp` varchar(255) NOT NULL,
  `id_huyen` bigint(20) unsigned NOT NULL,
  `ten_huyen` varchar(255) NOT NULL,
  `id_phuong` bigint(20) unsigned NOT NULL,
  `ten_phuong` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `dm_nghidaihan`

```sql
CREATE TABLE `dm_nghidaihan` (
  `id_nghidaihan` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `nghidaihan` varchar(255) NOT NULL,
  PRIMARY KEY (`id_nghidaihan`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ergobaby_products`

```sql
CREATE TABLE `ergobaby_products` (
  `products_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `products_code` varchar(100) NOT NULL,
  `part_code` tinyint(4) NOT NULL,
  `steps` varchar(255) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `products_rate` float NOT NULL DEFAULT '0',
  `products_rate_header` float NOT NULL DEFAULT '0',
  `note` varchar(255) DEFAULT NULL,
  `kiemtra` tinyint(4) NOT NULL DEFAULT '1',
  `time_entry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`products_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ergobaby_quantity_products`

```sql
CREATE TABLE `ergobaby_quantity_products` (
  `ergobaby_quantity_products_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `id_donvi` int(10) NOT NULL,
  `products_code` varchar(100) NOT NULL,
  `to_month` date NOT NULL,
  `part_code` tinyint(4) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`ergobaby_quantity_products_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `luongcoban`

```sql
CREATE TABLE `luongcoban` (
  `id_lcb` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngay_batdau` date NOT NULL,
  `luongcoban` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`id_lcb`),
  UNIQUE KEY `ngay_batdau` (`ngay_batdau`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc`

```sql
CREATE TABLE `mcc` (
  `ngaythangnam` date DEFAULT NULL,
  `gio` time DEFAULT NULL,
  `ma_the` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc3_log`

```sql
CREATE TABLE `mcc3_log` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL,
  KEY `ngaythangnam` (`ngaythangnam`),
  KEY `ma_nhanvien` (`ma_nhanvien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc3_log_all`

```sql
CREATE TABLE `mcc3_log_all` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `giovao` time DEFAULT NULL,
  `giora` time DEFAULT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc3_log_debug`

```sql
CREATE TABLE `mcc3_log_debug` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  `kieuthaisan` tinyint(4) DEFAULT NULL,
  KEY `ngaythangnam` (`ngaythangnam`),
  KEY `ma_nhanvien` (`ma_nhanvien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2023-06-02 14:21:24`

### `mcc_analysis`

```sql
CREATE TABLE `mcc_analysis` (
  `ma_the` bigint(20) DEFAULT NULL,
  `ngaythangnam` date DEFAULT NULL,
  `gio_trongngay` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_chitiet_ngaycong`

```sql
CREATE TABLE `mcc_chitiet_ngaycong` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `MEMORY` | Collation: `utf8_general_ci` | Created: `2026-08-02 08:41:32`

### `mcc_chitiet_quetthe_tho`

```sql
CREATE TABLE `mcc_chitiet_quetthe_tho` (
  `ngaythangnam` date DEFAULT NULL,
  `gio` time DEFAULT NULL,
  `ma_the` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_debug`

```sql
CREATE TABLE `mcc_debug` (
  `ngaythangnam` date DEFAULT NULL,
  `gio` time DEFAULT NULL,
  `ma_the` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2024-05-20 16:44:48`

### `mcc_f`

```sql
CREATE TABLE `mcc_f` (
  `fname` date NOT NULL,
  PRIMARY KEY (`fname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_log`

```sql
CREATE TABLE `mcc_log` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_log_chitiet_quetthe`

```sql
CREATE TABLE `mcc_log_chitiet_quetthe` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_log_chitiet_quetthe_tho`

```sql
CREATE TABLE `mcc_log_chitiet_quetthe_tho` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_log_month`

```sql
CREATE TABLE `mcc_log_month` (
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngaythangnam` date NOT NULL,
  `h1` time NOT NULL,
  `h2` time NOT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_log_temp`

```sql
CREATE TABLE `mcc_log_temp` (
  `ngaythangnam` date NOT NULL,
  `ma_nhanvien` varchar(16) NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_log_thaisan`

```sql
CREATE TABLE `mcc_log_thaisan` (
  `ma_nhanvien` varchar(16) DEFAULT NULL,
  `ngaythangnam` date NOT NULL,
  `h1` time DEFAULT NULL,
  `h2` time DEFAULT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL
) ENGINE=MEMORY DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `MEMORY` | Collation: `utf8_general_ci` | Created: `2026-08-02 08:41:32`

### `mcc_temp`

```sql
CREATE TABLE `mcc_temp` (
  `ngaythangnam` date DEFAULT NULL,
  `gio` time DEFAULT NULL,
  `ma_the` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_temp_debug`

```sql
CREATE TABLE `mcc_temp_debug` (
  `ngaythangnam` date DEFAULT NULL,
  `gio` time DEFAULT NULL,
  `ma_the` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2023-06-01 15:31:27`

### `mcc_working_day`

```sql
CREATE TABLE `mcc_working_day` (
  `day` date DEFAULT NULL,
  `holiday` tinyint(4) DEFAULT NULL,
  `disable` tinyint(4) DEFAULT NULL,
  `closingTime` time DEFAULT NULL,
  `id_donvi` int(10) DEFAULT NULL,
  KEY `day` (`day`),
  KEY `id_donvi` (`id_donvi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `mcc_working_day_copy`

```sql
CREATE TABLE `mcc_working_day_copy` (
  `day` date DEFAULT NULL,
  `holiday` tinyint(4) DEFAULT NULL,
  `disable` tinyint(4) DEFAULT NULL,
  `closingTime` time DEFAULT NULL,
  `id_donvi` int(10) DEFAULT NULL,
  KEY `day` (`day`),
  KEY `id_donvi` (`id_donvi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_bhxh`

```sql
CREATE TABLE `ns_bhxh` (
  `id_bhxh` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `so_bhxh` int(10) NOT NULL,
  `ngay_batdau` date NOT NULL,
  `ngay_ketthuc` date NOT NULL,
  `lydo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_bhxh`),
  UNIQUE KEY `ma_nhanvien` (`ma_nhanvien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_chucvu`

```sql
CREATE TABLE `ns_chucvu` (
  `id_chucvu` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `chucvu` varchar(45) NOT NULL,
  PRIMARY KEY (`id_chucvu`),
  UNIQUE KEY `chucvu` (`chucvu`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_chuyencan`

```sql
CREATE TABLE `ns_chuyencan` (
  `id_chuyencan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngay_tru` date NOT NULL,
  `truchuyencan` smallint(6) NOT NULL,
  `lydo` varchar(255) DEFAULT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_chuyencan`)
) ENGINE=InnoDB AUTO_INCREMENT=8823 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_congviec`

```sql
CREATE TABLE `ns_congviec` (
  `id_congviec` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `congviec` varchar(100) NOT NULL,
  PRIMARY KEY (`id_congviec`),
  UNIQUE KEY `congviec` (`congviec`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_dantoc`

```sql
CREATE TABLE `ns_dantoc` (
  `id_dantoc` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `dantoc` varchar(45) NOT NULL,
  PRIMARY KEY (`id_dantoc`),
  UNIQUE KEY `dantoc` (`dantoc`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_donvi`

```sql
CREATE TABLE `ns_donvi` (
  `id_donvi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donvi` varchar(45) NOT NULL,
  PRIMARY KEY (`id_donvi`),
  UNIQUE KEY `donvi` (`donvi`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_donvi_12-07-2017`

```sql
CREATE TABLE `ns_donvi_12-07-2017` (
  `id_donvi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `donvi` varchar(45) NOT NULL,
  PRIMARY KEY (`id_donvi`),
  UNIQUE KEY `donvi` (`donvi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_hdld`

```sql
CREATE TABLE `ns_hdld` (
  `id_hdld` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `loaihopdong` varchar(255) NOT NULL,
  PRIMARY KEY (`id_hdld`),
  UNIQUE KEY `loaihopdong` (`loaihopdong`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:21`

### `ns_hesoluong`

```sql
CREATE TABLE `ns_hesoluong` (
  `id_hsl` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `hesoluong` float unsigned NOT NULL,
  `hesoluongnew` float unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `lydo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_hsl`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_hocvan`

```sql
CREATE TABLE `ns_hocvan` (
  `id_hocvan` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `hocvan` varchar(45) NOT NULL,
  PRIMARY KEY (`id_hocvan`),
  UNIQUE KEY `hocvan` (`hocvan`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_honnhan`

```sql
CREATE TABLE `ns_honnhan` (
  `id_honnhan` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `honnhan` varchar(45) NOT NULL,
  PRIMARY KEY (`id_honnhan`),
  UNIQUE KEY `honnhan` (`honnhan`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_kiluat`

```sql
CREATE TABLE `ns_kiluat` (
  `id_kiluat` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `kiluat` varchar(255) NOT NULL,
  PRIMARY KEY (`id_kiluat`),
  UNIQUE KEY `kiluat` (`kiluat`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_nghidaihan`

```sql
CREATE TABLE `ns_nghidaihan` (
  `id_ns_nghidaihan` bigint(9) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `id_nghidaihan` tinyint(4) unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `ngay_ketthuc` date DEFAULT NULL,
  `songaynghi` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `lydo` varchar(255) NOT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  `note` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ns_nghidaihan`),
  KEY `FK_ns_nghidaihan_dm_nghidaihan` (`id_nghidaihan`),
  CONSTRAINT `FK_ns_nghidaihan_dm_nghidaihan` FOREIGN KEY (`id_nghidaihan`) REFERENCES `dm_nghidaihan` (`id_nghidaihan`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=789 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_nhansu`

```sql
CREATE TABLE `ns_nhansu` (
  `ma_nhanvien` varchar(16) NOT NULL,
  `ma_the` bigint(16) unsigned DEFAULT NULL,
  `ho` varchar(255) NOT NULL,
  `ten` varchar(25) NOT NULL,
  `socmnd` bigint(20) NOT NULL,
  `ngaysinh` date NOT NULL,
  `gioitinh` char(8) NOT NULL,
  `id_quoctich` tinyint(4) unsigned NOT NULL,
  `id_dantoc` tinyint(4) unsigned NOT NULL,
  `ngaycapcmnd` date NOT NULL,
  `noicapcmnd` tinyint(4) NOT NULL,
  `noisinh` tinyint(4) NOT NULL,
  `id_hocvan` tinyint(4) unsigned NOT NULL,
  `id_honnhan` tinyint(4) unsigned NOT NULL,
  `nguyenquan` varchar(255) NOT NULL,
  `diachithuongtru` varchar(255) NOT NULL,
  `diachicungu` varchar(255) NOT NULL,
  `id_donvi` int(10) unsigned NOT NULL,
  `id_chucvu` tinyint(4) unsigned NOT NULL,
  `id_congviec` tinyint(4) unsigned NOT NULL,
  `ngayvaolam` date NOT NULL,
  `ngayhetthuviec` date NOT NULL,
  `hesoluong` float unsigned NOT NULL,
  `id_hdld` tinyint(4) unsigned DEFAULT NULL,
  `id_nghidaihan` tinyint(4) unsigned DEFAULT NULL,
  `ngay_nghiviec` date DEFAULT NULL,
  `btkt` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `id_donvi` (`id_donvi`),
  KEY `FK_ns_nhansu_ns_chucvu` (`id_chucvu`),
  KEY `FK_ns_nhansu_ns_quoctich` (`id_quoctich`),
  KEY `FK_ns_nhansu_ns_dantoc` (`id_dantoc`),
  KEY `FK_ns_nhansu_ns_hocvan` (`id_hocvan`),
  KEY `FK_ns_nhansu_ns_honnhan` (`id_honnhan`),
  KEY `FK_ns_nhansu_ns_congviec` (`id_congviec`),
  KEY `FK_ns_nhansu_ns_hdld` (`id_hdld`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_nhansu_13_03_2015`

```sql
CREATE TABLE `ns_nhansu_13_03_2015` (
  `ma_nhanvien` varchar(16) NOT NULL,
  `ma_the` bigint(16) unsigned DEFAULT NULL,
  `ho` varchar(255) NOT NULL,
  `ten` varchar(25) NOT NULL,
  `socmnd` bigint(20) NOT NULL,
  `ngaysinh` date NOT NULL,
  `gioitinh` char(8) NOT NULL,
  `id_quoctich` tinyint(4) unsigned NOT NULL,
  `id_dantoc` tinyint(4) unsigned NOT NULL,
  `ngaycapcmnd` date NOT NULL,
  `noicapcmnd` tinyint(4) NOT NULL,
  `noisinh` tinyint(4) NOT NULL,
  `id_hocvan` tinyint(4) unsigned NOT NULL,
  `id_honnhan` tinyint(4) unsigned NOT NULL,
  `nguyenquan` varchar(255) NOT NULL,
  `diachithuongtru` varchar(255) NOT NULL,
  `diachicungu` varchar(255) NOT NULL,
  `id_donvi` int(10) unsigned NOT NULL,
  `id_chucvu` tinyint(4) unsigned NOT NULL,
  `id_congviec` tinyint(4) unsigned NOT NULL,
  `ngayvaolam` date NOT NULL,
  `ngayhetthuviec` date NOT NULL,
  `hesoluong` float unsigned NOT NULL,
  `id_hdld` tinyint(4) unsigned DEFAULT NULL,
  `id_nghidaihan` tinyint(4) unsigned DEFAULT NULL,
  `ngay_nghiviec` date DEFAULT NULL,
  `btkt` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `id_donvi` (`id_donvi`),
  KEY `FK_ns_nhansu_ns_chucvu` (`id_chucvu`),
  KEY `FK_ns_nhansu_ns_quoctich` (`id_quoctich`),
  KEY `FK_ns_nhansu_ns_dantoc` (`id_dantoc`),
  KEY `FK_ns_nhansu_ns_hocvan` (`id_hocvan`),
  KEY `FK_ns_nhansu_ns_honnhan` (`id_honnhan`),
  KEY `FK_ns_nhansu_ns_congviec` (`id_congviec`),
  KEY `FK_ns_nhansu_ns_hdld` (`id_hdld`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_nhansu_bo`

```sql
CREATE TABLE `ns_nhansu_bo` (
  `ma_nhanvien` varchar(16) NOT NULL,
  `ma_the` bigint(16) unsigned DEFAULT NULL,
  `ho` varchar(255) NOT NULL,
  `ten` varchar(25) NOT NULL,
  `socmnd` bigint(20) NOT NULL,
  `ngaysinh` date NOT NULL,
  `gioitinh` char(8) NOT NULL,
  `id_quoctich` tinyint(4) unsigned NOT NULL,
  `id_dantoc` tinyint(4) unsigned NOT NULL,
  `ngaycapcmnd` date NOT NULL,
  `noicapcmnd` tinyint(4) NOT NULL,
  `noisinh` tinyint(4) NOT NULL,
  `id_hocvan` tinyint(4) unsigned NOT NULL,
  `id_honnhan` tinyint(4) unsigned NOT NULL,
  `nguyenquan` varchar(255) NOT NULL,
  `diachithuongtru` varchar(255) NOT NULL,
  `diachicungu` varchar(255) NOT NULL,
  `id_donvi` int(10) unsigned NOT NULL,
  `id_chucvu` tinyint(4) unsigned NOT NULL,
  `id_congviec` tinyint(4) unsigned NOT NULL,
  `ngayvaolam` date NOT NULL,
  `ngayhetthuviec` date NOT NULL,
  `hesoluong` float unsigned NOT NULL,
  `id_hdld` tinyint(4) unsigned DEFAULT NULL,
  `id_nghidaihan` tinyint(4) unsigned DEFAULT NULL,
  `ngay_nghiviec` date DEFAULT NULL,
  `btkt` tinyint(4) NOT NULL DEFAULT '0',
  UNIQUE KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `id_donvi` (`id_donvi`),
  KEY `FK_ns_nhansu_ns_chucvu` (`id_chucvu`),
  KEY `FK_ns_nhansu_ns_quoctich` (`id_quoctich`),
  KEY `FK_ns_nhansu_ns_dantoc` (`id_dantoc`),
  KEY `FK_ns_nhansu_ns_hocvan` (`id_hocvan`),
  KEY `FK_ns_nhansu_ns_honnhan` (`id_honnhan`),
  KEY `FK_ns_nhansu_ns_congviec` (`id_congviec`),
  KEY `FK_ns_nhansu_ns_hdld` (`id_hdld`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_phepnam`

```sql
CREATE TABLE `ns_phepnam` (
  `id_phepnam` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `phepnam` varchar(45) NOT NULL,
  PRIMARY KEY (`id_phepnam`),
  UNIQUE KEY `phepnam` (`phepnam`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_quoctich`

```sql
CREATE TABLE `ns_quoctich` (
  `id_quoctich` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `quoctich` varchar(45) NOT NULL,
  PRIMARY KEY (`id_quoctich`),
  UNIQUE KEY `quoctich` (`quoctich`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_thaisan`

```sql
CREATE TABLE `ns_thaisan` (
  `id_thaisan` tinyint(4) unsigned NOT NULL AUTO_INCREMENT,
  `thaisan` varchar(45) NOT NULL,
  PRIMARY KEY (`id_thaisan`),
  UNIQUE KEY `thaisan` (`thaisan`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_tinhthanh`

```sql
CREATE TABLE `ns_tinhthanh` (
  `id_tinhthanh` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `tinhthanh` varchar(45) NOT NULL,
  PRIMARY KEY (`id_tinhthanh`),
  UNIQUE KEY `tinhthanh` (`tinhthanh`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_tncn`

```sql
CREATE TABLE `ns_tncn` (
  `id_tncn` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `maso_tncn` int(10) unsigned DEFAULT NULL,
  `ngaycap_maso` date DEFAULT NULL,
  `ngay_batdau` date DEFAULT NULL,
  `lydo` varchar(255) DEFAULT NULL,
  `notes` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tncn`)
) ENGINE=InnoDB AUTO_INCREMENT=1028 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_xinphep`

```sql
CREATE TABLE `ns_xinphep` (
  `id_xinphep` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `id_phepnam` tinyint(4) unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `ngay_ketthuc` date NOT NULL,
  `songaynghi` float DEFAULT NULL,
  `lydo` varchar(125) NOT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_xinphep`),
  KEY `FK_ns_xinphep_ns_phepnam` (`id_phepnam`),
  KEY `ma_nhanvien` (`ma_nhanvien`),
  CONSTRAINT `FK_ns_xinphep_ns_phepnam` FOREIGN KEY (`id_phepnam`) REFERENCES `ns_phepnam` (`id_phepnam`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19029 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_xuly_kiluat`

```sql
CREATE TABLE `ns_xuly_kiluat` (
  `id_xuly_kiluat` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `id_kiluat` tinyint(4) unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `ngay_ketthuc` date NOT NULL,
  `songaynghi` int(11) NOT NULL,
  `lydo` varchar(125) NOT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_xuly_kiluat`),
  KEY `FK_ns_xuly_kiluat_ns_kiluat` (`id_kiluat`),
  CONSTRAINT `FK_ns_xuly_kiluat_ns_kiluat` FOREIGN KEY (`id_kiluat`) REFERENCES `ns_kiluat` (`id_kiluat`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `ns_xuly_thaisan`

```sql
CREATE TABLE `ns_xuly_thaisan` (
  `id_xuly_thaisan` bigint(20) NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `id_thaisan` tinyint(4) unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `ngay_ketthuc` date NOT NULL,
  `songaynghi` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_xuly_thaisan`),
  KEY `FK_ns_xuly_thaisan_ns_thaisan` (`id_thaisan`),
  CONSTRAINT `FK_ns_xuly_thaisan_ns_thaisan` FOREIGN KEY (`id_thaisan`) REFERENCES `ns_thaisan` (`id_thaisan`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `report_vangmat`

```sql
CREATE TABLE `report_vangmat` (
  `idreport_vangmat` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) DEFAULT NULL,
  `ma_the` bigint(16) unsigned DEFAULT NULL,
  `hoten` varchar(255) DEFAULT NULL,
  `id_donvi` int(11) DEFAULT NULL,
  `loainghi` varchar(50) DEFAULT NULL,
  `ngay_batdau` date DEFAULT NULL,
  `ngay_ketthuc` date DEFAULT NULL,
  `vaotre` varchar(50) DEFAULT NULL,
  `rasom` varchar(50) DEFAULT NULL,
  `songaynghi` tinyint(4) unsigned DEFAULT NULL,
  `lydo` varchar(255) DEFAULT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`idreport_vangmat`)
) ENGINE=MEMORY AUTO_INCREMENT=247 DEFAULT CHARSET=utf8;
```

- Engine: `MEMORY` | Collation: `utf8_general_ci` | Created: `2026-10-14 08:23:04`

### `salary_log_month`

```sql
CREATE TABLE `salary_log_month` (
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngaythangnam` date NOT NULL,
  `h1` time NOT NULL,
  `h2` time NOT NULL,
  `h3` time DEFAULT NULL,
  `h4` time DEFAULT NULL,
  `h5` time DEFAULT NULL,
  `h6` time DEFAULT NULL,
  KEY `ma_nhanvien` (`ma_nhanvien`),
  KEY `ngaythangnam` (`ngaythangnam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `thaydoi_chucvu`

```sql
CREATE TABLE `thaydoi_chucvu` (
  `id_thaydoi_chucvu` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngay_batdau` date NOT NULL,
  `lydo` varchar(255) NOT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  `note` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_thaydoi_chucvu`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `thaydoi_congviec`

```sql
CREATE TABLE `thaydoi_congviec` (
  `id_thaydoi_congviec` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngay_batdau` date NOT NULL,
  `lydo` varchar(255) NOT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  `note` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_thaydoi_congviec`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `thaydoi_donvi`

```sql
CREATE TABLE `thaydoi_donvi` (
  `id_thaydoi_donvi` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `ngay_batdau` date NOT NULL,
  `lydo` varchar(255) NOT NULL,
  `ghichu` varchar(255) DEFAULT NULL,
  `note` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_thaydoi_donvi`)
) ENGINE=InnoDB AUTO_INCREMENT=353 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `thaydoi_hdld`

```sql
CREATE TABLE `thaydoi_hdld` (
  `id_thaydoi_hdld` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `id_hdld` tinyint(4) unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `ngay_ketthuc` date DEFAULT NULL,
  PRIMARY KEY (`id_thaydoi_hdld`)
) ENGINE=InnoDB AUTO_INCREMENT=1028 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `thechamcong`

```sql
CREATE TABLE `thechamcong` (
  `id_tcc` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ma_nhanvien` varchar(16) NOT NULL,
  `ma_the` bigint(16) unsigned NOT NULL,
  `ngay_batdau` date NOT NULL,
  `sotien` bigint(16) unsigned NOT NULL,
  `lydo` varchar(255) NOT NULL,
  `note` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tcc`)
) ENGINE=InnoDB AUTO_INCREMENT=1362 DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `user`

```sql
CREATE TABLE `user` (
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `jsonmenu` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

### `xeploai_cuoinam`

```sql
CREATE TABLE `xeploai_cuoinam` (
  `ma_nhanvien` varchar(50) NOT NULL,
  `t1` float NOT NULL DEFAULT '0',
  `t2` float NOT NULL DEFAULT '0',
  `t3` float NOT NULL DEFAULT '0',
  `t4` float NOT NULL DEFAULT '0',
  `t5` float NOT NULL DEFAULT '0',
  `t6` float NOT NULL DEFAULT '0',
  `t7` float NOT NULL DEFAULT '0',
  `t8` float NOT NULL DEFAULT '0',
  `t9` float NOT NULL DEFAULT '0',
  `t10` float NOT NULL DEFAULT '0',
  `t11` float NOT NULL DEFAULT '0',
  `t12` float NOT NULL DEFAULT '0',
  `vang` float NOT NULL DEFAULT '0',
  `total` float NOT NULL DEFAULT '0',
  `tongcdpn` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

- Engine: `InnoDB` | Collation: `utf8_general_ci` | Created: `2022-02-11 22:17:22`

## Tổng quan routines

- Functions: 37
- Procedures: 6

## Functions

### `dayCount`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `dayCount`(
	`t` FLOAT
) RETURNS float
BEGIN
	DECLARE n FLOAT;

	SET n = 0;

	IF t = 0.00 THEN RETURN 0; END IF;
	IF t <= 2.00 THEN SET n = 0.25; END IF;
	IF t > 2.00 AND t <= 4.00 THEN SET n = 0.5; END IF;
	IF t > 4.00 AND t <= 6.00 THEN SET n = 0.75; END IF;
	IF t > 6.00 THEN SET n = 1; END IF;

	RETURN n;
END$$
DELIMITER ;
```

### `fixHCTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `fixHCTime`(
	`t` FLOAT
) RETURNS float
BEGIN
	IF t >= 8 THEN SET t=8; END IF;	
	RETURN t;
END$$
DELIMITER ;
```

### `fixRawTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `fixRawTime`(
	`t` TIME,
	`f` TIME
) RETURNS time
BEGIN
	IF t < '07:30:00' THEN
		RETURN '07:30:00';
	END IF;

	IF t > f THEN
		RETURN ADDTIME(TIMEDIFF(f,'00:02:00'), SEC_TO_TIME(FLOOR(RAND()*11*60)));
	END IF;

	RETURN t;
END$$
DELIMITER ;
```

### `fixTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `fixTime`(
	`t` TIME,
	`startTime` TIME,
	`finishTime` TIME
) RETURNS time
BEGIN
	IF t < startTime THEN RETURN startTime; END IF;	
	IF t > "11:15:00" AND t < "12:15:00" THEN SET t = "12:15:00"; END IF;
	IF t > "16:30:00" THEN SET t = "16:30:00"; END IF;
	IF t > finishTime THEN RETURN finishTime; END IF;
	RETURN t;
END$$
DELIMITER ;
```

### `fixTime1`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `fixTime1`(
	`t` TIME,
	`startTime` TIME,
	`finishTime` TIME
) RETURNS time
BEGIN
	IF t < startTime THEN RETURN startTime; END IF;
	IF t > "11:15" AND t < "12:15" THEN SET t = "12:15"; END IF;
	IF t > "16:30" THEN SET t = "16:30"; END IF;
	IF t > finishTime THEN RETURN finishTime; END IF;
	RETURN t;
END$$
DELIMITER ;
```

### `fixthaisan`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `fixthaisan`(
	`t` time,
	`kieu` tinyint
) RETURNS time
BEGIN
	IF kieu > 1 THEN
		IF t >= "07:30:00" THEN RETURN "07:30:00"; END IF;
	ELSE
		IF t >= "08:00:00" THEN RETURN "08:00:00"; END IF;
	END IF;

	RETURN t;
END$$
DELIMITER ;
```

### `fnDiligenceSum`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `fnDiligenceSum`(
	`_sum` INT
) RETURNS int(11)
BEGIN
	DECLARE result INT;

	IF _sum >= 20 THEN SET result = 0; END IF;
	IF _sum < 20 THEN SET result = 20 - _sum; END IF;
	IF _sum IS NULL THEN SET result = 20; END IF;

	RETURN result;
END$$
DELIMITER ;
```

### `fnmDiligence`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `fnmDiligence`(
	`_m` INT
) RETURNS int(11)
BEGIN
	DECLARE per INT;

	IF _m=20 THEN SET per = 300000; END IF;
	IF _m=15 THEN SET per = 225000; END IF;
	IF _m=10 THEN SET per = 150000; END IF;
	IF _m=5 THEN SET per = 75000; END IF;	

	RETURN per;
END$$
DELIMITER ;
```

### `giohanhchanh_quetthe_ao`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `giohanhchanh_quetthe_ao`(
	`tongthoigian` TIME
) RETURNS time
BEGIN
	IF tongthoigian > '08:00:00' THEN RETURN '08:00:00'; END IF;

	RETURN tongthoigian;
END$$
DELIMITER ;
```

### `giolamthem_quetthe_ao`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `giolamthem_quetthe_ao`(`tongthoigian` TIME, `giohanhchanh` TIME) RETURNS time
BEGIN

	IF giohanhchanh < '08:00:00' THEN RETURN 0; END IF;

	IF  TIMEDIFF(tongthoigian,giohanhchanh) > '01:30:00' THEN RETURN '01:30:00'; END IF;

	RETURN TIMEDIFF(tongthoigian,giohanhchanh);

END$$
DELIMITER ;
```

### `holidayTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `holidayTime`(
	`workingtime` FLOAT,
	`overtime` FLOAT,
	`isholiday` TINYINT
) RETURNS float
BEGIN

	IF isholiday = 1 THEN 
		RETURN workingtime + overtime;
	END IF;

	RETURN 0;
END$$
DELIMITER ;
```

### `holidayTime_backup`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `holidayTime_backup`(`h1` time, `h2` time, `h3` time, `h4` time, `h5` time, `h6` time, `startTime` TIME, `finishTime` time, `holiday` TINYINT, `donvi` INT, `disable` TINYINT) RETURNS time
BEGIN

	DECLARE segment1 TIME;

	DECLARE segment2 TIME;

	DECLARE segment3 TIME;



	IF disable = 1 THEN RETURN 0; END IF;

	IF holiday = 0 THEN RETURN 0; END IF;

	IF donvi IN(1,2,3,4,5,6,7) THEN RETURN officeTime(h1,h2,h3,h4,h5,h6,startTime,finishTime,holiday,donvi,disable); END IF;



	#IF h2 > '16:30:00' THEN SET h2 = '16:30:00'; END IF;

	SET h1 = fixTime(h1,startTime,finishTime);

	SET h2 = fixTime(h2,startTime,finishTime);

	SET segment1 = workinghoursSegment(h1,h2);

	

	IF h3 IS NULL THEN RETURN segment1;

	ELSE

		#IF h4 > '16:30:00' THEN SET h4 = '16:30:00'; END IF;

		SET h3 = fixTime(h3,startTime,finishTime);

		SET h4 = fixTime(h4,startTime,finishTime);

		SET segment2 = workinghoursSegment(h3,h4);

	END IF;



	IF h5 IS NULL THEN RETURN ADDTIME(segment1,segment2);

	ELSE

		#IF h6 > '16:30:00' THEN SET h6 = '16:30:00'; END IF;

		SET h5 = fixTime(h5,startTime,finishTime);

		SET h6 = fixTime(h6,startTime,finishTime);

		SET segment3 = workinghoursSegment(h5,h6);

		RETURN ADDTIME(ADDTIME(segment1,segment2),segment3);

	END IF;

	

	RETURN segment1;

END$$
DELIMITER ;
```

### `holidayTime_thaisan`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `holidayTime_thaisan`(
	`h1` time,
	`h2` time,
	`h3` time,
	`h4` time,
	`h5` time,
	`h6` time,
	`startTime` TIME,
	`finishTime` time,
	`holiday` TINYINT,
	`donvi` INT,
	`kieuthaisan` tinyint,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE segment1 TIME;
	DECLARE segment2 TIME;
	DECLARE segment3 TIME;


	IF disable = 1 THEN RETURN 0; END IF;
	IF holiday = 0 THEN RETURN 0; END IF;

	IF kieuthaisan is not null THEN RETURN officeTime_thaisan(h1,h2,h3,h4,h5,h6,startTime,finishTime,holiday,donvi,kieuthaisan,disable); END IF;
	IF donvi IN(1,2,3,4,5,6,7) THEN RETURN officeTime(h1,h2,h3,h4,h5,h6,startTime,finishTime,holiday,donvi,disable); END IF;


	SET h1 = fixTime(h1,startTime,finishTime);
	SET h2 = fixTime(h2,startTime,finishTime);
	SET segment1 = workinghoursSegment(h1,h2);

	
	IF h3 IS NULL THEN RETURN segment1;
	ELSE
		SET h3 = fixTime(h3,startTime,finishTime);
		SET h4 = fixTime(h4,startTime,finishTime);
		SET segment2 = workinghoursSegment(h3,h4);
	END IF;


	IF h5 IS NULL THEN RETURN ADDTIME(segment1,segment2);
	ELSE
		SET h5 = fixTime(h5,startTime,finishTime);
		SET h6 = fixTime(h6,startTime,finishTime);
		SET segment3 = workinghoursSegment(h5,h6);

		RETURN ADDTIME(ADDTIME(segment1,segment2),segment3);
	END IF;

	RETURN segment1;
END$$
DELIMITER ;
```

### `maternityFixOverTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `maternityFixOverTime`(
	`t` time,
	`kieu` tinyint
) RETURNS time
BEGIN
	IF kieu=2 THEN RETURN 0; END IF;

	RETURN t;
END$$
DELIMITER ;
```

### `maternityFixOverTimeKT`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `maternityFixOverTimeKT`(
	`t` TIME,
	`kieu` TINYINT
) RETURNS time
BEGIN
	IF kieu IS NOT NULL THEN RETURN 0; END IF;

	RETURN t;
END$$
DELIMITER ;
```

### `maternityFixWorkingTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `maternityFixWorkingTime`(
	`t` time,
	`kieu` tinyint
) RETURNS time
BEGIN
	# IMPORTANT FUNCTION

	IF kieu IN (1,2,3) THEN
		IF t >= "07:00" THEN RETURN "07:00"; END IF;
	ELSE 
		IF t >= "08:00" THEN RETURN "08:00"; END IF;
	END IF;

	RETURN t;
END$$
DELIMITER ;
```

### `officeTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `officeTime`(
	`h1` time,
	`h2` time,
	`h3` time,
	`h4` time,
	`h5` time,
	`h6` time,
	`startTime` time,
	`finishTime` time,
	`holiday` TINYINT,
	`donvi` INT,
	`disable` TINYINT
) RETURNS time
BEGIN

	DECLARE segment1 TIME;
	DECLARE segment2 TIME;
	DECLARE segment3 TIME;
	
	# this variable overrides the input startTime value
	# and will also override any value defined in the program
	DECLARE fixed_startTime TIME DEFAULT '07:30:00';
	
	# defines the rule for the end of working hours
	DECLARE fixed_endTime TIME DEFAULT '16:30:00';
	
	#
	IF disable = 1 THEN RETURN 0; END IF;

	#IF holiday = 1 THEN RETURN holidayTime(h1,h2,h3,h4,h5,h6,startTime,finishTime,1,donvi); END IF;


	IF h2 > fixed_endTime THEN SET h2 = fixed_endTime; END IF;

	SET h1 = fixTime(h1,fixed_startTime,finishTime);
	SET h2 = fixTime(h2,fixed_startTime,finishTime);
	SET segment1 = workinghoursSegment(h1,h2);

	IF h3 IS NULL THEN RETURN segment1;
	ELSE
		IF h4 > fixed_endTime THEN SET h4 = fixed_endTime; END IF;
		SET h3 = fixTime(h3,fixed_startTime,finishTime);
		SET h4 = fixTime(h4,fixed_startTime,finishTime);
		SET segment2 = workinghoursSegment(h3,h4);
	END IF;


	IF h5 IS NULL THEN RETURN ADDTIME(segment1,segment2);
	ELSE
		IF h6 > fixed_endTime THEN SET h6 = fixed_endTime; END IF;
		SET h5 = fixTime(h5,fixed_startTime,finishTime);
		SET h6 = fixTime(h6,fixed_startTime,finishTime);
		SET segment3 = workinghoursSegment(h5,h6);

		RETURN ADDTIME(ADDTIME(segment1,segment2),segment3);
	END IF;

	RETURN segment1;
END$$
DELIMITER ;
```

### `officeTime_thaisan`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `officeTime_thaisan`(
	`h1` time,
	`h2` time,
	`h3` time,
	`h4` time,
	`h5` time,
	`h6` time,
	`startTime` time,
	`finishTime` time,
	`holiday` TINYINT,
	`donvi` INT,
	`kieuthaisan` tinyint,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE segment1 TIME;
	DECLARE segment2 TIME;
	DECLARE segment3 TIME;

	DECLARE fixed_startTime TIME DEFAULT '07:30:00';
	DECLARE fixed_endTime TIME DEFAULT '15:30:00'; # bau con nho - chi tinh den 15h30 ??? luu y cho nay can kiem tra
	
	#
	IF disable = 1 THEN RETURN 0; END IF;

	IF holiday = 1 AND kieuthaisan IN (1,2,3) THEN RETURN 0; END IF;

	#
	IF h2 > fixed_endTime THEN SET h2 = fixed_endTime; END IF;
	SET h1 = fixTime(h1,fixed_startTime,finishTime);
	SET h2 = fixTime(h2,fixed_startTime,finishTime);
	SET segment1 = workinghoursSegment(h1,h2);

	#
	IF h3 IS NULL THEN RETURN fixthaisan(segment1,kieuthaisan);
	ELSE
		IF h4 > fixed_endTime THEN SET h4 = fixed_endTime; END IF;
		SET h3 = fixTime(h3,fixed_startTime,finishTime);
		SET h4 = fixTime(h4,fixed_startTime,finishTime);
		SET segment2 = workinghoursSegment(h3,h4);
	END IF;

	#
	IF h5 IS NULL THEN RETURN fixthaisan(ADDTIME(segment1,segment2),kieuthaisan);
	ELSE
		IF h6 > fixed_endTime THEN SET h6 = fixed_endTime; END IF;
		SET h5 = fixTime(h5,fixed_startTime,finishTime);
		SET h6 = fixTime(h6,fixed_startTime,finishTime);
		SET segment3 = workinghoursSegment(h5,h6);

		RETURN fixthaisan(ADDTIME(ADDTIME(segment1,segment2),segment3),kieuthaisan);
	END IF;

	RETURN fixthaisan(segment1,kieuthaisan);
END$$
DELIMITER ;
```

### `overTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `overTime`(
	`h2` time,
	`h4` time,
	`h6` time,
	`finishTime` time,
	`donvi` INT,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE startovertime TIME;
	DECLARE firstend TIME;

	IF disable = 1 THEN RETURN 0; END IF;
	IF donvi IN(1,2,3,4,5,6,7) THEN RETURN 0; END IF;

	SET startovertime = '16:30:00'; 

	IF finishTime < startovertime THEN RETURN 0; END IF;

	SET firstend = overtimeFixTime(h2,finishTime);

	IF h4 IS NOT NULL THEN SET firstend = overtimeFixTime(h4,finishTime); END IF;
	IF h6 IS NOT NULL THEN SET firstend = overtimeFixTime(h6,finishTime); END IF;

	RETURN TIMEDIFF(firstend,startovertime);
END$$
DELIMITER ;
```

### `overTime1`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `overTime1`(
	`h2` TIME,
	`h4` TIME,
	`h6` TIME,
	`finishTime` TIME,
	`donvi` INT,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE startovertime TIME;
	DECLARE firstend TIME;


	IF disable = 1 THEN RETURN 0; END IF;
	IF donvi IN(1,2,3,4,5,6,7) THEN RETURN 0; END IF;


	SET startovertime = '16:30:00'; # OLD
	IF finishTime < startovertime THEN RETURN 0; END IF; # OLD


	SET firstend = overtimeFixTime1(h2,finishTime);


	IF h4 IS NOT NULL THEN SET firstend = overtimeFixTime1(h4,finishTime); END IF;
	IF h6 IS NOT NULL THEN SET firstend = overtimeFixTime1(h6,finishTime); END IF;


	RETURN TIMEDIFF(firstend,startovertime);
END$$
DELIMITER ;
```

### `overtimeFixTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `overtimeFixTime`(
	`t` time,
	`finishTime` time
) RETURNS time
BEGIN
	IF t < '16:30:00' THEN SET t = '16:30:00'; END IF;
	IF t > finishTime THEN SET t = finishTime; END IF;
	
	RETURN t;
END$$
DELIMITER ;
```

### `overtimeFixTime1`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `overtimeFixTime1`(
	`t` TIME,
	`finishTime` TIME
) RETURNS time
BEGIN
	IF t < '16:30:00' THEN SET t = '16:30:00'; END IF; # +old
	IF t > finishTime THEN SET t = finishTime; END IF; # +old

	RETURN t;
END$$
DELIMITER ;
```

### `partTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `partTime`(
	`h2` time,
	`h4` time,
	`h6` time,
	`finishTime` time,
	`holiday` TINYINT,
	`donvi` INT,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE startovertime TIME;
	DECLARE firstend TIME;


	IF disable = 1 THEN RETURN 0; END IF;
	IF donvi IN(1,2,3,4,5,6,7) THEN RETURN 0; END IF;


	SET startovertime = '16:30:00'; 

	IF finishTime < startovertime THEN RETURN 0; END IF;

	SET firstend = overtimeFixTime(h2, finishTime);


	IF h4 IS NOT NULL THEN SET firstend = overtimeFixTime(h4, finishTime); END IF;
	IF h6 IS NOT NULL THEN SET firstend = overtimeFixTime(h6, finishTime); END IF;

	RETURN TIMEDIFF(firstend, startovertime);
END$$
DELIMITER ;
```

### `partTime_thaisan`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `partTime_thaisan`(
	`h2` time,
	`h4` time,
	`h6` time,
	`finishTime` time,
	`holiday` TINYINT,
	`donvi` INT,
	`kieuthaisan` tinyint,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE startovertime TIME;
	DECLARE firstend TIME;

	IF disable = 1 THEN RETURN 0; END IF;
	IF kieuthaisan is not null THEN return 0; end if;
	IF donvi IN(1,2,3,4,5,6,7) THEN RETURN 0; END IF;


	SET startovertime = '16:30:00'; 

	IF finishTime < startovertime THEN RETURN 0; END IF;
	SET firstend = overtimeFixTime(h2, finishTime);

	IF h4 IS NOT NULL THEN SET firstend = overtimeFixTime(h4, finishTime); END IF;
	IF h6 IS NOT NULL THEN SET firstend = overtimeFixTime(h6, finishTime); END IF;

	RETURN TIMEDIFF(firstend,startovertime);
END$$
DELIMITER ;
```

### `sua_giohanhchanh`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `sua_giohanhchanh`(
	`t` TIME,
	`k_thaisan` TINYINT
) RETURNS time
    COMMENT 'ham nay dung cho bang ao'
BEGIN

	if k_thaisan in(1) then

		if t > '08:00:00' then return '08:00:00'; end if; 

	end if;

	if k_thaisan in(2,3) then

		if t > '07:00:00' then return '07:00:00'; end if; 

	end if;

	

	return t;

END$$
DELIMITER ;
```

### `sua_giolamthem`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `sua_giolamthem`(`t` TIME, `giodongchuyen` TIME, `k_thaisan` TINYINT) RETURNS time
BEGIN

	#ham nay dung cho bang ao

	if k_thaisan in(1,2,3) then RETURN '00:00:00'; END IF;

	

	IF giodongchuyen='18:00:00' THEN

		IF t > '01:30:00' THEN RETURN '01:30:00'; END IF;

	END IF;

	IF giodongchuyen='19:00:00' THEN

		IF t > '02:30:00' THEN RETURN '02:30:00'; END IF;

	END IF;

	IF giodongchuyen='20:00:00' THEN

		IF t > '03:30:00' THEN RETURN '03:30:00'; END IF;

	END IF;

	RETURN t;

END$$
DELIMITER ;
```

### `thaisan_themgio`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `thaisan_themgio`(`t` FLOAT, `kieu` tinyint) RETURNS int(11)
BEGIN

if kieu > 0 and t > 6.00 then return 1; end if;



#RETURN 0;



#IF kieu > 1 THEN

#	IF t > 6.00 THEN RETURN 1; END IF;

# IF ... END IF;

#END IF;

#

#



RETURN 0;



END$$
DELIMITER ;
```

### `tim_chedo`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `tim_chedo`(
	`employeecode` VARCHAR(16),
	`datecompare` DATE
) RETURNS tinyint(4)
BEGIN

	DECLARE snn TINYINT;

	SET snn = (
		SELECT songaynghi
		FROM ns_xinphep
		WHERE employeecode=ma_nhanvien and datecompare=ngay_batdau and id_phepnam=3
		LIMIT 1
	);

	
	RETURN snn;
END$$
DELIMITER ;
```

### `tim_phepnam`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `tim_phepnam`(`employeecode` VARCHAR(16), `datecompare` DATE) RETURNS tinyint(4)
BEGIN

	DECLARE snn TINYINT;

	SET snn = (SELECT songaynghi from ns_xinphep where employeecode=ma_nhanvien and datecompare=ngay_batdau and id_phepnam=4 limit 1);

	

	RETURN snn;

END$$
DELIMITER ;
```

### `tim_thaisan`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `tim_thaisan`(manhanvien VARCHAR(16),ngaythangsosanh DATE) RETURNS tinyint(4)
BEGIN

DECLARE kieuthaisan TINYINT;

	set kieuthaisan =

	(SELECT id_thaisan FROM ns_xuly_thaisan

	WHERE manhanvien=ns_xuly_thaisan.ma_nhanvien

	AND ngaythangsosanh between ngay_batdau and ngay_ketthuc);

RETURN kieuthaisan;

END$$
DELIMITER ;
```

### `timeSegValid`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `timeSegValid`(`si` DATE, `ei` DATE, `sc` DATE, `ec` DATE) RETURNS tinyint(1)
BEGIN

	#si = ngay bat dau (nhap lieu)

	#ei = ngay ket thuc (nhap lieu)

	#sc = ngay bat dau (so sanh)

	#ec = ngay ket thuc (so sanh)	

	IF ((si < sc) AND (ei < sc)) OR ((si > ec) AND (ei > ec)) THEN RETURN FALSE; END IF;

	RETURN TRUE;

END$$
DELIMITER ;
```

### `tinh_chedo`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `tinh_chedo`(`startDate` DATE, `endDate` DATE) RETURNS tinyint(4)
BEGIN

	RETURN NULL;

END$$
DELIMITER ;
```

### `tongngaynghitrongnam`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `tongngaynghitrongnam`(`Param1` FLOAT, `Param2` FLOAT, `Param3` FLOAT, `Param4` FLOAT, `Param5` FLOAT, `Param6` FLOAT, `Param7` FLOAT, `Param8` FLOAT, `Param9` FLOAT, `Param10` FLOAT, `Param11` FLOAT, `Param12` FLOAT) RETURNS float
BEGIN

	RETURN Param1+Param2+Param3+Param4+Param5+Param6+Param7+Param8+Param9+Param10+Param11+Param12;

END$$
DELIMITER ;
```

### `workingTime`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `workingTime`(
	`h1` time,
	`h2` time,
	`h3` time,
	`h4` time,
	`h5` time,
	`h6` time,
	`startTime` time,
	`finishTime` time,
	`donvi` INT,
	`disable` TINYINT
) RETURNS time
BEGIN


	DECLARE segment1 TIME;
	DECLARE segment2 TIME;
	DECLARE segment3 TIME;
	
	DECLARE fixed_endTime TIME DEFAULT '16:30:00';

	IF disable = 1 THEN RETURN 0; END IF;

	#
	IF h2 > fixed_endTime THEN SET h2 = fixed_endTime; END IF;

	SET h1 = fixTime(h1, startTime, finishTime);
	SET h2 = fixTime(h2, startTime, finishTime);

	SET segment1 = workinghoursSegment(h1, h2);
	IF h3 IS NULL THEN RETURN segment1;


	ELSE
		IF h3 > fixed_endTime THEN SET h3 = fixed_endTime; END IF;
		IF h4 > fixed_endTime THEN SET h4 = fixed_endTime; END IF;

		SET h3 = fixTime(h3, startTime, finishTime);
		SET h4 = fixTime(h4, startTime, finishTime);

		SET segment2 = workinghoursSegment(h3, h4);
	END IF;



	IF h5 IS NULL THEN RETURN ADDTIME(segment1, segment2);
	ELSE

		IF h5 > fixed_endTime THEN SET h5 = fixed_endTime; END IF;
		IF h6 > fixed_endTime THEN SET h6 = fixed_endTime; END IF;


		SET h5 = fixTime(h5, startTime, finishTime);
		SET h6 = fixTime(h6, startTime, finishTime);


		SET segment3 = workinghoursSegment(h5, h6);

		RETURN ADDTIME(ADDTIME(segment1, segment2), segment3);
	END IF;


	RETURN segment1;
END$$
DELIMITER ;
```

### `workingTime1`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `workingTime1`(
	`h1` TIME,
	`h2` TIME,
	`h3` TIME,
	`h4` TIME,
	`h5` TIME,
	`h6` TIME,
	`startTime` TIME,
	`finishTime` TIME,
	`donvi` INT,
	`disable` TINYINT
) RETURNS time
BEGIN
	DECLARE segment1 TIME;
	DECLARE segment2 TIME;
	DECLARE segment3 TIME;

	IF disable = 1 THEN RETURN 0; END IF;

	IF h2 > '16:30' THEN SET h2 = '16:30'; END IF; # old # 16:00

	SET h1 = fixTime1(h1, startTime, finishTime);
	SET h2 = fixTime1(h2, startTime, finishTime);

	SET segment1 = workinghoursSegment1(h1,h2);


	IF h3 IS NULL THEN RETURN segment1;
	ELSE
		IF h3 > '16:30' THEN SET h3 = '16:30'; END IF; # old # 16:00
		IF h4 > '16:30' THEN SET h4 = '16:30'; END IF; # old # 16:00

		SET h3 = fixTime1(h3,startTime,finishTime);
		SET h4 = fixTime1(h4,startTime,finishTime);

		SET segment2 = workinghoursSegment1(h3,h4);
	END IF;


	IF h5 IS NULL THEN RETURN ADDTIME(segment1,segment2);
	ELSE
		IF h5 > '16:30' THEN SET h5 = '16:30'; END IF; # old # 16:00
		IF h6 > '16:30' THEN SET h6 = '16:30'; END IF; # old # 16:00
		
		SET h5 = fixTime1(h5,startTime,finishTime);
		SET h6 = fixTime1(h6,startTime,finishTime);
		
		SET segment3 = workinghoursSegment1(h5,h6);

		RETURN ADDTIME(ADDTIME(segment1,segment2),segment3);
	END IF;

	RETURN segment1;
END$$
DELIMITER ;
```

### `workinghoursSegment`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` FUNCTION `workinghoursSegment`(
	`s` time,
	`f` time
) RETURNS time
BEGIN
	DECLARE break TIME;


	IF s <= '11:15:00' AND f >= '12:15:00' THEN SET break = '01:00:00'; END IF;
	IF s <= '11:15:00' AND f >= '16:30:00' THEN SET break = '01:00:00'; END IF;

	IF break IS NULL THEN SET break = 0; END IF;

	RETURN TIMEDIFF(TIMEDIFF(f, s), break);
END$$
DELIMITER ;
```

### `workinghoursSegment1`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` FUNCTION `workinghoursSegment1`(
	`s` TIME,
	`f` TIME
) RETURNS time
BEGIN
	DECLARE break TIME;

	IF s <= '11:15:00' AND f >= '12:15:00' THEN SET break = '01:00:00'; END IF;
	IF s <= '11:15:00' AND f >= '16:30:00' THEN SET break = '01:00:00'; END IF;

	IF break IS NULL THEN SET break = 0; END IF;

	RETURN TIMEDIFF(TIMEDIFF(f,s),break);
END$$
DELIMITER ;
```

## Procedures

### `___xinphep_auto`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `___xinphep_auto`(IN `id` INT, IN `startDate` DATE, IN `endDate` DATE, IN `c` TINYINT)
BEGIN

	DECLARE ds VARCHAR(12);

	DECLARE i TINYINT;

	DECLARE s TEXT;

	DECLARE q TEXT;

	

	#SET d = DATEDIFF(endDate,startDate) + 1;

	SET i = 0;

	

	WHILE i < c DO

		SET ds = CONCAT('"', DATE_FORMAT( DATE_ADD(startDate, INTERVAL i DAY), '%Y-%m-%d'), '"');

		SET s = CONCAT_WS(',', s, CONCAT('(', id, ',', ds, ',', ds , ', 1)'));

		SET i = i + 1; 

	END WHILE;

	

	SET @q = CONCAT('INSERT INTO ti(id,startDate,endDate,cd) VALUES',s);

	

	SELECT @q;

	#PREPARE pq FROM @q;	

	#EXECUTE pq;

END$$
DELIMITER ;
```

### `fill_calendar`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `fill_calendar`(

	IN `startdate` DATE,

	IN `stopdate` DATE

)
BEGIN

DECLARE currentdate DATE;

    SET currentdate = startdate;

    WHILE currentdate < stopdate DO

        INSERT INTO calendar(dt,y,m,d,dn,isHoliday) VALUES (

                        currentdate,

                        YEAR(currentdate),

                        MONTH(currentdate),

                        DAY(currentdate),

                        LEFT(DATE_FORMAT(currentdate,'%W'),2),0

                        );

        SET currentdate = ADDDATE(currentdate,INTERVAL 1 DAY);

    END WHILE;

END$$
DELIMITER ;
```

### `luongthang`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `luongthang`(IN `_id_donvi` INT, IN `_from_date` DATE, IN `_to_date` DATE)
BEGIN


	select ma_nhanvien,ho,ten from ns_nhansu where id_donvi = _id_donvi;


END$$
DELIMITER ;
```

### `nhanvien_moi_trongthang`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `nhanvien_moi_trongthang`()
BEGIN

	SELECT ho,ten,donvi FROM ns_nhansu

	JOIN ns_donvi USING(id_donvi)

	WHERE (left(right(CURDATE(),5),2)= left(right(ngayvaolam,5),2)) AND (left(CURDATE(),4)=left(ngayvaolam,4));

END$$
DELIMITER ;
```

### `suagio_thaisan`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `suagio_thaisan`(IN `h2` TIME, IN `h3` INT, IN `h4` INT, IN `h5` INT, IN `h6` INT)
BEGIN

if(h2>'16:30:00') then set h2 = '16:30:00'; end if;

END$$
DELIMITER ;
```

### `tinh_chedo`

```sql
DELIMITER $$
CREATE DEFINER=`root`@`%` PROCEDURE `tinh_chedo`(IN `startDate` DATE, IN `endDate` DATE)
BEGIN

	SELECT ma_nhanvien, SUM(songaynghi) AS cd FROM ns_xinphep

	WHERE id_phepnam = 3 AND (startDate BETWEEN ngay_batdau AND ngay_ketthuc)

	GROUP BY ma_nhanvien;

END$$
DELIMITER ;
```

## Triggers

(không có trigger)

## Events

(không có event)
