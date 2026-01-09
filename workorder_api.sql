-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versi server:                 10.4.32-MariaDB - mariadb.org binary distribution
-- OS Server:                    Win64
-- HeidiSQL Versi:               12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Membuang struktur basisdata untuk workorder_api
DROP DATABASE IF EXISTS `workorder_api`;
CREATE DATABASE IF NOT EXISTS `workorder_api` /*!40100 DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci */;
USE `workorder_api`;

-- membuang struktur untuk table workorder_api.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.cache: ~2 rows (lebih kurang)
DELETE FROM `cache`;
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-a75f3f172bfb296f2e10cbfc6dfc1883', 'i:1;', 1767849260),
	('laravel-cache-a75f3f172bfb296f2e10cbfc6dfc1883:timer', 'i:1767849260;', 1767849260);

-- membuang struktur untuk table workorder_api.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.cache_locks: ~0 rows (lebih kurang)
DELETE FROM `cache_locks`;

-- membuang struktur untuk table workorder_api.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.failed_jobs: ~0 rows (lebih kurang)
DELETE FROM `failed_jobs`;

-- membuang struktur untuk table workorder_api.internal_notifications
DROP TABLE IF EXISTS `internal_notifications`;
CREATE TABLE IF NOT EXISTS `internal_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `npp` varchar(50) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'unread',
  `uuid_pengajuan` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.internal_notifications: ~38 rows (lebih kurang)
DELETE FROM `internal_notifications`;
INSERT INTO `internal_notifications` (`id`, `npp`, `judul`, `pesan`, `status`, `uuid_pengajuan`, `created_at`, `updated_at`) VALUES
	(1, '690829503', 'Pengajuan Baru Masuk', 'Ada pengajuan baru dengan nomor surat 000001/PB/01/2026 menunggu persetujuan Anda.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:07:02', '2026-01-04 21:07:02'),
	(2, '690831023', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000001/PB/01/2026.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:13:02', '2026-01-04 21:13:02'),
	(3, '6908321002', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:13:03', '2026-01-04 21:13:03'),
	(4, '6908321002', 'Penugasan ', 'Pengajuan Anda telah ditugaskan oleh Ade Fajr Ariav, S.Kom', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:27:37', '2026-01-04 21:27:37'),
	(5, '6908322003', 'Penugasan', 'Anda ditugaskan dalam SPK: 000001/PB/01/2026', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:27:37', '2026-01-04 21:27:37'),
	(6, '6908321002', 'Penugasan', 'Anda telah ditugaskan menjadi PIC pada SPK: 000001/PB/01/2026', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:27:37', '2026-01-04 21:27:37'),
	(7, '690829503', 'Persetujuan SPK', 'Halo A. Sigit Dwiyoga, S.Kom, SPK menunggu tanda tangan Anda.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:36:36', '2026-01-04 21:36:36'),
	(8, '690829503', 'Persetujuan SPK', 'Halo A. Sigit Dwiyoga, S.Kom, SPK menunggu tanda tangan Anda.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-04 21:38:06', '2026-01-04 21:38:06'),
	(9, '6908321002', 'Pengajuan Baru Masuk', 'Ada pengajuan baru dengan nomor surat 000002/PP/01/2026 menunggu persetujuan Anda.', 'unread', '06ec31b9-0477-4d7d-9b27-4580052c8143', '2026-01-05 06:17:28', '2026-01-05 06:17:28'),
	(10, '6908321002', 'Pengajuan Baru Masuk', 'Ada pengajuan baru dengan nomor surat 000003/PP/01/2026 menunggu persetujuan Anda.', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:20:40', '2026-01-05 06:20:40'),
	(11, '690830401', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000003/PP/01/2026.', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:31:20', '2026-01-05 06:31:20'),
	(12, '6908319016', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:31:22', '2026-01-05 06:31:22'),
	(13, '690830401', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000002/PP/01/2026.', 'unread', '06ec31b9-0477-4d7d-9b27-4580052c8143', '2026-01-05 06:35:33', '2026-01-05 06:35:33'),
	(14, '6908321002', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', '06ec31b9-0477-4d7d-9b27-4580052c8143', '2026-01-05 06:35:35', '2026-01-05 06:35:35'),
	(15, '690830401', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000003/PP/01/2026.', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:38:16', '2026-01-05 06:38:16'),
	(16, '6908319016', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:38:18', '2026-01-05 06:38:18'),
	(17, '6908319016', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: rejected', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:38:55', '2026-01-05 06:38:55'),
	(18, '6908319016', 'Penugasan ', 'Pengajuan Anda telah ditugaskan oleh Ade Fajr Ariav, S.Kom', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:55:05', '2026-01-05 06:55:05'),
	(19, '6908321002', 'Penugasan', 'Anda ditugaskan dalam SPK: 000003/PP/01/2026', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:55:05', '2026-01-05 06:55:05'),
	(20, '6908321002', 'Penugasan', 'Anda telah ditugaskan menjadi PIC pada SPK: 000003/PP/01/2026', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 06:55:05', '2026-01-05 06:55:05'),
	(21, '690830401', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000003/PP/01/2026.', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 07:28:08', '2026-01-05 07:28:08'),
	(22, '6908319016', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 07:28:10', '2026-01-05 07:28:10'),
	(23, '690830401', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000003/PP/01/2026.', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 07:29:59', '2026-01-05 07:29:59'),
	(24, '6908319016', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 07:30:01', '2026-01-05 07:30:01'),
	(25, '690830401', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000003/PP/01/2026.', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 07:31:31', '2026-01-05 07:31:31'),
	(26, '6908319016', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', 'ef1fa4ae-092c-4649-abe8-847f612fe930', '2026-01-05 07:31:33', '2026-01-05 07:31:33'),
	(27, '690839804', 'TTD SPK', 'Halo Arief Endrawan J, S.E., SPK  menunggu tanda tangan Anda.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-05 21:25:04', '2026-01-05 21:25:04'),
	(28, '6908321002', 'SPK Disetujui', 'Halo , SPK Anda telah disetujui oleh seluruh pihak.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-05 21:59:23', '2026-01-05 21:59:23'),
	(29, '6908321002', 'SPK Disetujui', 'Halo , SPK Anda telah disetujui oleh seluruh pihak.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-06 06:06:40', '2026-01-06 06:06:40'),
	(30, '6908321002', 'SPK Disetujui', 'Halo , SPK Anda telah disetujui oleh seluruh pihak.', 'unread', '02e158f8-f713-4b12-b941-0a589cd8d5e7', '2026-01-06 07:27:25', '2026-01-06 07:27:25'),
	(31, '690829503', 'Pengajuan Baru Masuk', 'Ada pengajuan baru dengan nomor surat 000004/PB/01/2026 menunggu persetujuan Anda.', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-06 18:23:39', '2026-01-06 18:23:39'),
	(32, '6908313003', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000004/PB/01/2026.', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-06 21:45:48', '2026-01-06 21:45:48'),
	(33, '690829503', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-06 21:45:49', '2026-01-06 21:45:49'),
	(34, '6908313003', 'Ada Penugasan SPK Baru', 'Ada SPK baru yang perlu Anda tugaskan untuk pengajuan dengan nomor surat 000004/PB/01/2026.', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-07 07:19:42', '2026-01-07 07:19:42'),
	(35, '690829503', 'Status Pengajuan Diupdate', 'Pengajuan Anda telah diupdate menjadi: approved', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-07 07:19:43', '2026-01-07 07:19:43'),
	(36, '690829503', 'Penugasan ', 'Pengajuan Anda telah ditugaskan oleh Ade Fajr Ariav, S.Kom', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-07 18:19:26', '2026-01-07 18:19:26'),
	(37, '1987654322', 'Penugasan', 'Anda ditugaskan dalam SPK: 000004/PB/01/2026', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-07 18:19:27', '2026-01-07 18:19:27'),
	(38, '1987654321', 'Penugasan', 'Anda telah ditugaskan menjadi PIC pada SPK: 000004/PB/01/2026', 'unread', '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '2026-01-07 18:19:27', '2026-01-07 18:19:27');

-- membuang struktur untuk table workorder_api.jobs
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.jobs: ~0 rows (lebih kurang)
DELETE FROM `jobs`;

-- membuang struktur untuk table workorder_api.job_batches
DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.job_batches: ~0 rows (lebih kurang)
DELETE FROM `job_batches`;

-- membuang struktur untuk table workorder_api.masterhal
DROP TABLE IF EXISTS `masterhal`;
CREATE TABLE IF NOT EXISTS `masterhal` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `nama_jenis` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `masterhal_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.masterhal: ~3 rows (lebih kurang)
DELETE FROM `masterhal`;
INSERT INTO `masterhal` (`id`, `kode`, `nama_jenis`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'PK', 'Perbaikan Kerusakan', 1, NULL, NULL),
	(2, 'PB', 'Pemeliharaan Barang', 1, NULL, NULL),
	(3, 'PP', 'Pengaduan Perbaikan', 1, NULL, NULL);

-- membuang struktur untuk table workorder_api.masterjenispekerjaan
DROP TABLE IF EXISTS `masterjenispekerjaan`;
CREATE TABLE IF NOT EXISTS `masterjenispekerjaan` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(50) NOT NULL,
  `nama_pekerjaan` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `masterjenispekerjaan_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.masterjenispekerjaan: ~3 rows (lebih kurang)
DELETE FROM `masterjenispekerjaan`;
INSERT INTO `masterjenispekerjaan` (`id`, `kode`, `nama_pekerjaan`, `status`) VALUES
	(1, 'PM', 'Perbaikan Menyeluruh', 1),
	(2, 'PK', 'Perbaikan Komponen', 1),
	(3, 'PR', 'Perbaikan Ringan', 1);

-- membuang struktur untuk table workorder_api.master_status
DROP TABLE IF EXISTS `master_status`;
CREATE TABLE IF NOT EXISTS `master_status` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `master_status_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.master_status: ~5 rows (lebih kurang)
DELETE FROM `master_status`;
INSERT INTO `master_status` (`id`, `code`, `name`, `created_at`, `updated_at`) VALUES
	(1, 'SE', 'Selesai', NULL, NULL),
	(2, 'BS', 'Belum Selesai', NULL, NULL),
	(3, 'TS', 'Tidak Selesai', NULL, NULL),
	(4, 'ME', 'Menunggu', NULL, NULL),
	(5, 'PS', 'Proses', NULL, NULL);

-- membuang struktur untuk table workorder_api.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.migrations: ~13 rows (lebih kurang)
DELETE FROM `migrations`;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(55, '2026_01_05_000001_create_internal_notifications_table', 1),
	(56, '2026_01_05_000002_create_masterhal_table', 2),
	(57, '2026_01_05_000003_create_masterjenispekerjaan_table', 3),
	(58, '2026_01_05_000004_create_master_status_table', 4),
	(59, '2026_01_05_000005_create_pengajuans_table', 5),
	(60, '2026_01_05_000006_create_spks_table', 6),
	(61, '2026_01_05_000007_create_timelines_table', 7),
	(62, '2026_01_05_000008_update_users_table', 8),
	(63, '0001_01_01_000001_create_cache_table', 9),
	(64, '0001_01_01_000002_create_jobs_table', 10),
	(65, '2026_01_05_040201_add_timestamps_to_users_table', 11),
	(66, '2026_01_05_041641_add_is_deleted_to_spks_table', 12),
	(67, '2026_01_05_133356_add_kd_satker_to_spks_table', 13);

-- membuang struktur untuk table workorder_api.pengajuans
DROP TABLE IF EXISTS `pengajuans`;
CREATE TABLE IF NOT EXISTS `pengajuans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `no_surat` varchar(255) DEFAULT NULL,
  `no_referensi` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `hal_id` bigint(20) unsigned NOT NULL,
  `kd_satker` varchar(50) DEFAULT NULL,
  `npp_kepala_satker` varchar(50) DEFAULT NULL,
  `satker` varchar(255) DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `file` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`file`)),
  `name_pelapor` varchar(255) NOT NULL,
  `npp_pelapor` varchar(50) NOT NULL,
  `tlp_pelapor` varchar(30) DEFAULT NULL,
  `ttd_pelapor` varchar(255) DEFAULT NULL,
  `mengetahui` varchar(255) NOT NULL DEFAULT '0',
  `mengetahui_name` varchar(255) DEFAULT NULL,
  `mengetahui_npp` varchar(255) DEFAULT NULL,
  `mengetahui_tlp` varchar(30) DEFAULT NULL,
  `ttd_mengetahui` varchar(255) DEFAULT NULL,
  `catatan_status` text DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pengajuans_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.pengajuans: ~4 rows (lebih kurang)
DELETE FROM `pengajuans`;
INSERT INTO `pengajuans` (`id`, `uuid`, `no_surat`, `no_referensi`, `status`, `hal_id`, `kd_satker`, `npp_kepala_satker`, `satker`, `kode_barang`, `keterangan`, `file`, `name_pelapor`, `npp_pelapor`, `tlp_pelapor`, `ttd_pelapor`, `mengetahui`, `mengetahui_name`, `mengetahui_npp`, `mengetahui_tlp`, `ttd_mengetahui`, `catatan_status`, `is_deleted`, `created_at`, `updated_at`) VALUES
	(1, '02e158f8-f713-4b12-b941-0a589cd8d5e7', '000001/PB/01/2026', '000003/PP/01/2026', 'pending', 3, '04.02.02', '690831003', '07', '2020209', 'Test 3', '["work-order\\/2026\\/01\\/work-order-87c54c75-c124-4242-8e7d-bac0f7003e40-05010175-1002-202412-1767586015029-0.jpeg.jpg","work-order\\/2026\\/01\\/work-order-87c54c75-c124-4242-8e7d-bac0f7003e40-05010175-1002-202412-1767586015029-1.jpeg.jpg"]', 'Ade Fajr Ariav, S.Kom', '6908321002', '083838191709', 'work-order/2026/01/ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1767586015029.png.png', 'Plt. Kepala Sub Bidang Teknologi dan Informasi', 'A. Sigit Dwiyoga, S.Kom', '690829503', '081325545076', NULL, NULL, 0, '2026-01-04 21:07:01', '2026-01-07 18:39:44'),
	(2, '06ec31b9-0477-4d7d-9b27-4580052c8143', '000002/PP/01/2026', '000003/PK/11/2025', 'approved', 3, '04.01', '690830401', 'Unit IT', 'BRG-001', 'Perbaikan Komputer', '["work-order\\/2025\\/11\\/&filename=ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1763431313626.jpg.jpg"]', 'Ade Fajr Ariav, S.Kom', '6908321002', '083838191709', 'work-order/2025/11/&filename=ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1763431313626.jpg.jpg', 'ka cab/ka bag/ka bid/kasatker', 'Ade Fajr Ariav, S.Kom', '6908321002', '08', 'uploads/ttd/123.png', NULL, 0, '2026-01-05 06:17:26', '2026-01-05 06:35:30'),
	(3, 'ef1fa4ae-092c-4649-abe8-847f612fe930', '000003/PP/01/2026', '000001/PB/01/2026', 'approved', 2, '04.03.02', '690831023', 'Unit IT', '0091', 'tolong segera di perbaiki', '["work-order\\/2025\\/11\\/&filename=ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1763431313626.jpg.jpg","uplod\\/\\/ft12","uplod\\/\\/ft12 tess"]', 'Angger Priyardhan Putro, S.Kom', '6908319016', '082227779845', 'work-order/2025/11/&filename=ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1763431313626.jpg.jpg', 'ka cab/ka bag/ka bid/kasatker', 'Ade Fajr Ariav, S.Kom', '6908321002', '08', 'uploads/ttd/123.png', NULL, 0, '2026-01-05 06:20:39', '2026-01-07 18:10:15'),
	(4, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '000004/PB/01/2026', '000004/PB/01/2026', 'approved', 3, '04.01.03', '6908313003', '07', '7770009', 'Test 2 2\n\n\n<i>000001/PB/01/2026</i>', '["work-order\\/2026\\/01\\/work-order-87c54c75-c124-4242-8e7d-bac0f7003e40-05010175-1002-202412-1767586015029-0.jpeg.jpg","work-order\\/2026\\/01\\/work-order-87c54c75-c124-4242-8e7d-bac0f7003e40-05010175-1002-202412-1767586015029-1.jpeg.jpg"]', 'Ade Fajr Ariav, S.Kom', '690829503', '083838191709', 'work-order/2026/01/ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1767749000109.png.png', 'Plt. Kepala Sub Bidang Teknologi dan Informasi', 'A. Sigit Dwiyoga, S.Kom', '690829503', '081325545076', 'work-order/2026/01//ttd-mengetahui-2beaa45b-42c2-4a53-a18c-b4a75783d44e.png.png', NULL, 0, '2026-01-06 18:23:38', '2026-01-07 07:19:39');

-- membuang struktur untuk table workorder_api.spks
DROP TABLE IF EXISTS `spks`;
CREATE TABLE IF NOT EXISTS `spks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid_pengajuan` char(36) NOT NULL,
  `kd_satker` varchar(50) DEFAULT NULL,
  `jenis_pekerjaan_id` bigint(20) unsigned DEFAULT NULL,
  `no_surat` varchar(255) DEFAULT NULL,
  `no_referensi` varchar(255) DEFAULT NULL,
  `kode_barang` varchar(255) DEFAULT NULL,
  `status_id` bigint(20) unsigned DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `uraian_pekerjaan` text DEFAULT NULL,
  `stafs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stafs`)),
  `penanggung_jawab_npp` varchar(50) DEFAULT NULL,
  `penanggung_jawab_tlp` varchar(30) DEFAULT NULL,
  `penanggung_jawab_name` varchar(255) DEFAULT NULL,
  `penanggung_jawab_ttd` longtext DEFAULT NULL,
  `menyetujui` varchar(250) NOT NULL DEFAULT '0',
  `menyetujui_name` varchar(255) DEFAULT NULL,
  `menyetujui_npp` varchar(50) DEFAULT NULL,
  `menyetujui_tlp` varchar(30) DEFAULT NULL,
  `menyetujui_ttd` longtext DEFAULT NULL,
  `mengetahui` varchar(250) NOT NULL DEFAULT '0',
  `mengetahui_name` varchar(255) DEFAULT NULL,
  `mengetahui_npp` varchar(50) DEFAULT NULL,
  `mengetahui_tlp` varchar(30) DEFAULT NULL,
  `mengetahui_ttd` longtext DEFAULT NULL,
  `npp_kepala_satker` varchar(50) DEFAULT NULL,
  `file` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`file`)),
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.spks: ~5 rows (lebih kurang)
DELETE FROM `spks`;
INSERT INTO `spks` (`id`, `uuid_pengajuan`, `kd_satker`, `jenis_pekerjaan_id`, `no_surat`, `no_referensi`, `kode_barang`, `status_id`, `tanggal`, `uraian_pekerjaan`, `stafs`, `penanggung_jawab_npp`, `penanggung_jawab_tlp`, `penanggung_jawab_name`, `penanggung_jawab_ttd`, `menyetujui`, `menyetujui_name`, `menyetujui_npp`, `menyetujui_tlp`, `menyetujui_ttd`, `mengetahui`, `mengetahui_name`, `mengetahui_npp`, `mengetahui_tlp`, `mengetahui_ttd`, `npp_kepala_satker`, `file`, `is_deleted`, `created_at`, `updated_at`) VALUES
	(1, '02e158f8-f713-4b12-b941-0a589cd8d5e7', '04.03.02', 2, '000001/PB/01/2026', NULL, '202020', 3, '2026-01-05', 'perbaikan komputer kantor', '[{"npp":"6908321002","nama":"Ade Fajr Ariav, S.Kom","tlp":"083838191709","is_penanggung_jawab":true},{"npp":"6908322003","nama":"Bondan Pramana, S.Kom","tlp":"08978987396","is_penanggung_jawab":false}]', '6908321002', '083838191709', 'Ade Fajr Ariav, S.Kom', 'work-order/2026/01/spk-work-order-000001-PB-01-2026-1767587877596-0.png.png', 'Sub Bidang Teknologi dan Informasi', 'A. Sigit Dwiyoga, S.Kom', '690829503', '081325545076', 'work-order/2026/01/spk-work-order-000001-PB-01-2026-1767673480849-0.png.png', 'Bidang Pengembangan Program', 'Arief Endrawan J, S.E.', '690839804', '0816698965', NULL, '690831023', '["work-order\\/2026\\/01\\/spk-work-order-000001-PB-01-2026-1767587780924-0.jpeg.jpg","work-order\\/2026\\/01\\/spk-work-order-000001-PB-01-2026-1767587780924-1.png.png"]', 0, '2026-01-04 21:13:02', '2026-01-06 07:27:24'),
	(3, '06ec31b9-0477-4d7d-9b27-4580052c8143', '04.01', NULL, '000002/PP/01/2026', '000003/PK/11/2025', 'BRG-001', 4, '2026-01-05', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '690830401', NULL, 0, '2026-01-05 06:35:33', '2026-01-05 06:35:33'),
	(6, 'ef1fa4ae-092c-4649-abe8-847f612fe930', '04.01', NULL, '000003/PP/01/2026', '000003/PK/11/2025', 'BRG-001', 4, '2026-01-05', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '690830401', NULL, 0, '2026-01-05 07:29:59', '2026-01-05 07:29:59'),
	(7, 'ef1fa4ae-092c-4649-abe8-847f612fe930', '04.01', NULL, '000003/PP/01/2026', '000003/PK/11/2025', 'BRG-001', 4, '2026-01-05', NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '690830401', NULL, 0, '2026-01-05 07:31:31', '2026-01-05 07:31:31'),
	(8, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', '04.01.03', NULL, '000004/PB/01/2026', '000001/PB/01/2026', '7770009', 5, '2026-01-07', NULL, '[{"npp":"1987654321","nama":"Andi Pratama","tlp":"081234567890","is_penanggung_jawab":true},{"npp":"1987654322","nama":"Siti Rahma","tlp":"081298765432","is_penanggung_jawab":false}]', '1987654321', '081234567890', 'Andi Pratama', NULL, '0', NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, NULL, '6908313003', NULL, 0, '2026-01-06 21:45:48', '2026-01-07 18:19:22');

-- membuang struktur untuk table workorder_api.timelines
DROP TABLE IF EXISTS `timelines`;
CREATE TABLE IF NOT EXISTS `timelines` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid_pengajuan` char(36) NOT NULL,
  `source` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.timelines: ~45 rows (lebih kurang)
DELETE FROM `timelines`;
INSERT INTO `timelines` (`id`, `uuid_pengajuan`, `source`, `title`, `status`, `message`, `created_at`, `updated_at`) VALUES
	(1, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'pengajuan', 'Pengajuan Baru Dibuat', 'pending', 'Pengajuan berhasil dibuat oleh pelapor.', '2026-01-04 21:07:01', '2026-01-04 21:07:01'),
	(2, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved', '2026-01-04 21:13:00', '2026-01-04 21:13:00'),
	(3, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'SPK Ditugaskan', '5', 'SPK ditugaskan oleh Ade Fajr Ariav, S.Kom', '-', '2026-01-04 21:27:36', '2026-01-04 21:27:36'),
	(4, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'PIC UPDATE', 'SPK Diperbarui', 'PIC memperbarui data SPK & menentukan mengetahui', 'Ade Fajr Ariav, S.Kom', '2026-01-04 21:36:35', '2026-01-04 21:36:35'),
	(5, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'PIC UPDATE', 'SPK Diperbarui', 'PIC memperbarui data SPK & menentukan mengetahui', 'Ade Fajr Ariav, S.Kom', '2026-01-04 21:38:06', '2026-01-04 21:38:06'),
	(6, '06ec31b9-0477-4d7d-9b27-4580052c8143', 'pengajuan', 'Pengajuan Baru Dibuat', 'pending', 'Pengajuan berhasil dibuat oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 06:17:26', '2026-01-05 06:17:26'),
	(7, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'pengajuan', 'Pengajuan Baru Dibuat', 'pending', 'Pengajuan berhasil dibuat oleh Angger Priyardhan Putro, S.Kom.', '2026-01-05 06:20:39', '2026-01-05 06:20:39'),
	(8, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 06:31:16', '2026-01-05 06:31:16'),
	(9, '06ec31b9-0477-4d7d-9b27-4580052c8143', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 06:35:30', '2026-01-05 06:35:30'),
	(10, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 06:37:05', '2026-01-05 06:37:05'),
	(11, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 06:38:11', '2026-01-05 06:38:11'),
	(12, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'rejected', 'Ditolak oleh Ade Fajr Ariav, S.Kom. Catatan: tidak sesuai sop', '2026-01-05 06:38:53', '2026-01-05 06:38:53'),
	(13, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'spk', 'SPK Ditugaskan', 'Ditugaskan', 'SPK ditugaskan oleh Ade Fajr Ariav, S.Kom kepada staf: Dimas Saputra, ade', '2026-01-05 06:55:04', '2026-01-05 06:55:04'),
	(14, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 07:28:03', '2026-01-05 07:28:03'),
	(15, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 07:29:55', '2026-01-05 07:29:55'),
	(16, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh Ade Fajr Ariav, S.Kom.', '2026-01-05 07:31:19', '2026-01-05 07:31:19'),
	(17, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'spk', 'Persetujuan SPK', 'approved', 'SPK telah disetujui oleh A. Sigit Dwiyoga, S.Kom.', '2026-01-05 21:25:03', '2026-01-05 21:25:03'),
	(18, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'spk', 'TTD SPK', 'signed', 'SPK telah ditandatangani oleh Arief Endrawan J, S.E..', '2026-01-05 21:59:22', '2026-01-05 21:59:22'),
	(19, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'spk', 'TTD SPK', 'signed', 'SPK telah ditandatangani oleh Arief Endrawan J, S.E..', '2026-01-06 06:06:39', '2026-01-06 06:06:39'),
	(20, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'spk', 'TTD SPK', 'signed', 'SPK telah ditandatangani oleh Arief Endrawan J, S.E..', '2026-01-06 07:27:24', '2026-01-06 07:27:24'),
	(21, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'pengajuan', 'Pengajuan Baru Dibuat', 'pending', 'Pengajuan berhasil dibuat oleh Ade Fajr Ariav, S.Kom.', '2026-01-06 18:23:38', '2026-01-06 18:23:38'),
	(22, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Pengajuan telah diperbarui.', '2026-01-06 20:43:12', '2026-01-06 20:43:12'),
	(23, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Pengajuan telah diperbarui.', '2026-01-06 20:43:59', '2026-01-06 20:43:59'),
	(24, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Pengajuan telah diperbarui.', '2026-01-06 20:44:59', '2026-01-06 20:44:59'),
	(25, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Pengajuan telah diperbarui.', '2026-01-06 20:45:52', '2026-01-06 20:45:52'),
	(26, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Pengajuan telah diperbarui.', '2026-01-06 20:49:36', '2026-01-06 20:49:36'),
	(27, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Pengajuan telah diperbarui.', '2026-01-06 20:50:15', '2026-01-06 20:50:15'),
	(28, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 20:52:30', '2026-01-06 20:52:30'),
	(29, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 20:55:33', '2026-01-06 20:55:33'),
	(30, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 20:55:59', '2026-01-06 20:55:59'),
	(31, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 20:56:11', '2026-01-06 20:56:11'),
	(32, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 20:59:17', '2026-01-06 20:59:17'),
	(33, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'edit', 'Pengajuan Diedit', 'approved', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 21:00:14', '2026-01-06 21:00:14'),
	(34, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'edit', 'Pengajuan Diedit', 'approved', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 21:00:45', '2026-01-06 21:00:45'),
	(35, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 21:12:52', '2026-01-06 21:12:52'),
	(36, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 21:16:32', '2026-01-06 21:16:32'),
	(37, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'edit', 'Pengajuan Diedit', 'approved', 'Data pengajuan diperbarui oleh pelapor', '2026-01-06 21:19:49', '2026-01-06 21:19:49'),
	(38, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh A. Sigit Dwiyoga, S.Kom.', '2026-01-06 21:45:46', '2026-01-06 21:45:46'),
	(39, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-07 07:19:00', '2026-01-07 07:19:00'),
	(40, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'status', 'Status Pengajuan Diupdate', 'approved', 'Status diupdate menjadi approved oleh A. Sigit Dwiyoga, S.Kom.', '2026-01-07 07:19:39', '2026-01-07 07:19:39'),
	(41, 'ef1fa4ae-092c-4649-abe8-847f612fe930', 'edit', 'Pengajuan Diedit', 'approved', 'Data pengajuan diperbarui oleh pelapor', '2026-01-07 18:10:15', '2026-01-07 18:10:15'),
	(42, '2beaa45b-42c2-4a53-a18c-b4a75783d44e', 'spk', 'SPK Ditugaskan', 'Ditugaskan', 'SPK ditugaskan oleh Charisma Mayang S, S.Hum kepada staf: Andi Pratama, Siti Rahma', '2026-01-07 18:19:24', '2026-01-07 18:19:24'),
	(43, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-07 18:27:58', '2026-01-07 18:27:58'),
	(44, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-07 18:34:33', '2026-01-07 18:34:33'),
	(45, '02e158f8-f713-4b12-b941-0a589cd8d5e7', 'edit', 'Pengajuan Diedit', 'pending', 'Data pengajuan diperbarui oleh pelapor', '2026-01-07 18:39:44', '2026-01-07 18:39:44');

-- membuang struktur untuk table workorder_api.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `npp` varchar(50) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `ttd_path` varchar(255) DEFAULT NULL,
  `ttd_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ttd_list`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_npp_unique` (`npp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Membuang data untuk tabel workorder_api.users: ~4 rows (lebih kurang)
DELETE FROM `users`;
INSERT INTO `users` (`id`, `name`, `npp`, `is_default`, `ttd_path`, `ttd_list`, `created_at`, `updated_at`) VALUES
	(1, 'Ade Fajr Ariav, S.Kom', '6908321002', 0, 'work-order/2026/01/ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1767749000109.png.png', NULL, '2026-01-04 21:03:00', '2026-01-06 18:23:35'),
	(2, 'Angger Priyardhan Putro, S.Kom', '6908319016', 0, 'work-order/2025/11/&filename=ttd-pelapor-87c54c75-c124-4242-8e7d-bac0f7003e40-1763431313626.jpg.jpg', NULL, '2026-01-05 06:20:37', '2026-01-05 06:20:37'),
	(3, 'Arief Endrawan J, S.E.', '690839804', 0, 'work-order/2026/01/spk-work-order-000001-PB-01-2026-1767709604567-0.png.png', NULL, '2026-01-06 06:06:39', '2026-01-06 07:27:24'),
	(4, 'A. Sigit Dwiyoga, S.Kom', '690829503', 0, 'work-order/2026/01//ttd-mengetahui-2beaa45b-42c2-4a53-a18c-b4a75783d44e.png.png', NULL, '2026-01-06 21:45:46', '2026-01-07 07:19:39');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
