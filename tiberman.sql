/*
 Navicat Premium Data Transfer

 Source Server         : Lokal
 Source Server Type    : MySQL
 Source Server Version : 100432 (10.4.32-MariaDB)
 Source Host           : localhost:3306
 Source Schema         : tiberman

 Target Server Type    : MySQL
 Target Server Version : 100432 (10.4.32-MariaDB)
 File Encoding         : 65001

 Date: 05/02/2026 11:23:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for audit_trails
-- ----------------------------
DROP TABLE IF EXISTS `audit_trails`;
CREATE TABLE `audit_trails`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `surat_jalan_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NULL DEFAULT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `audit_trails_user_id_foreign`(`user_id` ASC) USING BTREE,
  INDEX `audit_trails_surat_jalan_id_action_index`(`surat_jalan_id` ASC, `action` ASC) USING BTREE,
  CONSTRAINT `audit_trails_surat_jalan_id_foreign` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `audit_trails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 22 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of audit_trails
-- ----------------------------
INSERT INTO `audit_trails` VALUES (9, 7, 1, 'CREATE', 'Membuat surat jalan baru', '2026-02-04 15:50:17', '2026-02-04 15:50:17');
INSERT INTO `audit_trails` VALUES (10, 8, 1, 'CREATE', 'Membuat surat jalan baru', '2026-02-04 15:51:15', '2026-02-04 15:51:15');
INSERT INTO `audit_trails` VALUES (11, 8, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 15:52:06', '2026-02-04 15:52:06');
INSERT INTO `audit_trails` VALUES (12, 8, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 16:15:33', '2026-02-04 16:15:33');
INSERT INTO `audit_trails` VALUES (13, 8, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 16:16:27', '2026-02-04 16:16:27');
INSERT INTO `audit_trails` VALUES (14, 8, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 16:16:55', '2026-02-04 16:16:55');
INSERT INTO `audit_trails` VALUES (15, 8, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 16:26:41', '2026-02-04 16:26:41');
INSERT INTO `audit_trails` VALUES (16, 8, 2, 'PROOF', 'Upload bukti serah terima & set delivered', '2026-02-04 16:27:10', '2026-02-04 16:27:10');
INSERT INTO `audit_trails` VALUES (17, 9, 1, 'CREATE', 'Membuat surat jalan baru', '2026-02-04 21:54:49', '2026-02-04 21:54:49');
INSERT INTO `audit_trails` VALUES (18, 8, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 22:01:37', '2026-02-04 22:01:37');
INSERT INTO `audit_trails` VALUES (19, 9, 2, 'SCAN', 'Scan QR & update lokasi', '2026-02-04 22:13:42', '2026-02-04 22:13:42');
INSERT INTO `audit_trails` VALUES (20, 9, 2, 'SCAN', 'SCAN lokasi', '2026-02-04 22:41:09', '2026-02-04 22:41:09');
INSERT INTO `audit_trails` VALUES (21, 9, 2, 'SCAN', 'SCAN lokasi', '2026-02-04 22:44:58', '2026-02-04 22:44:58');

-- ----------------------------
-- Table structure for delivery_proofs
-- ----------------------------
DROP TABLE IF EXISTS `delivery_proofs`;
CREATE TABLE `delivery_proofs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `surat_jalan_id` bigint UNSIGNED NOT NULL,
  `receiver_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `photo_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `delivery_proofs_surat_jalan_id_unique`(`surat_jalan_id` ASC) USING BTREE,
  CONSTRAINT `delivery_proofs_surat_jalan_id_foreign` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of delivery_proofs
-- ----------------------------
INSERT INTO `delivery_proofs` VALUES (1, 8, 'Penerima Tes', 'proofs/9Jk6WxIR9HLLHp4Si554LVKukYmSJR27Pn3MZ4As.jpg', '2026-02-04 23:26:00', '2026-02-04 16:27:10', '2026-02-04 16:27:10');

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for groups
-- ----------------------------
DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of groups
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 16 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '2014_10_12_100000_create_password_reset_tokens_table', 1);
INSERT INTO `migrations` VALUES (3, '2014_10_12_100000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES (4, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` VALUES (5, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (6, '2026_02_04_000001_create_groups_table', 1);
INSERT INTO `migrations` VALUES (7, '2026_02_04_000002_add_role_and_group_id_to_users_table', 1);
INSERT INTO `migrations` VALUES (8, '2026_02_04_000003_create_surat_jalan_table', 1);
INSERT INTO `migrations` VALUES (9, '2026_02_04_000004_create_tracking_logs_table', 1);
INSERT INTO `migrations` VALUES (10, '2026_02_04_000005_create_delivery_proofs_table', 1);
INSERT INTO `migrations` VALUES (11, '2026_02_04_000006_create_audit_trails_table', 1);
INSERT INTO `migrations` VALUES (12, '2026_02_04_134554_create_surat_jalans_table', 2);
INSERT INTO `migrations` VALUES (13, '2026_02_04_134603_create_scans_table', 2);
INSERT INTO `migrations` VALUES (14, '2026_02_04_134609_create_proofs_table', 2);
INSERT INTO `migrations` VALUES (15, '2026_02_04_222029_add_antispoof_columns_to_tracking_logs_table', 2);

-- ----------------------------
-- Table structure for password_reset_tokens
-- ----------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_reset_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets`  (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  INDEX `password_resets_email_index`(`email` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_resets
-- ----------------------------

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token` ASC) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type` ASC, `tokenable_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for proofs
-- ----------------------------
DROP TABLE IF EXISTS `proofs`;
CREATE TABLE `proofs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of proofs
-- ----------------------------

-- ----------------------------
-- Table structure for scans
-- ----------------------------
DROP TABLE IF EXISTS `scans`;
CREATE TABLE `scans`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of scans
-- ----------------------------

-- ----------------------------
-- Table structure for surat_jalan
-- ----------------------------
DROP TABLE IF EXISTS `surat_jalan`;
CREATE TABLE `surat_jalan`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_surat_jalan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_code_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('created','on_delivery','delivered') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `created_by` bigint UNSIGNED NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `surat_jalan_kode_surat_jalan_unique`(`kode_surat_jalan` ASC) USING BTREE,
  INDEX `surat_jalan_created_by_foreign`(`created_by` ASC) USING BTREE,
  INDEX `surat_jalan_status_created_by_index`(`status` ASC, `created_by` ASC) USING BTREE,
  CONSTRAINT `surat_jalan_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of surat_jalan
-- ----------------------------
INSERT INTO `surat_jalan` VALUES (7, 'SJ-20260204-MJ4X20', 'qrcodes/SJ-20260204-MJ4X20.svg', 'created', 1, '2026-02-04 15:50:17', '2026-02-04 15:50:17');
INSERT INTO `surat_jalan` VALUES (8, 'SJ-20260204-SUBYA0', 'qrcodes/SJ-20260204-SUBYA0.svg', 'delivered', 1, '2026-02-04 15:51:15', '2026-02-04 16:27:10');
INSERT INTO `surat_jalan` VALUES (9, 'SJ-20260204-Z46FQZ', 'qrcodes/SJ-20260204-Z46FQZ.svg', 'on_delivery', 1, '2026-02-04 21:54:45', '2026-02-04 22:13:42');

-- ----------------------------
-- Table structure for tracking_logs
-- ----------------------------
DROP TABLE IF EXISTS `tracking_logs`;
CREATE TABLE `tracking_logs`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `surat_jalan_id` bigint UNSIGNED NOT NULL,
  `latitude` decimal(10, 6) NOT NULL,
  `longitude` decimal(10, 6) NOT NULL,
  `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `ip_latitude` decimal(10, 7) NULL DEFAULT NULL,
  `ip_longitude` decimal(10, 7) NULL DEFAULT NULL,
  `distance_ip_km` decimal(10, 2) NULL DEFAULT NULL,
  `is_suspicious` tinyint(1) NOT NULL DEFAULT 0,
  `suspicious_reason` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `scan_by` bigint UNSIGNED NULL DEFAULT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `tracking_logs_scan_by_foreign`(`scan_by` ASC) USING BTREE,
  INDEX `tracking_logs_surat_jalan_id_scanned_at_index`(`surat_jalan_id` ASC, `scanned_at` ASC) USING BTREE,
  CONSTRAINT `tracking_logs_scan_by_foreign` FOREIGN KEY (`scan_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  CONSTRAINT `tracking_logs_surat_jalan_id_foreign` FOREIGN KEY (`surat_jalan_id`) REFERENCES `surat_jalan` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tracking_logs
-- ----------------------------
INSERT INTO `tracking_logs` VALUES (1, 8, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 15:52:06', '2026-02-04 15:52:06', '2026-02-04 15:52:06');
INSERT INTO `tracking_logs` VALUES (2, 8, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 16:15:33', '2026-02-04 16:15:33', '2026-02-04 16:15:33');
INSERT INTO `tracking_logs` VALUES (3, 8, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 16:16:27', '2026-02-04 16:16:27', '2026-02-04 16:16:27');
INSERT INTO `tracking_logs` VALUES (4, 8, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 16:16:55', '2026-02-04 16:16:55', '2026-02-04 16:16:55');
INSERT INTO `tracking_logs` VALUES (5, 8, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 16:26:41', '2026-02-04 16:26:41', '2026-02-04 16:26:41');
INSERT INTO `tracking_logs` VALUES (6, 8, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 22:01:37', '2026-02-04 22:01:37', '2026-02-04 22:01:37');
INSERT INTO `tracking_logs` VALUES (7, 9, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, NULL, 2, '2026-02-04 22:13:42', '2026-02-04 22:13:42', '2026-02-04 22:13:42');
INSERT INTO `tracking_logs` VALUES (8, 9, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 2, '2026-02-04 22:41:09', '2026-02-04 22:41:09', '2026-02-04 22:41:09');
INSERT INTO `tracking_logs` VALUES (9, 9, -7.972200, 112.636100, '127.0.0.1', NULL, NULL, NULL, 0, NULL, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 2, '2026-02-04 22:44:58', '2026-02-04 22:44:58', '2026-02-04 22:44:58');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('superuser','admin','creator','view_only') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'creator',
  `group_id` bigint UNSIGNED NULL DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email` ASC) USING BTREE,
  INDEX `users_group_id_foreign`(`group_id` ASC) USING BTREE,
  INDEX `users_role_group_id_index`(`role` ASC, `group_id` ASC) USING BTREE,
  CONSTRAINT `users_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'Odette Carson', 'ficuqiv@mailinator.com', NULL, '$2y$12$ehzS8.fEbEAa8MO7PvnLJ.q.dsd4wfg67tGUBPQSLHDIKpSU5tx6.', 'creator', NULL, NULL, '2026-02-04 13:49:08', '2026-02-04 13:49:08');
INSERT INTO `users` VALUES (2, 'Joy Burton', 'fygowec@mailinator.com', NULL, '$2y$12$0gxOY..L9fvEaFLWR.NeAupVHW.fKJT.GCbUqMXpdRO11k9Fxm/xW', 'superuser', NULL, NULL, '2026-02-04 15:21:25', '2026-02-04 15:21:25');

SET FOREIGN_KEY_CHECKS = 1;
