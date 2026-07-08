-- ================================================================
-- PRODUCTION SCHEMA UPDATES & MIGRATION SQL SCRIPT
-- Run this script in phpMyAdmin on your production database
-- ================================================================

-- ----------------------------------------------------------------
-- PHASE 1: ALTER EXISTING TABLES
-- ----------------------------------------------------------------

-- 1. Widening role column in admins table
ALTER TABLE `admins` MODIFY `role` VARCHAR(255) NOT NULL DEFAULT 'editor';

-- 2. Adding master_seva_id column and foreign key to donation_transactions
ALTER TABLE `donation_transactions` 
  ADD COLUMN `master_seva_id` INT DEFAULT NULL AFTER `seva_id`,
  ADD KEY `fk_transactions_master_seva` (`master_seva_id`),
  ADD CONSTRAINT `fk_transactions_master_seva` FOREIGN KEY (`master_seva_id`) REFERENCES `master_sevas` (`id`) ON DELETE SET NULL;

-- ----------------------------------------------------------------
-- PHASE 2: CREATE NEW TABLES
-- ----------------------------------------------------------------

-- Table: rbac_roles
CREATE TABLE `rbac_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Machine-readable identifier, e.g. content_manager',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human-readable name, e.g. Content Manager',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'What this role is for',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'System roles cannot be deleted via UI',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rbac_permissions
CREATE TABLE `rbac_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. donations.view',
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Module name, e.g. donations, festivals',
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Action name, e.g. view, create, edit, delete, export',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human-readable label, e.g. View Donations',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_perm_module` (`module`),
  KEY `idx_perm_action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rbac_role_permissions
CREATE TABLE `rbac_role_permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_role_perm` (`role_id`,`permission_id`),
  KEY `fk_rp_perm` (`permission_id`),
  CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `rbac_permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: rbac_user_roles
CREATE TABLE `rbac_user_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_by` int DEFAULT NULL COMMENT 'Admin ID who assigned this role',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_role` (`admin_id`,`role_id`),
  KEY `fk_ur_role` (`role_id`),
  KEY `fk_ur_assigner` (`assigned_by`),
  CONSTRAINT `fk_ur_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ur_assigner` FOREIGN KEY (`assigned_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `rbac_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sudamaseva_donors
CREATE TABLE `sudamaseva_donors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `donor_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sudamaseva',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','inactive','paused') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_phone` (`phone`),
  KEY `idx_email` (`email`),
  KEY `idx_status` (`status`),
  KEY `idx_source` (`source`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sudamaseva_subscriptions
CREATE TABLE `sudamaseva_subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `donor_id` int NOT NULL,
  `amount` int NOT NULL COMMENT 'Monthly amount in INR',
  `razorpay_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_plan_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','completed','paused','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `total_installments` int NOT NULL DEFAULT '0' COMMENT '0 = open-ended; >0 = fixed plan',
  `installments_paid` int NOT NULL DEFAULT '0',
  `source` enum('migrated','new') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `old_user_id` int DEFAULT NULL COMMENT 'Reference to old tbl_users.id (migrated only)',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_razorpay_sub` (`razorpay_subscription_id`),
  KEY `idx_donor` (`donor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_sub_donor` FOREIGN KEY (`donor_id`) REFERENCES `sudamaseva_donors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sudamaseva_payments
CREATE TABLE `sudamaseva_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subscription_id` int DEFAULT NULL COMMENT 'NULL for orphan/migrated payments',
  `donor_id` int DEFAULT NULL COMMENT 'Denormalized; nullable for orphan payments',
  `amount` int NOT NULL COMMENT 'Amount paid in INR',
  `installment_number` int NOT NULL DEFAULT '0' COMMENT '0 = orphan/unlinked payment',
  `razorpay_payment_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_order_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `razorpay_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_status` enum('created','attempted','paid','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'created',
  `payment_date` datetime DEFAULT NULL,
  `receipt_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `is_migrated` tinyint(1) NOT NULL DEFAULT '0',
  `old_ins_pay_id` int DEFAULT NULL COMMENT 'Reference to old tbl_rec_ins_pay.id',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_razorpay_pay` (`razorpay_payment_id`),
  KEY `idx_subscription` (`subscription_id`),
  KEY `idx_donor` (`donor_id`),
  KEY `idx_installment` (`installment_number`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_pay_donor` FOREIGN KEY (`donor_id`) REFERENCES `sudamaseva_donors` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `sudamaseva_subscriptions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sudamaseva_receipts
CREATE TABLE `sudamaseva_receipts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int NOT NULL,
  `receipt_no` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receipt_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `receipt_data` json DEFAULT NULL COMMENT 'Full receipt details (name, amount, date, etc.)',
  `is_80g_eligible` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt_no` (`receipt_no`),
  KEY `idx_payment` (`payment_id`),
  KEY `idx_receipt_date` (`receipt_date`),
  KEY `idx_80g` (`is_80g_eligible`),
  CONSTRAINT `fk_rec_payment` FOREIGN KEY (`payment_id`) REFERENCES `sudamaseva_payments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: panihati_expenses
CREATE TABLE `panihati_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('expense','income') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'expense',
  `particulars` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Miscellaneous',
  `expense_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------
-- PHASE 3: SEED RBAC SYSTEM ROLES & SYSTEM PERMISSIONS
-- ----------------------------------------------------------------

-- Seeding rbac_roles
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (1, 'super_admin', 'Super Administrator', 'Unrestricted access across all system modules. Bypasses all permission checks.', 1, 1, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (2, 'temple_admin', 'Temple Administrator', 'Full access to all operational modules. Cannot manage admin users, roles, or system settings.', 1, 2, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (3, 'donation_manager', 'Donation Manager', 'Manage donations, causes, and related reporting.', 1, 3, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (4, 'festival_manager', 'Festival Manager', 'Manage festivals, events, and seva catalog.', 1, 4, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (5, 'accounts', 'Accounts / Finance', 'View financial data, reports, exports, and process refunds.', 1, 5, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (6, 'content_manager', 'Content Manager', 'Manage blogs and website content.', 1, 6, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (7, 'report_viewer', 'Report Viewer', 'Read-only access to reports and dashboards.', 1, 7, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (8, 'devotee_care', 'Devotee Care', 'Manage devotee records and relationships.', 1, 8, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (9, 'volunteer_coordinator', 'Volunteer Coordinator', 'Manage volunteers and assignments.', 1, 9, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (10, 'event_coordinator', 'Event Coordinator', 'Manage special events and programs.', 1, 10, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_roles` (`id`, `slug`, `name`, `description`, `is_system`, `sort_order`, `is_active`) VALUES (11, 'read_only', 'Read Only User', 'View-only access across permitted modules. No create/edit/delete rights.', 1, 11, 1) ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);

-- Seeding rbac_permissions
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (1, 'dashboard.view', 'dashboard', 'view', 'View Dashboard', 'Admin dashboard overview', 0, 10) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (2, 'donations.view', 'donations', 'view', 'View Donations', 'Transaction logs, cause management', 0, 110) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (3, 'donations.create', 'donations', 'create', 'Create Donations', 'Transaction logs, cause management', 0, 120) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (4, 'donations.edit', 'donations', 'edit', 'Edit Donations', 'Transaction logs, cause management', 0, 130) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (5, 'donations.delete', 'donations', 'delete', 'Delete Donations', 'Transaction logs, cause management', 0, 140) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (6, 'donations.export', 'donations', 'export', 'Export Donations', 'Transaction logs, cause management', 0, 150) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (7, 'festivals.view', 'festivals', 'view', 'View Festivals', 'Festival/cause listing and management', 0, 210) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (8, 'festivals.create', 'festivals', 'create', 'Create Festivals', 'Festival/cause listing and management', 0, 220) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (9, 'festivals.edit', 'festivals', 'edit', 'Edit Festivals', 'Festival/cause listing and management', 0, 230) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (10, 'festivals.delete', 'festivals', 'delete', 'Delete Festivals', 'Festival/cause listing and management', 0, 240) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (11, 'festivals.export', 'festivals', 'export', 'Export Festivals', 'Festival/cause listing and management', 0, 250) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (12, 'seva_catalog.view', 'seva_catalog', 'view', 'View Seva Catalog', 'Master seva catalog management', 0, 310) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (13, 'seva_catalog.create', 'seva_catalog', 'create', 'Create Seva Catalog', 'Master seva catalog management', 0, 320) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (14, 'seva_catalog.edit', 'seva_catalog', 'edit', 'Edit Seva Catalog', 'Master seva catalog management', 0, 330) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (15, 'seva_catalog.delete', 'seva_catalog', 'delete', 'Delete Seva Catalog', 'Master seva catalog management', 0, 340) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (16, 'seva_catalog.export', 'seva_catalog', 'export', 'Export Seva Catalog', 'Master seva catalog management', 0, 350) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (17, 'blogs.view', 'blogs', 'view', 'View Blogs & Content', 'Blog posts and content management', 0, 410) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (18, 'blogs.create', 'blogs', 'create', 'Create Blogs & Content', 'Blog posts and content management', 0, 420) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (19, 'blogs.edit', 'blogs', 'edit', 'Edit Blogs & Content', 'Blog posts and content management', 0, 430) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (20, 'blogs.delete', 'blogs', 'delete', 'Delete Blogs & Content', 'Blog posts and content management', 0, 440) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (21, 'blogs.export', 'blogs', 'export', 'Export Blogs & Content', 'Blog posts and content management', 0, 450) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (22, 'bookings.view', 'bookings', 'view', 'View Bookings', 'Puja and Yagya bookings', 0, 510) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (23, 'bookings.create', 'bookings', 'create', 'Create Bookings', 'Puja and Yagya bookings', 0, 520) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (24, 'bookings.edit', 'bookings', 'edit', 'Edit Bookings', 'Puja and Yagya bookings', 0, 530) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (25, 'bookings.delete', 'bookings', 'delete', 'Delete Bookings', 'Puja and Yagya bookings', 0, 540) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (26, 'bookings.export', 'bookings', 'export', 'Export Bookings', 'Puja and Yagya bookings', 0, 550) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (27, 'panihati.view', 'panihati', 'view', 'View Panihati Yatra', 'Yatra registration and management', 0, 610) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (28, 'panihati.create', 'panihati', 'create', 'Create Panihati Yatra', 'Yatra registration and management', 0, 620) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (29, 'panihati.edit', 'panihati', 'edit', 'Edit Panihati Yatra', 'Yatra registration and management', 0, 630) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (30, 'panihati.delete', 'panihati', 'delete', 'Delete Panihati Yatra', 'Yatra registration and management', 0, 640) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (31, 'panihati.export', 'panihati', 'export', 'Export Panihati Yatra', 'Yatra registration and management', 0, 650) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (32, 'sudamaseva.view', 'sudamaseva', 'view', 'View Sudamaseva', 'Subscription donation management', 0, 710) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (33, 'sudamaseva.create', 'sudamaseva', 'create', 'Create Sudamaseva', 'Subscription donation management', 0, 720) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (34, 'sudamaseva.edit', 'sudamaseva', 'edit', 'Edit Sudamaseva', 'Subscription donation management', 0, 730) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (35, 'sudamaseva.delete', 'sudamaseva', 'delete', 'Delete Sudamaseva', 'Subscription donation management', 0, 740) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (36, 'sudamaseva.export', 'sudamaseva', 'export', 'Export Sudamaseva', 'Subscription donation management', 0, 750) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (37, 'reports.view', 'reports', 'view', 'View Reports', 'Donation reports and dashboards', 0, 810) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (38, 'reports.export', 'reports', 'export', 'Export Reports', 'Donation reports and dashboards', 0, 850) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (39, 'devotees.view', 'devotees', 'view', 'View Devotees', 'Devotee management', 0, 910) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (40, 'devotees.create', 'devotees', 'create', 'Create Devotees', 'Devotee management', 0, 920) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (41, 'devotees.edit', 'devotees', 'edit', 'Edit Devotees', 'Devotee management', 0, 930) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (42, 'devotees.delete', 'devotees', 'delete', 'Delete Devotees', 'Devotee management', 0, 940) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (43, 'devotees.export', 'devotees', 'export', 'Export Devotees', 'Devotee management', 0, 950) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (44, 'volunteers.view', 'volunteers', 'view', 'View Volunteers', 'Volunteer management', 0, 1010) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (45, 'volunteers.create', 'volunteers', 'create', 'Create Volunteers', 'Volunteer management', 0, 1020) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (46, 'volunteers.edit', 'volunteers', 'edit', 'Edit Volunteers', 'Volunteer management', 0, 1030) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (47, 'volunteers.delete', 'volunteers', 'delete', 'Delete Volunteers', 'Volunteer management', 0, 1040) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (48, 'volunteers.export', 'volunteers', 'export', 'Export Volunteers', 'Volunteer management', 0, 1050) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (49, 'events.view', 'events', 'view', 'View Events', 'Special events and programs', 0, 1110) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (50, 'events.create', 'events', 'create', 'Create Events', 'Special events and programs', 0, 1120) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (51, 'events.edit', 'events', 'edit', 'Edit Events', 'Special events and programs', 0, 1130) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (52, 'events.delete', 'events', 'delete', 'Delete Events', 'Special events and programs', 0, 1140) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (53, 'events.export', 'events', 'export', 'Export Events', 'Special events and programs', 0, 1150) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (54, 'audit_logs.view', 'audit_logs', 'view', 'View Audit Logs', 'System audit log viewing (reserved for future use)', 0, 1210) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);
INSERT INTO `rbac_permissions` (`id`, `slug`, `module`, `action`, `label`, `description`, `is_system`, `sort_order`) VALUES (55, 'audit_logs.export', 'audit_logs', 'export', 'Export Audit Logs', 'System audit log viewing (reserved for future use)', 0, 1250) ON DUPLICATE KEY UPDATE `label`=VALUES(`label`), `description`=VALUES(`description`), `is_system`=VALUES(`is_system`);

-- Seeding default role_permissions
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (1, 2, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (2, 2, 2);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (3, 2, 3);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (4, 2, 4);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (5, 2, 5);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (6, 2, 6);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (7, 2, 7);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (8, 2, 8);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (9, 2, 9);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (10, 2, 10);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (11, 2, 11);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (12, 2, 12);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (13, 2, 13);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (14, 2, 14);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (15, 2, 15);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (16, 2, 16);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (17, 2, 17);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (18, 2, 18);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (19, 2, 19);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (20, 2, 20);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (21, 2, 21);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (22, 2, 22);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (23, 2, 23);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (24, 2, 24);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (25, 2, 25);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (26, 2, 26);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (27, 2, 27);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (28, 2, 28);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (29, 2, 29);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (30, 2, 30);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (31, 2, 31);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (32, 2, 32);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (33, 2, 33);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (34, 2, 34);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (35, 2, 35);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (36, 2, 36);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (37, 2, 37);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (38, 2, 38);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (39, 2, 39);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (40, 2, 40);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (41, 2, 41);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (42, 2, 42);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (43, 2, 43);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (44, 2, 44);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (45, 2, 45);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (46, 2, 46);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (47, 2, 47);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (48, 2, 48);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (49, 2, 49);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (50, 2, 50);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (51, 2, 51);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (52, 2, 52);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (53, 2, 53);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (54, 2, 54);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (55, 2, 55);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (56, 3, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (57, 3, 2);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (58, 3, 3);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (59, 3, 4);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (60, 3, 5);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (61, 3, 6);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (62, 3, 12);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (63, 3, 32);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (64, 3, 33);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (65, 3, 34);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (66, 3, 36);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (67, 3, 37);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (68, 3, 38);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (69, 4, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (70, 4, 7);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (71, 4, 8);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (72, 4, 9);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (73, 4, 10);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (74, 4, 11);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (75, 4, 12);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (76, 4, 13);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (77, 4, 14);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (78, 4, 15);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (79, 4, 16);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (80, 4, 49);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (81, 4, 50);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (82, 4, 51);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (83, 4, 52);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (84, 5, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (85, 5, 2);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (86, 5, 4);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (87, 5, 6);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (88, 5, 22);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (89, 5, 26);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (90, 5, 31);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (91, 5, 32);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (92, 5, 36);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (93, 5, 37);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (94, 5, 38);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (95, 6, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (96, 6, 7);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (97, 6, 8);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (98, 6, 9);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (99, 6, 10);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (100, 6, 17);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (101, 6, 18);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (102, 6, 19);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (103, 6, 20);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (104, 6, 21);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (105, 7, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (106, 7, 2);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (107, 7, 7);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (108, 7, 37);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (109, 7, 38);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (110, 8, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (111, 8, 39);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (112, 8, 40);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (113, 8, 41);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (114, 8, 42);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (115, 8, 43);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (116, 8, 44);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (117, 9, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (118, 9, 39);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (119, 9, 40);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (120, 9, 41);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (121, 9, 44);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (122, 9, 45);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (123, 9, 46);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (124, 9, 47);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (125, 9, 48);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (126, 10, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (127, 10, 7);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (128, 10, 8);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (129, 10, 9);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (130, 10, 27);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (131, 10, 28);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (132, 10, 29);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (133, 10, 49);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (134, 10, 50);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (135, 10, 51);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (136, 10, 52);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (137, 11, 1);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (138, 11, 2);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (139, 11, 7);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (140, 11, 17);
INSERT IGNORE INTO `rbac_role_permissions` (`id`, `role_id`, `permission_id`) VALUES (141, 11, 37);

