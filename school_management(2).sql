-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 23, 2025 at 08:00 PM
-- Server version: 8.4.6-0ubuntu0.25.04.3
-- PHP Version: 8.4.5

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `name`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '2024-2025', '2024-09-01', '2025-06-30', 0, '2025-11-16 10:51:35', '2025-11-16 10:51:35'),
(2, '2025-2026', '2025-09-01', '2026-06-30', 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35'),
(3, '2026-2027', '2026-09-01', '2027-06-30', 0, '2025-11-16 10:51:35', '2025-11-16 10:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `allowances`
--

CREATE TABLE `allowances` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_ahl_exempted` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent','Late','Excused') COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auto_recorded_payments`
--

CREATE TABLE `auto_recorded_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payer_account` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date NOT NULL,
  `narration` text COLLATE utf8mb4_unicode_ci,
  `status` enum('recorded','verified','rejected','unmatched') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'recorded',
  `matched_student_id` bigint UNSIGNED DEFAULT NULL,
  `matched_admission_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_notes` text COLLATE utf8mb4_unicode_ci,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `auto_recorded_payments`
--

INSERT INTO `auto_recorded_payments` (`id`, `reference_number`, `transaction_id`, `amount`, `payment_method`, `account_number`, `payer_name`, `payer_phone`, `payer_account`, `payment_date`, `narration`, `status`, `matched_student_id`, `matched_admission_number`, `verification_notes`, `verified_by`, `verified_at`, `created_at`, `updated_at`) VALUES
(1, 'MPESA2210TEST001', 'TESTTXN2210001', 5000.00, 'mpesa', '2210', 'John Doe - Test Parent', '254712345678', '254712345678', '2025-11-20', 'School fees payment for admission number 2210 - Test payment for auto-allocation', 'recorded', NULL, '2210', NULL, NULL, NULL, '2025-11-20 12:34:11', '2025-11-20 12:34:11'),
(2, 'TKKD3ANJZ8', NULL, 50.00, 'mpesa', '174379', 'Test', '254712345678', NULL, '2025-11-20', 'MPesa Payment - 2210', 'recorded', 1, '2210', NULL, NULL, NULL, '2025-11-20 17:28:31', '2025-11-20 17:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `bank_statement_imports`
--

CREATE TABLE `bank_statement_imports` (
  `id` bigint UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `records_imported` int NOT NULL DEFAULT '0',
  `records_skipped` int NOT NULL DEFAULT '0',
  `import_notes` text COLLATE utf8mb4_unicode_ci,
  `imported_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `blog_category_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_tags`
--

CREATE TABLE `blog_tags` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `physical_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_identification_pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `careers`
--

CREATE TABLE `careers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `employment_type_id` bigint UNSIGNED DEFAULT NULL,
  `language_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline_date` date NOT NULL,
  `job_description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customisations`
--

CREATE TABLE `customisations` (
  `id` bigint UNSIGNED NOT NULL,
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `primary_color_rgb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color_light` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color_light_rgb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color_rgb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color_light` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color_light_rgb` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `button_style` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customisations`
--

INSERT INTO `customisations` (`id`, `primary_color`, `primary_color_rgb`, `primary_color_light`, `primary_color_light_rgb`, `secondary_color`, `secondary_color_rgb`, `secondary_color_light`, `secondary_color_light_rgb`, `button_style`, `created_at`, `updated_at`) VALUES
(1, '#25615a', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-16 10:51:35', '2025-11-16 10:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Science', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Languages', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Library', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `divisions`
--

CREATE TABLE `divisions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `divisions`
--

INSERT INTO `divisions` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'High School', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `emergency_contacts`
--

CREATE TABLE `emergency_contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `relationship_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint UNSIGNED NOT NULL,
  `staff_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_hire` date DEFAULT NULL,
  `use_existing_user` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `employment_type_id` bigint UNSIGNED DEFAULT NULL,
  `employment_status_id` bigint UNSIGNED DEFAULT NULL,
  `honorific_id` bigint UNSIGNED DEFAULT NULL,
  `marital_status_id` bigint UNSIGNED DEFAULT NULL,
  `gender_id` bigint UNSIGNED NOT NULL,
  `religion_id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secondary_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_physical_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_physical_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identification_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_identification_pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_system_access` tinyint(1) NOT NULL DEFAULT '0',
  `in_payroll` tinyint(1) NOT NULL DEFAULT '0',
  `pays_paye` tinyint(1) NOT NULL DEFAULT '0',
  `pays_sha` tinyint(1) NOT NULL DEFAULT '0',
  `sha_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays_nssf` tinyint(1) DEFAULT NULL,
  `nssf_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays_housing_levy` tinyint(1) NOT NULL DEFAULT '0',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `staff_number`, `date_of_hire`, `use_existing_user`, `user_id`, `employment_type_id`, `employment_status_id`, `honorific_id`, `marital_status_id`, `gender_id`, `religion_id`, `first_name`, `middle_name`, `last_name`, `email`, `primary_phone`, `secondary_phone`, `permanent_physical_address`, `secondary_physical_address`, `postal_address`, `identification_number`, `tax_identification_pin`, `has_system_access`, `in_payroll`, `pays_paye`, `pays_sha`, `sha_no`, `pays_nssf`, `nssf_no`, `pays_housing_levy`, `password`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'EMP-0000001', '2025-11-19', 0, NULL, 2, 1, 1, 5, 1, 2, 'ATHUMAN', 'MWACHIZUNGU', 'MASUDI', 'athumanimasudi51@gmail.com', '0768156513', NULL, NULL, NULL, NULL, '39045933', NULL, 1, 0, 0, 0, '', 0, '', 0, '$2y$12$m/25WtrB5CvVPhhBKPIokOyF.DpPy2hK0uQwgmGyzAp8s4BnY/aoy', '2025-11-21 16:25:13', '2025-11-23 11:22:08', NULL),
(2, 'EMP-0000002', '2025-11-21', 0, NULL, 2, 1, 1, 2, 1, 2, 'Ali', NULL, 'Masudi', NULL, '0789654312', NULL, NULL, NULL, NULL, '1234567', NULL, 1, 0, 0, 0, NULL, NULL, NULL, 0, '$2y$12$2EWrv/IYpfyf1a4HajL9PuvvE1zLNDuRWVlsBDv46b4BN/84oaciG', '2025-11-21 17:26:59', '2025-11-21 17:27:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_class`
--

CREATE TABLE `employee_class` (
  `id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `is_class_teacher` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `employee_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_class`
--

INSERT INTO `employee_class` (`id`, `teacher_id`, `class_id`, `subject_id`, `academic_year_id`, `is_class_teacher`, `notes`, `created_at`, `updated_at`, `employee_id`) VALUES
(3, 1, 1, NULL, 2, 1, NULL, '2025-11-22 06:41:59', '2025-11-22 06:41:59', 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee_deductions`
--

CREATE TABLE `employee_deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `deduction_id` bigint UNSIGNED NOT NULL,
  `amount` int NOT NULL,
  `freeze` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_incomes`
--

CREATE TABLE `employee_incomes` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `income_id` bigint UNSIGNED NOT NULL,
  `amount` int NOT NULL,
  `freeze` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employment_statuses`
--

CREATE TABLE `employment_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_statuses`
--

INSERT INTO `employment_statuses` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Active', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'On Leave', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Resigned', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Retired', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'Suspended', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employment_types`
--

CREATE TABLE `employment_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_types`
--

INSERT INTO `employment_types` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Pensionable', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Full-Time', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Part-Time', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Internship', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'Contract', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(6, 'Other', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','active','completed','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_marks`
--

CREATE TABLE `exam_marks` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_submission_id` bigint UNSIGNED DEFAULT NULL,
  `exam_subject_id` bigint UNSIGNED DEFAULT NULL,
  `student_id` bigint UNSIGNED DEFAULT NULL,
  `class_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `exam_id` bigint UNSIGNED DEFAULT NULL,
  `marks_obtained` decimal(10,2) DEFAULT NULL,
  `maximum_marks` decimal(10,2) DEFAULT NULL,
  `grade` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `remarks` longtext COLLATE utf8mb4_unicode_ci,
  `rejection_reason` longtext COLLATE utf8mb4_unicode_ci,
  `submitted_by` bigint UNSIGNED DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_skills`
--

CREATE TABLE `exam_skills` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED NOT NULL,
  `skill_id` bigint UNSIGNED NOT NULL,
  `weightage` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_students`
--

CREATE TABLE `exam_students` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_subjects`
--

CREATE TABLE `exam_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `exam_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `max_marks` int NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_submissions`
--

CREATE TABLE `exam_submissions` (
  `id` bigint UNSIGNED NOT NULL,
  `exam_id` bigint UNSIGNED NOT NULL,
  `class_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED NOT NULL,
  `submitted_by` bigint UNSIGNED NOT NULL,
  `submitted_at` timestamp NOT NULL,
  `students_count` int NOT NULL DEFAULT '0',
  `subjects_count` int NOT NULL DEFAULT '0',
  `total_marks_count` int NOT NULL DEFAULT '0',
  `marks_entered_count` int NOT NULL DEFAULT '0',
  `completion_percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `original_fee_structure_id` bigint UNSIGNED DEFAULT NULL,
  `fee_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tuition',
  `amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(10,2) NOT NULL,
  `academic_year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','partial','paid','overdue','carried_over') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_carry_over` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `student_id`, `rank_id`, `original_fee_structure_id`, `fee_type`, `amount`, `paid_amount`, `balance`, `academic_year`, `term`, `due_date`, `status`, `is_carry_over`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'tuition', 35000.00, 50.00, 34950.00, '2024/2025', '1', '2025-12-22', 'partial', 0, '', '2025-11-20 12:24:53', '2025-11-21 08:11:09'),
(2, 1, 1, 1, 'other', 2000.00, 0.00, 2000.00, '2024/2025', '1', '2025-12-23', 'pending', 0, '', '2025-11-20 12:24:53', '2025-11-21 07:49:11'),
(3, 1, 1, 1, 'transport', 3000.00, 0.00, 3000.00, '2024/2025', '1', '2025-12-25', 'pending', 0, '', '2025-11-20 12:24:53', '2025-11-20 12:24:53'),
(4, 2, 1, 1, 'tuition', 35000.00, 0.00, 35000.00, '2024/2025', '1', '2025-12-22', 'pending', 0, '', '2025-11-20 12:24:53', '2025-11-21 08:11:09'),
(5, 2, 1, 1, 'other', 2000.00, 0.00, 2000.00, '2024/2025', '1', '2025-12-25', 'pending', 0, '', '2025-11-20 12:24:53', '2025-11-20 12:24:53'),
(6, 2, 1, 1, 'transport', 3000.00, 0.00, 3000.00, '2024/2025', '1', '2025-12-25', 'pending', 0, '', '2025-11-20 12:24:53', '2025-11-20 12:24:53'),
(7, 3, 1, 1, 'tuition', 35000.00, 0.00, 35000.00, '2024/2025', '1', '2025-12-22', 'pending', 0, '', '2025-11-20 21:11:36', '2025-11-21 08:11:09'),
(8, 3, 1, 1, 'exam', 2000.00, 0.00, 2000.00, '2024/2025', '1', '2025-12-25', 'pending', 0, '', '2025-11-20 21:11:36', '2025-11-20 21:11:36'),
(9, 3, 1, 1, 'transport', 3000.00, 0.00, 3000.00, '2024/2025', '1', '2025-12-25', 'pending', 0, '', '2025-11-20 21:11:36', '2025-11-20 21:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoices`
--

CREATE TABLE `fee_invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `fee_structure_id` bigint UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(10,2) NOT NULL,
  `status` enum('pending','partial','paid','overdue','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_carry_over` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoices`
--

INSERT INTO `fee_invoices` (`id`, `student_id`, `rank_id`, `fee_structure_id`, `invoice_number`, `academic_year`, `term`, `due_date`, `total_amount`, `paid_amount`, `balance`, `status`, `is_carry_over`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 1, NULL, 'INV-202511-0001', '2024/2025', '1', '2025-12-25', 35000.00, 0.00, 35000.00, 'pending', 0, 'Term 1 Fees', '2025-11-20 21:11:36', '2025-11-20 21:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `fee_invoice_items`
--

CREATE TABLE `fee_invoice_items` (
  `id` bigint UNSIGNED NOT NULL,
  `fee_invoice_id` bigint UNSIGNED NOT NULL,
  `item_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_invoice_items`
--

INSERT INTO `fee_invoice_items` (`id`, `fee_invoice_id`, `item_name`, `amount`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tuition Fee', 30000.00, '', '2025-11-20 21:11:36', '2025-11-20 21:11:36'),
(2, 1, 'examination fees', 2000.00, '', '2025-11-20 21:11:36', '2025-11-20 21:11:36'),
(3, 1, 'Transport Fee', 3000.00, '', '2025-11-20 21:11:36', '2025-11-20 21:11:36');

-- --------------------------------------------------------

--
-- Table structure for table `fee_payments`
--

CREATE TABLE `fee_payments` (
  `id` bigint UNSIGNED NOT NULL,
  `fee_id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` date NOT NULL,
  `status` enum('pending','completed','failed','reversed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `auto_recorded_payment_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_payments`
--

INSERT INTO `fee_payments` (`id`, `fee_id`, `student_id`, `amount`, `payment_method`, `reference_number`, `transaction_id`, `payment_date`, `status`, `notes`, `verified_by`, `verified_at`, `auto_recorded_payment_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 50.00, 'mpesa', 'TKKD3ANJZ8', NULL, '2025-11-20', 'completed', 'Auto-allocated from M-Pesa payment', NULL, NULL, 2, '2025-11-20 17:28:31', '2025-11-20 17:28:31');

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` bigint UNSIGNED NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `academic_year` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `term` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `additional_fees` json DEFAULT NULL,
  `due_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `rank_id`, `academic_year`, `term`, `amount`, `description`, `additional_fees`, `due_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, '2024/2025', '1', 35000.00, '', '[{\"name\": \"examination fees\", \"amount\": 2000, \"description\": \"\"}, {\"name\": \"Transport Fee\", \"amount\": 3000, \"description\": \"\"}]', '2025-12-22', 1, '2025-11-20 11:23:22', '2025-11-21 08:10:49'),
(2, 2, '2024/2025', '1', 20000.00, '', '[{\"name\": \"activity fee\", \"amount\": 4000, \"description\": \"\"}]', '2025-12-25', 1, '2025-11-20 11:26:09', '2025-11-21 13:15:35');

-- --------------------------------------------------------

--
-- Table structure for table `fee_transfers`
--

CREATE TABLE `fee_transfers` (
  `id` bigint UNSIGNED NOT NULL,
  `from_student_id` bigint UNSIGNED NOT NULL,
  `to_student_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initiated_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genders`
--

CREATE TABLE `genders` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genders`
--

INSERT INTO `genders` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Male', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Female', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grading_scheme`
--

CREATE TABLE `grading_scheme` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default Scheme',
  `min_score` int NOT NULL,
  `max_score` int NOT NULL,
  `grade` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `relationship_id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identification_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profession` text COLLATE utf8mb4_unicode_ci,
  `has_system_access` tinyint(1) NOT NULL DEFAULT '0',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `honorifics`
--

CREATE TABLE `honorifics` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `honorifics`
--

INSERT INTO `honorifics` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Mr.', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Mrs.', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Md.', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Prof.', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'Lec.', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `incomes`
--

CREATE TABLE `incomes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `institutions`
--

CREATE TABLE `institutions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `physical_address` text COLLATE utf8mb4_unicode_ci,
  `postal_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_identification_pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mission` longtext COLLATE utf8mb4_unicode_ci,
  `vision` longtext COLLATE utf8mb4_unicode_ci,
  `motto` longtext COLLATE utf8mb4_unicode_ci,
  `x_profile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fb_profile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ig_profile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_profile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube_profile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_size` int NOT NULL DEFAULT '200',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `institutions`
--

INSERT INTO `institutions` (`id`, `name`, `email`, `phone`, `country`, `state`, `city`, `physical_address`, `postal_address`, `tax_identification_pin`, `mission`, `vision`, `motto`, `x_profile`, `fb_profile`, `ig_profile`, `tiktok_profile`, `youtube_profile`, `logo_size`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Albasam Comprehensive School', 'info@albasamcomprehensive.sc.ke', '+254 776 160 927', 'KENYA', 'MOMBASA', 'MOMBASA', '', '86716-80100', '', '', '', NULL, '', '', '', '', '', 200, '2025-11-16 10:51:35', '2025-11-21 07:17:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_titles`
--

CREATE TABLE `job_titles` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary_grade_id` bigint UNSIGNED NOT NULL,
  `salary_scale_id` bigint UNSIGNED DEFAULT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_titles`
--

INSERT INTO `job_titles` (`id`, `title`, `salary_grade_id`, `salary_scale_id`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Chief Principal', 1, 1, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(2, 'Senior Principal', 2, 2, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(3, 'Principal', 3, 3, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(4, 'Deputy Principal I', 3, 3, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(5, 'Deputy Principal II', 4, 4, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(6, 'Senior Master I', 4, 4, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(7, 'Senior Lecturer I', 4, 4, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(8, 'Senior Master II', 5, 5, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(9, 'Deputy Principal III', 5, 5, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(10, 'Senior Head Teacher', 5, 5, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(11, 'Senior Lecturer II', 5, 5, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(12, 'Senior Master III', 6, 6, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(13, 'Senior Lecturer III', 6, 6, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(14, 'Head Teacher', 6, 6, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(15, 'Deputy Head Teacher I', 6, 6, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(16, 'Senior Lecturer IV', 7, 7, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(17, 'Senior Master IV', 7, 7, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(18, 'Deputy Head Teacher II', 7, 7, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(19, 'Secondary Teacher I', 8, 8, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(20, 'Lecturer I', 8, 8, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(21, 'Senior Teacher I', 8, 8, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(22, 'Secondary Teacher II', 9, 9, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(23, 'Lecturer II', 9, 9, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(24, 'Senior Teacher II', 9, 9, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(25, 'Secondary Teacher III', 10, 10, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(26, 'Lecturer III', 10, 10, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(27, 'Primary Teacher I', 10, 10, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL),
(28, 'Primary Teacher II', 11, 11, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint UNSIGNED NOT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `weekday` int DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marital_statuses`
--

CREATE TABLE `marital_statuses` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `marital_statuses`
--

INSERT INTO `marital_statuses` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Married', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Single', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Single-Parent', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Divorced', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'Other', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint UNSIGNED NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint UNSIGNED NOT NULL,
  `page_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `has_children` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `child_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `component` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `page_id`, `title`, `type`, `url`, `has_children`, `parent_id`, `child_type`, `component`, `order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Home', 'page', NULL, 0, NULL, NULL, NULL, 1, '2025-11-16 10:51:35', '2025-11-16 10:51:35'),
(2, 2, 'About Us', 'page', NULL, 0, NULL, NULL, NULL, 2, '2025-11-16 10:51:35', '2025-11-16 10:51:35'),
(3, 3, 'Contact Us', 'page', NULL, 0, NULL, NULL, NULL, 3, '2025-11-16 10:51:35', '2025-11-16 10:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_01_06_105133_create_sessions_table', 1),
(4, '2025_01_06_105219_create_institutions_table', 1),
(5, '2025_01_06_105228_create_branches_table', 1),
(6, '2025_01_06_105234_create_divisions_table', 1),
(7, '2025_01_06_105531_create_users_table', 1),
(8, '2025_01_06_105536_create_teachers_table', 1),
(9, '2025_01_06_105925_create_ranks_table', 1),
(10, '2025_01_06_110036_create_students_table', 1),
(11, '2025_01_06_120000_create_academic_years_table', 1),
(12, '2025_01_07_000000_create_employee_class_table', 1),
(13, '2025_01_08_083524_create_website_tables', 1),
(14, '2025_01_12_084627_create_attendance_records_table', 1),
(15, '2025_01_13_034426_create_guardians_table', 1),
(16, '2025_01_22_144320_laratrust_setup_tables', 1),
(17, '2025_02_04_124613_create_media_table', 1),
(18, '2025_06_17_022453_create_blog_categories_table', 1),
(19, '2025_06_17_022454_create_blogs_table', 1),
(20, '2025_06_21_131706_create_languages_table', 1),
(21, '2025_06_21_131922_create_careers_table', 1),
(22, '2025_06_26_144607_create_seo_metas_table', 1),
(23, '2025_07_21_193141_create_allowance_table', 1),
(24, '2025_07_21_193511_create_payroll_allowance_table', 1),
(25, '2025_07_21_202246_create_personal_access_tokens_table', 1),
(26, '2025_07_22_154447_create_deduction_table', 1),
(27, '2025_07_22_154604_create_payroll_deductions_table', 1),
(28, '2025_07_26_103035_create_incomes_table', 1),
(29, '2025_07_26_124410_create_employee_incomes_table', 1),
(30, '2025_07_26_175212_create_employee_deductions_table', 1),
(31, '2025_08_02_123054_create_payrolls_table', 1),
(32, '2025_08_02_124549_create_payroll_details_table', 1),
(33, '2025_09_26_114402_create_exams_table', 1),
(34, '2025_09_26_114447_create_exam_subjects_table', 1),
(35, '2025_09_26_114533_create_grading_scheme_table', 1),
(36, '2025_09_26_115448_create_exam_students_table', 1),
(37, '2025_09_26_115700_create_exam_submissions_table', 1),
(38, '2025_09_26_115720_create_exam_marks_table', 1),
(39, '2025_11_04_115653_update_exam_marks_table_for_approval_system', 1),
(40, '2025_11_04_140547_add_submission_fields_to_exam_marks_table', 1),
(41, '2025_11_04_153734_add_counts_to_exam_submissions_table', 1),
(42, '2025_11_04_161453_add_academic_year_to_exams_table', 1),
(43, '2025_11_05_221028_add_missing_columns_to_exam_submissions_table', 1),
(44, '2025_11_06_132851_add_user_id_to_students_table', 1),
(45, '2025_11_10_154831_create_skills_table', 1),
(46, '2025_11_10_154922_create_exam_skills_table', 1),
(47, '2025_11_10_162317_add_soft_deletes_to_skills_table', 1),
(48, '2025_11_12_150410_add_employee_id_to_employee_class_table', 1),
(49, '2025_11_16_130038_create_fee_management_tables', 1),
(50, '2025_01_16_000001_create_auto_recorded_payments_table', 2),
(51, 'xxxx_xx_xx_xxxxxx_add_fee_type_to_fees_table', 3),
(52, '2025_11_18_200630_add_additional_fees_to_fee_structures_table', 4),
(53, '2025_11_18_202323_change_additional_fees_column_type_in_fee_structures_table', 5),
(54, '2025_11_18_204303_create_fee_invoices_table', 6),
(55, '2025_11_18_204350_create_fee_invoice_items_table', 6),
(56, '2025_11_18_214424_add_fee_structure_id_to_fee_invoices_table', 7),
(57, '2025_11_20_111243_add_auto_recorded_payment_id_to_fee_payments_table', 8),
(58, '2025_11_23_102223_create_student_promotions_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `is_home` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `title`, `slug`, `description`, `published`, `is_home`, `created_at`, `updated_at`) VALUES
(1, 'Home', '/', NULL, 1, 0, '2025-11-16 10:51:35', '2025-11-16 10:51:35'),
(2, 'About Us', 'about-us', NULL, 1, 0, '2025-11-16 10:51:35', '2025-11-16 10:51:35'),
(3, 'Contact Us', 'contact-us', NULL, 1, 0, '2025-11-16 10:51:35', '2025-11-16 10:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint UNSIGNED NOT NULL,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `basic_salary` int DEFAULT NULL,
  `total_allowances` int DEFAULT NULL,
  `gross_salary` int DEFAULT NULL,
  `tax_relief` int DEFAULT NULL,
  `paye` int DEFAULT NULL,
  `total_deductions` int DEFAULT NULL,
  `net_salary` int DEFAULT NULL,
  `pay_date` date DEFAULT NULL,
  `year` int DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `closed_at` timestamp NULL DEFAULT NULL,
  `employee_type_id` bigint DEFAULT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_allowances`
--

CREATE TABLE `payroll_allowances` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `allowance_id` bigint UNSIGNED NOT NULL,
  `amount` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_included` tinyint(1) NOT NULL DEFAULT '0',
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_deductions`
--

CREATE TABLE `payroll_deductions` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `deduction_id` bigint UNSIGNED NOT NULL,
  `amount` int NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deducted` tinyint(1) NOT NULL DEFAULT '0',
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_details`
--

CREATE TABLE `payroll_details` (
  `id` bigint UNSIGNED NOT NULL,
  `payroll_id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint DEFAULT NULL,
  `amount` int NOT NULL,
  `balance` int DEFAULT NULL,
  `ahl_exempted` tinyint(1) NOT NULL DEFAULT '0',
  `month` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employer` int NOT NULL DEFAULT '0',
  `source` smallint NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_insurance` tinyint(1) NOT NULL DEFAULT '0',
  `accumulated_amount` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `display_name`, `code`, `description`, `created_at`, `updated_at`) VALUES
(1, 'access-admissions-workspace', 'Access Admissions-workspace', 'Admission-Management', 'Access Admissions-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(2, 'add-admissions', 'Add Admissions', 'Admission-Management', 'Add Admissions', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(3, 'edit-admissions', 'Edit Admissions', 'Admission-Management', 'Edit Admissions', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(4, 'delete-admissions', 'Delete Admissions', 'Admission-Management', 'Delete Admissions', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(5, 'access-students-workspace', 'Access Students-workspace', 'Admission-Management', 'Access Students-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(6, 'view-students', 'View Students', 'Admission-Management', 'View Students', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(7, 'add-students', 'Add Students', 'Admission-Management', 'Add Students', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(8, 'edit-students', 'Edit Students', 'Admission-Management', 'Edit Students', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(9, 'delete-students', 'Delete Students', 'Admission-Management', 'Delete Students', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(10, 'access-class-workspace', 'Access Class-workspace', 'Class-Management', 'Access Class-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(11, 'view-classes', 'View Classes', 'Class-Management', 'View Classes', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(12, 'add-classes', 'Add Classes', 'Class-Management', 'Add Classes', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(13, 'edit-classes', 'Edit Classes', 'Class-Management', 'Edit Classes', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(14, 'delete-classes', 'Delete Classes', 'Class-Management', 'Delete Classes', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(15, 'access-calendar', 'Access Calendar', 'Class-Management', 'Access Calendar', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(16, 'access-time-table', 'Access Time-table', 'Class-Management', 'Access Time-table', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(17, 'add-lessons', 'Add Lessons', 'Class-Management', 'Add Lessons', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(18, 'edit-lessons', 'Edit Lessons', 'Class-Management', 'Edit Lessons', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(19, 'delete-lessons', 'Delete Lessons', 'Class-Management', 'Delete Lessons', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(20, 'access-attendance-workspace', 'Access Attendance-workspace', 'Class-Management', 'Access Attendance-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(21, 'access-attendance-report', 'Access Attendance-report', 'Class-Management', 'Access Attendance-report', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(22, 'access-employee-workspace', 'Access Employee-workspace', 'Employee-Management', 'Access Employee-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(23, 'view-employees', 'View Employees', 'Employee-Management', 'View Employees', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(24, 'add-employees', 'Add Employees', 'Employee-Management', 'Add Employees', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(25, 'edit-employees', 'Edit Employees', 'Employee-Management', 'Edit Employees', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(26, 'delete-employees', 'Delete Employees', 'Employee-Management', 'Delete Employees', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(27, 'access-teacher-workspace', 'Access Teacher-workspace', 'Employee-Management', 'Access Teacher-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(28, 'view-teachers', 'View Teachers', 'Employee-Management', 'View Teachers', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(29, 'add-teachers', 'Add Teachers', 'Employee-Management', 'Add Teachers', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(30, 'edit-teachers', 'Edit Teachers', 'Employee-Management', 'Edit Teachers', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(31, 'delete-teachers', 'Delete Teachers', 'Employee-Management', 'Delete Teachers', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(32, 'access-bulk-sms', 'Access Bulk-sms', 'Sms-Management', 'Access Bulk-sms', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(33, 'access-sms-outbox', 'Access Sms-outbox', 'Sms-Management', 'Access Sms-outbox', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(34, 'access-institution-workspace', 'Access Institution-workspace', 'Settings', 'Access Institution-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(35, 'add-institution', 'Add Institution', 'Settings', 'Add Institution', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(36, 'edit-institution', 'Edit Institution', 'Settings', 'Edit Institution', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(37, 'access-divisions-workspace', 'Access Divisions-workspace', 'Settings', 'Access Divisions-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(38, 'add-divisions', 'Add Divisions', 'Settings', 'Add Divisions', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(39, 'edit-divisions', 'Edit Divisions', 'Settings', 'Edit Divisions', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(40, 'delete-divisions', 'Delete Divisions', 'Settings', 'Delete Divisions', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(41, 'access-streams-workspace', 'Access Streams-workspace', 'Settings', 'Access Streams-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(42, 'add-streams', 'Add Streams', 'Settings', 'Add Streams', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(43, 'edit-streams', 'Edit Streams', 'Settings', 'Edit Streams', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(44, 'delete-streams', 'Delete Streams', 'Settings', 'Delete Streams', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(45, 'access-subjects-workspace', 'Access Subjects-workspace', 'Settings', 'Access Subjects-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(46, 'add-subjects', 'Add Subjects', 'Settings', 'Add Subjects', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(47, 'edit-subjects', 'Edit Subjects', 'Settings', 'Edit Subjects', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(48, 'delete-subjects', 'Delete Subjects', 'Settings', 'Delete Subjects', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(49, 'access-users-workspace', 'Access Users-workspace', 'User-Management', 'Access Users-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(50, 'add-users', 'Add Users', 'User-Management', 'Add Users', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(51, 'edit-users', 'Edit Users', 'User-Management', 'Edit Users', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(52, 'view-users', 'View Users', 'User-Management', 'View Users', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(53, 'delete-users', 'Delete Users', 'User-Management', 'Delete Users', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(54, 'access-roles-workspace', 'Access Roles-workspace', 'User-Management', 'Access Roles-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(55, 'add-roles', 'Add Roles', 'User-Management', 'Add Roles', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(56, 'edit-roles', 'Edit Roles', 'User-Management', 'Edit Roles', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(57, 'delete-roles', 'Delete Roles', 'User-Management', 'Delete Roles', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(58, 'access-pages-workspace', 'Access Pages-workspace', 'Website-Management', 'Access Pages-workspace', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(59, 'add-pages', 'Add Pages', 'Website-Management', 'Add Pages', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(60, 'edit-pages', 'Edit Pages', 'Website-Management', 'Edit Pages', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(61, 'delete-pages', 'Delete Pages', 'Website-Management', 'Delete Pages', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(62, 'add-page-sections', 'Add Page-sections', 'Website-Management', 'Add Page-sections', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(63, 'edit-page-sections', 'Edit Page-sections', 'Website-Management', 'Edit Page-sections', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(64, 'delete-page-sections', 'Delete Page-sections', 'Website-Management', 'Delete Page-sections', '2025-11-16 10:51:36', '2025-11-16 10:51:36'),
(65, 'access-fees-workspace', 'Access Fees Workspace', 'ACCESS_FEES_WORKSPACE', 'Allows access to the fees management workspace', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(66, 'view-fee-structures', 'View Fee Structures', 'VIEW_FEE_STRUCTURES', 'Allows viewing fee structures', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(67, 'add-fee-structures', 'Add Fee Structures', 'ADD_FEE_STRUCTURES', 'Allows creating new fee structures', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(68, 'edit-fee-structures', 'Edit Fee Structures', 'EDIT_FEE_STRUCTURES', 'Allows editing existing fee structures', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(69, 'delete-fee-structures', 'Delete Fee Structures', 'DELETE_FEE_STRUCTURES', 'Allows deleting fee structures', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(70, 'generate-fees', 'Generate Fees', 'GENERATE_FEES', 'Allows generating student fees from fee structures', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(71, 'view-fee-payments', 'View Fee Payments', 'VIEW_FEE_PAYMENTS', 'Allows viewing fee payments', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(72, 'process-fee-payments', 'Process Fee Payments', 'PROCESS_FEE_PAYMENTS', 'Allows processing fee payments', '2025-11-19 20:09:12', '2025-11-19 20:09:12'),
(73, 'view-fee-reports', 'View Fee Reports', 'VIEW_FEE_REPORTS', 'Allows viewing fee reports', '2025-11-19 20:09:12', '2025-11-19 20:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_user`
--

CREATE TABLE `permission_user` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `team_id` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_user`
--

INSERT INTO `permission_user` (`permission_id`, `user_id`, `user_type`, `team_id`) VALUES
(1, 1, 'App\\Models\\User', NULL),
(2, 1, 'App\\Models\\User', NULL),
(3, 1, 'App\\Models\\User', NULL),
(4, 1, 'App\\Models\\User', NULL),
(5, 1, 'App\\Models\\User', NULL),
(6, 1, 'App\\Models\\User', NULL),
(7, 1, 'App\\Models\\User', NULL),
(8, 1, 'App\\Models\\User', NULL),
(9, 1, 'App\\Models\\User', NULL),
(10, 1, 'App\\Models\\User', NULL),
(11, 1, 'App\\Models\\User', NULL),
(12, 1, 'App\\Models\\User', NULL),
(13, 1, 'App\\Models\\User', NULL),
(14, 1, 'App\\Models\\User', NULL),
(15, 1, 'App\\Models\\User', NULL),
(16, 1, 'App\\Models\\User', NULL),
(17, 1, 'App\\Models\\User', NULL),
(18, 1, 'App\\Models\\User', NULL),
(19, 1, 'App\\Models\\User', NULL),
(20, 1, 'App\\Models\\User', NULL),
(21, 1, 'App\\Models\\User', NULL),
(22, 1, 'App\\Models\\User', NULL),
(23, 1, 'App\\Models\\User', NULL),
(24, 1, 'App\\Models\\User', NULL),
(25, 1, 'App\\Models\\User', NULL),
(26, 1, 'App\\Models\\User', NULL),
(27, 1, 'App\\Models\\User', NULL),
(28, 1, 'App\\Models\\User', NULL),
(29, 1, 'App\\Models\\User', NULL),
(30, 1, 'App\\Models\\User', NULL),
(31, 1, 'App\\Models\\User', NULL),
(32, 1, 'App\\Models\\User', NULL),
(33, 1, 'App\\Models\\User', NULL),
(34, 1, 'App\\Models\\User', NULL),
(35, 1, 'App\\Models\\User', NULL),
(36, 1, 'App\\Models\\User', NULL),
(37, 1, 'App\\Models\\User', NULL),
(38, 1, 'App\\Models\\User', NULL),
(39, 1, 'App\\Models\\User', NULL),
(40, 1, 'App\\Models\\User', NULL),
(41, 1, 'App\\Models\\User', NULL),
(42, 1, 'App\\Models\\User', NULL),
(43, 1, 'App\\Models\\User', NULL),
(44, 1, 'App\\Models\\User', NULL),
(45, 1, 'App\\Models\\User', NULL),
(46, 1, 'App\\Models\\User', NULL),
(47, 1, 'App\\Models\\User', NULL),
(48, 1, 'App\\Models\\User', NULL),
(49, 1, 'App\\Models\\User', NULL),
(50, 1, 'App\\Models\\User', NULL),
(51, 1, 'App\\Models\\User', NULL),
(52, 1, 'App\\Models\\User', NULL),
(53, 1, 'App\\Models\\User', NULL),
(54, 1, 'App\\Models\\User', NULL),
(55, 1, 'App\\Models\\User', NULL),
(56, 1, 'App\\Models\\User', NULL),
(57, 1, 'App\\Models\\User', NULL),
(58, 1, 'App\\Models\\User', NULL),
(59, 1, 'App\\Models\\User', NULL),
(60, 1, 'App\\Models\\User', NULL),
(61, 1, 'App\\Models\\User', NULL),
(62, 1, 'App\\Models\\User', NULL),
(63, 1, 'App\\Models\\User', NULL),
(64, 1, 'App\\Models\\User', NULL),
(65, 1, 'App\\Models\\User', NULL),
(66, 1, 'App\\Models\\User', NULL),
(67, 1, 'App\\Models\\User', NULL),
(68, 1, 'App\\Models\\User', NULL),
(69, 1, 'App\\Models\\User', NULL),
(70, 1, 'App\\Models\\User', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qualifications`
--

CREATE TABLE `qualifications` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `qualification_type_id` bigint UNSIGNED DEFAULT NULL,
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `course_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year_of_completion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qualification_types`
--

CREATE TABLE `qualification_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qualification_types`
--

INSERT INTO `qualification_types` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Degree', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Diploma', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Certificate', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ranks`
--

CREATE TABLE `ranks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `division_id` bigint UNSIGNED DEFAULT NULL,
  `stream_id` bigint UNSIGNED DEFAULT NULL,
  `teacher_id` bigint UNSIGNED DEFAULT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ranks`
--

INSERT INTO `ranks` (`id`, `name`, `division_id`, `stream_id`, `teacher_id`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Form 1', 1, 1, 1, 1, '2025-11-16 10:51:34', '2025-11-23 17:04:17', NULL),
(2, 'Form 1', 1, 2, NULL, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Form 1', 1, 3, NULL, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `relationships`
--

CREATE TABLE `relationships` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `relationships`
--

INSERT INTO `relationships` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Father', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Mother', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Husband', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Wife', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'Brother', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(6, 'Sister', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(7, 'Uncle', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(8, 'Aunt', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(9, 'Grandparent', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(10, 'Other', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `religions`
--

CREATE TABLE `religions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `religions`
--

INSERT INTO `religions` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Christian', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Islam', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Hindu', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Other', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `role_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `team_id` int UNSIGNED DEFAULT NULL,
  `user_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_grades`
--

CREATE TABLE `salary_grades` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_grades`
--

INSERT INTO `salary_grades` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'D5', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'D4', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'D3', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'D2', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'D1', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(6, 'C5', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(7, 'C4', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(8, 'C3', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(9, 'C2', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(10, 'C1', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(11, 'B5', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `salary_scales`
--

CREATE TABLE `salary_scales` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salary_grade_id` bigint UNSIGNED NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_scales`
--

INSERT INTO `salary_scales` (`id`, `name`, `salary_grade_id`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'T-Scale 15', 1, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'T-Scale 14', 2, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'T-Scale 13', 3, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'T-Scale 12', 4, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(5, 'T-Scale 11', 5, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(6, 'T-Scale 10', 6, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(7, 'T-Scale 9', 7, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(8, 'T-Scale 8', 8, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(9, 'T-Scale 7', 9, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(10, 'T-Scale 6', 10, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(11, 'T-Scale 5', 11, 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint UNSIGNED NOT NULL,
  `page_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `component_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` tinyint NOT NULL DEFAULT '0',
  `section_has_image` tinyint(1) NOT NULL DEFAULT '1',
  `include_contact_cards` tinyint(1) NOT NULL DEFAULT '0',
  `section_image_first` tinyint(1) NOT NULL DEFAULT '1',
  `has_cta_buttons` tinyint(1) NOT NULL DEFAULT '1',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `map_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `section_cta_buttons`
--

CREATE TABLE `section_cta_buttons` (
  `id` bigint UNSIGNED NOT NULL,
  `section_id` bigint UNSIGNED NOT NULL,
  `page_id` bigint UNSIGNED NOT NULL,
  `cta_button_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cta_button_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'primary-btn',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seo_metas`
--

CREATE TABLE `seo_metas` (
  `id` bigint UNSIGNED NOT NULL,
  `seo_able_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seo_able_id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `keywords` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  `logged_in_as` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siblings`
--

CREATE TABLE `siblings` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` tinyint DEFAULT NULL,
  `gender_id` bigint UNSIGNED DEFAULT NULL,
  `current_school` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `subject_id` bigint UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `specialization_areas`
--

CREATE TABLE `specialization_areas` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specialization_areas`
--

INSERT INTO `specialization_areas` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Sciences', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Languages', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Humanities', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(4, 'Religious Education', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `streams`
--

CREATE TABLE `streams` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `streams`
--

INSERT INTO `streams` (`id`, `name`, `activated`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Aberdare', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(2, 'Satima', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL),
(3, 'Kinangop', 1, '2025-11-16 10:51:34', '2025-11-16 10:51:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint UNSIGNED NOT NULL,
  `student_admission_id` bigint UNSIGNED NOT NULL,
  `gender_id` bigint UNSIGNED DEFAULT NULL,
  `religion_id` bigint UNSIGNED DEFAULT NULL,
  `rank_id` bigint UNSIGNED NOT NULL,
  `admission_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_certificate_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `citizenship` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `county` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_school` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kcpe_score` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `physical_disability` text COLLATE utf8mb4_unicode_ci,
  `hobby` text COLLATE utf8mb4_unicode_ci,
  `medical_details` longtext COLLATE utf8mb4_unicode_ci,
  `character_book` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_admission_id`, `gender_id`, `religion_id`, `rank_id`, `admission_number`, `first_name`, `middle_name`, `last_name`, `date_of_birth`, `birth_certificate_number`, `citizenship`, `county`, `ward`, `permanent_address`, `previous_school`, `kcpe_score`, `physical_disability`, `hobby`, `medical_details`, `character_book`, `created_at`, `updated_at`, `deleted_at`, `user_id`) VALUES
(1, 1, 1, 2, 1, '2210', 'Athuman', 'ALI', 'masudi', '2025-11-22 21:00:00', NULL, '', '', '', '', '', '', '', '', '', '', '2025-11-18 07:01:51', '2025-11-23 17:32:01', NULL, NULL),
(2, 2, 1, 1, 1, '2211', 'JOHN', 'DOE', 'JOHN', '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2025-11-19 10:53:44', '2025-11-19 10:53:44', NULL, NULL),
(3, 3, 1, 2, 1, '2212', 'HAMISI', 'BAKARI', 'DZIMWENGA', '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, NULL, '2025-11-20 21:11:36', '2025-11-20 21:11:36', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_admissions`
--

CREATE TABLE `student_admissions` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `date_of_exit` date DEFAULT NULL,
  `division_id` bigint UNSIGNED DEFAULT NULL,
  `has_exit_school` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_admissions`
--

INSERT INTO `student_admissions` (`id`, `date`, `date_of_exit`, `division_id`, `has_exit_school`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '2025-11-18', NULL, 1, 0, '2025-11-18 07:01:51', '2025-11-18 07:01:51', NULL),
(2, '2025-11-19', NULL, 1, 0, '2025-11-19 10:53:44', '2025-11-19 10:53:44', NULL),
(3, '2025-11-20', NULL, 1, 0, '2025-11-20 21:11:36', '2025-11-20 21:11:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_promotions`
--

CREATE TABLE `student_promotions` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `from_class_id` bigint UNSIGNED NOT NULL,
  `to_class_id` bigint UNSIGNED NOT NULL,
  `academic_year_id` bigint UNSIGNED NOT NULL,
  `promoted_by` bigint UNSIGNED NOT NULL,
  `special_promotion` tinyint(1) NOT NULL DEFAULT '0',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `has_completed_all_terms` tinyint(1) NOT NULL DEFAULT '0',
  `completed_terms` json DEFAULT NULL,
  `promoted_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `student_id` bigint UNSIGNED NOT NULL,
  `subject_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `group` tinyint NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `honorific_id` bigint UNSIGNED DEFAULT NULL,
  `job_title_id` bigint UNSIGNED DEFAULT NULL,
  `specialization_area_id` bigint UNSIGNED DEFAULT NULL,
  `tsc_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `years_of_experience` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `employee_id`, `first_name`, `middle_name`, `last_name`, `honorific_id`, `job_title_id`, `specialization_area_id`, `tsc_number`, `years_of_experience`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 1, 'ATHUMAN', 'MWACHIZUNGU', 'MASUDI', 1, 27, 2, '23454', NULL, '2025-11-21 16:25:13', '2025-11-23 11:22:08', NULL),
(2, NULL, 2, 'Ali', NULL, 'Masudi', 1, 14, 3, '564322', NULL, '2025-11-21 17:26:59', '2025-11-21 17:26:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activated` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `branch_id`, `name`, `username`, `email`, `email_verified_at`, `phone`, `password`, `activated`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'John Doe', 'admin', 'admin@app.com', NULL, NULL, '$2y$12$06ZqeekLD/ltlwyQ6r6BOOLvEIlWFh/xlhg298yghrF9f6wbn1OJW', 1, NULL, '2025-11-16 10:51:35', '2025-11-16 10:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `work_histories`
--

CREATE TABLE `work_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `employee_id` bigint UNSIGNED NOT NULL,
  `institution_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `year_of_completion` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `allowances`
--
ALTER TABLE `allowances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendance_records_teacher_id_foreign` (`teacher_id`),
  ADD KEY `attendance_records_student_id_foreign` (`student_id`),
  ADD KEY `attendance_records_rank_id_foreign` (`rank_id`);

--
-- Indexes for table `auto_recorded_payments`
--
ALTER TABLE `auto_recorded_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auto_recorded_payments_reference_number_unique` (`reference_number`),
  ADD KEY `auto_recorded_payments_verified_by_foreign` (`verified_by`),
  ADD KEY `auto_recorded_payments_reference_number_index` (`reference_number`),
  ADD KEY `auto_recorded_payments_transaction_id_index` (`transaction_id`),
  ADD KEY `auto_recorded_payments_status_index` (`status`),
  ADD KEY `auto_recorded_payments_payment_date_index` (`payment_date`),
  ADD KEY `auto_recorded_payments_payment_method_index` (`payment_method`),
  ADD KEY `auto_recorded_payments_matched_admission_number_index` (`matched_admission_number`),
  ADD KEY `auto_recorded_payments_matched_student_id_index` (`matched_student_id`),
  ADD KEY `auto_recorded_payments_account_number_index` (`account_number`),
  ADD KEY `auto_recorded_payments_status_payment_date_index` (`status`,`payment_date`),
  ADD KEY `auto_recorded_payments_payment_method_status_index` (`payment_method`,`status`);

--
-- Indexes for table `bank_statement_imports`
--
ALTER TABLE `bank_statement_imports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_statement_imports_imported_by_foreign` (`imported_by`),
  ADD KEY `bank_statement_imports_account_number_index` (`account_number`),
  ADD KEY `bank_statement_imports_payment_method_index` (`payment_method`),
  ADD KEY `bank_statement_imports_created_at_index` (`created_at`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blogs_user_id_foreign` (`user_id`),
  ADD KEY `blogs_blog_category_id_foreign` (`blog_category_id`);

--
-- Indexes for table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `careers`
--
ALTER TABLE `careers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `careers_user_id_foreign` (`user_id`),
  ADD KEY `careers_language_id_foreign` (`language_id`),
  ADD KEY `careers_employment_type_id_foreign` (`employment_type_id`);

--
-- Indexes for table `customisations`
--
ALTER TABLE `customisations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emergency_contacts_employee_id_foreign` (`employee_id`),
  ADD KEY `emergency_contacts_relationship_id_foreign` (`relationship_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_primary_phone_unique` (`primary_phone`),
  ADD UNIQUE KEY `employees_staff_number_unique` (`staff_number`),
  ADD UNIQUE KEY `employees_email_unique` (`email`),
  ADD KEY `employees_employment_type_id_foreign` (`employment_type_id`),
  ADD KEY `employees_employment_status_id_foreign` (`employment_status_id`),
  ADD KEY `employees_honorific_id_foreign` (`honorific_id`),
  ADD KEY `employees_marital_status_id_foreign` (`marital_status_id`),
  ADD KEY `employees_gender_id_foreign` (`gender_id`),
  ADD KEY `employees_religion_id_foreign` (`religion_id`),
  ADD KEY `employees_user_id_index` (`user_id`);

--
-- Indexes for table `employee_class`
--
ALTER TABLE `employee_class`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_class_subject_year_unique` (`teacher_id`,`class_id`,`subject_id`,`academic_year_id`),
  ADD KEY `employee_class_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `employee_class_teacher_id_academic_year_id_index` (`teacher_id`,`academic_year_id`),
  ADD KEY `employee_class_class_id_academic_year_id_index` (`class_id`,`academic_year_id`),
  ADD KEY `employee_class_subject_id_academic_year_id_index` (`subject_id`,`academic_year_id`),
  ADD KEY `employee_class_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_deductions_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_deductions_deduction_id_foreign` (`deduction_id`);

--
-- Indexes for table `employee_incomes`
--
ALTER TABLE `employee_incomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_incomes_employee_id_foreign` (`employee_id`),
  ADD KEY `employee_incomes_income_id_foreign` (`income_id`);

--
-- Indexes for table `employment_statuses`
--
ALTER TABLE `employment_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employment_types`
--
ALTER TABLE `employment_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_academic_year_id_foreign` (`academic_year_id`);

--
-- Indexes for table `exam_marks`
--
ALTER TABLE `exam_marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_marks_exam_submission_id_foreign` (`exam_submission_id`),
  ADD KEY `exam_marks_exam_subject_id_foreign` (`exam_subject_id`),
  ADD KEY `exam_marks_class_id_foreign` (`class_id`),
  ADD KEY `exam_marks_teacher_id_foreign` (`teacher_id`),
  ADD KEY `exam_marks_submitted_by_foreign` (`submitted_by`),
  ADD KEY `exam_marks_approved_by_foreign` (`approved_by`),
  ADD KEY `exam_marks_rejected_by_foreign` (`rejected_by`),
  ADD KEY `exam_marks_status_index` (`status`),
  ADD KEY `exam_marks_submitted_at_index` (`submitted_at`),
  ADD KEY `exam_marks_exam_id_class_id_index` (`exam_id`,`class_id`),
  ADD KEY `exam_marks_student_id_exam_subject_id_index` (`student_id`,`exam_subject_id`);

--
-- Indexes for table `exam_skills`
--
ALTER TABLE `exam_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_skills_exam_id_skill_id_unique` (`exam_id`,`skill_id`),
  ADD KEY `exam_skills_skill_id_foreign` (`skill_id`);

--
-- Indexes for table `exam_students`
--
ALTER TABLE `exam_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_students_exam_id_class_id_student_id_unique` (`exam_id`,`class_id`,`student_id`),
  ADD KEY `exam_students_class_id_foreign` (`class_id`),
  ADD KEY `exam_students_student_id_foreign` (`student_id`);

--
-- Indexes for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_subjects_exam_id_foreign` (`exam_id`);

--
-- Indexes for table `exam_submissions`
--
ALTER TABLE `exam_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_submissions_exam_id_foreign` (`exam_id`),
  ADD KEY `exam_submissions_class_id_foreign` (`class_id`),
  ADD KEY `exam_submissions_teacher_id_foreign` (`teacher_id`),
  ADD KEY `exam_submissions_submitted_by_foreign` (`submitted_by`),
  ADD KEY `exam_submissions_approved_by_foreign` (`approved_by`),
  ADD KEY `exam_submissions_rejected_by_foreign` (`rejected_by`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fees_rank_id_foreign` (`rank_id`),
  ADD KEY `fees_student_id_status_index` (`student_id`,`status`),
  ADD KEY `fees_academic_year_term_index` (`academic_year`,`term`),
  ADD KEY `fees_due_date_index` (`due_date`),
  ADD KEY `fees_status_index` (`status`),
  ADD KEY `fees_is_carry_over_index` (`is_carry_over`),
  ADD KEY `fees_student_id_academic_year_term_index` (`student_id`,`academic_year`,`term`),
  ADD KEY `fees_original_fee_structure_id_index` (`original_fee_structure_id`),
  ADD KEY `fees_fee_type_index` (`fee_type`);

--
-- Indexes for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fee_invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `fee_invoices_rank_id_foreign` (`rank_id`),
  ADD KEY `fee_invoices_student_id_academic_year_term_index` (`student_id`,`academic_year`,`term`),
  ADD KEY `fee_invoices_due_date_status_index` (`due_date`,`status`),
  ADD KEY `fee_invoices_invoice_number_index` (`invoice_number`),
  ADD KEY `fee_invoices_fee_structure_id_foreign` (`fee_structure_id`);

--
-- Indexes for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_invoice_items_fee_invoice_id_index` (`fee_invoice_id`);

--
-- Indexes for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fee_payments_reference_number_status_unique` (`reference_number`,`status`),
  ADD KEY `fee_payments_verified_by_foreign` (`verified_by`),
  ADD KEY `fee_payments_student_id_payment_date_index` (`student_id`,`payment_date`),
  ADD KEY `fee_payments_reference_number_index` (`reference_number`),
  ADD KEY `fee_payments_payment_method_index` (`payment_method`),
  ADD KEY `fee_payments_status_index` (`status`),
  ADD KEY `fee_payments_payment_date_index` (`payment_date`),
  ADD KEY `fee_payments_fee_id_status_index` (`fee_id`,`status`),
  ADD KEY `fee_payments_auto_recorded_id_index` (`auto_recorded_payment_id`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fee_structures_rank_id_academic_year_term_unique` (`rank_id`,`academic_year`,`term`),
  ADD KEY `fee_structures_academic_year_term_index` (`academic_year`,`term`),
  ADD KEY `fee_structures_is_active_index` (`is_active`);

--
-- Indexes for table `fee_transfers`
--
ALTER TABLE `fee_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_transfers_from_student_id_index` (`from_student_id`),
  ADD KEY `fee_transfers_to_student_id_index` (`to_student_id`),
  ADD KEY `fee_transfers_initiated_by_index` (`initiated_by`),
  ADD KEY `fee_transfers_created_at_index` (`created_at`);

--
-- Indexes for table `genders`
--
ALTER TABLE `genders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grading_scheme`
--
ALTER TABLE `grading_scheme`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guardians_email_unique` (`email`),
  ADD UNIQUE KEY `guardians_phone_unique` (`phone`),
  ADD UNIQUE KEY `guardians_identification_number_unique` (`identification_number`),
  ADD KEY `guardians_student_id_foreign` (`student_id`),
  ADD KEY `guardians_relationship_id_foreign` (`relationship_id`);

--
-- Indexes for table `honorifics`
--
ALTER TABLE `honorifics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incomes`
--
ALTER TABLE `incomes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `institutions`
--
ALTER TABLE `institutions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_titles`
--
ALTER TABLE `job_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_titles_title_unique` (`title`),
  ADD KEY `job_titles_salary_grade_id_index` (`salary_grade_id`),
  ADD KEY `job_titles_salary_scale_id_index` (`salary_scale_id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessons_rank_id_foreign` (`rank_id`),
  ADD KEY `lessons_subject_id_foreign` (`subject_id`),
  ADD KEY `lessons_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `marital_statuses`
--
ALTER TABLE `marital_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media_uuid_unique` (`uuid`),
  ADD KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  ADD KEY `media_order_column_index` (`order_column`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menus_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_title_unique` (`title`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payrolls_employee_id_foreign` (`employee_id`),
  ADD KEY `payrolls_user_id_foreign` (`user_id`);

--
-- Indexes for table `payroll_allowances`
--
ALTER TABLE `payroll_allowances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_allowances_allowance_id_foreign` (`allowance_id`),
  ADD KEY `payroll_allowances_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_deductions_deduction_id_foreign` (`deduction_id`),
  ADD KEY `payroll_deductions_employee_id_foreign` (`employee_id`);

--
-- Indexes for table `payroll_details`
--
ALTER TABLE `payroll_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_details_payroll_id_foreign` (`payroll_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `permission_user`
--
ALTER TABLE `permission_user`
  ADD UNIQUE KEY `permission_user_user_id_permission_id_user_type_team_id_unique` (`user_id`,`permission_id`,`user_type`,`team_id`),
  ADD KEY `permission_user_permission_id_foreign` (`permission_id`),
  ADD KEY `permission_user_team_id_foreign` (`team_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `qualifications_employee_id_foreign` (`employee_id`),
  ADD KEY `qualifications_qualification_type_id_foreign` (`qualification_type_id`);

--
-- Indexes for table `qualification_types`
--
ALTER TABLE `qualification_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ranks`
--
ALTER TABLE `ranks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ranks_division_id_foreign` (`division_id`),
  ADD KEY `ranks_stream_id_foreign` (`stream_id`),
  ADD KEY `ranks_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `relationships`
--
ALTER TABLE `relationships`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `religions`
--
ALTER TABLE `religions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD UNIQUE KEY `role_user_user_id_role_id_user_type_team_id_unique` (`user_id`,`role_id`,`user_type`,`team_id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`),
  ADD KEY `role_user_team_id_foreign` (`team_id`);

--
-- Indexes for table `salary_grades`
--
ALTER TABLE `salary_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `salary_grades_name_unique` (`name`);

--
-- Indexes for table `salary_scales`
--
ALTER TABLE `salary_scales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `salary_scales_name_unique` (`name`),
  ADD KEY `salary_scales_salary_grade_id_index` (`salary_grade_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sections_page_id_index` (`page_id`);

--
-- Indexes for table `section_cta_buttons`
--
ALTER TABLE `section_cta_buttons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_cta_buttons_section_id_foreign` (`section_id`),
  ADD KEY `section_cta_buttons_page_id_foreign` (`page_id`);

--
-- Indexes for table `seo_metas`
--
ALTER TABLE `seo_metas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seo_metas_seo_able_type_seo_able_id_index` (`seo_able_type`,`seo_able_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `siblings`
--
ALTER TABLE `siblings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siblings_student_id_foreign` (`student_id`),
  ADD KEY `siblings_gender_id_foreign` (`gender_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `skills_name_subject_id_unique` (`name`,`subject_id`),
  ADD KEY `skills_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `specialization_areas`
--
ALTER TABLE `specialization_areas`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `streams`
--
ALTER TABLE `streams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_admission_number_unique` (`admission_number`),
  ADD UNIQUE KEY `unique_admission_number` (`admission_number`),
  ADD KEY `students_student_admission_id_foreign` (`student_admission_id`),
  ADD KEY `students_gender_id_foreign` (`gender_id`),
  ADD KEY `students_religion_id_foreign` (`religion_id`),
  ADD KEY `students_rank_id_foreign` (`rank_id`),
  ADD KEY `students_user_id_foreign` (`user_id`),
  ADD KEY `admission_number_index` (`admission_number`);

--
-- Indexes for table `student_admissions`
--
ALTER TABLE `student_admissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_admissions_division_id_foreign` (`division_id`);

--
-- Indexes for table `student_promotions`
--
ALTER TABLE `student_promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_promotions_student_id_academic_year_id_unique` (`student_id`,`academic_year_id`),
  ADD KEY `student_promotions_from_class_id_foreign` (`from_class_id`),
  ADD KEY `student_promotions_to_class_id_foreign` (`to_class_id`),
  ADD KEY `student_promotions_academic_year_id_foreign` (`academic_year_id`),
  ADD KEY `student_promotions_promoted_by_foreign` (`promoted_by`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_subjects_student_id_foreign` (`student_id`),
  ADD KEY `student_subjects_subject_id_foreign` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_name_unique` (`name`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teachers_user_id_index` (`user_id`),
  ADD KEY `teachers_employee_id_index` (`employee_id`),
  ADD KEY `teachers_honorific_id_index` (`honorific_id`),
  ADD KEY `teachers_job_title_id_index` (`job_title_id`),
  ADD KEY `teachers_specialization_area_id_index` (`specialization_area_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teams_name_unique` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_username_unique` (`username`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `work_histories`
--
ALTER TABLE `work_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_histories_employee_id_foreign` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `allowances`
--
ALTER TABLE `allowances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auto_recorded_payments`
--
ALTER TABLE `auto_recorded_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bank_statement_imports`
--
ALTER TABLE `bank_statement_imports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_tags`
--
ALTER TABLE `blog_tags`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `careers`
--
ALTER TABLE `careers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customisations`
--
ALTER TABLE `customisations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `divisions`
--
ALTER TABLE `divisions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employee_class`
--
ALTER TABLE `employee_class`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_incomes`
--
ALTER TABLE `employee_incomes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employment_statuses`
--
ALTER TABLE `employment_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employment_types`
--
ALTER TABLE `employment_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_marks`
--
ALTER TABLE `exam_marks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_skills`
--
ALTER TABLE `exam_skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_students`
--
ALTER TABLE `exam_students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_submissions`
--
ALTER TABLE `exam_submissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fee_payments`
--
ALTER TABLE `fee_payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fee_transfers`
--
ALTER TABLE `fee_transfers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `genders`
--
ALTER TABLE `genders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `grading_scheme`
--
ALTER TABLE `grading_scheme`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `honorifics`
--
ALTER TABLE `honorifics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `institutions`
--
ALTER TABLE `institutions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_titles`
--
ALTER TABLE `job_titles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marital_statuses`
--
ALTER TABLE `marital_statuses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_allowances`
--
ALTER TABLE `payroll_allowances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_details`
--
ALTER TABLE `payroll_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qualifications`
--
ALTER TABLE `qualifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qualification_types`
--
ALTER TABLE `qualification_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ranks`
--
ALTER TABLE `ranks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `relationships`
--
ALTER TABLE `relationships`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `religions`
--
ALTER TABLE `religions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_grades`
--
ALTER TABLE `salary_grades`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `salary_scales`
--
ALTER TABLE `salary_scales`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `section_cta_buttons`
--
ALTER TABLE `section_cta_buttons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seo_metas`
--
ALTER TABLE `seo_metas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siblings`
--
ALTER TABLE `siblings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `specialization_areas`
--
ALTER TABLE `specialization_areas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `streams`
--
ALTER TABLE `streams`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_admissions`
--
ALTER TABLE `student_admissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_promotions`
--
ALTER TABLE `student_promotions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `work_histories`
--
ALTER TABLE `work_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`),
  ADD CONSTRAINT `attendance_records_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `attendance_records_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `auto_recorded_payments`
--
ALTER TABLE `auto_recorded_payments`
  ADD CONSTRAINT `auto_recorded_payments_matched_student_id_foreign` FOREIGN KEY (`matched_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `auto_recorded_payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `bank_statement_imports`
--
ALTER TABLE `bank_statement_imports`
  ADD CONSTRAINT `bank_statement_imports_imported_by_foreign` FOREIGN KEY (`imported_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `blogs`
--
ALTER TABLE `blogs`
  ADD CONSTRAINT `blogs_blog_category_id_foreign` FOREIGN KEY (`blog_category_id`) REFERENCES `blog_categories` (`id`),
  ADD CONSTRAINT `blogs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `careers`
--
ALTER TABLE `careers`
  ADD CONSTRAINT `careers_employment_type_id_foreign` FOREIGN KEY (`employment_type_id`) REFERENCES `employment_types` (`id`),
  ADD CONSTRAINT `careers_language_id_foreign` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`),
  ADD CONSTRAINT `careers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `emergency_contacts`
--
ALTER TABLE `emergency_contacts`
  ADD CONSTRAINT `emergency_contacts_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `emergency_contacts_relationship_id_foreign` FOREIGN KEY (`relationship_id`) REFERENCES `relationships` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_employment_status_id_foreign` FOREIGN KEY (`employment_status_id`) REFERENCES `employment_statuses` (`id`),
  ADD CONSTRAINT `employees_employment_type_id_foreign` FOREIGN KEY (`employment_type_id`) REFERENCES `employment_types` (`id`),
  ADD CONSTRAINT `employees_gender_id_foreign` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`id`),
  ADD CONSTRAINT `employees_honorific_id_foreign` FOREIGN KEY (`honorific_id`) REFERENCES `honorifics` (`id`),
  ADD CONSTRAINT `employees_marital_status_id_foreign` FOREIGN KEY (`marital_status_id`) REFERENCES `marital_statuses` (`id`),
  ADD CONSTRAINT `employees_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`),
  ADD CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `employee_class`
--
ALTER TABLE `employee_class`
  ADD CONSTRAINT `employee_class_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_class_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_class_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_class_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employee_class_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_deductions`
--
ALTER TABLE `employee_deductions`
  ADD CONSTRAINT `employee_deductions_deduction_id_foreign` FOREIGN KEY (`deduction_id`) REFERENCES `deductions` (`id`),
  ADD CONSTRAINT `employee_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employee_incomes`
--
ALTER TABLE `employee_incomes`
  ADD CONSTRAINT `employee_incomes_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_incomes_income_id_foreign` FOREIGN KEY (`income_id`) REFERENCES `incomes` (`id`);

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_marks`
--
ALTER TABLE `exam_marks`
  ADD CONSTRAINT `exam_marks_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_exam_subject_id_foreign` FOREIGN KEY (`exam_subject_id`) REFERENCES `exam_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_exam_submission_id_foreign` FOREIGN KEY (`exam_submission_id`) REFERENCES `exam_submissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_marks_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_skills`
--
ALTER TABLE `exam_skills`
  ADD CONSTRAINT `exam_skills_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_skills_skill_id_foreign` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_students`
--
ALTER TABLE `exam_students`
  ADD CONSTRAINT `exam_students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_students_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_students_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_subjects`
--
ALTER TABLE `exam_subjects`
  ADD CONSTRAINT `exam_subjects_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_submissions`
--
ALTER TABLE `exam_submissions`
  ADD CONSTRAINT `exam_submissions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_submissions_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_submissions_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_submissions_rejected_by_foreign` FOREIGN KEY (`rejected_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_submissions_submitted_by_foreign` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_submissions_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fees`
--
ALTER TABLE `fees`
  ADD CONSTRAINT `fees_original_fee_structure_id_foreign` FOREIGN KEY (`original_fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fees_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fees_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_invoices`
--
ALTER TABLE `fee_invoices`
  ADD CONSTRAINT `fee_invoices_fee_structure_id_foreign` FOREIGN KEY (`fee_structure_id`) REFERENCES `fee_structures` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_invoices_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_invoice_items`
--
ALTER TABLE `fee_invoice_items`
  ADD CONSTRAINT `fee_invoice_items_fee_invoice_id_foreign` FOREIGN KEY (`fee_invoice_id`) REFERENCES `fee_invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_payments`
--
ALTER TABLE `fee_payments`
  ADD CONSTRAINT `fee_payments_auto_recorded_payment_id_foreign` FOREIGN KEY (`auto_recorded_payment_id`) REFERENCES `auto_recorded_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_payments_fee_id_foreign` FOREIGN KEY (`fee_id`) REFERENCES `fees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_payments_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_transfers`
--
ALTER TABLE `fee_transfers`
  ADD CONSTRAINT `fee_transfers_from_student_id_foreign` FOREIGN KEY (`from_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_transfers_initiated_by_foreign` FOREIGN KEY (`initiated_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fee_transfers_to_student_id_foreign` FOREIGN KEY (`to_student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_relationship_id_foreign` FOREIGN KEY (`relationship_id`) REFERENCES `relationships` (`id`),
  ADD CONSTRAINT `guardians_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `job_titles`
--
ALTER TABLE `job_titles`
  ADD CONSTRAINT `job_titles_salary_grade_id_foreign` FOREIGN KEY (`salary_grade_id`) REFERENCES `salary_grades` (`id`),
  ADD CONSTRAINT `job_titles_salary_scale_id_foreign` FOREIGN KEY (`salary_scale_id`) REFERENCES `salary_scales` (`id`);

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`),
  ADD CONSTRAINT `lessons_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `lessons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menus` (`id`);

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `payrolls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payroll_allowances`
--
ALTER TABLE `payroll_allowances`
  ADD CONSTRAINT `payroll_allowances_allowance_id_foreign` FOREIGN KEY (`allowance_id`) REFERENCES `allowances` (`id`),
  ADD CONSTRAINT `payroll_allowances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `payroll_deductions`
--
ALTER TABLE `payroll_deductions`
  ADD CONSTRAINT `payroll_deductions_deduction_id_foreign` FOREIGN KEY (`deduction_id`) REFERENCES `deductions` (`id`),
  ADD CONSTRAINT `payroll_deductions_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `payroll_details`
--
ALTER TABLE `payroll_details`
  ADD CONSTRAINT `payroll_details_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`);

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `permission_user`
--
ALTER TABLE `permission_user`
  ADD CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `permission_user_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `qualifications`
--
ALTER TABLE `qualifications`
  ADD CONSTRAINT `qualifications_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `qualifications_qualification_type_id_foreign` FOREIGN KEY (`qualification_type_id`) REFERENCES `qualification_types` (`id`);

--
-- Constraints for table `ranks`
--
ALTER TABLE `ranks`
  ADD CONSTRAINT `ranks_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`),
  ADD CONSTRAINT `ranks_stream_id_foreign` FOREIGN KEY (`stream_id`) REFERENCES `streams` (`id`),
  ADD CONSTRAINT `ranks_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_user_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `salary_scales`
--
ALTER TABLE `salary_scales`
  ADD CONSTRAINT `salary_scales_salary_grade_id_foreign` FOREIGN KEY (`salary_grade_id`) REFERENCES `salary_grades` (`id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`);

--
-- Constraints for table `section_cta_buttons`
--
ALTER TABLE `section_cta_buttons`
  ADD CONSTRAINT `section_cta_buttons_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`),
  ADD CONSTRAINT `section_cta_buttons_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`);

--
-- Constraints for table `siblings`
--
ALTER TABLE `siblings`
  ADD CONSTRAINT `siblings_gender_id_foreign` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`id`),
  ADD CONSTRAINT `siblings_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_gender_id_foreign` FOREIGN KEY (`gender_id`) REFERENCES `genders` (`id`),
  ADD CONSTRAINT `students_rank_id_foreign` FOREIGN KEY (`rank_id`) REFERENCES `ranks` (`id`),
  ADD CONSTRAINT `students_religion_id_foreign` FOREIGN KEY (`religion_id`) REFERENCES `religions` (`id`),
  ADD CONSTRAINT `students_student_admission_id_foreign` FOREIGN KEY (`student_admission_id`) REFERENCES `student_admissions` (`id`),
  ADD CONSTRAINT `students_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_admissions`
--
ALTER TABLE `student_admissions`
  ADD CONSTRAINT `student_admissions_division_id_foreign` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`);

--
-- Constraints for table `student_promotions`
--
ALTER TABLE `student_promotions`
  ADD CONSTRAINT `student_promotions_academic_year_id_foreign` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_promotions_from_class_id_foreign` FOREIGN KEY (`from_class_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_promotions_promoted_by_foreign` FOREIGN KEY (`promoted_by`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_promotions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_promotions_to_class_id_foreign` FOREIGN KEY (`to_class_id`) REFERENCES `ranks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `student_subjects_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_subjects_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `teachers_honorific_id_foreign` FOREIGN KEY (`honorific_id`) REFERENCES `honorifics` (`id`),
  ADD CONSTRAINT `teachers_job_title_id_foreign` FOREIGN KEY (`job_title_id`) REFERENCES `job_titles` (`id`),
  ADD CONSTRAINT `teachers_specialization_area_id_foreign` FOREIGN KEY (`specialization_area_id`) REFERENCES `specialization_areas` (`id`),
  ADD CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `work_histories`
--
ALTER TABLE `work_histories`
  ADD CONSTRAINT `work_histories_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
