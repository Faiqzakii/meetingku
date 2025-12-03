-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 03, 2025 at 07:20 AM
-- Server version: 11.4.9-MariaDB
-- PHP Version: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `datakalt_meeting`
--

-- --------------------------------------------------------

--
-- Table structure for table `meeting`
--

CREATE TABLE `meeting` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_keg` varchar(100) NOT NULL,
  `ruangan_id` int(10) UNSIGNED NOT NULL,
  `pegawai_id` int(11) UNSIGNED DEFAULT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meeting`
--

INSERT INTO `meeting` (`id`, `nama_keg`, `ruangan_id`, `pegawai_id`, `waktu_mulai`, `waktu_selesai`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Rapat Pembangunan Gedung', 3, 6, '2025-02-17 14:00:00', '2025-02-17 16:00:00', 'approved', '2025-02-17 12:23:13', '2025-02-17 19:46:19'),
(2, 'Evaluasi Kegiatan Tim IPDS dan PSS se-Provinsi Kalimantan Utara', 4, 6, '2025-02-17 14:00:00', '2025-02-17 16:00:00', 'approved', '2025-02-17 13:45:37', '2025-02-17 20:19:31'),
(3, 'FGD Penyusunan Publikasi Provinsi Kalimantan Utara Dalam Angka 2025', 3, 6, '2025-02-18 14:00:00', '2025-02-18 16:30:00', 'approved', '2025-02-17 14:08:39', '2025-02-17 14:54:25'),
(4, 'FGD Penyusunan Publikasi Provinsi Kalimantan Utara Dalam Angka 2025', 3, 6, '2025-02-19 14:00:00', '2025-02-19 16:30:00', 'approved', '2025-02-17 14:09:31', '2025-02-17 14:54:31'),
(5, 'Rapat Pembangunan Gedung', 3, 6, '2025-02-19 09:00:00', '2025-02-19 12:00:00', 'approved', '2025-02-17 14:54:10', '2025-02-17 14:54:16'),
(8, 'Evaluasi Lapangan Susenas Maret dan Seruti Triwulan 1 2025', 4, 55, '2025-02-21 14:30:00', '2025-02-21 16:30:00', 'approved', '2025-02-17 22:48:30', '2025-02-17 22:49:57'),
(9, 'Rapat Tim Inovasi BPS Kalimantan Utara', 3, 56, '2025-02-20 09:00:00', '2025-02-20 11:00:00', 'approved', '2025-02-18 07:10:45', '2025-02-18 07:11:38'),
(11, 'Knowledge Sharing Pelaksanaan FGD Standar Pelayanan Publik', 4, 40, '2025-02-20 08:00:00', '2025-02-20 10:00:00', 'approved', '2025-02-18 12:06:06', '2025-02-18 12:06:12'),
(12, 'Rapat Tim Penilai Proposal Kompas Utara 2025', 3, 56, '2025-02-21 08:00:00', '2025-02-21 10:00:00', 'approved', '2025-02-18 15:14:27', '2025-02-18 15:14:31'),
(14, 'Rapat Tim Humas', 3, 40, '2025-02-19 08:00:00', '2025-02-19 09:00:00', 'approved', '2025-02-19 06:57:06', '2025-02-19 06:57:26'),
(17, 'Workshop Implementasi QG untuk Admin QG Kabupaten/Kota ', 4, 21, '2025-02-25 08:00:00', '2025-02-25 12:00:00', 'approved', '2025-02-21 09:24:05', '2025-02-21 09:29:26'),
(18, 'Internalisasi Pembina Statistik Sektoral', 3, 40, '2025-02-24 15:00:00', '2025-02-24 17:00:00', 'approved', '2025-02-21 12:50:00', '2025-02-21 12:50:03'),
(19, 'Technical Meeting Seleksi Presentasi dan Wawancara Kompas Utara 2025', 4, 56, '2025-02-24 14:30:00', '2025-02-24 16:30:00', 'approved', '2025-02-21 13:02:38', '2025-02-21 13:02:44'),
(20, 'Rekonsiliasi Data Harga Provinsi Kalimantan Utara', 4, 40, '2025-02-28 10:00:00', '2025-02-28 12:00:00', 'approved', '2025-02-24 09:00:02', '2025-02-28 06:35:14'),
(21, 'Edukasi Statistik#1 - Pojok Statistik UBT', 4, 40, '2025-02-28 08:30:00', '2025-02-08 11:30:00', 'approved', '2025-02-24 09:00:43', '2025-02-25 17:12:19'),
(22, 'Briefing WASPADA Susenas Maret 2025', 4, 40, '2025-02-26 14:30:00', '2025-02-26 16:30:00', 'approved', '2025-02-24 13:59:50', '2025-02-24 13:59:53'),
(24, 'Seleksi Presentasi dan Wawancara Kompas Utara 2025', 5, 56, '2025-02-26 08:30:00', '2025-02-26 12:30:00', 'approved', '2025-02-25 07:26:18', '2025-02-25 07:26:24'),
(25, 'Rapat Korsavista', 3, NULL, '2025-02-26 07:45:00', '2025-02-26 08:45:00', 'approved', '2025-02-25 08:27:37', '2025-02-25 10:56:10'),
(27, 'Pelatihan Pendamping PKH DTSEN 2025', 4, 55, '2025-02-27 07:30:00', '2025-02-27 23:59:59', 'approved', '2025-02-25 17:15:00', '2025-02-25 17:15:23'),
(28, 'Sosialisasi Penggunaan CoreTax', 4, 47, '2025-02-27 09:00:00', '2025-02-27 12:00:00', 'approved', '2025-02-26 12:38:50', '2025-02-26 12:39:03'),
(29, 'Pengumuman dan Penganugerahan (Awarding) Kompas Utara Tahun 2025', 5, 56, '2025-02-28 14:00:00', '2025-02-28 16:00:00', 'approved', '2025-02-26 14:48:10', '2025-02-28 06:33:58'),
(30, 'Dwijasta #1 : Briefing Penyusunan Publikasi Statistik Sektoral (Agen Statistik)', 4, 14, '2025-03-04 14:00:00', '2025-03-04 15:00:00', 'approved', '2025-03-02 13:07:34', '2025-03-03 10:26:30'),
(31, 'Rapat Tim Pojok : Briefing fitur-fitur aplikasi dalam penyelenggaraan edukasi ', 4, 14, '2025-03-04 08:30:00', '2025-03-04 09:30:00', 'approved', '2025-03-02 13:14:25', '2025-03-03 10:26:27'),
(32, 'Rilis BRS 3 Maret 2025', 5, 40, '2025-03-03 12:00:00', '2025-03-03 15:00:00', 'approved', '2025-03-03 10:27:33', '2025-03-03 10:27:35'),
(33, 'Rapat Tim Pembangunan Gedung BPS Provinsi Kalimantan Utara', 5, 40, '2025-03-03 14:30:00', '2025-03-03 16:30:00', 'approved', '2025-03-03 10:28:07', '2025-03-03 10:28:10'),
(34, 'Rapat Rutin Pegawai Bulan Maret 2025', 3, 40, '2025-03-03 08:30:00', '2025-03-03 10:30:00', 'approved', '2025-03-03 10:29:06', '2025-03-03 10:29:09'),
(35, 'Rapat Pembangunan Gedung BPS Provinsi Kalimantan Utara', 3, 40, '2025-03-07 09:00:00', '2025-03-07 23:59:59', 'approved', '2025-03-07 09:00:41', '2025-03-07 09:00:44'),
(36, 'Sosialisasi dan Refreshing Pengelolaan BMN', 4, 40, '2025-03-07 09:00:00', '2025-03-07 12:00:00', 'approved', '2025-03-07 09:01:28', '2025-03-07 09:01:33'),
(37, 'Rapat CAN BPS Provinsi Kalimantan Utara', 6, 40, '2025-03-06 13:30:00', '2025-03-06 15:30:00', 'approved', '2025-03-07 09:02:21', '2025-03-07 09:02:21'),
(38, 'Rapat Pra Rekonda IKK se-Provinsi Kalimantan Utara', 4, 40, '2025-03-13 09:00:00', '2025-03-13 10:00:00', 'approved', '2025-03-07 09:04:05', '2025-03-07 09:04:33'),
(39, 'Sosialisasi PEKPPP BPS se-Provinsi Kalimantan Utara', 4, 40, '2025-03-11 13:00:00', '2025-03-11 16:00:00', 'approved', '2025-03-07 09:05:18', '2025-03-07 09:05:23'),
(40, 'Apel dan Snow Monday BPS Provinsi Kalimantan Utara', 4, 40, '2025-03-10 08:00:00', '2025-03-10 10:00:00', 'approved', '2025-03-07 09:06:02', '2025-03-07 09:06:06'),
(42, 'Rapat Tim Pilar Penguatan Akuntabilitas', 6, 21, '2025-03-07 14:30:00', '2025-03-07 15:30:00', 'approved', '2025-03-07 09:39:02', '2025-03-07 09:40:31'),
(43, 'Pelatihan Inda SKLNPRT', 4, 40, '2025-03-18 08:30:00', '2025-03-18 23:59:59', 'approved', '2025-03-10 09:18:36', '2025-03-10 09:18:38'),
(44, 'Rapat bersama konsultan ISO 9001:2015', 4, 40, '2025-03-10 14:00:00', '2025-03-10 16:00:00', 'approved', '2025-03-10 09:21:04', '2025-03-10 09:21:19'),
(46, 'Edukasi Statistik Pojok Statistik', 4, 24, '2025-03-20 08:30:00', '2025-03-20 11:30:00', 'approved', '2025-03-11 13:43:06', '2025-03-17 11:11:22'),
(47, 'Coaching Clinic#1 - PEKPPP', 4, 54, '2025-03-13 13:00:00', '2025-03-13 15:00:00', 'approved', '2025-03-13 07:04:52', '2025-03-13 07:04:52'),
(48, 'Rapat Tim Inovasi BPS Provinsi Kalimantan Utara', 4, 56, '2025-03-20 13:30:00', '2025-03-20 14:30:00', 'approved', '2025-03-17 08:50:36', '2025-03-17 08:50:41'),
(49, 'Coaching Clinic#2 - PEKPPP', 4, 54, '2025-03-18 13:00:00', '2025-03-18 14:00:00', 'approved', '2025-03-17 08:55:25', '2025-03-17 11:10:13'),
(50, 'Briefing Agen Statistik untuk Persiapan Edukasi Statistik Episode 17', 4, 24, '2025-03-19 13:00:00', '2025-03-19 15:00:00', 'approved', '2025-03-18 13:19:45', '2025-03-18 13:22:56'),
(52, 'Coaching Clinic#3 - PEKPPP', 4, 54, '2025-03-20 08:00:00', '2025-03-20 09:00:00', 'approved', '2025-03-19 11:12:30', '2025-03-20 10:53:22'),
(53, 'Rapat Penyusunan Metadata Statistik Sektoral Disdikbud', 6, 15, '2025-03-19 13:15:00', '2025-03-19 15:15:00', 'approved', '2025-03-19 12:04:09', '2025-03-19 12:04:09'),
(55, 'Coaching Clinic#4 - PEKPPP', 4, 54, '2025-03-25 13:00:00', '2025-03-25 15:00:00', 'approved', '2025-03-24 08:04:17', '2025-03-24 08:04:17'),
(56, 'Rapat Koordinasi Tindak Lanjut PEKPPP Kako #1', 4, 54, '2025-04-17 14:00:00', '2025-04-17 16:00:00', 'approved', '2025-04-15 08:58:59', '2025-04-16 13:16:50'),
(57, 'Halalbihalal Pegawai BPS Seluruh Indonesia', 5, 40, '2025-04-16 10:00:00', '2025-04-16 14:00:00', 'approved', '2025-04-16 13:21:31', '2025-04-16 13:22:00'),
(58, 'Rapat Pembangunan Gedung Kantor', 4, 40, '2025-04-16 15:30:00', '2025-04-16 17:30:00', 'approved', '2025-04-16 13:22:30', '2025-04-16 13:22:32'),
(59, 'Rapat Internalisasi SE2026 dan Pembuatan Contoh Narasi Aktivitas Usaha', 4, 40, '2025-04-17 09:00:00', '2025-04-17 12:00:00', 'approved', '2025-04-16 16:36:25', '2025-04-16 16:36:29'),
(60, 'Rapat Koordinasi Tindak Lanjut PEKPPP Kako #2', 4, 54, '2025-04-23 14:00:00', '2025-04-23 15:00:00', 'approved', '2025-04-22 06:51:21', '2025-04-22 06:58:12'),
(61, 'Edukasi Statistik - Pojok Statistik UBT', 4, 40, '2025-04-25 14:00:00', '2025-04-25 17:00:00', 'approved', '2025-04-22 07:01:26', '2025-04-22 07:01:28'),
(62, 'Rapat Koordinasi Tindak Lanjut PEKPPP Kako #3', 4, 54, '2025-04-28 14:00:00', '2025-04-28 15:00:00', 'approved', '2025-04-25 10:17:47', '2025-04-26 13:40:25'),
(63, 'Rapat Koordinasi Tindak Lanjut PEKPPP Kako #4', 4, 54, '2025-04-30 14:00:00', '2025-04-30 15:00:00', 'approved', '2025-04-29 06:50:24', '2025-04-29 06:50:24'),
(67, 'Briefing Operator QG BPS Prov Kaltara', 6, 21, '2025-05-07 09:00:00', '2025-05-07 10:00:00', 'approved', '2025-05-02 14:41:50', '2025-05-07 10:28:17'),
(71, 'Konserda PDRB Triwulan I-2024', 4, 27, '2025-05-08 14:00:00', '2025-05-08 16:00:00', 'approved', '2025-05-06 06:43:49', '2025-05-08 10:02:48'),
(72, 'Pembinaan Statistik Sektoral BKAD', 6, 53, '2025-05-08 08:00:00', '2025-05-08 09:00:00', 'approved', '2025-05-07 10:24:32', '2025-05-07 10:29:04'),
(73, 'Rapat Tim Manajemen Lapangan SE2026', 3, 56, '2025-05-08 08:00:00', '2025-05-08 10:00:00', 'approved', '2025-05-07 20:10:57', '2025-05-07 20:11:12'),
(74, 'Rapat SE 2026', 6, 17, '2025-05-08 10:00:00', '2025-05-08 12:00:00', 'approved', '2025-05-08 07:32:39', '2025-05-08 08:55:48'),
(75, 'Rapat Persiapan Pemutakhiran Wilkerstat SE2026', 4, 40, '2025-05-14 14:00:00', '2025-05-14 16:00:00', 'approved', '2025-05-08 10:04:27', '2025-05-08 10:04:29'),
(76, 'Undangan Rapat Kinerja Tim Statistik Produksi Caturwulan 1-2025', 4, 14, '2025-05-14 14:00:00', '2025-05-14 16:00:00', 'approved', '2025-05-08 14:21:05', '2025-05-08 14:21:05'),
(77, 'Peminjaman untuk ujian substansi', 6, 26, '2025-05-22 07:30:00', '2025-05-22 11:30:00', 'approved', '2025-05-09 09:57:33', '2025-05-15 15:38:09'),
(78, 'Pelatihan Petugas Survei Komstrat', 4, 22, '2025-05-26 09:00:00', '2025-05-26 23:59:59', 'approved', '2025-05-09 13:52:17', '2025-05-15 15:38:17'),
(79, 'Pelatihan Petugas Survei Komstrat', 4, 22, '2025-05-27 08:00:00', '2025-05-27 23:59:59', 'approved', '2025-05-09 13:53:05', '2025-05-15 15:38:20'),
(80, 'Rapat Kinerja Pojok Statistik UBT Caturwulan 1-2025', 4, 14, '2025-05-19 10:00:00', '2025-05-19 12:00:00', 'approved', '2025-05-15 14:34:17', '2025-05-15 15:38:05'),
(81, 'Nobar Sesaat - Pojok Statistik UBT', 4, 40, '2025-05-21 09:00:00', '2025-05-21 12:00:00', 'approved', '2025-05-15 15:39:04', '2025-05-15 15:42:55'),
(82, 'Rapat Tim Inovasi', 3, 54, '2025-05-21 14:00:00', '2025-05-21 15:00:00', 'approved', '2025-05-19 07:22:50', '2025-05-19 13:11:23'),
(83, 'Sharing Knowledge SE 2026 dengan BPS Jatim', 4, 33, '2025-05-20 14:00:00', '2025-05-20 16:00:00', 'approved', '2025-05-19 13:12:08', '2025-05-19 13:12:21'),
(85, 'Rekonsiliasi Fenomena Rilis Indikator Strategis', 4, 51, '2025-05-28 10:00:00', '2025-05-28 12:00:00', 'approved', '2025-05-19 13:16:30', '2025-05-19 13:16:30'),
(86, 'Dwijasta #2 - Pembinaan Agen Pojok Statistik', 4, 54, '2025-05-24 14:00:00', '2025-05-24 15:00:00', 'approved', '2025-05-23 06:59:23', '2025-05-23 06:59:23'),
(87, 'Penyusunan Dokumen Pedoman Mutu dan Formulir', 4, 15, '2025-06-04 09:00:00', '2025-06-04 23:59:59', 'approved', '2025-06-02 08:29:41', '2025-06-02 08:29:41'),
(88, 'Finalisasi Dokumen dan Persiapan Audit Internal ISO 9001:2015', 4, 15, '2025-06-16 09:00:00', '2025-06-16 23:59:59', 'approved', '2025-06-05 09:40:02', '2025-06-05 14:20:11'),
(89, 'Internalisasi SE2026 Tim PSS BPS Provinsi Kalimantan Utara', 3, 56, '2025-06-13 14:00:00', '2025-06-13 16:00:00', 'approved', '2025-06-05 14:20:06', '2025-06-05 14:20:14'),
(90, 'Rapat Hasil Desk Evaluation PEKPPP BPS Bulungan', 4, 54, '2025-06-10 14:00:00', '2025-06-10 15:00:00', 'approved', '2025-06-09 19:10:17', '2025-06-10 09:55:54'),
(91, 'Rapat Hasil Desk Evaluation PEKPPP BPS Malinau', 4, 54, '2025-06-11 14:00:00', '2025-06-11 15:00:00', 'approved', '2025-06-09 19:11:13', '2025-06-10 09:55:51'),
(92, 'Rapat tim Suaraku', 6, 56, '2025-06-10 14:00:00', '2025-06-10 15:00:00', 'approved', '2025-06-10 09:56:38', '2025-06-10 09:56:40'),
(93, 'Rapat Hasil Desk Evaluation PEKPPP BPS Nunukan', 4, 54, '2025-06-12 14:00:00', '2025-06-12 15:00:00', 'approved', '2025-06-12 06:32:22', '2025-06-12 06:32:22'),
(94, 'Rapat Hasil Desk Evaluation PEKPPP BPS Malinau', 4, 54, '2025-06-13 10:00:00', '2025-06-13 11:00:00', 'approved', '2025-06-12 06:33:13', '2025-06-12 06:33:13'),
(95, 'Rapat Hasil Desk Evaluation PEKPPP BPS Tarakan', 4, 54, '2025-06-13 14:00:00', '2025-06-13 15:00:00', 'approved', '2025-06-12 06:33:41', '2025-06-12 06:33:41'),
(96, 'Rapat Hasil Desk Evaluation PEKPPP BPS KTT', 4, 54, '2025-06-12 10:00:00', '2025-06-12 11:00:00', 'approved', '2025-06-12 06:34:09', '2025-06-12 06:34:09'),
(97, 'Rapat Pembahasan Tema Talkshow TVRI (Humas dan tim teknis)', 3, 23, '2025-06-16 10:00:00', '2025-06-16 11:00:00', 'approved', '2025-06-16 07:14:59', '2025-06-16 07:20:30'),
(98, 'Nobar Sesaat', 4, 54, '2025-06-20 09:00:00', '2025-06-20 11:00:00', 'approved', '2025-06-18 06:58:16', '2025-06-18 07:50:49'),
(99, 'Rekonsiliasi Daerah Data Indeks Kemahalan Konstruksi Provinsi Kalimantan Utara 2025', 4, 33, '2025-06-19 08:30:00', '2025-06-19 23:59:59', 'approved', '2025-06-18 07:55:28', '2025-06-18 07:55:28'),
(101, 'Rapat Persiapan Penilaian Pojok Statistik', 3, 14, '2025-07-04 10:00:00', '2025-07-04 12:00:00', 'approved', '2025-07-02 07:41:09', '2025-07-04 13:00:23'),
(102, 'Pertemuan dengan Kesbangpol terkait pengajuan Rekomendasi Statistik', 6, 15, '2025-07-07 10:00:00', '2025-07-07 12:00:00', 'approved', '2025-07-07 07:39:07', '2025-07-07 07:40:12'),
(103, 'Rapat Persiapan Monev Perekonomian Regional', 5, 25, '2025-07-10 09:30:00', '2025-07-10 13:30:00', 'approved', '2025-07-07 08:55:58', '2025-07-08 06:47:21'),
(104, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - CC&CA', 3, 40, '2025-07-11 09:00:00', '2025-07-11 11:00:00', 'approved', '2025-07-08 07:35:08', '2025-07-08 07:35:30'),
(105, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - Produksi', 3, 40, '2025-07-18 14:00:00', '2025-07-18 16:00:00', 'approved', '2025-07-08 07:36:01', '2025-07-15 09:58:55'),
(106, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - Distribusi', 3, 40, '2025-07-25 09:00:00', '2025-07-25 11:00:00', 'approved', '2025-07-08 07:36:29', '2025-07-08 07:38:30'),
(107, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - Distribusi dan Umum', 3, 40, '2025-08-07 09:00:00', '2025-08-07 11:00:00', 'approved', '2025-07-08 07:36:54', '2025-08-04 10:59:29'),
(108, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - IPDS', 3, 40, '2025-08-15 09:00:00', '2025-08-15 11:00:00', 'approved', '2025-07-08 07:37:17', '2025-07-08 07:38:38'),
(109, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - Sosial', 3, 40, '2025-08-22 09:00:00', '2025-08-22 11:00:00', 'cancelled', '2025-07-08 07:37:58', '2025-07-08 07:38:42'),
(110, 'Bedah Buku, Ilmu, dan Pengalaman (Bulungan) 2025 - Neraca & Sosial', 3, 40, '2025-09-04 08:00:00', '2025-09-04 12:00:00', 'approved', '2025-07-08 07:38:21', '2025-08-28 16:01:51'),
(111, 'Rapat Pengumpulan Data Dasar Penghitungan PDRB', 3, 27, '2025-07-11 14:00:00', '2025-07-11 15:00:00', 'approved', '2025-07-08 13:00:14', '2025-07-09 22:36:50'),
(112, 'Sosialisasi Implementasi ISO 9001 Sistem Manajemen Mutu PST BPS Provinsi Kalimantan Utara', 3, 15, '2025-07-11 10:30:00', '2025-07-11 11:30:00', 'approved', '2025-07-08 14:51:30', '2025-07-09 22:36:38'),
(113, 'FGD Pertumbuhan Ekonomi Triwulan II-2025', 3, 25, '2025-07-17 08:00:00', '2025-07-17 12:00:00', 'approved', '2025-07-09 13:39:57', '2025-07-14 09:46:38'),
(116, 'Rapat Persiapan Monev Perekonomian Regional', 5, 25, '2025-07-10 13:30:00', '2025-07-10 16:30:00', 'approved', '2025-07-10 06:48:06', '2025-07-10 15:52:50'),
(117, 'Briefing Inda Pemutakhiran Kerangka Geospasial dan Muatan Wilkerstat SE2026', 4, 40, '2025-07-16 09:00:00', '2025-07-16 12:00:00', 'approved', '2025-07-11 14:40:41', '2025-07-11 14:40:46'),
(118, 'Nobar Sesaat EPS #21 \"Main data, Bukan Perasaan: Regresi Linear Buat Kamu yang Butuh Kepastian\"', 4, 40, '2025-07-15 09:00:00', '2025-07-15 12:00:00', 'approved', '2025-07-14 07:57:09', '2025-07-15 09:58:59'),
(119, 'Rapat Tinjauan Manajemen ISO 9001', 4, 15, '2025-07-18 09:00:00', '2025-07-18 23:59:59', 'approved', '2025-07-14 12:42:23', '2025-07-15 09:58:30'),
(120, 'Rapat Evaluasi Capaian TPSS Triwulan 2-2025 Provinsi Kalimantan Utara', 4, 15, '2025-07-31 14:00:00', '2025-07-31 16:00:00', 'approved', '2025-07-22 06:40:29', '2025-07-22 09:08:47'),
(121, 'Rapat Evaluasi Pelaksanaan Kegiatan Tim Statistik Sosial', 4, 40, '2025-07-25 14:00:00', '2025-07-25 17:00:00', 'approved', '2025-07-22 09:08:39', '2025-07-22 09:08:44'),
(122, 'Rapat Evaluasi Kegiatan Tim Statistik Sosial', 4, 40, '2025-07-28 14:00:00', '2025-07-28 16:00:00', 'approved', '2025-07-28 13:10:18', '2025-07-28 13:10:21'),
(123, 'Rekonsiliasi Harga BPS se-Provinsi Kalimantan Utara', 4, 40, '2025-07-29 14:30:00', '2025-07-29 16:30:00', 'approved', '2025-07-28 13:11:06', '2025-07-28 13:11:10'),
(124, 'Konsultasi Pengolahan dan Penyusunan Laporan SKM Bakesbangpol 2025', 6, 13, '2025-08-05 09:00:00', '2025-08-05 11:00:00', 'approved', '2025-08-05 07:15:13', '2025-08-05 07:46:06'),
(125, 'Rapat persiapan pemeriksaan dokumen bukti dukung Pojok Statistik UBT Kaltara', 6, 21, '2025-08-07 11:00:00', '2025-08-07 12:00:00', 'approved', '2025-08-06 14:06:44', '2025-08-06 14:06:44'),
(126, 'Konsultasi Lanjutan Pengolahan dan Penyusunan Laporan SKM Bakesbangpol 2025', 6, 13, '2025-08-11 14:00:00', '2025-08-11 16:00:00', 'approved', '2025-08-11 06:18:28', '2025-08-11 06:27:15'),
(127, 'Pembahasan Input Metadata pada Portal INDAH', 6, 15, '2025-08-12 14:00:00', '2025-08-12 16:00:00', 'approved', '2025-08-11 12:09:35', '2025-08-11 13:30:42'),
(128, 'Konsultasi Lanjutan Pengolahan dan Penyusunan Laporan SKM Bakesbangpol 2025', 3, 13, '2025-08-12 14:00:00', '2025-08-12 16:00:00', 'approved', '2025-08-11 13:26:43', '2025-08-11 13:34:26'),
(129, 'Briefing UDPE Lanjutan 2025', 3, 56, '2025-08-13 14:00:00', '2025-08-13 16:00:00', 'approved', '2025-08-12 06:16:37', '2025-08-12 06:16:55'),
(130, 'Konsultasi Lanjutan Pengolahan dan Penyusunan Laporan SKM Bakesbangpol 2025', 6, 13, '2025-08-13 14:00:00', '2025-08-13 16:00:00', 'approved', '2025-08-12 13:12:07', '2025-08-12 13:13:46'),
(131, 'Rapat Persiapan Monitoring Evaluasi PDRB Lapangan Usaha', 3, 32, '2025-08-14 10:00:00', '2025-08-14 12:00:00', 'approved', '2025-08-14 06:41:53', '2025-08-14 06:41:53'),
(132, 'Rapat Persiapan Kegiatan  17 Agustus dan Hari Statistik Nasional 2025', 3, 13, '2025-08-15 14:00:00', '2025-08-15 16:00:00', 'approved', '2025-08-15 12:56:09', '2025-08-15 12:59:01'),
(133, 'Wawancara IST Tahap 2 Tahun 2025', 6, 13, '2025-08-15 14:30:00', '2025-08-15 16:30:00', 'approved', '2025-08-15 12:56:49', '2025-08-15 12:59:03'),
(134, 'Rapat Evaluasi Statistik Sosial: Sakernas Agustus 2025', 4, 23, '2025-08-19 13:30:00', '2025-08-19 14:30:00', 'approved', '2025-08-18 13:38:31', '2025-08-18 13:38:31'),
(135, 'Briefing Sharing Session Bimbel Polstat STIS', 4, 40, '2025-08-20 14:00:00', '2025-08-20 15:00:00', 'approved', '2025-08-19 07:09:02', '2025-08-19 07:09:07'),
(136, 'Sharing Session Matematika Bimbel Polstat STIS', 4, 40, '2025-08-21 14:00:00', '2025-08-21 16:00:00', 'approved', '2025-08-19 07:09:52', '2025-08-19 07:09:56'),
(137, 'Rapat Persiapan Kegiatan HUT RI 2025 BPS Provinsi Kalimantan Utara', 3, 55, '2025-08-20 09:00:00', '2025-08-20 11:00:00', 'approved', '2025-08-20 07:06:03', '2025-08-20 07:06:03'),
(138, 'Pojok Statistik: Edukasi Statistik - IKG (Indeks Ketimpangan Gender)', 4, 23, '2025-08-26 08:30:00', '2025-08-26 10:30:00', 'approved', '2025-08-25 06:51:09', '2025-08-26 14:21:32'),
(139, 'Audit Stage 2 ISO BPS Provinsi Kalimantan Utara', 3, 56, '2025-08-27 08:30:00', '2025-08-27 23:59:59', 'approved', '2025-08-26 14:21:27', '2025-08-26 14:21:36'),
(140, 'Rapat panitia persiapan HSN 2025', 6, 56, '2025-08-28 14:00:00', '2025-08-28 16:00:00', 'approved', '2025-08-26 14:22:47', '2025-08-26 14:22:54'),
(141, 'Briefing Pengolahan Muatan Wilkerstat 2025', 4, 40, '2025-09-04 14:00:00', '2025-09-04 16:00:00', 'approved', '2025-08-28 16:02:30', '2025-08-28 16:02:32'),
(142, 'Rapat Persiapan HSN 2025 BPS Provinsi Kalimantan Utara', 3, 55, '2025-09-02 08:00:00', '2025-09-02 10:00:00', 'approved', '2025-09-01 20:29:39', '2025-09-01 20:29:39'),
(143, 'Technical Meeting HSN se-BPS Kaltara', 4, 54, '2025-09-07 09:00:00', '2025-09-07 10:00:00', 'approved', '2025-09-03 14:19:58', '2025-09-04 16:48:08'),
(144, 'Briefing Clash Of Statisticians BPS Prov Kaltara', 6, 56, '2025-09-08 11:00:00', '2025-09-08 12:00:00', 'approved', '2025-09-08 07:44:28', '2025-09-08 07:44:32'),
(145, 'Konsultasi Kegiatan Bimtek Manajemen Data bersama DKISP', 6, 13, '2025-09-08 14:00:00', '2025-09-08 16:00:00', 'approved', '2025-09-08 09:20:24', '2025-09-08 09:26:59'),
(147, 'Rapat Kinerja Statistik Produksi Caturwulan 2-2025', 4, 14, '2025-09-22 09:00:00', '2025-09-22 11:00:00', 'approved', '2025-09-08 13:28:47', '2025-09-09 09:47:55'),
(148, 'Gladi Resik Persiapan Wawancara IST Tahap 3-2025', 3, 13, '2025-09-09 14:00:00', '2025-09-09 16:00:00', 'approved', '2025-09-09 06:36:02', '2025-09-09 06:56:25'),
(149, 'Pembukaan Rangkaian HSN 2025', 4, 54, '2025-09-10 14:00:00', '2025-09-10 15:00:00', 'approved', '2025-09-09 06:36:29', '2025-09-09 09:47:41'),
(150, 'Wawancara IST Tahap II ', 4, 23, '2025-09-10 14:30:00', '2025-09-10 15:30:00', 'approved', '2025-09-09 09:43:53', '2025-09-09 09:47:46'),
(151, 'Rapat Evaluasi Statistik Sosial: Susenas & Sakernas Agustus 2025', 4, 23, '2025-09-12 14:00:00', '2025-09-12 16:00:00', 'approved', '2025-09-09 09:45:26', '2025-09-09 09:47:52'),
(152, 'Semifinal Clash of Statistician HSN BPS Prov Kaltara', 6, 42, '2025-09-11 02:45:00', '2025-09-11 04:45:00', 'approved', '2025-09-11 06:49:07', '2025-09-11 06:49:30'),
(153, 'Babak Penyisihan Clash of Statistician BPS Prov Kaltara', 6, 42, '2025-09-10 02:45:00', '2025-09-10 04:45:00', 'approved', '2025-09-11 06:50:35', '2025-09-11 06:50:35'),
(154, 'Rapat Fun Games Puncak HSN BPS Provinsi Kaltara', 6, 42, '2025-09-15 14:30:00', '2025-09-15 15:30:00', 'approved', '2025-09-15 12:51:13', '2025-09-15 12:51:45'),
(155, 'Konsultasi Penghimpunan Metadata Statistik', 6, 13, '2025-09-16 14:00:00', '2025-09-16 16:00:00', 'approved', '2025-09-16 06:30:04', '2025-09-16 07:45:40'),
(156, 'Pojok Statistik: Edukasi Statistik - Sensus Ekonomi 2026', 4, 33, '2025-09-22 13:30:00', '2025-09-22 15:30:00', 'approved', '2025-09-16 06:51:41', '2025-09-22 14:46:36'),
(157, 'Set Up ruangan Final Clash of Statistician', 6, 35, '2025-09-17 13:00:00', '2025-09-17 17:00:00', 'approved', '2025-09-16 10:52:58', '2025-09-16 10:52:58'),
(158, 'Set Up ruangan dan Gladi Final Clash of Statistician ', 3, 35, '2025-09-18 07:00:00', '2025-09-18 23:59:59', 'approved', '2025-09-16 10:54:50', '2025-09-16 10:54:50'),
(159, 'Grand Final Clash of Statistician', 6, 35, '2025-09-19 07:00:00', '2025-09-19 10:00:00', 'approved', '2025-09-16 10:55:43', '2025-09-16 10:55:43'),
(160, 'Set Up ruangan dan Gladi Final Clash of Statistician ', 6, 35, '2025-09-18 07:00:00', '2025-09-18 23:59:59', 'approved', '2025-09-16 10:56:25', '2025-09-16 10:56:25'),
(161, 'Tenis Meja', 3, 22, '2025-09-18 00:00:00', '2025-09-18 23:59:59', 'approved', '2025-09-16 15:43:16', '2025-09-16 15:43:16'),
(162, 'Persiapan Tenis Meja', 3, 22, '2025-09-17 16:00:00', '2025-09-17 20:00:00', 'approved', '2025-09-16 15:43:42', '2025-09-16 15:43:42'),
(163, 'Rapat Persiapan Benuanta Fest 2025', 3, 33, '2025-09-22 09:00:00', '2025-09-22 11:00:00', 'approved', '2025-09-19 14:18:46', '2025-09-22 06:28:55'),
(164, 'Rapat Pengentrian FP BOS Kegiatan MBG', 6, 21, '2025-09-22 08:30:00', '2025-09-22 09:30:00', 'approved', '2025-09-22 07:19:16', '2025-09-22 14:46:39'),
(165, 'HSN Internal Cabor Tenis Meja', 3, 22, '2025-09-23 13:00:00', '2025-09-23 17:00:00', 'approved', '2025-09-22 14:20:03', '2025-09-22 14:46:31'),
(166, 'Talentics', 3, 34, '2025-09-24 12:00:00', '2025-09-24 13:00:00', 'approved', '2025-09-22 14:48:10', '2025-09-22 14:51:21'),
(167, 'Talentics', 3, 34, '2025-09-24 12:00:00', '2025-09-24 16:00:00', 'approved', '2025-09-22 14:48:50', '2025-09-22 14:51:19'),
(168, 'Talentics', 3, 34, '2025-09-26 12:00:00', '2025-09-26 16:00:00', 'approved', '2025-09-22 14:49:26', '2025-09-22 14:51:23'),
(169, 'Briefing Tim Mockup Field Evaluation Pembangunan ZI Menuju WBK', 6, 13, '2025-09-29 14:00:00', '2025-09-29 16:00:00', 'approved', '2025-09-28 21:14:52', '2025-09-29 06:39:28'),
(170, 'Snow Monday Persiapan Field Evaluation Pembangunan ZI Menuju WBK', 4, 13, '2025-09-29 10:00:00', '2025-09-29 12:00:00', 'approved', '2025-09-29 06:41:59', '2025-09-29 06:41:59'),
(171, 'Rapat Persiapan Sosialisasi SE2026', 3, 33, '2025-10-03 14:30:00', '2025-10-03 16:30:00', 'approved', '2025-10-02 07:40:08', '2025-10-03 10:32:20'),
(172, 'Rapat Evaluasi dan Monitoring Kegiatan IPDS Kabupaten Kota Triwulan III-2025', 4, 54, '2025-10-07 14:00:00', '2025-10-07 16:00:00', 'approved', '2025-10-03 08:31:04', '2025-10-03 10:32:31'),
(173, 'Rapat Hasil Mockup Field Evaluation Pembangunan ZI Menuju WBK', 6, 13, '2025-10-06 14:00:00', '2025-10-06 16:00:00', 'approved', '2025-10-03 10:14:46', '2025-10-03 10:32:29'),
(174, 'Rapat Kedua Persiapan Benuanta Fest 2025', 3, 33, '2025-10-07 09:00:00', '2025-10-07 11:00:00', 'approved', '2025-10-03 15:41:16', '2025-10-06 20:44:14'),
(176, 'Rapat Hasil Mockup Field Evaluation Pembangunan ZI Menuju WBK', 3, 13, '2025-10-06 10:00:00', '2025-10-06 12:00:00', 'approved', '2025-10-06 07:10:20', '2025-10-06 07:16:32'),
(177, 'Persiapan Campus Data Insight', 3, 54, '2025-10-08 13:30:00', '2025-10-08 15:30:00', 'approved', '2025-10-06 14:14:23', '2025-10-06 20:44:38'),
(178, 'FGD Pertumbuhan Ekonomi Triwulan 3-2025', 3, 27, '2025-10-16 08:00:00', '2025-10-16 12:00:00', 'approved', '2025-10-09 09:15:13', '2025-10-09 13:11:38'),
(179, 'Rapat Tim Pemilihan  Pegawai Teladan Triwulan III Tahun 2025', 3, 18, '2025-10-09 14:00:00', '2025-10-09 16:00:00', 'approved', '2025-10-09 11:58:13', '2025-10-09 13:11:41'),
(180, 'Briefing Persiapan Nobar Sesaat Eps.24', 4, 42, '2025-10-13 14:00:00', '2025-10-13 15:00:00', 'approved', '2025-10-13 07:04:49', '2025-10-13 14:04:24'),
(181, 'Nobar Sesaat Eps.24 \"Regresi dengan Variabel Bebas Kualitatif (Reglog)\"', 4, 42, '2025-10-15 09:00:00', '2025-10-15 12:00:00', 'approved', '2025-10-13 07:16:46', '2025-10-13 14:04:29'),
(182, 'Rapat Evaluasi Pembangunan ZI menuju WBBM BPS Provinsi Kalimantan Utara', 3, 13, '2025-10-20 10:00:00', '2025-10-20 12:00:00', 'approved', '2025-10-13 09:29:02', '2025-10-13 14:04:36'),
(183, 'Rapat Koordinasi Evaluasi MBG, FGD SE, dan SE ', 3, 53, '2025-10-13 15:00:00', '2025-10-13 16:00:00', 'approved', '2025-10-13 10:33:21', '2025-10-13 10:46:43'),
(184, 'Rapat Perlengkapan Benuanta Fest', 6, 22, '2025-10-14 14:00:00', '2025-10-14 15:00:00', 'approved', '2025-10-13 10:51:36', '2025-10-14 10:05:49'),
(185, 'Rapat Rutin Tim Bagian Umum', 6, 42, '2025-10-14 08:30:00', '2025-10-14 11:30:00', 'approved', '2025-10-14 08:39:57', '2025-10-14 09:19:57'),
(186, 'Rapat Tim Acara', 6, 16, '2025-10-17 09:00:00', '2025-10-17 11:00:00', 'approved', '2025-10-14 09:21:47', '2025-10-16 09:46:02'),
(187, 'Rapat Persiapan Benuanta Fest bersama EO', 3, 33, '2025-10-17 14:00:00', '2025-10-17 16:00:00', 'approved', '2025-10-14 15:04:24', '2025-10-15 07:08:48'),
(188, 'Rapat Pembahasan Pemeliharaan Gedung', 6, 42, '2025-10-15 08:30:00', '2025-10-15 11:30:00', 'approved', '2025-10-15 07:03:07', '2025-10-15 07:03:15'),
(189, 'Rapat persiapan CDI', 3, 56, '2025-10-15 14:00:00', '2025-10-15 15:00:00', 'approved', '2025-10-15 12:15:08', '2025-10-15 12:15:34'),
(190, 'Praktek Pengisian Kipapp Bagi PPPK', 6, 18, '2025-10-16 09:00:00', '2025-10-16 12:00:00', 'approved', '2025-10-16 07:48:04', '2025-10-16 09:17:39'),
(191, 'Sharing Knowledge Web Kasulampua', 4, 15, '2025-10-20 14:00:00', '2025-10-20 16:00:00', 'approved', '2025-10-16 09:15:51', '2025-10-16 09:17:37'),
(192, 'Rapat panitia persiapan CDI 2025', 6, 56, '2025-10-17 14:30:00', '2025-10-17 16:30:00', 'approved', '2025-10-16 13:07:51', '2025-10-16 13:07:54'),
(193, 'Asesment JF Muda BPS Provinsi Kalimantan Utara', 3, 18, '2025-10-23 09:00:00', '2025-10-23 12:00:00', 'approved', '2025-10-17 11:26:41', '2025-10-17 15:01:23'),
(194, 'Rapat Pegawai', 6, 45, '2025-10-17 16:00:00', '2025-10-17 17:00:00', 'approved', '2025-10-17 13:08:11', '2025-10-17 14:51:24'),
(195, 'Rapat Bulanan Outsourcing dan PPPK', 6, 48, '2025-10-20 21:00:00', '2025-10-20 22:00:00', 'approved', '2025-10-19 17:21:42', '2025-10-19 17:21:52'),
(196, 'Rapat Ketiga Persiapan Benuanta Fest', 3, 33, '2025-10-22 09:00:00', '2025-10-22 11:00:00', 'approved', '2025-10-20 12:25:56', '2025-10-20 21:24:09'),
(197, 'Rapat Evaluasi Capaian TPSS Triwulan III-2025', 4, 54, '2025-10-23 14:00:00', '2025-10-23 16:00:00', 'approved', '2025-10-20 12:57:37', '2025-10-20 21:24:13'),
(199, 'Rapat Finalisasi Persiapan CDI', 4, 54, '2025-10-22 14:00:00', '2025-10-22 16:00:00', 'approved', '2025-10-21 09:06:42', '2025-10-21 09:06:42'),
(200, 'Rapat Ketiga Persiapan Benuanta Fest (Lanjutan)', 3, 33, '2025-10-22 14:00:00', '2025-10-22 16:00:00', 'approved', '2025-10-22 11:06:18', '2025-10-22 11:06:18'),
(201, 'Pelatihan Innas Survei Khusus MBG Tahap 2', 4, 40, '2025-10-24 08:30:00', '2025-10-24 23:59:59', 'approved', '2025-10-24 10:14:18', '2025-10-24 10:14:28'),
(203, 'Rekonsiliasi Data Survei VPBD 2025', 4, 51, '2025-10-24 14:30:00', '2025-10-24 15:30:00', 'approved', '2025-10-24 10:20:18', '2025-10-24 10:22:16'),
(205, 'Rapat Awal Persiapan Peresmian Gedung BPS provinsi', 6, 42, '2025-10-27 14:00:00', '2025-10-27 16:00:00', 'approved', '2025-10-27 07:39:42', '2025-10-27 07:40:23'),
(206, 'Lintas Cerita Edisi 4', 3, 13, '2025-10-31 10:00:00', '2025-10-31 12:00:00', 'approved', '2025-10-27 10:57:25', '2025-10-27 10:58:10'),
(207, 'Rapat Persiapan Rilis PDRB Triwulan III', 4, 40, '2025-10-27 14:00:00', '2025-10-27 16:00:00', 'approved', '2025-10-27 15:32:44', '2025-10-27 15:32:51'),
(208, 'Indepth Interview Evaluasi Pasca Diklat', 6, 18, '2025-10-29 13:30:00', '2025-10-29 16:30:00', 'approved', '2025-10-28 10:55:27', '2025-10-29 08:16:42'),
(210, 'Indepth Interview Evaluasi Pasca Diklat', 6, 18, '2025-10-31 08:00:00', '2025-10-31 23:59:59', 'approved', '2025-10-28 10:56:58', '2025-10-29 08:17:58'),
(211, 'Indepth Interview Evaluasi Pasca Diklat', 3, 18, '2025-10-30 08:00:00', '2025-10-30 23:59:59', 'approved', '2025-10-28 12:46:44', '2025-10-29 08:17:48'),
(213, 'Rapat briefing Penjaga Stand Benuanta Fest', 3, 16, '2025-10-31 14:00:00', '2025-10-31 16:00:00', 'approved', '2025-10-28 13:16:06', '2025-10-29 08:18:37'),
(214, 'Rekonsiliasi Fenomena Rilis Indikator Strategis ', 4, 51, '2025-10-29 14:30:00', '2025-10-29 16:30:00', 'approved', '2025-10-29 08:05:55', '2025-10-29 08:23:29'),
(215, 'Konsolidasi Pegawai', 3, 45, '2025-11-03 14:00:00', '2025-11-03 16:00:00', 'approved', '2025-10-31 12:56:36', '2025-11-01 08:14:10'),
(216, 'Rapat Tim Acara Peresmian Gedung Kantor BPS Provinsi Kalimantan Utara', 3, 13, '2025-11-03 08:00:00', '2025-11-03 10:00:00', 'approved', '2025-11-03 06:24:13', '2025-11-03 06:27:57'),
(217, 'Rapat Koordinasi bersama Protokoler BPS Pusat untuk Acara Peresmian Gedung', 4, 13, '2025-11-04 09:00:00', '2025-11-04 10:00:00', 'approved', '2025-11-03 06:29:48', '2025-11-03 19:03:29'),
(218, 'Rapat Kedua Tim Peresmian Gedung', 3, 42, '2025-11-03 14:00:00', '2025-11-03 16:00:00', 'approved', '2025-11-03 14:40:18', '2025-11-03 14:40:35'),
(220, 'Rapat rutin layanan umum', 6, 42, '2025-11-04 14:00:00', '2025-11-04 16:00:00', 'approved', '2025-11-03 14:43:49', '2025-11-03 14:44:13'),
(222, 'Rapat Tim Perkap Peresmian Gedung', 6, 42, '2025-11-05 08:00:00', '2025-11-05 10:00:00', 'approved', '2025-11-03 14:45:23', '2025-11-03 14:45:35'),
(224, 'Briefing Penjelasan Pengisian SKD (Petugas Jaga Stand Benuanta Fest)', 3, 33, '2025-11-04 14:30:00', '2025-11-04 15:30:00', 'approved', '2025-11-04 07:05:00', '2025-11-04 07:23:31'),
(225, 'Rapat Rutin Tim Bagian Umum', 6, 42, '2025-11-04 08:00:00', '2025-11-04 10:00:00', 'approved', '2025-11-04 07:09:55', '2025-11-04 07:11:25'),
(228, 'Rapat Persiapan Pembukaan Benuanta Fest', 3, 33, '2025-11-06 09:00:00', '2025-11-06 12:00:00', 'approved', '2025-11-04 07:22:06', '2025-11-04 10:06:29'),
(229, 'Rapat Pembahasan anggaran peresmian gedung', 6, 42, '2025-11-06 07:45:00', '2025-11-06 08:45:00', 'approved', '2025-11-06 06:48:44', '2025-11-06 06:49:00'),
(230, 'Rapat Progress 1 Tim Acara Peresmian Gedung Kantor', 3, 13, '2025-11-10 09:00:00', '2025-11-10 11:00:00', 'approved', '2025-11-10 06:33:57', '2025-11-10 06:36:47'),
(231, 'Rapat Penyusunan Materi Pimpinan (Tim UKK)', 3, 23, '2025-11-11 09:00:00', '2025-11-11 11:00:00', 'approved', '2025-11-10 10:32:46', '2025-11-10 12:51:35'),
(232, 'Rapat Evaluasi Kegiatan Statistik Sosial ', 4, 23, '2025-11-17 14:00:00', '2025-11-17 15:00:00', 'approved', '2025-11-14 09:31:13', '2025-11-18 06:38:28'),
(233, 'Rapat persiapan peresmian gedung kantor bersama BPS Kota Tarakan dan Bulungan', 4, 13, '2025-11-17 14:00:00', '2025-11-17 16:00:00', 'approved', '2025-11-17 07:47:21', '2025-11-18 06:37:57'),
(234, 'Rapat bersama BI dan DJPB', 4, 40, '2025-11-18 15:00:00', '2025-11-18 16:00:00', 'approved', '2025-11-18 06:38:56', '2025-11-18 06:39:04'),
(235, 'Sosiasialisasi IKP oleh BPS RI', 3, 34, '2025-11-25 09:00:00', '2025-11-25 12:00:00', 'pending', '2025-11-25 06:46:54', '2025-11-25 06:46:54'),
(236, 'Rekonsiliasi Fenomena Rilis Indikator Strategis ', 3, 51, '2025-10-28 14:30:00', '2025-10-28 16:30:00', 'pending', '2025-11-26 07:02:27', '2025-11-26 07:02:27'),
(237, 'Nobar Sesaat #25', 4, 54, '2025-11-28 14:00:00', '2025-11-28 16:00:00', 'pending', '2025-11-26 14:47:54', '2025-11-26 14:47:54');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
(1, '2025-02-15-090323', 'App\\Database\\Migrations\\CreatePegawaiTable', 'default', 'App', 1739702838, 1),
(2, '2025-02-15-090325', 'App\\Database\\Migrations\\CreateRuanganTable', 'default', 'App', 1739702838, 1),
(3, '2025-02-15-090359', 'App\\Database\\Migrations\\CreateMeetingTable', 'default', 'App', 1739702838, 1),
(4, '2025-10-24-130000', 'App\\Database\\Migrations\\AlterMeetingPegawaiFkSetNull', 'default', 'App', 1761288531, 2);

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(18) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id`, `nama`, `nip`, `username`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
(6, 'Admin', '123456789123456789', 'admin', '$2y$10$dVa2buDo4qQGQbg6G8DQb.C3.Ym9YVu6.BjTDS8k7NvUZhzlHjfH.', 1, '2025-02-17 01:22:23', '2025-02-18 09:13:34'),
(10, 'Basran, SE', '196508071986031003', 'basran', '$2y$10$ak.NSCk0M82FrMlJzzgjue23U6gv.Z5/ysdTm6Q3KVojMg8uZ40Ya', 0, '2025-02-17 20:43:50', '2025-02-17 20:43:50'),
(11, 'Yuni Satriyani, SE, MAP', '197506231994012001', 'satriyani', '$2y$10$Z0XLwFnL4iaYltQuYS8.feiwwGzfqOtyHr6p6o1aqqwEdjpETapoy', 0, '2025-02-17 20:43:51', '2025-02-17 20:43:51'),
(12, 'Mat Bandri, SE, MHP', '197112311994011002', 'mbandri', '$2y$10$YjA5iHfT/8XstgBZzp8cfeU2G1FSNpxZC2eiqU7zNyAs7YbfKtVxO', 0, '2025-02-17 20:43:51', '2025-02-17 20:43:51'),
(13, 'Dede Kurniyawan, SST, M.Si.', '199210072014121001', 'kurniyawan.dede', '$2y$10$/Fq2/1wYorG6ymNMi19nvuqeo.7jLhx/j7ft8fmTCYlKT35QAvaRi', 0, '2025-02-17 20:43:51', '2025-02-17 20:43:51'),
(14, 'Risna Yuliani, SST', '199306282016022002', 'risna.yuliani', '$2y$10$CaQ1l/bow.JDogqJh0gISumV6lhnJLCrAruI7oyN1dKDBra9rbyHO', 0, '2025-02-17 20:43:51', '2025-02-17 20:43:51'),
(15, 'Annisa Yuli Pratiwi, SST, M.T.', '199307102016022001', 'annisa.yuli', '$2y$10$VaHMMYxLelqNtotdUAK4cOJxHV95CVmlyAr0yMrg5gWF91t8EibZG', 0, '2025-02-17 20:43:52', '2025-02-17 20:43:52'),
(16, 'Andika Veriyanto, SST', '199301252016021001', 'andika.veriyanto', '$2y$10$ouKgxTLCXj/n.JmK0tHGYOytWlofnEQgGjxwZh1vzRhRbUmOHAMVS', 0, '2025-02-17 20:43:52', '2025-02-17 20:43:52'),
(17, 'Mohamad Syahrul Muharom, SST', '199208092014121001', 'syahrul.muharom', '$2y$10$yfxdVHZlr9mpzZ20VHB2JueX6RtnEfVHMd2O.JcldJsSRiBG/rA5u', 0, '2025-02-17 20:43:52', '2025-02-17 20:43:52'),
(18, 'Arfiana Eka Saputeri, S.Tr.Stat.', '199612212019122001', 'arfiana.eka', '$2y$10$FyJ86Lh5Ou6.kRgbC51AWeRUVe12ezgR56YL6j40zSyuvTK0Dlsy.', 0, '2025-02-17 20:43:52', '2025-02-17 20:43:52'),
(19, 'Ferika Ainun Nisa\', S.Tr.Stat.', '199803312019122001', 'ferika.nisa', '$2y$10$d1DqIbJQTCpCO8Zx90xaYeR0.5ZOgb.V6NFtBr/W5ge4EyAeF7Kia', 0, '2025-02-17 20:43:52', '2025-02-17 20:43:52'),
(21, 'Nabila Daisy Prima, S.Tr.Stat.', '199706202019012002', 'nabiladp', '$2y$10$amESWUCV5Zure5zRL1IJ5O42iqlnJSFEje0WcPBx3MxNVp2G60z.u', 0, '2025-02-17 20:43:53', '2025-02-17 20:43:53'),
(22, 'Faris Lahudin, S.Tr.Stat.', '199609022019011002', 'faris.lahudin', '$2y$10$HA7caWH6lYcI2wbMV7UPTuJHsX/P9I9SjSnZ7Uygm5RKOIp4O4rpu', 0, '2025-02-17 20:43:53', '2025-02-17 20:43:53'),
(23, 'Fadlilah Rahmawati, S.Tr.Stat.', '199606302019012001', 'fadlilah.r', '$2y$10$WrtHFKTtMKUVhaYT6GV6tuQfGYpy4E6.QcqdtyrEWY9NO58v2Xur2', 0, '2025-02-17 20:43:53', '2025-02-17 20:43:53'),
(24, 'Ayu Faridah, S.Tr.Stat.', '199505162019012002', 'ayu.faridah', '$2y$10$UmiGf969sMe57TNxODGSVuyMCGIGNx0HO.MBYpvtEFB1nMYrhMM3q', 0, '2025-02-17 20:43:54', '2025-02-17 20:43:54'),
(25, 'Anisa Ramadhani, S.Tr.Stat.', '199602132019012002', 'anisa.ramadhani', '$2y$10$S92Z6J01QEmcn.feG9uWz.9IqI21XkDU.A4e6uXGYKNVrMU5gwMKm', 0, '2025-02-17 20:43:54', '2025-02-17 20:43:54'),
(26, 'Anggia Firmanti Hermadita, S.Tr.Stat', '199507182019012001', 'anggiafh', '$2y$10$9rpMn3hZhJ6bMkhxiedm.utZczne4mpJE9Qb8QVMrAZkDeaoYkUIW', 0, '2025-02-17 20:43:54', '2025-02-17 20:43:54'),
(27, 'Wenny Sulastri Kusumawati, SST', '199504192018022001', 'wenny.sulastri', '$2y$10$hi.HD0e0UHCCbupCdB6FGecMsyM/UcV3f3gA04ok0QipynTJwxkZe', 0, '2025-02-17 20:43:54', '2025-02-17 20:43:54'),
(28, 'Shaleh Abdul Ghani, S.Tr.Stat.', '199701052019121002', 'abdul.ghani', '$2y$10$2qaE2kF7uFE/BQkmzPmZOetYp8rcFuUDNIuQoQ2iKiSW5KFJqDpeW', 0, '2025-02-17 20:43:54', '2025-02-17 20:43:54'),
(29, 'Syafina Beta Putranti, S.Tr.Stat.', '199609052019122001', 'syafina.beta', '$2y$10$ZzD9f3O57imOnY83CjsNx.5H3qIE.ZH23ECK1njZgbhH4Dg64G/Bq', 0, '2025-02-17 20:43:55', '2025-02-17 20:43:55'),
(30, 'Wisnu Damar Budimulia, S.Tr.Stat.', '199609132019121001', 'wisnu.damar', '$2y$10$eUs/pFsxsHeVCOUt1jbON.JLwAMmosf//9u4YKHw6ckEPUaMBACb2', 0, '2025-02-17 20:43:55', '2025-02-17 20:43:55'),
(32, 'Dewi Herjayanti, S.Tr.Stat.', '199704232021042001', 'dewi.herjayanti', '$2y$10$MwBAuQv7j.Ag7sEHc5y4aOZVGQnHEX3JCCDJuZt3pWW8t03QtYRwG', 0, '2025-02-17 20:43:55', '2025-02-17 20:43:55'),
(33, 'Junezarra Thie Dea Giselle, S.Tr.Stat.', '199906282021042001', 'thie.dea', '$2y$10$F1IoYN7hOIUjfxUqT.KSKezyPxyBuigQkyOw70R25ZY0paoJ0rmna', 0, '2025-02-17 20:43:56', '2025-02-17 20:43:56'),
(34, 'Muhammad Ali Irfan, S.Tr.Stat.', '199702172021041001', 'ali.irfan', '$2y$10$co4KRcx8iHgl1z3b.8BVUOqB4IDhXF9wJ0EytHcuPRTP0vWzZQjRG', 0, '2025-02-17 20:43:56', '2025-02-17 20:43:56'),
(35, 'Ahmad Rizal Hadi Sandhori, S.Tr.Stat.', '199802152021041001', 'rizal.hadi', '$2y$10$qrRucZZlJ7GPfnCUB0kRzuzNavQDHFmaAtdwYUxuu0xkY0N43LBB6', 0, '2025-02-17 20:43:56', '2025-02-17 20:43:56'),
(36, 'Firman Sidqi, S.Tr.Stat.', '199712192021041002', 'firman.sidqi', '$2y$10$HWpAIP9/NdcSpOdiLzkx2uu19qGP6u2z4DRlyDrRTKrh.fClQzdFK', 0, '2025-02-17 20:43:56', '2025-02-17 20:43:56'),
(37, 'Vivi Novitasari Azis, S.E.', '199107092022032006', 'vivi.novitasari', '$2y$10$zVC1piR73gHhKdj/xnSgmegNsMNWxoChEq7usFg6c.Xj0z5ZwK1HS', 0, '2025-02-17 20:43:57', '2025-02-17 20:43:57'),
(38, 'Chafri Fajar Erwandra, S.Tr.Stat.', '199807032022011001', 'chafri.fajar', '$2y$10$46KcIQEw4Oh12botnmPP9./pvMfFI5av8Bi.vUXQNLS.eeLaBUTd.', 0, '2025-02-17 20:43:57', '2025-02-17 20:43:57'),
(39, 'Citra Redia Putri, S.Tr.Stat.', '199906112022012003', 'citraredia', '$2y$10$Hz1P.gCWnfGGwCr.zsnWCuWQH8VeuOVPjOYqjSQyhCdgiOlXKw96u', 0, '2025-02-17 20:43:57', '2025-02-17 20:43:57'),
(40, 'Faiq Zakki Mubarok, S.Tr.Stat.', '199908292022011004', 'faiq.zakki', '$2y$10$F7S9tn3ZH2Wa5E.e0bwgg.IQsQekJ6JlL1o9UbJ61rAKiYb1f3gfe', 1, '2025-02-17 20:43:57', '2025-02-17 20:43:57'),
(41, 'Dina Fristantiningtyas Wiliyani Hapsari, A.Md.Stat', '199908262022032009', 'dina.wiliyani', '$2y$10$YXAwUb5hsAB2.XxgoO/3.Oyxiic0OfQIiIQX94AoWvAh1xwmiWgjW', 0, '2025-02-17 20:43:57', '2025-02-17 20:43:57'),
(42, 'Bella Rotuaito N, A.Md.', '199710242024212007', 'bellarn-pppk', '$2y$10$AVTJFzM4e5MsPnBNHa7ZhepJMagcgjI0vPH4zXOMbuNrEuh8YAVIS', 1, '2025-02-17 20:43:58', '2025-06-26 07:56:36'),
(43, 'Chairunnisa Julfadlina, SST', '198707152012112001', 'c.julfadlina', '$2y$10$9nQfNLZYfwchPuFkC/NEt.VLWLbyFW/yhN4/kwRwO0Ld33dIT7Po6', 0, '2025-02-17 20:43:58', '2025-02-17 20:43:58'),
(44, 'Muryanto, SST., M.Si.', '197909052000121005', 'muryanto', '$2y$10$frXNdZAD514wLT9uf5vhbu2GoeD6ZzxYSGXlQ1lZ0hYrFRXlni0cK', 0, '2025-02-17 20:43:58', '2025-02-17 20:43:58'),
(45, 'Margareta Thriana Herumarwati, S.Psi.', '198011272011012004', 'margareta.thriana', '$2y$10$iy2BkzIiV7M9e5rZIIPoLuUIElIZtjTgoL47jfvspuBd0ErQMcVP6', 0, '2025-02-17 20:43:58', '2025-02-17 20:43:58'),
(46, 'Budi Setya Wibowo, S.Si', '197311241997031002', 'setyawibowo', '$2y$10$oqF/E32A7PT33jEgYCAW7uVB.rGO8octtKnDRm8rb4fTwpT9bZoMO', 0, '2025-02-17 20:43:59', '2025-02-17 20:43:59'),
(47, 'Erma Maslahatul Umami, S.Stat.', '198807142011012024', 'erma.umami', '$2y$10$iXSFAVt8Gn/WZiDnueu92O0CTQjbDmYpe3nT/TbcJQjjyiFX48h9e', 0, '2025-02-17 20:43:59', '2025-02-17 20:43:59'),
(48, 'Rizky Hadifianto, S.Tr.Stat.', '199603082019011002', 'rizky.hadifianto', '$2y$10$npOy3764KAeSG.p8AwV35u54XP3/Arjl7Sb2LZXNG51.y8aOs2P4K', 1, '2025-02-17 20:43:59', '2025-02-17 20:43:59'),
(49, 'Dwi Octami, S.Sos', '198310162023212018', 'dwioctami-pppk', '$2y$10$hKJD72.kMqksvguxk4exq.uT8PBGKzOCv9sxht7dpaykJSiGGqwvC', 0, '2025-02-17 20:43:59', '2025-02-17 20:43:59'),
(50, 'Yohanes Leonardus, A.Md.Kb.N.', '200004062022011003', 'yohanes.leonardus', '$2y$10$rMZzhURNks5QEqC6BzP6negIsIwqQ5B8wbL.1KiaJ7EaTB6.GamFa', 0, '2025-02-17 20:43:59', '2025-02-17 20:43:59'),
(51, 'Siti Zakiyatul Fadhilah, A.Md.Stat.', '200212012023102002', 'siti.fadhilah', '$2y$10$XQyb73YUAv.1y6KOJ7PWEe9UX/WRmbTFF/J1pOsGCJ//PRDVrECdu', 0, '2025-02-17 20:44:00', '2025-02-17 20:44:00'),
(52, 'Stevanus Ronaldo, S.Tr.Stat.', '199708022019011002', 'stevanus.ronaldo', '$2y$10$6T3M2k7ZXisZp82/4U9auO0DdGC5gJIGufLVImw24DWU6Np83grY6', 0, '2025-02-17 20:44:00', '2025-02-17 20:44:00'),
(53, 'M. Abd. Aziz Assyaukani, S.Tr.Stat.', '199706102021041001', 'aziz.assyaukani', '$2y$10$sbAxzHcSnq7IhfdIaYeRPeHl2f4XwGnzykYWk/CdWtqZ4D.cSM1ee', 0, '2025-02-17 20:44:00', '2025-02-17 20:44:00'),
(54, 'Nurdiana Rihadatul Hasmar, A.Md.Stat.', '200109112023102002', 'nurdiana.rh', '$2y$10$f1/TdcDmLy37eZtte.DWyONFxlUXpMuCTrX31k8H5gRbgOINplVOC', 0, '2025-02-17 20:44:00', '2025-02-17 20:44:00'),
(55, 'Naufalul Ikbar, S.Tr.Stat.', '199703162019121001', 'naufalul.ikbar', '$2y$10$X2u/P/lIeepzx9i/Sne51.jaV9jVUwbhUIoEGy878cPwjaCDiwn1i', 0, '2025-02-17 20:44:01', '2025-02-17 20:44:01'),
(56, 'Zulfa Mufakkir Habibi, S.Tr.Stat.', '199508282019011002', 'zulfamufakkir', '$2y$10$7OIcTlFjivL450AtNN63rua/Af4dNz5Oc/iXbZ95D0xpzOrIkFdzC', 1, '2025-02-17 20:44:01', '2025-02-17 20:44:01'),
(57, 'Mas\'ud Rifai, SST., M.M.', '197712161999121001', 'mas_ud', '$2y$10$WY52U38U5UjpO4Pqn8q9w.HfCXvu6vlCIDsxqHqqoU7.a7X3PgKSe', 0, '2025-02-17 20:45:25', '2025-02-17 20:45:25'),
(59, 'Karno, SE', '197206232003121002', 'kar', '$2y$10$rNds/n24z/CCk.QHiNDBHeS5F2HbYt7WohxKli4pNbfDhFP5jxSXS', 0, '2025-02-17 20:45:26', '2025-02-17 20:45:26'),
(60, 'Lucky Hafid Kusuma S.Tr.Stat.', '199908312022011004', 'lucky.hafid', '$2y$10$siRlwNWKNCkxtlweuUw4xuHnf2GXOGoyQps1PWF8ELxSGqcCW4VYK', 0, '2025-10-27 15:35:45', '2025-10-27 15:35:45'),
(61, 'Chairunnisa Fauzia Samu S.Tr.Stat.', '200007232023022004', 'fauzia.samu', '$2y$10$o3uedLNo5albvgQCIDvB1O9EpzJZxt5ycRqaBbo2/AGXzGT7pFe1G', 0, '2025-10-27 15:36:02', '2025-10-27 15:36:02'),
(62, 'Fachri Izzudin Lazuardi S.Tr.Stat.', '199810122021041001', 'fachri.lazuardi', '$2y$10$Fqd5wljW15CnkKQtuu3oU.ZYfK/NG75flu4tMTNejkkZIaudY45i.', 1, '2025-10-27 15:36:22', '2025-10-27 15:36:22');

-- --------------------------------------------------------

--
-- Table structure for table `ruangan`
--

CREATE TABLE `ruangan` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_ruangan` varchar(100) NOT NULL,
  `tipe` enum('Online','Offline','Hybrid') NOT NULL DEFAULT 'Offline',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ruangan`
--

INSERT INTO `ruangan` (`id`, `nama_ruangan`, `tipe`, `created_at`, `updated_at`) VALUES
(3, 'Ruang Rapat BPS Provinsi Kalimantan Utara', 'Offline', '2025-02-17 11:03:18', '2025-02-17 11:03:18'),
(4, 'Zoom BPS Provinsi Kalimantan Utara', 'Online', '2025-02-17 12:04:58', '2025-02-17 12:04:58'),
(5, 'Hybrid Ruang Rapat dan Zoom Meeting', 'Hybrid', '2025-02-17 19:18:58', '2025-02-17 19:18:58'),
(6, 'Ruang Rapat Atas BPS Provinsi Kalimantan Utara', 'Offline', '2025-02-28 17:05:37', '2025-02-28 17:05:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `meeting`
--
ALTER TABLE `meeting`
  ADD PRIMARY KEY (`id`),
  ADD KEY `meeting_pegawai_id_foreign` (`pegawai_id`),
  ADD KEY `meeting_ruangan_id_foreign` (`ruangan_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `ruangan`
--
ALTER TABLE `ruangan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `meeting`
--
ALTER TABLE `meeting`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `ruangan`
--
ALTER TABLE `ruangan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meeting`
--
ALTER TABLE `meeting`
  ADD CONSTRAINT `meeting_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `meeting_ruangan_id_foreign` FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
