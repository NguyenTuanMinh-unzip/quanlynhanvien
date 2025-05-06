-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: quanlynhanvien
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `backups`
--

DROP TABLE IF EXISTS `backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `backup_name` varchar(255) NOT NULL,
  `backup_path` varchar(255) NOT NULL,
  `backup_size` int(11) NOT NULL,
  `backup_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `backups`
--

LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
INSERT INTO `backups` VALUES (1,'Sao lưu thủ công 27/04/2025 04:23:24','C:\\xampp\\htdocs\\quanlynhanvien\\pages/../../backups/backup_2025-04-27_145747.sql',17873,'2025-04-27 14:57:47');
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bangluongthang`
--

DROP TABLE IF EXISTS `bangluongthang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bangluongthang` (
  `MaBangLuong` varchar(20) NOT NULL,
  `MaNV` varchar(10) NOT NULL,
  `Thang` varchar(2) NOT NULL,
  `Nam` varchar(4) NOT NULL,
  `LuongCoBan` decimal(15,2) NOT NULL,
  `NgayCongThucTe` decimal(5,1) NOT NULL,
  `LuongTheoNgayCong` decimal(15,2) NOT NULL,
  `GioTangCa` decimal(5,1) NOT NULL,
  `LuongTangCa` decimal(15,2) NOT NULL,
  `PhuCap` decimal(15,2) NOT NULL,
  `Thuong` decimal(15,2) NOT NULL,
  `Phat` decimal(15,2) NOT NULL,
  `BHXH` decimal(15,2) NOT NULL,
  `BHYT` decimal(15,2) NOT NULL,
  `ThueTNCN` decimal(15,2) NOT NULL,
  `ThucLanh` decimal(15,2) NOT NULL,
  `NgayCapNhat` datetime NOT NULL,
  PRIMARY KEY (`MaBangLuong`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `bangluongthang_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bangluongthang`
--

LOCK TABLES `bangluongthang` WRITE;
/*!40000 ALTER TABLE `bangluongthang` DISABLE KEYS */;
INSERT INTO `bangluongthang` VALUES ('BL1744656972','NV0108','04','2025',5000000.00,1.0,227272.73,0.0,0.00,2846855.00,0.00,0.00,405000.00,75000.00,259412.77,2334714.95,'2025-04-15 04:50:50');
/*!40000 ALTER TABLE `bangluongthang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chamcong`
--

DROP TABLE IF EXISTS `chamcong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chamcong` (
  `MaCC` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `Ngay` date NOT NULL,
  `GioVao` time DEFAULT NULL,
  `GioRa` time DEFAULT NULL,
  `GioTangCa` decimal(5,2) DEFAULT NULL,
  `SoGioLam` decimal(5,2) DEFAULT NULL,
  `TrangThai` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaCC`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `chamcong_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chamcong`
--

LOCK TABLES `chamcong` WRITE;
/*!40000 ALTER TABLE `chamcong` DISABLE KEYS */;
INSERT INTO `chamcong` VALUES ('CC17446547','NV0108','2025-04-15','09:18:00','16:21:00',0.00,7.05,'Đã hoàn thành','Không'),('CC17446553','NV0110','2025-04-15','01:29:00','18:29:00',9.00,8.00,'Đã hoàn thành',' | '),('CC17456936','NV001','2025-04-27','01:54:32','01:54:33',0.00,0.00,'Đã hoàn thành',' | ');
/*!40000 ALTER TABLE `chamcong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ktkl`
--

DROP TABLE IF EXISTS `ktkl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ktkl` (
  `MaKTKL` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `LoaiSuKien` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `MoTa` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `HinhAnh` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SoTien` decimal(15,2) DEFAULT NULL,
  `NgayApDung` date NOT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaKTKL`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `ktkl_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ktkl`
--

LOCK TABLES `ktkl` WRITE;
/*!40000 ALTER TABLE `ktkl` DISABLE KEYS */;
/*!40000 ALTER TABLE `ktkl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `luong`
--

DROP TABLE IF EXISTS `luong`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `luong` (
  `MaLuong` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `LuongCoBan` decimal(15,2) NOT NULL,
  `PhuCapAnTrua` decimal(15,2) DEFAULT NULL,
  `PhuCapDiLai` decimal(15,2) DEFAULT NULL,
  `Thuong` decimal(15,2) DEFAULT NULL,
  `Phat` decimal(15,2) DEFAULT NULL,
  `BHXH` decimal(15,2) DEFAULT NULL,
  `BHYT` decimal(15,2) DEFAULT NULL,
  `ThueTNCN` decimal(15,2) DEFAULT NULL,
  `NgayCapNhat` date NOT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaLuong`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `luong_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `luong`
--

LOCK TABLES `luong` WRITE;
/*!40000 ALTER TABLE `luong` DISABLE KEYS */;
INSERT INTO `luong` VALUES ('L174465678','NV0108',5000000.00,423423.00,2423432.00,0.00,0.00,8.10,1.50,10.00,'2025-04-15','');
/*!40000 ALTER TABLE `luong` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nghiphep`
--

DROP TABLE IF EXISTS `nghiphep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nghiphep` (
  `MaNP` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `LyDo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `TrangThai` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `NguoiPheDuyet` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaNP`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `nghiphep_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nghiphep`
--

LOCK TABLES `nghiphep` WRITE;
/*!40000 ALTER TABLE `nghiphep` DISABLE KEYS */;
/*!40000 ALTER TABLE `nghiphep` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nghiviec`
--

DROP TABLE IF EXISTS `nghiviec`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nghiviec` (
  `MaNghiViec` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `NgayThongBao` date NOT NULL,
  `NgayNghiViec` date NOT NULL,
  `LyDo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `TinhTrangBanGiao` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  PRIMARY KEY (`MaNghiViec`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `nghiviec_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nghiviec`
--

LOCK TABLES `nghiviec` WRITE;
/*!40000 ALTER TABLE `nghiviec` DISABLE KEYS */;
/*!40000 ALTER TABLE `nghiviec` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nhanvien`
--

DROP TABLE IF EXISTS `nhanvien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nhanvien` (
  `MaNV` varchar(10) NOT NULL,
  `Avatar` varchar(255) DEFAULT NULL,
  `Hoten` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `SDT` varchar(15) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `CCCD` varchar(20) DEFAULT NULL,
  `DiaChi` text DEFAULT NULL,
  `GioiTinh` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `NgaySinh` date DEFAULT NULL,
  `NgayVaoLam` date NOT NULL,
  `ChucVu` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `MaPb` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`MaNV`),
  UNIQUE KEY `SDT` (`SDT`),
  UNIQUE KEY `CCCD` (`CCCD`),
  UNIQUE KEY `Email` (`Email`),
  KEY `MaPb` (`MaPb`),
  CONSTRAINT `nhanvien_ibfk_1` FOREIGN KEY (`MaPb`) REFERENCES `phongban` (`MaPb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nhanvien`
--

LOCK TABLES `nhanvien` WRITE;
/*!40000 ALTER TABLE `nhanvien` DISABLE KEYS */;
INSERT INTO `nhanvien` VALUES ('90504',NULL,'Nguyễn Tuấn Minh','0343523200',NULL,'031203000456',NULL,'Nam','2003-05-24','2025-02-10','admin','ad'),('NV001',NULL,'Nguyễn Văn A','0901234567','a@gmail.com','012345678901','An Tiến, An Lão, Hải Phòng','Nam','1990-01-15','2020-06-01','Nhân viên','IT1'),('NV0012',NULL,'Lê Thị B','0902123456',NULL,'0123456785902',NULL,'Nữ','1982-03-15','2012-06-01','Phó Giám đốc','MKT'),('NV002',NULL,'Trần Thị B','0912345678',NULL,'012345678902',NULL,'Nữ','1992-03-22','2021-07-15','Nhân viên','IT1'),('NV003',NULL,'Lê Văn C','0923456789',NULL,'012345678903',NULL,'Nam','1989-11-10','2019-01-10','Nhân viên','MKT'),('NV004',NULL,'Phạm Thị D','0934567890',NULL,'012345678904',NULL,'Nữ','1995-05-18','2022-09-20','Thực tập sinh','MKT'),('NV005',NULL,'Hoàng Văn E','0945678901',NULL,'012345678905',NULL,'Nam','1993-07-25','2020-12-01','Nhân viên','MKT'),('NV006',NULL,'Đặng Thị F','0956789012',NULL,'012345678906',NULL,'Nữ','1994-08-30','2021-05-10','Nhân viên','MKT'),('NV007',NULL,'Vũ Văn G','0967890123',NULL,'012345678907',NULL,'Nam','1991-09-09','2018-03-25','Trợ lý','MKT'),('NV008',NULL,'Ngô Thị H','0978901234',NULL,'012345678908',NULL,'Nữ','1996-04-04','2023-01-01','Nhân viên','MKT'),('NV009',NULL,'Tạ Văn I','0989012345',NULL,'012345678909',NULL,'Nam','1997-12-12','2022-08-08','Thực tập sinh','MKT'),('NV010',NULL,'Bùi Thị J','0990123456',NULL,'012345678910',NULL,'Nữ','1990-06-06','2017-10-10','Phó phòng','MKT'),('NV0101',NULL,'Nguyễn Văn A','0901123456',NULL,'01234567855901',NULL,'Nam','1980-01-01','2010-05-01','Giám đốc','IT1'),('NV0103',NULL,'Trần Văn C','0903123456',NULL,'0123455678903',NULL,'Nam','1985-04-20','2013-07-15','Trưởng phòng','KT'),('NV0104',NULL,'Phạm Thị D','0904123456',NULL,'0123455678904',NULL,'Nữ','1987-05-25','2014-08-20','Nhân viên','IT1'),('NV0105',NULL,'Đỗ Văn E','0905123456',NULL,'0123455678905',NULL,'Nam','1990-06-30','2015-09-10','Nhân viên','IT1'),('NV0106',NULL,'Ngô Thị F','0906123456',NULL,'0123545678906',NULL,'Nữ','1992-07-12','2016-10-01','Nhân viên','MKT'),('NV0107',NULL,'Vũ Văn G','0907123456',NULL,'0123455678907',NULL,'Nam','1993-08-18','2017-11-05','Nhân viên','MKT'),('NV0108',NULL,'Bùi Thị H','0908123456',NULL,'01253455678908',NULL,'Nữ','1994-09-22','2018-12-15','Trưởng phòng','IT1'),('NV0109',NULL,'Đặng Văn I','0909123456',NULL,'0123545678909',NULL,'Nam','1995-10-05','2019-01-20','Trưởng phòng','MKT'),('NV0110',NULL,'Hồ Thị K','0910123456',NULL,'0123455678910',NULL,'Nữ','1996-11-11','2019-03-12','Phó Giám đốc','KT'),('NV0111',NULL,'Tạ Văn L','0911123456',NULL,'0123455678911',NULL,'Nam','1997-12-25','2020-04-25','Nhân viên','KT'),('NV0112',NULL,'Lý Thị M','0912123456',NULL,'0123455678912',NULL,'Nữ','1998-01-17','2020-06-10','Nhân viên','MKT'),('NV0113',NULL,'Trịnh Văn N','0913123456',NULL,'0125345678913',NULL,'Nam','1990-03-08','2018-08-08','Nhân viên','IT1'),('NV014',NULL,'Cao Thị O','0914123456',NULL,'0123456578914',NULL,'Nữ','1991-05-29','2019-10-01','Nhân viên','KT'),('NV015',NULL,'Lâm Văn P','0915123456',NULL,'0123456578915',NULL,'Nam','1992-07-10','2021-02-15','Nhân viên','KT'),('NV016',NULL,'Tống Thị Q','0916123456',NULL,'0123455678916',NULL,'Nữ','1993-09-19','2021-05-05','Trưởng phòng','KT'),('NV017',NULL,'Triệu Văn R','0917123456',NULL,'0123455678917',NULL,'Nam','1994-11-30','2021-07-22','Nhân viên','MKT'),('NV018',NULL,'Phan Thị S','0918123456',NULL,'0123455678918',NULL,'Nữ','1995-01-15','2022-01-01','Nhân viên','IT1'),('NV019',NULL,'Hoàng Văn T','0919123456',NULL,'012345678920',NULL,'Nữ','1997-06-06','2022-08-08','Nhân viên','IT1');
/*!40000 ALTER TABLE `nhanvien` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `otp_requests`
--

DROP TABLE IF EXISTS `otp_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `otp_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `MaNV` varchar(10) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `otp_code` (`otp_code`),
  KEY `MaNV` (`MaNV`),
  CONSTRAINT `otp_requests_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `otp_requests`
--

LOCK TABLES `otp_requests` WRITE;
/*!40000 ALTER TABLE `otp_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `otp_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phongban`
--

DROP TABLE IF EXISTS `phongban`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `phongban` (
  `MaPb` varchar(10) NOT NULL,
  `TenPhongBan` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`MaPb`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phongban`
--

LOCK TABLES `phongban` WRITE;
/*!40000 ALTER TABLE `phongban` DISABLE KEYS */;
INSERT INTO `phongban` VALUES ('ad','admin'),('IT1','Internet'),('KT','Kế toán'),('MKT','Marketing');
/*!40000 ALTER TABLE `phongban` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taikhoan`
--

DROP TABLE IF EXISTS `taikhoan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `taikhoan` (
  `Taikhoan` varchar(50) NOT NULL,
  `Matkhau` varchar(255) NOT NULL,
  `Chucvu` varchar(50) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`Taikhoan`),
  UNIQUE KEY `MaNV` (`MaNV`),
  CONSTRAINT `taikhoan_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taikhoan`
--

LOCK TABLES `taikhoan` WRITE;
/*!40000 ALTER TABLE `taikhoan` DISABLE KEYS */;
INSERT INTO `taikhoan` VALUES ('admin','admin','admin','90504'),('buithijNV010','$2y$10$rW1hCfhITCLvgKv.JdUI7eZ2WUZl2Xx/vaMYVuXbHDi62GHmgkRU.','Nhân viên','NV010'),('NV001','nhanvien','Nhân viên','NV001'),('NV003','$2y$10$pnUf65UrB3.OgEu/kgfH0eXihSxR8xErJPxZaT7QnaMUYb1ivw29O','Nhân viên','NV003'),('NV005','$2y$10$zX70QBUnREX1ShU8NVi/ze1LXa1F/6Jax4vEXlPG3NuurwetRrL3W','Quản trị viên','NV005'),('NV008','nv8','Quản trị viên','NV008'),('NV0108','','Nhân viên','NV0108'),('NV0110','nhanvien','Nhân viên','NV0110'),('NV014','nhanvien','Nhân viên','NV014');
/*!40000 ALTER TABLE `taikhoan` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-04-27 14:58:09
