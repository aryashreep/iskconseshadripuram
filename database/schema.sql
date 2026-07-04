-- ============================================
-- ISKCON Donation System - Complete Schema
-- ============================================

-- 1. SEVA CATEGORIES (reusable across festivals)
CREATE TABLE IF NOT EXISTS `donation_seva_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `sanskrit_name` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. DONATION CAUSES (each festival/service = one cause)
CREATE TABLE IF NOT EXISTS `donation_causes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `short_title` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `history` TEXT DEFAULT NULL COMMENT 'Rich historical background of the festival/service',
    `significance` TEXT DEFAULT NULL COMMENT 'Spiritual significance and importance',
    `benefits` TEXT DEFAULT NULL COMMENT 'Blessings and benefits of donating/participating',
    `category` ENUM('festival','ekadashi','appearance','disappearance','event','service','construction','general') NOT NULL,
    `subcategory` VARCHAR(100) DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `parent_cause_id` INT DEFAULT NULL,
    `is_time_bound` TINYINT(1) NOT NULL DEFAULT 0,
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `allow_one_time` TINYINT(1) NOT NULL DEFAULT 1,
    `allow_monthly` TINYINT(1) NOT NULL DEFAULT 0,
    `default_mode` ENUM('one_time','monthly') NOT NULL DEFAULT 'one_time',
    `min_amount` DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    `form_type` ENUM('tiers','quantity','multi_item') NOT NULL DEFAULT 'tiers',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `page_type` VARCHAR(50) DEFAULT NULL,
    `page_slug` VARCHAR(255) DEFAULT NULL,
    `content_body` LONGTEXT DEFAULT NULL COMMENT 'Full HTML article content for festival/service detail pages',
    `quick_stats` TEXT DEFAULT NULL COMMENT 'Quick info box data (calendar, highlights, etc.)',
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_causes_parent` FOREIGN KEY (`parent_cause_id`) REFERENCES `donation_causes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. CAUSE-SEVA LINK (pricing per category per cause)
CREATE TABLE IF NOT EXISTS `donation_cause_sevas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cause_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_sevas_cause` FOREIGN KEY (`cause_id`) REFERENCES `donation_causes`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sevas_category` FOREIGN KEY (`category_id`) REFERENCES `donation_seva_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. DONATION TRANSACTIONS (one-time payments)
CREATE TABLE IF NOT EXISTS `donation_transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cause_id` INT DEFAULT NULL,
    `seva_id` INT DEFAULT NULL,
    `donor_name` VARCHAR(150) NOT NULL,
    `donor_email` VARCHAR(150) DEFAULT NULL,
    `donor_phone` VARCHAR(30) DEFAULT NULL,
    `donor_address` TEXT DEFAULT NULL,
    `pan_number` VARCHAR(20) DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
    `donation_mode` ENUM('one_time','monthly') NOT NULL,
    `quantity` INT DEFAULT 1,
    `source_type` VARCHAR(50) DEFAULT NULL,
    `source_slug` VARCHAR(255) DEFAULT NULL,
    `source_url` VARCHAR(500) DEFAULT NULL,
    `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_signature` VARCHAR(255) DEFAULT NULL,
    `payment_status` ENUM('created','attempted','paid','failed','cancelled','refunded') NOT NULL DEFAULT 'created',
    `notes` TEXT DEFAULT NULL,
    `metadata_json` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_transactions_cause` FOREIGN KEY (`cause_id`) REFERENCES `donation_causes`(`id`),
    CONSTRAINT `fk_transactions_seva` FOREIGN KEY (`seva_id`) REFERENCES `donation_cause_sevas`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. DONATION SUBSCRIPTIONS (monthly/recurring)
CREATE TABLE IF NOT EXISTS `donation_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cause_id` INT NOT NULL,
    `seva_id` INT DEFAULT NULL,
    `donor_name` VARCHAR(150) NOT NULL,
    `donor_email` VARCHAR(150) DEFAULT NULL,
    `donor_phone` VARCHAR(30) DEFAULT NULL,
    `donor_address` TEXT DEFAULT NULL,
    `pan_number` VARCHAR(20) DEFAULT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'INR',
    `interval_unit` ENUM('monthly','quarterly','yearly') NOT NULL DEFAULT 'monthly',
    `interval_count` INT NOT NULL DEFAULT 1,
    `source_type` VARCHAR(50) DEFAULT NULL,
    `source_slug` VARCHAR(255) DEFAULT NULL,
    `source_url` VARCHAR(500) DEFAULT NULL,
    `razorpay_plan_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_subscription_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_customer_id` VARCHAR(100) DEFAULT NULL,
    `subscription_status` ENUM('created','authenticated','active','paused','halted','cancelled','completed','failed') NOT NULL DEFAULT 'created',
    `start_at` DATETIME DEFAULT NULL,
    `ended_at` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `metadata_json` JSON DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_subscriptions_cause` FOREIGN KEY (`cause_id`) REFERENCES `donation_causes`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. DONATION PLANS (cached Razorpay plans)
CREATE TABLE IF NOT EXISTS `donation_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cause_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `interval_unit` VARCHAR(20) NOT NULL DEFAULT 'monthly',
    `interval_count` INT NOT NULL DEFAULT 1,
    `razorpay_plan_id` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_plan` (`cause_id`, `amount`, `interval_unit`, `interval_count`),
    CONSTRAINT `fk_plans_cause` FOREIGN KEY (`cause_id`) REFERENCES `donation_causes`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. WEBHOOK LOGS (Razorpay events)
CREATE TABLE IF NOT EXISTS `donation_webhook_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(100) NOT NULL,
    `entity_id` VARCHAR(100) DEFAULT NULL,
    `payload` LONGTEXT NOT NULL,
    `processed` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. MASTER SEVA CATALOG (Enterprise Catalog System)
-- ============================================

-- 8a. MASTER SEVA CATEGORIES (10 top-level umbrella categories)
CREATE TABLE IF NOT EXISTS `master_seva_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `sanskrit_name` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-hand-holding-heart',
    `description` TEXT DEFAULT NULL,
    `parent_id` INT DEFAULT NULL COMMENT 'Self-referencing for sub-category hierarchy',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_msc_parent` FOREIGN KEY (`parent_id`) REFERENCES `master_seva_categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8b. MASTER SEVAS (Single source of truth — one row per unique seva)
CREATE TABLE IF NOT EXISTS `master_sevas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `name` VARCHAR(255) NOT NULL,
    `sanskrit_name` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `short_description` VARCHAR(255) DEFAULT NULL,
    `category_id` INT NOT NULL COMMENT 'FK to master_seva_categories',
    `default_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `min_amount` DECIMAL(10,2) DEFAULT NULL,
    `max_amount` DECIMAL(10,2) DEFAULT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `icon` VARCHAR(100) DEFAULT 'fa-hand-holding-heart',
    `allow_multiple` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Allow multiple sponsorship (quantity > 1)',
    `max_quantity` INT NOT NULL DEFAULT 1 COMMENT 'Maximum quantity per sponsorship',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_time_bound` TINYINT(1) NOT NULL DEFAULT 0,
    `available_from` DATE DEFAULT NULL,
    `available_until` DATE DEFAULT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_ms_category` FOREIGN KEY (`category_id`) REFERENCES `master_seva_categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8c. FESTIVAL-SEVA LINK TABLE (with override support)
CREATE TABLE IF NOT EXISTS `donation_cause_master_sevas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cause_id` INT NOT NULL,
    `master_seva_id` INT NOT NULL,
    `override_amount` DECIMAL(10,2) DEFAULT NULL COMMENT 'NULL = use master default',
    `override_description` TEXT DEFAULT NULL COMMENT 'NULL = use master default',
    `override_max_quantity` INT DEFAULT NULL COMMENT 'NULL = use master default',
    `override_allow_multiple` TINYINT(1) DEFAULT NULL COMMENT 'NULL = use master default',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_dcms_cause` FOREIGN KEY (`cause_id`) REFERENCES `donation_causes`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_dcms_master` FOREIGN KEY (`master_seva_id`) REFERENCES `master_sevas`(`id`),
    UNIQUE KEY `uq_cause_master_seva` (`cause_id`, `master_seva_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. BLOGS (articles and spiritual content)
-- ============================================
CREATE TABLE IF NOT EXISTS `blogs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL COMMENT 'Short card description shown in listings',
    `icon` VARCHAR(100) NOT NULL COMMENT 'FontAwesome icon class',
    `banner_image` VARCHAR(500) DEFAULT NULL,
    `published_date` DATE DEFAULT NULL,
    `tags` TEXT DEFAULT NULL COMMENT 'JSON array of tags',
    `content_body` LONGTEXT DEFAULT NULL COMMENT 'Full HTML article content with {{BASE_URL}} placeholders',
    `meta_title` VARCHAR(255) DEFAULT NULL,
    `meta_description` VARCHAR(500) DEFAULT NULL,
    `is_published` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Indexes for performance
CREATE INDEX `idx_blogs_slug` ON `blogs`(`slug`);
CREATE INDEX `idx_blogs_date` ON `blogs`(`published_date`);
CREATE INDEX `idx_blogs_published` ON `blogs`(`is_published`);

-- Indexes for performance
CREATE INDEX `idx_causes_category` ON `donation_causes`(`category`);
CREATE INDEX `idx_causes_active` ON `donation_causes`(`is_active`);
CREATE INDEX `idx_causes_featured` ON `donation_causes`(`featured`);
CREATE INDEX `idx_causes_page` ON `donation_causes`(`page_type`, `page_slug`);
CREATE INDEX `idx_sevas_cause` ON `donation_cause_sevas`(`cause_id`);
CREATE INDEX `idx_sevas_category` ON `donation_cause_sevas`(`category_id`);
CREATE INDEX `idx_transactions_status` ON `donation_transactions`(`payment_status`);
CREATE INDEX `idx_transactions_cause` ON `donation_transactions`(`cause_id`);
CREATE INDEX `idx_transactions_razorpay` ON `donation_transactions`(`razorpay_order_id`);
CREATE INDEX `idx_subscriptions_cause` ON `donation_subscriptions`(`cause_id`);
CREATE INDEX `idx_subscriptions_status` ON `donation_subscriptions`(`subscription_status`);
CREATE INDEX `idx_subscriptions_razorpay` ON `donation_subscriptions`(`razorpay_subscription_id`);
