-- ============================================================
-- SQL SCHEMA UPDATE FOR LIVE PRODUCTION DATABASE (Compatible with all MySQL / phpMyAdmin versions)
-- ============================================================

-- 1. Create order_reports Table
CREATE TABLE IF NOT EXISTS `order_reports` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_notes` text COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_reports_order_type_order_id_index` (`order_type`, `order_id`),
  KEY `order_reports_status_index` (`status`),
  CONSTRAINT `order_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Add Reporting Columns to sms_orders
ALTER TABLE `sms_orders` ADD `is_reported` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `sms_orders` ADD `reported_at` timestamp NULL DEFAULT NULL;
ALTER TABLE `sms_orders` ADD `report_status` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- 3. Add Reporting Columns to boost_orders
ALTER TABLE `boost_orders` ADD `is_reported` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `boost_orders` ADD `reported_at` timestamp NULL DEFAULT NULL;
ALTER TABLE `boost_orders` ADD `report_status` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- 4. Add Reporting Columns to log_orders
ALTER TABLE `log_orders` ADD `is_reported` tinyint(1) NOT NULL DEFAULT '0';
ALTER TABLE `log_orders` ADD `reported_at` timestamp NULL DEFAULT NULL;
ALTER TABLE `log_orders` ADD `report_status` varchar(255) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

-- 5. Create email_logs Table
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED NULL DEFAULT NULL,
  `recipient_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'single',
  `recipients` text COLLATE utf8mb4_unicode_ci NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('queued', 'sent', 'failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'queued',
  `error` text COLLATE utf8mb4_unicode_ci NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `email_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Add Failure Tracking Columns to boost_services
ALTER TABLE `boost_services` ADD `fail_count` int UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `boost_services` ADD `last_error` text COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `boost_services` ADD `last_failed_at` timestamp NULL DEFAULT NULL;

-- 7. Add Provider Column to sms_orders
ALTER TABLE `sms_orders` ADD `provider` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '5sim';

-- 8. Create log_category_videos Table
CREATE TABLE IF NOT EXISTS `log_category_videos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `platform` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `video_url` text COLLATE utf8mb4_unicode_ci NULL,
  `video_file` varchar(255) COLLATE utf8mb4_unicode_ci NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_category_videos_platform_index` (`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

