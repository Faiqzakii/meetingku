-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for meetingku
CREATE DATABASE IF NOT EXISTS `meetingku` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `meetingku`;

-- Dumping structure for table meetingku.meeting
CREATE TABLE IF NOT EXISTS `meeting` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_keg` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `ruangan_id` int unsigned NOT NULL,
  `pegawai_id` int unsigned NOT NULL,
  `waktu_mulai` datetime NOT NULL,
  `waktu_selesai` datetime NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `meeting_pegawai_id_foreign` (`pegawai_id`),
  KEY `meeting_ruangan_id_foreign` (`ruangan_id`),
  CONSTRAINT `meeting_pegawai_id_foreign` FOREIGN KEY (`pegawai_id`) REFERENCES `pegawai` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `meeting_ruangan_id_foreign` FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table meetingku.meeting: ~5 rows (approximately)
INSERT INTO `meeting` (`id`, `nama_keg`, `ruangan_id`, `pegawai_id`, `waktu_mulai`, `waktu_selesai`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'Rapat Pembangunan Gedung', 3, 6, '2025-02-17 14:00:00', '2025-02-17 16:00:00', 'approved', '2025-02-17 12:23:13', '2025-02-17 19:46:19'),
	(2, 'Evaluasi Kegiatan Tim IPDS dan PSS se-Provinsi Kalimantan Utara', 4, 6, '2025-02-17 14:00:00', '2025-02-17 16:00:00', 'approved', '2025-02-17 13:45:37', '2025-02-17 20:19:31'),
	(3, 'FGD Penyusunan Publikasi Provinsi Kalimantan Utara Dalam Angka 2025', 3, 6, '2025-02-18 14:00:00', '2025-02-18 16:30:00', 'approved', '2025-02-17 14:08:39', '2025-02-17 14:54:25'),
	(4, 'FGD Penyusunan Publikasi Provinsi Kalimantan Utara Dalam Angka 2025', 3, 6, '2025-02-19 14:00:00', '2025-02-19 16:30:00', 'approved', '2025-02-17 14:09:31', '2025-02-17 14:54:31'),
	(5, 'Rapat Pembangunan Gedung', 3, 6, '2025-02-19 09:00:00', '2025-02-19 12:00:00', 'approved', '2025-02-17 14:54:10', '2025-02-17 14:54:16');

-- Dumping structure for table meetingku.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `class` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `group` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `namespace` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `time` int NOT NULL,
  `batch` int unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table meetingku.migrations: ~3 rows (approximately)
INSERT INTO `migrations` (`id`, `version`, `class`, `group`, `namespace`, `time`, `batch`) VALUES
	(1, '2025-02-15-090323', 'App\\Database\\Migrations\\CreatePegawaiTable', 'default', 'App', 1739702838, 1),
	(2, '2025-02-15-090325', 'App\\Database\\Migrations\\CreateRuanganTable', 'default', 'App', 1739702838, 1),
	(3, '2025-02-15-090359', 'App\\Database\\Migrations\\CreateMeetingTable', 'default', 'App', 1739702838, 1);

-- Dumping structure for table meetingku.pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `nip` varchar(18) COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nip` (`nip`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table meetingku.pegawai: ~51 rows (approximately)
INSERT INTO `pegawai` (`id`, `nama`, `nip`, `username`, `password`, `is_admin`, `created_at`, `updated_at`) VALUES
	(6, 'Faiq Zakki Mubarok', '199908292022011001', 'faiqzakki', '$2y$10$QhcGu31u/TI7qMvBmDlSH.twWlygguPcti4ygYNmz9kHhZE88zg7K', 1, '2025-02-17 01:22:23', '2025-02-17 20:43:20'),
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
	(20, 'Siti Badriyah, S.Tr.Stat.', '199604152019012001', 'badriyah.siti', '$2y$10$c4Xb8OmjZX2gY/XoqAzKXehM2D3awOkD/ETMZzazgCaasjFJkYZfi', 0, '2025-02-17 20:43:53', '2025-02-17 20:43:53'),
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
	(31, 'Muhammad Nurul Alam Hasyim, SST', '199307062017011001', 'nurul.hasyim', '$2y$10$9Yz0uCNrvnXucQp6SGnfJOCZkMauKYSWCSI94Ve60opAaHdfwxKke', 0, '2025-02-17 20:43:55', '2025-02-17 20:43:55'),
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
	(42, 'Bella Rotuaito N, A.Md.', '199710242024212007', 'bellarn-pppk', '$2y$10$AVTJFzM4e5MsPnBNHa7ZhepJMagcgjI0vPH4zXOMbuNrEuh8YAVIS', 0, '2025-02-17 20:43:58', '2025-02-17 20:43:58'),
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
	(58, 'Kasifatul Asriyati, S.Tr.Stat.', '199509202019012002', 'ayi.k', '$2y$10$qxUv2gMS2mh9vOYZa0aq6eT8Xav80zzf0UuU7HT37J5qIN5ldNRhW', 0, '2025-02-17 20:45:26', '2025-02-17 20:45:26'),
	(59, 'Karno, SE', '197206232003121002', 'kar', '$2y$10$rNds/n24z/CCk.QHiNDBHeS5F2HbYt7WohxKli4pNbfDhFP5jxSXS', 0, '2025-02-17 20:45:26', '2025-02-17 20:45:26');

-- Dumping structure for table meetingku.ruangan
CREATE TABLE IF NOT EXISTS `ruangan` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nama_ruangan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tipe` enum('Online','Offline','Hybrid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Offline',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table meetingku.ruangan: ~3 rows (approximately)
INSERT INTO `ruangan` (`id`, `nama_ruangan`, `tipe`, `created_at`, `updated_at`) VALUES
	(3, 'Ruang Rapat BPS Provinsi Kalimantan Utara', 'Offline', '2025-02-17 11:03:18', '2025-02-17 11:03:18'),
	(4, 'Zoom BPS Provinsi Kalimantan Utara', 'Online', '2025-02-17 12:04:58', '2025-02-17 12:04:58'),
	(5, 'Hybrid Ruang Rapat dan Zoom Meeting', 'Hybrid', '2025-02-17 19:18:58', '2025-02-17 19:18:58');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
