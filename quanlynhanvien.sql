-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 04, 2025 lúc 07:42 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quanlynhanvien`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `backup_name` varchar(255) NOT NULL,
  `backup_path` varchar(255) NOT NULL,
  `backup_size` int(11) NOT NULL,
  `backup_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `backups`
--

INSERT INTO `backups` (`id`, `backup_name`, `backup_path`, `backup_size`, `backup_date`) VALUES
(1, 'Sao lưu thủ công 27/04/2025 04:23:24', 'C:\\xampp\\htdocs\\quanlynhanvien\\pages/../../backups/backup_2025-04-27_145747.sql', 17873, '2025-04-27 14:57:47'),
(2, 'Sao lưu thủ công 30/04/2025 23:48:15', 'C:\\xampp\\htdocs\\quanlynhanvien\\pages/../../backups/backup_2025-04-30_234819.sql', 18295, '2025-04-30 23:48:19'),
(3, 'Tự động sao lưu 2025-05-01 02:33:10', 'C:\\xampp\\htdocs\\quanlynhanvien\\pages/../backups/backup_2025-05-01_023310.sql', 18456, '2025-05-01 02:33:10'),
(4, 'Sao lưu thủ công 03/05/2025 02:27:30', 'C:\\xampp\\htdocs\\quanlynhanvien\\pages/../../backups/backup_2025-05-03_022734.sql', 13846, '2025-05-03 02:27:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `bangluongthang`
--

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
  `NgayCapNhat` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `bangluongthang`
--

INSERT INTO `bangluongthang` (`MaBangLuong`, `MaNV`, `Thang`, `Nam`, `LuongCoBan`, `NgayCongThucTe`, `LuongTheoNgayCong`, `GioTangCa`, `LuongTangCa`, `PhuCap`, `Thuong`, `Phat`, `BHXH`, `BHYT`, `ThueTNCN`, `ThucLanh`, `NgayCapNhat`) VALUES
('BL1746300536', 'HPSF002', '05', '2025', 5000000.00, 1.0, 227272.73, 0.0, 0.00, 0.00, 324234.00, 200000.00, 400000.00, 75000.00, 0.00, -123493.27, '2025-05-04 15:46:19');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chamcong`
--

CREATE TABLE `chamcong` (
  `MaCC` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `Ngay` date NOT NULL,
  `GioVao` time DEFAULT NULL,
  `GioRa` time DEFAULT NULL,
  `GioTangCa` decimal(5,2) DEFAULT NULL,
  `SoGioLam` decimal(5,2) DEFAULT NULL,
  `TrangThai` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chamcong`
--

INSERT INTO `chamcong` (`MaCC`, `MaNV`, `Ngay`, `GioVao`, `GioRa`, `GioTangCa`, `SoGioLam`, `TrangThai`, `GhiChu`) VALUES
('CC17462862', '90504', '2025-05-03', NULL, NULL, NULL, NULL, 'Vắng mặt', 'Tự động cập nhật'),
('CC17462864', 'HPSF001', '2025-05-03', NULL, NULL, NULL, NULL, 'Vắng mặt', 'Tự động cập nhật'),
('CC17462882', 'HPSF002', '2025-05-03', '23:03:37', '23:03:42', 0.00, 0.00, 'Đi muộn - Đã hoàn thành', ' | '),
('CC17463740', '90504', '2025-05-04', NULL, NULL, NULL, NULL, 'Vắng mặt', 'Tự động cập nhật');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ktkl`
--

CREATE TABLE `ktkl` (
  `MaKTKL` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `LoaiSuKien` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `MoTa` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `HinhAnh` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SoTien` decimal(15,2) DEFAULT NULL,
  `NgayApDung` date NOT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ktkl`
--

INSERT INTO `ktkl` (`MaKTKL`, `MaNV`, `LoaiSuKien`, `MoTa`, `HinhAnh`, `SoTien`, `NgayApDung`, `GhiChu`) VALUES
('KL17462882', 'HPSF002', 'Kỷ luật', 'Đi muộn ngày 03/05/2025 - Chấm công vào lúc 23:03:37', NULL, 100000.00, '2025-05-03', 'Tự động tạo từ hệ thống chấm công'),
('KT17462870', 'HPSF002', 'Khen thưởng', 'test', 'khenthuong_1746287018.jpg', 324234.00, '2025-05-03', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `luong`
--

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
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `luong`
--

INSERT INTO `luong` (`MaLuong`, `MaNV`, `LuongCoBan`, `PhuCapAnTrua`, `PhuCapDiLai`, `Thuong`, `Phat`, `BHXH`, `BHYT`, `ThueTNCN`, `NgayCapNhat`, `GhiChu`) VALUES
('L174629405', 'HPSF002', 5000000.00, 0.00, 0.00, 0.00, 0.00, 8.00, 1.50, 10.00, '2025-05-04', ''),
('L174635035', 'HPSF003', 7000000.00, 45435.00, 234234234.00, 0.00, 0.00, 8.00, 1.50, 10.00, '2025-05-04', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nghiphep`
--

CREATE TABLE `nghiphep` (
  `MaNP` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `LyDo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `TrangThai` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `NguoiPheDuyet` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nghiphep`
--

INSERT INTO `nghiphep` (`MaNP`, `MaNV`, `NgayBatDau`, `NgayKetThuc`, `LyDo`, `TrangThai`, `NguoiPheDuyet`, `GhiChu`) VALUES
('NP00001', 'HPSF002', '2025-05-04', '2026-02-20', 'Ốm', 'Đã duyệt', 'Nguyễn Tuấn Minh (admin)', 'Đã được phê duyệt vào ngày 04/05/2025');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nghiviec`
--

CREATE TABLE `nghiviec` (
  `MaNghiViec` varchar(10) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `NgayThongBao` date NOT NULL,
  `NgayNghiViec` date NOT NULL,
  `LyDo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `TinhTrangBanGiao` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `GhiChu` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

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
  `MaPb` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`MaNV`, `Avatar`, `Hoten`, `SDT`, `Email`, `CCCD`, `DiaChi`, `GioiTinh`, `NgaySinh`, `NgayVaoLam`, `ChucVu`, `MaPb`) VALUES
('90504', '90504_1746213121.jpg', 'Nguyễn Tuấn Minh', '0343523200', 'nguyentuanminhk3@gmail.com', '031203000456', '', 'Nam', '2003-05-24', '2025-02-10', 'admin', 'ad'),
('HPSF001', 'HPSF001_1746031639.png', 'Nguyễn Tuấn Minh', '1', 'admin@gmail.com', '1', '1', 'Nam', '2003-05-24', '2025-04-30', 'admin', 'ad'),
('HPSF002', 'HPSF002_1746264501.jpg', 'Nguyễn Văn A', '0901234567', 'Huethinguyen1111@gmail.com', '012345678902', 'Hải Phòng', 'Nam', '2025-05-03', '2025-05-03', 'Nhân viên', 'MKT'),
('HPSF003', 'HPSF003_1746289182.jpg', 'Nguyễn Văn B', '64563', 'hoanghuongsp@gmail.com', '436532465', NULL, 'Nữ', '2025-05-03', '2025-05-03', 'Nhân viên', 'KT');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `otp_requests`
--

CREATE TABLE `otp_requests` (
  `id` int(11) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phongban`
--

CREATE TABLE `phongban` (
  `MaPb` varchar(10) NOT NULL,
  `TenPhongBan` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phongban`
--

INSERT INTO `phongban` (`MaPb`, `TenPhongBan`) VALUES
('ad', 'admin'),
('IT1', 'Internet'),
('KT', 'Kế toán'),
('MKT', 'Marketing');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

CREATE TABLE `taikhoan` (
  `Taikhoan` varchar(50) NOT NULL,
  `Matkhau` varchar(255) NOT NULL,
  `Chucvu` varchar(50) NOT NULL,
  `MaNV` varchar(10) DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_failed_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan`
--

INSERT INTO `taikhoan` (`Taikhoan`, `Matkhau`, `Chucvu`, `MaNV`, `login_attempts`, `last_failed_login`, `created_at`, `updated_at`) VALUES
('admin', '$2y$10$9JHMawer9cZ.SpzZWG6aN.NLU/O7ZdzerbePaW8J2SlgwWH07e.OO', 'admin', '90504', 0, NULL, '2025-05-03 16:16:39', '2025-05-03 16:39:00'),
('HPSF001', '$2y$10$qVxOyIfYvvB1BJsh1TxhveGk7CqOkTj8/ycYhYEshNzZxR0uv5sGa', 'admin', 'HPSF001', 0, NULL, '2025-05-03 16:16:39', '2025-05-03 16:37:05'),
('HPSF002', '$2y$10$A8MnReFOUz0LCef6ZpRzy..NRYlTe9.Hng.bfsevK2yXdqhGqiJTC', 'Nhân viên', 'HPSF002', 0, NULL, '2025-05-04 08:55:44', '2025-05-04 08:55:44'),
('HPSF003', '$2y$10$au.uQlGVQM7kKaZ1WsDL/OFROMIoLZUmzC.qkRh2ts8dTaKFZuS3W', 'Nhân viên', 'HPSF003', 0, NULL, '2025-05-03 16:20:33', '2025-05-03 16:40:24');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `bangluongthang`
--
ALTER TABLE `bangluongthang`
  ADD PRIMARY KEY (`MaBangLuong`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `chamcong`
--
ALTER TABLE `chamcong`
  ADD PRIMARY KEY (`MaCC`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `ktkl`
--
ALTER TABLE `ktkl`
  ADD PRIMARY KEY (`MaKTKL`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `luong`
--
ALTER TABLE `luong`
  ADD PRIMARY KEY (`MaLuong`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `nghiphep`
--
ALTER TABLE `nghiphep`
  ADD PRIMARY KEY (`MaNP`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `nghiviec`
--
ALTER TABLE `nghiviec`
  ADD PRIMARY KEY (`MaNghiViec`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`MaNV`),
  ADD UNIQUE KEY `SDT` (`SDT`),
  ADD UNIQUE KEY `CCCD` (`CCCD`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `MaPb` (`MaPb`);

--
-- Chỉ mục cho bảng `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `otp_code` (`otp_code`),
  ADD KEY `MaNV` (`MaNV`);

--
-- Chỉ mục cho bảng `phongban`
--
ALTER TABLE `phongban`
  ADD PRIMARY KEY (`MaPb`);

--
-- Chỉ mục cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`Taikhoan`),
  ADD UNIQUE KEY `MaNV` (`MaNV`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `otp_requests`
--
ALTER TABLE `otp_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `bangluongthang`
--
ALTER TABLE `bangluongthang`
  ADD CONSTRAINT `bangluongthang_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `chamcong`
--
ALTER TABLE `chamcong`
  ADD CONSTRAINT `chamcong_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `ktkl`
--
ALTER TABLE `ktkl`
  ADD CONSTRAINT `ktkl_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `luong`
--
ALTER TABLE `luong`
  ADD CONSTRAINT `luong_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `nghiphep`
--
ALTER TABLE `nghiphep`
  ADD CONSTRAINT `nghiphep_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `nghiviec`
--
ALTER TABLE `nghiviec`
  ADD CONSTRAINT `nghiviec_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `nhanvien_ibfk_1` FOREIGN KEY (`MaPb`) REFERENCES `phongban` (`MaPb`);

--
-- Các ràng buộc cho bảng `otp_requests`
--
ALTER TABLE `otp_requests`
  ADD CONSTRAINT `otp_requests_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);

--
-- Các ràng buộc cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `taikhoan_ibfk_1` FOREIGN KEY (`MaNV`) REFERENCES `nhanvien` (`MaNV`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
