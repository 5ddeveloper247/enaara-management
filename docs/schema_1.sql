-- Enaara Management System - Schema 1
-- Core & Master tables only: Organization, Employee Type, Work Type, Attendance Modes,
-- Shift Type, SBUs, SBU Floors, Department, Roles, Leave Type
-- MySQL / MariaDB compatible

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------------
-- 1. Organization
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `organizations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `parent_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `email` VARCHAR(255) NULL DEFAULT NULL,
    `tax_no` VARCHAR(64) NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `address` TEXT NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `organizations_code_unique` (`code`),
    KEY `organizations_parent_id_foreign` (`parent_id`),
    CONSTRAINT `organizations_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 2. Employee Type
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `employee_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `rules_json` JSON NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `employee_types_org_code_unique` (`organization_id`, `code`),
    KEY `employee_types_organization_id_foreign` (`organization_id`),
    CONSTRAINT `employee_types_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 3. Work Type (Work Models)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `work_models` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `default_schedule_json` JSON NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `work_models_org_code_unique` (`organization_id`, `code`),
    KEY `work_models_organization_id_foreign` (`organization_id`),
    CONSTRAINT `work_models_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 4. Attendance Modes (Attendance Models)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `attendance_models` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `grace_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
    `policy_json` JSON NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `attendance_models_organization_id_foreign` (`organization_id`),
    CONSTRAINT `attendance_models_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 5. Shift Type
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `shift_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `break_duration_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_night_shift` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `shift_types_organization_id_foreign` (`organization_id`),
    CONSTRAINT `shift_types_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 6. SBUs (Strategic Business Units)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sbus` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `city` VARCHAR(255) NULL DEFAULT NULL,
    `address` TEXT NULL DEFAULT NULL,
    `latitude` DECIMAL(10, 8) NULL DEFAULT NULL,
    `longitude` DECIMAL(11, 8) NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sbus_organization_id_foreign` (`organization_id`),
    CONSTRAINT `sbus_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 7. SBU Floors
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `sbu_floors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sbu_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `floor_number` INT NULL DEFAULT NULL,
    `floor_type` ENUM('corporate', 'operational', 'mixed') NOT NULL DEFAULT 'operational',
    `is_restricted` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `sbu_floors_sbu_id_foreign` (`sbu_id`),
    CONSTRAINT `sbu_floors_sbu_id_foreign` FOREIGN KEY (`sbu_id`) REFERENCES `sbus` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 8. Department
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `parent_department_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `departments_org_code_unique` (`organization_id`, `code`),
    KEY `departments_organization_id_foreign` (`organization_id`),
    KEY `departments_parent_department_id_foreign` (`parent_department_id`),
    CONSTRAINT `departments_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `departments_parent_department_id_foreign` FOREIGN KEY (`parent_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 9. Roles
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `roles` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- 10. Leave Type
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `leave_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `annual_quota` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `leave_types_org_code_unique` (`organization_id`, `code`),
    KEY `leave_types_organization_id_foreign` (`organization_id`),
    CONSTRAINT `leave_types_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
