-- Enaara Management System - Database Schema
-- HRMS_Doc.docx + Time & System DB Desig.docx + README (no extra tables)
-- Includes: Organization, SBUs, SBU Floors, Master (departments, employee_types, work_models,
--   attendance_models, shift_types, leave_types), Employees, Users, Employee child tables,
--   Floor access, Attendance, Shifts, Leave, Geofencing, Regularization, Policies, Audit, etc.
-- MySQL / MariaDB compatible

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- ---------------------------------------------------------------------------
-- Core: Users & Auth (Laravel default + extensions)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `remember_token` VARCHAR(100) NULL DEFAULT NULL,
    `role` VARCHAR(64) NULL DEFAULT NULL,
    `employee_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    KEY `users_organization_id_foreign` (`organization_id`),
    KEY `users_employee_id_foreign` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `email` VARCHAR(255) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Organization (Multi-tenant)
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

CREATE TABLE IF NOT EXISTS `organization_admins` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `organization_admins_org_user_unique` (`organization_id`, `user_id`),
    KEY `organization_admins_user_id_foreign` (`user_id`),
    CONSTRAINT `organization_admins_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `organization_admins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Departments (HRMS doc: parent_department_id for hierarchy)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `departments` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `parent_department_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `head_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `departments_org_code_unique` (`organization_id`, `code`),
    KEY `departments_organization_id_foreign` (`organization_id`),
    KEY `departments_parent_department_id_foreign` (`parent_department_id`),
    KEY `departments_head_id_foreign` (`head_id`),
    CONSTRAINT `departments_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `departments_parent_department_id_foreign` FOREIGN KEY (`parent_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Master: Employee Types (HRMS doc)
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
-- Master: Work Models / Work Type (HRMS doc - Ordinary, Hybrid, WFH, Floating)
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
-- Master: Attendance Models / Attendance Modes (HRMS doc - Biometric, Mobile, Web, Manual)
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
-- SBUs - Strategic Business Units (Time & System DB doc)
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
-- SBU Floors (Time & System DB doc - floors within each SBU)
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
-- Roles & Permissions
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

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `module` VARCHAR(64) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_id` BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    KEY `role_permissions_permission_id_foreign` (`permission_id`),
    CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
    `user_id` BIGINT UNSIGNED NOT NULL,
    `role_id` BIGINT UNSIGNED NOT NULL,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    KEY `user_roles_role_id_foreign` (`role_id`),
    KEY `user_roles_organization_id_foreign` (`organization_id`),
    CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `user_roles_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Employees
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `employees` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `sbu_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `department_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `employee_type_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `work_model_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `attendance_model_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `shift_type_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `employee_code` VARCHAR(64) NOT NULL,
    `full_name` VARCHAR(255) NOT NULL,
    `first_name` VARCHAR(255) NULL DEFAULT NULL,
    `last_name` VARCHAR(255) NULL DEFAULT NULL,
    `cnic` VARCHAR(20) NULL DEFAULT NULL,
    `email` VARCHAR(255) NULL DEFAULT NULL,
    `phone` VARCHAR(15) NULL DEFAULT NULL,
    `date_of_joining` DATE NULL DEFAULT NULL,
    `designation` VARCHAR(255) NULL DEFAULT NULL,
    `employment_status` VARCHAR(64) NULL DEFAULT NULL,
    `reporting_to_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `employees_org_code_unique` (`organization_id`, `employee_code`),
    UNIQUE KEY `employees_cnic_unique` (`cnic`),
    KEY `employees_user_id_foreign` (`user_id`),
    KEY `employees_organization_id_foreign` (`organization_id`),
    KEY `employees_sbu_id_foreign` (`sbu_id`),
    KEY `employees_department_id_foreign` (`department_id`),
    KEY `employees_employee_type_id_foreign` (`employee_type_id`),
    KEY `employees_work_model_id_foreign` (`work_model_id`),
    KEY `employees_attendance_model_id_foreign` (`attendance_model_id`),
    KEY `employees_shift_type_id_foreign` (`shift_type_id`),
    KEY `employees_reporting_to_id_foreign` (`reporting_to_id`),
    CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `employees_sbu_id_foreign` FOREIGN KEY (`sbu_id`) REFERENCES `sbus` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_employee_type_id_foreign` FOREIGN KEY (`employee_type_id`) REFERENCES `employee_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_work_model_id_foreign` FOREIGN KEY (`work_model_id`) REFERENCES `work_models` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_attendance_model_id_foreign` FOREIGN KEY (`attendance_model_id`) REFERENCES `attendance_models` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_shift_type_id_foreign` FOREIGN KEY (`shift_type_id`) REFERENCES `shift_types` (`id`) ON DELETE SET NULL,
    CONSTRAINT `employees_reporting_to_id_foreign` FOREIGN KEY (`reporting_to_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `departments`
    ADD CONSTRAINT `departments_head_id_foreign` FOREIGN KEY (`head_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

ALTER TABLE `users`
    ADD CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL,
    ADD CONSTRAINT `users_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL;

-- ---------------------------------------------------------------------------
-- Employee Child Tables (HRMS doc)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `employee_bank_accounts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `account_title` VARCHAR(255) NULL DEFAULT NULL,
    `account_no` VARCHAR(64) NULL DEFAULT NULL,
    `bank_name_branch` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_bank_accounts_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_bank_accounts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_family_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `relation` VARCHAR(64) NULL DEFAULT NULL,
    `dob` DATE NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_family_members_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_family_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_educations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `degree` VARCHAR(255) NULL DEFAULT NULL,
    `field_of_study` VARCHAR(255) NULL DEFAULT NULL,
    `institute` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_educations_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_educations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_experiences` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `organization` VARCHAR(255) NULL DEFAULT NULL,
    `designation` VARCHAR(255) NULL DEFAULT NULL,
    `from_date` DATE NULL DEFAULT NULL,
    `to_date` DATE NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_experiences_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_experiences_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_documents` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `doc_type` VARCHAR(64) NULL DEFAULT NULL,
    `file_path` VARCHAR(500) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_documents_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_documents_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_history` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `event_type` VARCHAR(64) NULL DEFAULT NULL,
    `event_date` DATE NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_history_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_onboarding_checklist` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `item_name` VARCHAR(255) NULL DEFAULT NULL,
    `is_done` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_onboarding_checklist_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_onboarding_checklist_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_police_verifications` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `status` VARCHAR(64) NULL DEFAULT NULL,
    `verifying_authority` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_police_verifications_employee_id_foreign` (`employee_id`),
    CONSTRAINT `employee_police_verifications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Floor Access Control (Time & System DB doc - permanent / temporary with expiry)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `floor_access_grants` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `sbu_floor_id` BIGINT UNSIGNED NOT NULL,
    `access_type` ENUM('permanent', 'temporary') NOT NULL DEFAULT 'permanent',
    `valid_from` DATE NOT NULL,
    `valid_to` DATE NULL DEFAULT NULL,
    `reason` TEXT NULL DEFAULT NULL,
    `approved_by_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `floor_access_grants_employee_id_foreign` (`employee_id`),
    KEY `floor_access_grants_sbu_floor_id_foreign` (`sbu_floor_id`),
    KEY `floor_access_grants_approved_by_id_foreign` (`approved_by_id`),
    CONSTRAINT `floor_access_grants_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `floor_access_grants_sbu_floor_id_foreign` FOREIGN KEY (`sbu_floor_id`) REFERENCES `sbu_floors` (`id`) ON DELETE CASCADE,
    CONSTRAINT `floor_access_grants_approved_by_id_foreign` FOREIGN KEY (`approved_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Shift Types & Shifts (HRMS doc: shift_types scoped by organization_id)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `shift_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
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

CREATE TABLE IF NOT EXISTS `shifts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `shift_type_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `effective_from` DATE NOT NULL,
    `effective_to` DATE NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `shifts_organization_id_foreign` (`organization_id`),
    KEY `shifts_shift_type_id_foreign` (`shift_type_id`),
    CONSTRAINT `shifts_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `shifts_shift_type_id_foreign` FOREIGN KEY (`shift_type_id`) REFERENCES `shift_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `employee_shifts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `effective_from` DATE NOT NULL,
    `effective_to` DATE NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `employee_shifts_employee_id_foreign` (`employee_id`),
    KEY `employee_shifts_shift_id_foreign` (`shift_id`),
    CONSTRAINT `employee_shifts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `employee_shifts_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roster_entries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `shift_id` BIGINT UNSIGNED NOT NULL,
    `roster_date` DATE NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roster_entries_employee_date_unique` (`employee_id`, `roster_date`),
    KEY `roster_entries_shift_id_foreign` (`shift_id`),
    CONSTRAINT `roster_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `roster_entries_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Daily Logs / Attendance
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `attendance_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `log_date` DATE NOT NULL,
    `sbu_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `sbu_floor_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `shift_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `attendance_mode` ENUM('biometric', 'mobile_app', 'web', 'manual') NULL DEFAULT NULL,
    `check_in_at` DATETIME NULL DEFAULT NULL,
    `check_out_at` DATETIME NULL DEFAULT NULL,
    `work_duration_minutes` INT UNSIGNED NULL DEFAULT NULL,
    `status` ENUM('present', 'absent', 'half_day', 'leave', 'holiday', 'week_off', 'on_duty') NOT NULL DEFAULT 'absent',
    `check_in_latitude` DECIMAL(10, 8) NULL DEFAULT NULL,
    `check_in_longitude` DECIMAL(11, 8) NULL DEFAULT NULL,
    `check_out_latitude` DECIMAL(10, 8) NULL DEFAULT NULL,
    `check_out_longitude` DECIMAL(11, 8) NULL DEFAULT NULL,
    `is_late` TINYINT(1) NOT NULL DEFAULT 0,
    `is_early_departure` TINYINT(1) NOT NULL DEFAULT 0,
    `remarks` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `attendance_logs_employee_date_unique` (`employee_id`, `log_date`),
    KEY `attendance_logs_log_date_index` (`log_date`),
    KEY `attendance_logs_sbu_id_foreign` (`sbu_id`),
    KEY `attendance_logs_sbu_floor_id_foreign` (`sbu_floor_id`),
    KEY `attendance_logs_shift_id_foreign` (`shift_id`),
    CONSTRAINT `attendance_logs_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `attendance_logs_sbu_id_foreign` FOREIGN KEY (`sbu_id`) REFERENCES `sbus` (`id`) ON DELETE SET NULL,
    CONSTRAINT `attendance_logs_sbu_floor_id_foreign` FOREIGN KEY (`sbu_floor_id`) REFERENCES `sbu_floors` (`id`) ON DELETE SET NULL,
    CONSTRAINT `attendance_logs_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Regularization
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `regularization_categories` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(64) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `regularization_categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `regularization_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `attendance_log_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `category_id` BIGINT UNSIGNED NOT NULL,
    `request_date` DATE NOT NULL,
    `requested_check_in` DATETIME NULL DEFAULT NULL,
    `requested_check_out` DATETIME NULL DEFAULT NULL,
    `reason` TEXT NULL DEFAULT NULL,
    `evidence_path` VARCHAR(500) NULL DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `approved_by_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `approved_at` DATETIME NULL DEFAULT NULL,
    `rejection_reason` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `regularization_requests_employee_id_foreign` (`employee_id`),
    KEY `regularization_requests_attendance_log_id_foreign` (`attendance_log_id`),
    KEY `regularization_requests_category_id_foreign` (`category_id`),
    KEY `regularization_requests_approved_by_id_foreign` (`approved_by_id`),
    CONSTRAINT `regularization_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `regularization_requests_attendance_log_id_foreign` FOREIGN KEY (`attendance_log_id`) REFERENCES `attendance_logs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `regularization_requests_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `regularization_categories` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `regularization_requests_approved_by_id_foreign` FOREIGN KEY (`approved_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Geofencing
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `geofence_zones` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `latitude` DECIMAL(10, 8) NOT NULL,
    `longitude` DECIMAL(11, 8) NOT NULL,
    `radius_meters` INT UNSIGNED NOT NULL,
    `fence_type` ENUM('hard_lock', 'soft_lock') NOT NULL DEFAULT 'hard_lock',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `geofence_zones_organization_id_foreign` (`organization_id`),
    CONSTRAINT `geofence_zones_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `geofence_violations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `geofence_zone_id` BIGINT UNSIGNED NOT NULL,
    `attendance_log_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `violation_type` ENUM('out_of_zone', 'vpn_proxy') NOT NULL,
    `latitude` DECIMAL(10, 8) NULL DEFAULT NULL,
    `longitude` DECIMAL(11, 8) NULL DEFAULT NULL,
    `occurred_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `geofence_violations_employee_id_foreign` (`employee_id`),
    KEY `geofence_violations_geofence_zone_id_foreign` (`geofence_zone_id`),
    KEY `geofence_violations_attendance_log_id_foreign` (`attendance_log_id`),
    CONSTRAINT `geofence_violations_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `geofence_violations_geofence_zone_id_foreign` FOREIGN KEY (`geofence_zone_id`) REFERENCES `geofence_zones` (`id`) ON DELETE CASCADE,
    CONSTRAINT `geofence_violations_attendance_log_id_foreign` FOREIGN KEY (`attendance_log_id`) REFERENCES `attendance_logs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Leave Types & Balances
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `leave_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(64) NOT NULL,
    `code` VARCHAR(64) NULL DEFAULT NULL,
    `annual_quota` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `default_days_per_year` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `is_paid` TINYINT(1) NOT NULL DEFAULT 1,
    `requires_approval` TINYINT(1) NOT NULL DEFAULT 1,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `leave_types_slug_unique` (`slug`),
    UNIQUE KEY `leave_types_org_code_unique` (`organization_id`, `code`),
    KEY `leave_types_organization_id_foreign` (`organization_id`),
    CONSTRAINT `leave_types_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_balances` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `leave_type_id` BIGINT UNSIGNED NOT NULL,
    `year` SMALLINT UNSIGNED NOT NULL,
    `total_days` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `used_days` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `pending_days` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `leave_balances_employee_type_year_unique` (`employee_id`, `leave_type_id`, `year`),
    KEY `leave_balances_leave_type_id_foreign` (`leave_type_id`),
    CONSTRAINT `leave_balances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `leave_balances_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `leave_type_id` BIGINT UNSIGNED NOT NULL,
    `from_date` DATE NOT NULL,
    `to_date` DATE NOT NULL,
    `total_days` DECIMAL(5, 2) NOT NULL,
    `reason` TEXT NULL DEFAULT NULL,
    `medical_certificate_path` VARCHAR(500) NULL DEFAULT NULL,
    `status` ENUM('draft', 'pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    `current_approval_level` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `proxy_approver_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `leave_requests_employee_id_foreign` (`employee_id`),
    KEY `leave_requests_leave_type_id_foreign` (`leave_type_id`),
    KEY `leave_requests_from_date_index` (`from_date`),
    KEY `leave_requests_status_index` (`status`),
    CONSTRAINT `leave_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `leave_requests_leave_type_id_foreign` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_approvals` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `leave_request_id` BIGINT UNSIGNED NOT NULL,
    `approver_id` BIGINT UNSIGNED NOT NULL,
    `approval_level` TINYINT UNSIGNED NOT NULL,
    `action` ENUM('approved', 'rejected', 'forwarded') NOT NULL,
    `comments` TEXT NULL DEFAULT NULL,
    `acted_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `leave_approvals_leave_request_id_foreign` (`leave_request_id`),
    KEY `leave_approvals_approver_id_foreign` (`approver_id`),
    CONSTRAINT `leave_approvals_leave_request_id_foreign` FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests` (`id`) ON DELETE CASCADE,
    CONSTRAINT `leave_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `holidays` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `holiday_date` DATE NOT NULL,
    `year` SMALLINT UNSIGNED NOT NULL,
    `is_optional` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `holidays_organization_id_foreign` (`organization_id`),
    KEY `holidays_holiday_date_index` (`holiday_date`),
    CONSTRAINT `holidays_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `blackout_dates` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `department_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `from_date` DATE NOT NULL,
    `to_date` DATE NOT NULL,
    `reason` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `blackout_dates_organization_id_foreign` (`organization_id`),
    KEY `blackout_dates_department_id_foreign` (`department_id`),
    CONSTRAINT `blackout_dates_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `blackout_dates_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Policies (Bulk policy management, department-wise leave policies - README)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `policies` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `organization_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `department_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(64) NOT NULL,
    `content` LONGTEXT NULL DEFAULT NULL,
    `effective_from` DATE NULL DEFAULT NULL,
    `effective_to` DATE NULL DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `policies_organization_id_foreign` (`organization_id`),
    KEY `policies_department_id_foreign` (`department_id`),
    CONSTRAINT `policies_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
    CONSTRAINT `policies_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Overtime & Monthly Summary (README modules)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `overtime_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `attendance_log_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `request_date` DATE NOT NULL,
    `hours` DECIMAL(4, 2) NOT NULL,
    `reason` TEXT NULL DEFAULT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `approved_by_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `approved_at` DATETIME NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `overtime_requests_employee_id_foreign` (`employee_id`),
    KEY `overtime_requests_attendance_log_id_foreign` (`attendance_log_id`),
    KEY `overtime_requests_approved_by_id_foreign` (`approved_by_id`),
    CONSTRAINT `overtime_requests_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
    CONSTRAINT `overtime_requests_attendance_log_id_foreign` FOREIGN KEY (`attendance_log_id`) REFERENCES `attendance_logs` (`id`) ON DELETE SET NULL,
    CONSTRAINT `overtime_requests_approved_by_id_foreign` FOREIGN KEY (`approved_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `monthly_summaries` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `year` SMALLINT UNSIGNED NOT NULL,
    `month` TINYINT UNSIGNED NOT NULL,
    `total_present` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_absent` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_leave` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `total_overtime_hours` DECIMAL(5, 2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `monthly_summaries_employee_year_month_unique` (`employee_id`, `year`, `month`),
    CONSTRAINT `monthly_summaries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Audit Trails (Regularization: approval workflow with audit trail - README)
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `audit_trails` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `auditable_type` VARCHAR(255) NOT NULL,
    `auditable_id` BIGINT UNSIGNED NOT NULL,
    `action` VARCHAR(64) NOT NULL,
    `old_values` JSON NULL DEFAULT NULL,
    `new_values` JSON NULL DEFAULT NULL,
    `ip_address` VARCHAR(45) NULL DEFAULT NULL,
    `user_agent` TEXT NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `audit_trails_user_id_foreign` (`user_id`),
    KEY `audit_trails_auditable_index` (`auditable_type`, `auditable_id`),
    KEY `audit_trails_created_at_index` (`created_at`),
    CONSTRAINT `audit_trails_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
