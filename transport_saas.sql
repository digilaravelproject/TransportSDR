-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2026 at 01:13 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `transport_saas`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('transport-saas-cache-dashboard:kpis:1:2026-04-10', 'a:7:{s:5:\"trips\";a:7:{s:5:\"total\";i:2;s:5:\"today\";i:1;s:10:\"this_month\";i:2;s:9:\"scheduled\";i:1;s:7:\"ongoing\";i:1;s:9:\"completed\";i:0;s:9:\"cancelled\";i:0;}s:5:\"leads\";a:6:{s:5:\"total\";i:1;s:3:\"new\";i:0;s:14:\"followup_today\";i:0;s:9:\"converted\";i:1;s:4:\"lost\";i:0;s:15:\"conversion_rate\";d:100;}s:8:\"vehicles\";a:4:{s:5:\"total\";i:1;s:9:\"available\";i:0;s:7:\"on_trip\";i:1;s:8:\"inactive\";i:0;}s:5:\"staff\";a:4:{s:5:\"total\";i:2;s:7:\"drivers\";i:2;s:7:\"helpers\";i:0;s:9:\"available\";i:1;}s:7:\"revenue\";a:5:{s:5:\"today\";d:28000;s:9:\"this_week\";d:28000;s:10:\"this_month\";d:28000;s:9:\"this_year\";d:56000;s:15:\"pending_balance\";s:8:\"40300.00\";}s:9:\"customers\";a:2:{s:5:\"total\";i:1;s:10:\"this_month\";i:1;}s:9:\"corporate\";a:2:{s:5:\"total\";i:1;s:6:\"active\";i:1;}}', 1775829268),
('transport-saas-cache-dashboard:monthly-trip-revenue:1:12', 'a:3:{s:6:\"labels\";a:12:{i:0;s:8:\"May 2025\";i:1;s:8:\"Jun 2025\";i:2;s:8:\"Jul 2025\";i:3;s:8:\"Aug 2025\";i:4;s:8:\"Sep 2025\";i:5;s:8:\"Oct 2025\";i:6;s:8:\"Nov 2025\";i:7;s:8:\"Dec 2025\";i:8;s:8:\"Jan 2026\";i:9;s:8:\"Feb 2026\";i:10;s:8:\"Mar 2026\";i:11;s:8:\"Apr 2026\";}s:5:\"trips\";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:1;}s:7:\"revenue\";a:12:{i:0;d:0;i:1;d:0;i:2;d:0;i:3;d:0;i:4;d:0;i:5;d:0;i:6;d:0;i:7;d:0;i:8;d:0;i:9;d:0;i:10;d:0;i:11;d:28000;}}', 1775829618),
('transport-saas-cache-dashboard:notifications:1:2026-04-13-04', 'a:3:{s:5:\"total\";i:1;s:6:\"urgent\";i:0;s:13:\"notifications\";a:1:{i:0;a:8:{s:2:\"id\";s:14:\"corp-payment-1\";s:4:\"type\";s:25:\"corporate_payment_pending\";s:8:\"priority\";s:6:\"medium\";s:4:\"icon\";s:9:\"corporate\";s:5:\"title\";s:28:\"Payment Pending: TCS Lucknow\";s:7:\"message\";s:46:\"₹51480.00 pending — Invoice CINV-2026-0001\";s:4:\"meta\";a:3:{s:12:\"corporate_id\";i:1;s:10:\"payment_id\";i:1;s:7:\"balance\";s:8:\"51480.00\";}s:10:\"created_at\";s:10:\"2026-04-09\";}}}', 1776055789),
('transport-saas-cache-dashboard:performance:1:month:2026-04-10', 'a:7:{s:6:\"period\";s:5:\"month\";s:4:\"from\";s:10:\"2026-04-01\";s:2:\"to\";s:10:\"2026-04-30\";s:5:\"trips\";a:5:{s:6:\"labels\";a:1:{i:0;s:6:\"10 Apr\";}s:5:\"total\";a:1:{i:0;i:1;}s:9:\"completed\";a:1:{i:0;s:1:\"0\";}s:9:\"cancelled\";a:1:{i:0;s:1:\"0\";}s:7:\"revenue\";a:1:{i:0;d:28000;}}s:5:\"leads\";a:4:{s:6:\"labels\";a:1:{i:0;s:6:\"06 Apr\";}s:5:\"total\";a:1:{i:0;i:1;}s:9:\"converted\";a:1:{i:0;s:1:\"1\";}s:4:\"lost\";a:1:{i:0;s:1:\"0\";}}s:7:\"revenue\";a:4:{s:6:\"labels\";a:1:{i:0;s:6:\"10 Apr\";}s:7:\"revenue\";a:1:{i:0;d:28000;}s:9:\"collected\";a:1:{i:0;d:0;}s:7:\"pending\";a:1:{i:0;d:21400;}}s:11:\"comparisons\";a:2:{s:5:\"trips\";a:4:{s:7:\"current\";i:1;s:8:\"previous\";i:0;s:6:\"change\";d:100;s:5:\"trend\";s:2:\"up\";}s:7:\"revenue\";a:4:{s:7:\"current\";d:28000;s:8:\"previous\";d:0;s:6:\"change\";d:100;s:5:\"trend\";s:2:\"up\";}}}', 1775829484),
('transport-saas-cache-dashboard:performance:1:month:2026-04-13', 'a:7:{s:6:\"period\";s:5:\"month\";s:4:\"from\";s:10:\"2026-04-01\";s:2:\"to\";s:10:\"2026-04-30\";s:5:\"trips\";a:5:{s:6:\"labels\";a:1:{i:0;s:6:\"10 Apr\";}s:5:\"total\";a:1:{i:0;i:1;}s:9:\"completed\";a:1:{i:0;s:1:\"0\";}s:9:\"cancelled\";a:1:{i:0;s:1:\"0\";}s:7:\"revenue\";a:1:{i:0;d:28000;}}s:5:\"leads\";a:4:{s:6:\"labels\";a:1:{i:0;s:6:\"06 Apr\";}s:5:\"total\";a:1:{i:0;i:1;}s:9:\"converted\";a:1:{i:0;s:1:\"1\";}s:4:\"lost\";a:1:{i:0;s:1:\"0\";}}s:7:\"revenue\";a:4:{s:6:\"labels\";a:1:{i:0;s:6:\"10 Apr\";}s:7:\"revenue\";a:1:{i:0;d:28000;}s:9:\"collected\";a:1:{i:0;d:0;}s:7:\"pending\";a:1:{i:0;d:21400;}}s:11:\"comparisons\";a:2:{s:5:\"trips\";a:4:{s:7:\"current\";i:1;s:8:\"previous\";i:0;s:6:\"change\";d:100;s:5:\"trend\";s:2:\"up\";}s:7:\"revenue\";a:4:{s:7:\"current\";d:28000;s:8:\"previous\";d:0;s:6:\"change\";d:100;s:5:\"trend\";s:2:\"up\";}}}', 1776055482),
('transport-saas-cache-dashboard:performance:1:week:2026-04-13', 'a:7:{s:6:\"period\";s:4:\"week\";s:4:\"from\";s:10:\"2026-04-13\";s:2:\"to\";s:10:\"2026-04-19\";s:5:\"trips\";a:5:{s:6:\"labels\";a:0:{}s:5:\"total\";a:0:{}s:9:\"completed\";a:0:{}s:9:\"cancelled\";a:0:{}s:7:\"revenue\";a:0:{}}s:5:\"leads\";a:4:{s:6:\"labels\";a:0:{}s:5:\"total\";a:0:{}s:9:\"converted\";a:0:{}s:4:\"lost\";a:0:{}}s:7:\"revenue\";a:4:{s:6:\"labels\";a:0:{}s:7:\"revenue\";a:0:{}s:9:\"collected\";a:0:{}s:7:\"pending\";a:0:{}}s:11:\"comparisons\";a:2:{s:5:\"trips\";a:4:{s:7:\"current\";i:0;s:8:\"previous\";i:1;s:6:\"change\";d:-100;s:5:\"trend\";s:4:\"down\";}s:7:\"revenue\";a:4:{s:7:\"current\";d:0;s:8:\"previous\";d:28000;s:6:\"change\";d:-100;s:5:\"trend\";s:4:\"down\";}}}', 1776055387),
('transport-saas-cache-dashboard:performance:1:year:2026-04-13', 'a:7:{s:6:\"period\";s:4:\"year\";s:4:\"from\";s:10:\"2026-01-01\";s:2:\"to\";s:10:\"2026-12-31\";s:5:\"trips\";a:5:{s:6:\"labels\";a:2:{i:0;s:8:\"Apr 2026\";i:1;s:8:\"May 2026\";}s:5:\"total\";a:2:{i:0;i:1;i:1;i:1;}s:9:\"completed\";a:2:{i:0;s:1:\"0\";i:1;s:1:\"0\";}s:9:\"cancelled\";a:2:{i:0;s:1:\"0\";i:1;s:1:\"0\";}s:7:\"revenue\";a:2:{i:0;d:28000;i:1;d:28000;}}s:5:\"leads\";a:4:{s:6:\"labels\";a:1:{i:0;s:8:\"Apr 2026\";}s:5:\"total\";a:1:{i:0;i:1;}s:9:\"converted\";a:1:{i:0;s:1:\"1\";}s:4:\"lost\";a:1:{i:0;s:1:\"0\";}}s:7:\"revenue\";a:4:{s:6:\"labels\";a:2:{i:0;s:8:\"Apr 2026\";i:1;s:8:\"May 2026\";}s:7:\"revenue\";a:2:{i:0;d:28000;i:1;d:28000;}s:9:\"collected\";a:2:{i:0;d:0;i:1;d:10000;}s:7:\"pending\";a:2:{i:0;d:21400;i:1;d:18900;}}s:11:\"comparisons\";a:2:{s:5:\"trips\";a:4:{s:7:\"current\";i:2;s:8:\"previous\";i:0;s:6:\"change\";d:100;s:5:\"trend\";s:2:\"up\";}s:7:\"revenue\";a:4:{s:7:\"current\";d:56000;s:8:\"previous\";d:0;s:6:\"change\";d:100;s:5:\"trend\";s:2:\"up\";}}}', 1776055712),
('transport-saas-cache-dashboard:pl-trend:1:6', 'a:4:{s:6:\"labels\";a:6:{i:0;s:8:\"Nov 2025\";i:1;s:8:\"Dec 2025\";i:2;s:8:\"Jan 2026\";i:3;s:8:\"Feb 2026\";i:4;s:8:\"Mar 2026\";i:5;s:8:\"Apr 2026\";}s:6:\"income\";a:6:{i:0;d:0;i:1;d:0;i:2;d:0;i:3;d:0;i:4;d:0;i:5;d:78000;}s:7:\"expense\";a:6:{i:0;d:0;i:1;d:0;i:2;d:0;i:3;d:0;i:4;d:0;i:5;d:10180;}s:5:\"netPL\";a:6:{i:0;d:0;i:1;d:0;i:2;d:0;i:3;d:0;i:4;d:0;i:5;d:67820;}}', 1775829992),
('transport-saas-cache-dashboard:pl:1:2025-11-01:2025-11-30', 'a:6:{s:6:\"period\";a:2:{s:4:\"from\";s:10:\"2025-11-01\";s:2:\"to\";s:10:\"2025-11-30\";}s:6:\"income\";a:3:{s:12:\"trip_revenue\";d:0;s:17:\"corporate_revenue\";d:0;s:5:\"total\";d:0;}s:7:\"expense\";a:4:{s:4:\"fuel\";d:0;s:11:\"maintenance\";d:0;s:12:\"staff_salary\";d:0;s:5:\"total\";d:0;}s:6:\"net_pl\";d:0;s:13:\"profit_margin\";i:0;s:9:\"is_profit\";b:1;}', 1775829692),
('transport-saas-cache-dashboard:pl:1:2025-12-01:2025-12-31', 'a:6:{s:6:\"period\";a:2:{s:4:\"from\";s:10:\"2025-12-01\";s:2:\"to\";s:10:\"2025-12-31\";}s:6:\"income\";a:3:{s:12:\"trip_revenue\";d:0;s:17:\"corporate_revenue\";d:0;s:5:\"total\";d:0;}s:7:\"expense\";a:4:{s:4:\"fuel\";d:0;s:11:\"maintenance\";d:0;s:12:\"staff_salary\";d:0;s:5:\"total\";d:0;}s:6:\"net_pl\";d:0;s:13:\"profit_margin\";i:0;s:9:\"is_profit\";b:1;}', 1775829692),
('transport-saas-cache-dashboard:pl:1:2026-01-01:2026-01-31', 'a:6:{s:6:\"period\";a:2:{s:4:\"from\";s:10:\"2026-01-01\";s:2:\"to\";s:10:\"2026-01-31\";}s:6:\"income\";a:3:{s:12:\"trip_revenue\";d:0;s:17:\"corporate_revenue\";d:0;s:5:\"total\";d:0;}s:7:\"expense\";a:4:{s:4:\"fuel\";d:0;s:11:\"maintenance\";d:0;s:12:\"staff_salary\";d:0;s:5:\"total\";d:0;}s:6:\"net_pl\";d:0;s:13:\"profit_margin\";i:0;s:9:\"is_profit\";b:1;}', 1775829692),
('transport-saas-cache-dashboard:pl:1:2026-02-01:2026-02-28', 'a:6:{s:6:\"period\";a:2:{s:4:\"from\";s:10:\"2026-02-01\";s:2:\"to\";s:10:\"2026-02-28\";}s:6:\"income\";a:3:{s:12:\"trip_revenue\";d:0;s:17:\"corporate_revenue\";d:0;s:5:\"total\";d:0;}s:7:\"expense\";a:4:{s:4:\"fuel\";d:0;s:11:\"maintenance\";d:0;s:12:\"staff_salary\";d:0;s:5:\"total\";d:0;}s:6:\"net_pl\";d:0;s:13:\"profit_margin\";i:0;s:9:\"is_profit\";b:1;}', 1775829692),
('transport-saas-cache-dashboard:pl:1:2026-03-01:2026-03-31', 'a:6:{s:6:\"period\";a:2:{s:4:\"from\";s:10:\"2026-03-01\";s:2:\"to\";s:10:\"2026-03-31\";}s:6:\"income\";a:3:{s:12:\"trip_revenue\";d:0;s:17:\"corporate_revenue\";d:0;s:5:\"total\";d:0;}s:7:\"expense\";a:4:{s:4:\"fuel\";d:0;s:11:\"maintenance\";d:0;s:12:\"staff_salary\";d:0;s:5:\"total\";d:0;}s:6:\"net_pl\";d:0;s:13:\"profit_margin\";i:0;s:9:\"is_profit\";b:1;}', 1775829692),
('transport-saas-cache-dashboard:pl:1:2026-04-01:2026-04-30', 'a:6:{s:6:\"period\";a:2:{s:4:\"from\";s:10:\"2026-04-01\";s:2:\"to\";s:10:\"2026-04-30\";}s:6:\"income\";a:3:{s:12:\"trip_revenue\";d:28000;s:17:\"corporate_revenue\";d:50000;s:5:\"total\";d:78000;}s:7:\"expense\";a:4:{s:4:\"fuel\";d:7680;s:11:\"maintenance\";d:5300;s:12:\"staff_salary\";d:-2800;s:5:\"total\";d:10180;}s:6:\"net_pl\";d:67820;s:13:\"profit_margin\";d:86.95;s:9:\"is_profit\";b:1;}', 1775829692),
('transport-saas-cache-dashboard:revenue-source:1:2026-04', 'a:3:{s:6:\"labels\";a:2:{i:0;s:12:\"Trip Revenue\";i:1;s:17:\"Corporate Revenue\";}s:4:\"data\";a:2:{i:0;d:28000;i:1;d:101480;}s:6:\"colors\";a:2:{i:0;s:7:\"#1D9E75\";i:1;s:7:\"#534AB7\";}}', 1775829618),
('transport-saas-cache-dashboard:vehicle-pl:1:2026-04-01:2026-04-30', 'a:1:{i:0;a:9:{s:10:\"vehicle_id\";i:1;s:19:\"registration_number\";s:10:\"UP32AB1234\";s:4:\"type\";s:3:\"bus\";s:6:\"income\";d:28000;s:12:\"fuel_expense\";d:7680;s:19:\"maintenance_expense\";d:5300;s:13:\"total_expense\";d:12980;s:6:\"net_pl\";d:15020;s:9:\"is_profit\";b:1;}}', 1775829692);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cash_book_entries`
--

CREATE TABLE `cash_book_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `entry_type` enum('income','expense') NOT NULL,
  `payment_mode` enum('cash','online','cheque','upi','bank_transfer','neft','rtgs','imps') NOT NULL,
  `category` enum('trip_payment','advance_received','corporate_payment','lead_advance','other_income','fuel_expense','maintenance_expense','salary_payment','da_payment','advance_given','toll_charge','office_expense','vehicle_insurance','vehicle_tax','other_expense') NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `closing_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) NOT NULL,
  `entry_date` date NOT NULL,
  `party_name` varchar(255) DEFAULT NULL,
  `party_contact` varchar(15) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `cheque_number` varchar(255) DEFAULT NULL,
  `cheque_date` date DEFAULT NULL,
  `status` enum('confirmed','pending','bounced','cancelled') NOT NULL DEFAULT 'confirmed',
  `notes` text DEFAULT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cash_book_entries`
--

INSERT INTO `cash_book_entries` (`id`, `tenant_id`, `entry_type`, `payment_mode`, `category`, `reference_type`, `reference_id`, `reference_number`, `amount`, `opening_balance`, `closing_balance`, `description`, `entry_date`, `party_name`, `party_contact`, `transaction_id`, `bank_name`, `cheque_number`, `cheque_date`, `status`, `notes`, `receipt_path`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'income', 'cash', 'trip_payment', 'Trip', 1, 'TRP-2026-0001', 25000.00, 0.00, 25000.00, 'Trip payment received — TRP-2026-0001', '2026-04-13', 'Rahul Sharma', '9888888888', NULL, NULL, NULL, NULL, 'confirmed', 'Full payment received in cash', 'tenants/1/receipts/receipt-1-1776495575.jpg', 2, '2026-04-18 01:13:36', '2026-04-18 01:29:36', NULL),
(2, 1, 'expense', 'cash', 'fuel_expense', 'Vehicle', 1, NULL, 7400.00, 25000.00, 17600.00, 'Diesel fill — UP32AB1234', '2026-04-13', 'HP Petrol Pump', NULL, NULL, NULL, NULL, NULL, 'confirmed', NULL, NULL, 2, '2026-04-18 01:19:26', '2026-04-18 01:19:26', NULL),
(3, 1, 'income', 'cheque', 'corporate_payment', NULL, NULL, NULL, 85000.00, 17600.00, 102600.00, 'TCS monthly payment — April 2026', '2026-04-13', 'TCS Lucknow', NULL, NULL, 'HDFC Bank', '123456', '2026-04-15', 'pending', NULL, NULL, 2, '2026-04-18 01:22:26', '2026-04-18 01:22:26', NULL),
(4, 1, 'income', 'upi', 'trip_payment', 'Trip', 1, 'TRP-2026-0001', 15000.00, 17600.00, 32600.00, 'Online payment via upi_direct: TRP-2026-0001', '2026-04-18', 'Rahul Sharma', '9888888888', 'UTR123456789', NULL, NULL, NULL, 'confirmed', NULL, NULL, 2, '2026-04-18 01:32:19', '2026-04-18 01:32:19', NULL),
(5, 1, 'expense', 'upi', 'other_expense', 'Trip', 1, 'TRP-2026-0001', 5000.00, 32600.00, 27600.00, 'Refund: TRP-2026-0001 via upi_direct', '2026-04-18', 'Rahul Sharma', NULL, 'UTR987654321', NULL, NULL, NULL, 'confirmed', NULL, NULL, 2, '2026-04-18 04:01:54', '2026-04-18 04:01:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `corporates`
--

CREATE TABLE `corporates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gstin` varchar(15) DEFAULT NULL,
  `pan` varchar(10) DEFAULT NULL,
  `contract_type` enum('monthly','daily','trip_based') NOT NULL DEFAULT 'monthly',
  `monthly_package` decimal(12,2) NOT NULL DEFAULT 0.00,
  `per_day_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `per_km_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `extra_hour_rate` decimal(8,2) NOT NULL DEFAULT 0.00,
  `holiday_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_duty_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `included_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `included_hours` int(11) NOT NULL DEFAULT 0,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `number_of_vehicles` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `duty_type` enum('general','shift','shuttle') NOT NULL DEFAULT 'general',
  `is_gst` tinyint(1) NOT NULL DEFAULT 0,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 18.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `contract_start` date DEFAULT NULL,
  `contract_end` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `corporates`
--

INSERT INTO `corporates` (`id`, `tenant_id`, `company_name`, `contact_person`, `phone`, `email`, `address`, `gstin`, `pan`, `contract_type`, `monthly_package`, `per_day_rate`, `per_km_rate`, `extra_hour_rate`, `holiday_rate`, `extra_duty_rate`, `included_km`, `included_hours`, `vehicle_type`, `number_of_vehicles`, `duty_type`, `is_gst`, `gst_percent`, `is_active`, `contract_start`, `contract_end`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'TCS Lucknow', 'Rajan Mehta', '9876543210', 'transport@tcs.com', NULL, '09AAAAA0000A1Z5', NULL, 'monthly', 85000.00, 0.00, 12.00, 150.00, 1500.00, 1200.00, 2000.00, 8, 'sedan', 3, 'shift', 1, 18.00, 1, '2026-04-01', '2027-03-31', NULL, 2, '2026-04-09 10:15:04', '2026-04-09 10:15:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `corporate_duties`
--

CREATE TABLE `corporate_duties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `corporate_id` bigint(20) UNSIGNED NOT NULL,
  `duty_number` varchar(255) NOT NULL,
  `duty_date` date NOT NULL,
  `duty_type` enum('general','shift','shuttle') NOT NULL DEFAULT 'general',
  `duty_status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `shift_name` varchar(255) DEFAULT NULL,
  `shift_start` time DEFAULT NULL,
  `shift_end` time DEFAULT NULL,
  `vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vehicle_type` varchar(255) DEFAULT NULL,
  `number_of_vehicles` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `helper_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `drop_location` varchar(255) DEFAULT NULL,
  `route_details` text DEFAULT NULL,
  `start_km` decimal(10,2) DEFAULT NULL,
  `end_km` decimal(10,2) DEFAULT NULL,
  `total_km` decimal(10,2) DEFAULT NULL,
  `extra_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_hours` decimal(6,2) DEFAULT NULL,
  `extra_hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `is_holiday` tinyint(1) NOT NULL DEFAULT 0,
  `is_extra_duty` tinyint(1) NOT NULL DEFAULT 0,
  `base_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `extra_km_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_hour_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `holiday_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_duty_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `corporate_duties`
--

INSERT INTO `corporate_duties` (`id`, `tenant_id`, `corporate_id`, `duty_number`, `duty_date`, `duty_type`, `duty_status`, `shift_name`, `shift_start`, `shift_end`, `vehicle_id`, `vehicle_type`, `number_of_vehicles`, `driver_id`, `helper_id`, `pickup_location`, `drop_location`, `route_details`, `start_km`, `end_km`, `total_km`, `extra_km`, `total_hours`, `extra_hours`, `is_holiday`, `is_extra_duty`, `base_amount`, `extra_km_amount`, `extra_hour_amount`, `holiday_amount`, `extra_duty_amount`, `fine_amount`, `total_amount`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'DUT-2026-0001', '2026-04-08', 'shift', 'completed', 'Morning', '09:00:00', '18:00:00', 1, NULL, 1, 1, NULL, 'TCS Office Gate 1', 'Gomti Nagar', NULL, 46000.00, 46085.00, 85.00, 0.00, NULL, 0.00, 0, 0, 0.00, 0.00, 0.00, 0.00, 0.00, 500.00, -500.00, NULL, 2, '2026-04-09 10:17:24', '2026-04-09 10:19:41', NULL),
(2, 1, 1, 'DUT-2026-0002', '2026-04-14', 'general', 'completed', NULL, NULL, NULL, 1, NULL, 1, 1, NULL, NULL, NULL, NULL, 46200.00, 46350.00, 150.00, 0.00, NULL, 0.00, 1, 0, 0.00, 0.00, 0.00, 1500.00, 0.00, 0.00, 1500.00, NULL, 2, '2026-04-09 10:18:37', '2026-04-09 10:18:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `corporate_fines`
--

CREATE TABLE `corporate_fines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `corporate_id` bigint(20) UNSIGNED NOT NULL,
  `duty_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `fine_date` date NOT NULL,
  `status` enum('pending','deducted','waived') NOT NULL DEFAULT 'pending',
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `corporate_fines`
--

INSERT INTO `corporate_fines` (`id`, `tenant_id`, `corporate_id`, `duty_id`, `reason`, `amount`, `fine_date`, `status`, `payment_id`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Driver late by 45 minutes', 500.00, '2026-04-08', 'deducted', 1, 'Complained by client HR', 2, '2026-04-09 10:19:41', '2026-04-09 10:20:55');

-- --------------------------------------------------------

--
-- Table structure for table `corporate_payments`
--

CREATE TABLE `corporate_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `corporate_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `billing_period` varchar(255) NOT NULL,
  `billing_from` date NOT NULL,
  `billing_to` date NOT NULL,
  `total_duties` int(11) NOT NULL DEFAULT 0,
  `holiday_duties` int(11) NOT NULL DEFAULT 0,
  `extra_duties` int(11) NOT NULL DEFAULT 0,
  `total_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `base_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `extra_km_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `extra_hour_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `holiday_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `extra_duty_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fine_deduction` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_gst` tinyint(1) NOT NULL DEFAULT 0,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `cgst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sgst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `payment_mode` enum('cash','bank','cheque','upi') DEFAULT NULL,
  `paid_on` date DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `invoice_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `corporate_payments`
--

INSERT INTO `corporate_payments` (`id`, `tenant_id`, `corporate_id`, `invoice_number`, `billing_period`, `billing_from`, `billing_to`, `total_duties`, `holiday_duties`, `extra_duties`, `total_km`, `extra_km`, `base_amount`, `extra_km_amount`, `extra_hour_amount`, `holiday_amount`, `extra_duty_amount`, `fine_deduction`, `subtotal`, `is_gst`, `gst_percent`, `cgst`, `sgst`, `igst`, `tax_amount`, `total_amount`, `paid_amount`, `balance_amount`, `payment_status`, `payment_mode`, `paid_on`, `transaction_ref`, `invoice_path`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'CINV-2026-0001', '2026-04', '2026-04-01', '2026-04-30', 2, 1, 0, 235.00, 0.00, 85000.00, 0.00, 0.00, 1500.00, 0.00, 500.00, 86000.00, 1, 18.00, 7740.00, 7740.00, 0.00, 15480.00, 101480.00, 50000.00, 51480.00, 'partial', 'bank', '2026-05-05', 'NEFT123456', 'tenants/1/corporate-invoices/invoice-CINV-2026-0001.pdf', NULL, 2, '2026-04-09 10:20:55', '2026-04-09 10:23:54');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gstin` varchar(15) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `tenant_id`, `name`, `phone`, `email`, `address`, `gstin`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Rahul Sharma', '9888888888', 'rahul@example.com', 'Hazratganj, Lucknow', NULL, 1, '2026-04-03 10:43:32', '2026-04-03 10:43:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_categories`
--

CREATE TABLE `inventory_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_categories`
--

INSERT INTO `inventory_categories` (`id`, `tenant_id`, `name`, `description`, `icon`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Engine Parts', 'Engine related spare parts', 'engine', 1, 2, '2026-04-18 07:41:36', '2026-04-18 07:41:36');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_code` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `model_compatible` varchar(255) DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'piece',
  `quantity_in_stock` decimal(12,2) NOT NULL DEFAULT 0.00,
  `minimum_stock_level` decimal(12,2) NOT NULL DEFAULT 1.00,
  `maximum_stock_level` decimal(12,2) DEFAULT NULL,
  `reorder_level` decimal(12,2) NOT NULL DEFAULT 2.00,
  `purchase_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_stock_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `storage_location` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_type` enum('spare_part','consumable','tyre','tool','safety','electrical','body_part','office','other') NOT NULL DEFAULT 'spare_part',
  `condition` enum('new','good','fair','needs_replacement') NOT NULL DEFAULT 'new',
  `vendor_name` varchar(255) DEFAULT NULL,
  `vendor_contact` varchar(15) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `low_stock_alert_sent` tinyint(1) NOT NULL DEFAULT 0,
  `last_restocked_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `tenant_id`, `category_id`, `item_code`, `name`, `description`, `brand`, `model_compatible`, `unit`, `quantity_in_stock`, `minimum_stock_level`, `maximum_stock_level`, `reorder_level`, `purchase_price`, `selling_price`, `total_stock_value`, `storage_location`, `barcode`, `vehicle_id`, `item_type`, `condition`, `vendor_name`, `vendor_contact`, `is_active`, `low_stock_alert_sent`, `last_restocked_at`, `last_used_at`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'ITM-0001', 'Engine Oil 15W40', NULL, 'Castrol', NULL, 'liter', 61.00, 5.00, NULL, 8.00, 450.00, 500.00, 27450.00, 'Rack A-1', NULL, NULL, 'consumable', 'new', 'Castrol Dealer', '9876543210', 1, 0, '2026-04-18 07:45:48', '2026-04-18 07:47:00', '15W40 grade for diesel engines', 2, '2026-04-18 07:43:23', '2026-04-18 07:50:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_type` enum('stock_in','stock_out','adjustment','return','transfer','damage') NOT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `stock_before` decimal(12,2) NOT NULL,
  `stock_after` decimal(12,2) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `vendor_contact` varchar(15) DEFAULT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `received_by` varchar(255) DEFAULT NULL,
  `issued_to` varchar(255) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `tenant_id`, `item_id`, `transaction_type`, `quantity`, `stock_before`, `stock_after`, `unit_price`, `total_price`, `reference_type`, `reference_id`, `reference_number`, `vendor_name`, `vendor_contact`, `invoice_number`, `transaction_date`, `reason`, `received_by`, `issued_to`, `storage_location`, `notes`, `document_path`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'stock_in', 50.00, 20.00, 70.00, 450.00, 22500.00, NULL, NULL, NULL, 'Castrol Dealer', NULL, 'INV-2026-0123', '2026-04-13', NULL, 'Suresh Kumar', NULL, 'Rack A-1', 'Monthly stock purchase', 'tenants/1/inventory-docs/inv-doc-1-1776518738.jpg', 2, '2026-04-18 07:45:48', '2026-04-18 07:55:38'),
(2, 1, 1, 'stock_out', 6.00, 70.00, 64.00, 450.00, 2700.00, 'Vehicle', 1, 'UP32AB1234', NULL, NULL, NULL, '2026-04-13', 'Oil change — UP32AB1234', NULL, 'Mohan Driver', NULL, NULL, NULL, 2, '2026-04-18 07:47:00', '2026-04-18 07:47:00'),
(3, 1, 1, 'adjustment', 4.00, 64.00, 60.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13', 'Physical count mismatch — corrected after audit', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-18 07:48:09', '2026-04-18 07:48:09'),
(4, 1, 1, 'return', 2.00, 60.00, 62.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13', 'Unused oil returned from trip', NULL, 'Mohan Driver', NULL, NULL, NULL, 2, '2026-04-18 07:49:12', '2026-04-18 07:49:12'),
(5, 1, 1, 'damage', 1.00, 62.00, 61.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-13', 'Bottle broken during storage', NULL, NULL, NULL, NULL, NULL, 2, '2026-04-18 07:50:22', '2026-04-18 07:50:22');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `lead_number` varchar(255) NOT NULL,
  `enquiry_date` date NOT NULL,
  `trip_route` varchar(255) NOT NULL,
  `trip_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `duration_days` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `vehicle_type` varchar(255) NOT NULL,
  `seating_capacity` int(10) UNSIGNED NOT NULL,
  `number_of_vehicles` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `pickup_address` text NOT NULL,
  `destination_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`destination_points`)),
  `customer_name` varchar(255) NOT NULL,
  `customer_contact` varchar(15) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quoted_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `advance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_gst` tinyint(1) NOT NULL DEFAULT 0,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_with_tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `quotation_path` varchar(255) DEFAULT NULL,
  `bill_path` varchar(255) DEFAULT NULL,
  `quotation_sent_at` timestamp NULL DEFAULT NULL,
  `status` enum('new','contacted','followup','quoted','confirmed','converted','lost','cancelled') NOT NULL DEFAULT 'new',
  `source` enum('phone','website','whatsapp','email','walkin','reference','other') NOT NULL DEFAULT 'phone',
  `notes` text DEFAULT NULL,
  `followup_date` date DEFAULT NULL,
  `followup_notes` text DEFAULT NULL,
  `converted_trip_id` bigint(20) UNSIGNED DEFAULT NULL,
  `converted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `tenant_id`, `lead_number`, `enquiry_date`, `trip_route`, `trip_date`, `return_date`, `duration_days`, `vehicle_type`, `seating_capacity`, `number_of_vehicles`, `pickup_address`, `destination_points`, `customer_name`, `customer_contact`, `customer_email`, `customer_id`, `quoted_amount`, `advance_amount`, `is_gst`, `gst_percent`, `tax_amount`, `discount`, `total_with_tax`, `quotation_path`, `bill_path`, `quotation_sent_at`, `status`, `source`, `notes`, `followup_date`, `followup_notes`, `converted_trip_id`, `converted_at`, `created_by`, `assigned_to`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'LID-2026-0001', '2026-04-06', 'Lucknow to Delhi', '2026-05-10', '2026-05-12', 2, 'bus', 32, 1, 'Hazratganj, Lucknow', '[\"Kanpur\",\"Agra\",\"Delhi\"]', 'Rahul Sharma', '9888888888', 'rahul@example.com', NULL, 28000.00, 0.00, 0, 0.00, 0.00, 0.00, 28000.00, 'tenants/1/quotations/quotation-LID-2026-0001.pdf', 'tenants/1/lead-bills/bill-1.pdf', '2026-04-06 07:01:25', 'converted', 'phone', 'Updated: customer needs AC + music system', '2026-04-09', 'Call customer again tomorrow at 11 AM', 2, '2026-04-06 06:21:05', 2, NULL, '2026-04-06 06:01:42', '2026-04-14 23:18:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_03_153903_create_personal_access_tokens_table', 1),
(5, '2026_04_03_153957_create_permission_tables', 1),
(6, '2026_04_03_154738_create_tenants_table', 1),
(7, '2026_04_03_154739_add_fields_to_users_table', 1),
(8, '2026_04_03_154740_create_customers_table', 1),
(9, '2026_04_03_154741_create_vehicles_table', 1),
(10, '2026_04_03_154742_create_staff_table', 1),
(11, '2026_04_03_154743_create_trips_table', 1),
(12, '2026_04_03_154746_create_trip_payments_table', 1),
(13, '2026_04_04_121428_create_otps_table', 2),
(14, '2026_04_06_110906_create_leads_table', 3),
(15, '2026_04_06_121603_add_quotation_fields_to_leads_table', 4),
(16, '2026_04_07_061629_create_vehicle_fuel_logs_table', 5),
(17, '2026_04_07_061632_create_vehicle_maintenance_logs_table', 5),
(18, '2026_04_07_061633_create_vehicle_documents_table', 5),
(19, '2026_04_07_061634_create_vehicle_spare_parts_table', 5),
(20, '2026_04_07_061639_create_vehicle_ledgers_table', 5),
(21, '2026_04_08_092115_create_staff_attendance_table', 6),
(22, '2026_04_08_092116_create_staff_salaries_table', 6),
(23, '2026_04_08_092117_create_staff_advances_table', 6),
(24, '2026_04_08_092119_create_staff_documents_table', 6),
(25, '2026_04_08_092123_create_staff_da_logs_table', 6),
(26, '2026_04_08_122342_add_salary_fields_to_staff_table', 7),
(27, '2026_04_09_151622_create_corporates_table', 8),
(28, '2026_04_09_151628_create_corporate_duties_table', 8),
(29, '2026_04_09_151629_create_corporate_payments_table', 8),
(30, '2026_04_09_151647_create_corporate_fines_table', 8),
(31, '2026_04_14_084502_create_template_logs_table', 9),
(32, '2026_04_18_055614_create_cash_book_entries_table', 10),
(33, '2026_04_18_055615_create_online_payments_table', 10),
(34, '2026_04_18_055623_create_payment_qrs_table', 10),
(35, '2026_04_18_120655_create_inventory_categories_table', 11),
(36, '2026_04_18_120656_create_inventory_items_table', 11),
(37, '2026_04_18_120659_create_inventory_transactions_table', 11),
(38, '2026_04_21_105739_remove_plan_columns_from_tenants_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `online_payments`
--

CREATE TABLE `online_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `gateway` enum('razorpay','paytm','phonepe','googlepay','upi_direct','neft','rtgs','imps','bank_transfer','other') NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `gateway_order_id` varchar(255) DEFAULT NULL,
  `gateway_payment_id` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'INR',
  `payer_name` varchar(255) DEFAULT NULL,
  `payer_contact` varchar(15) DEFAULT NULL,
  `payer_upi_id` varchar(255) DEFAULT NULL,
  `payer_bank` varchar(255) DEFAULT NULL,
  `status` enum('pending','success','failed','refunded','partially_refunded') NOT NULL DEFAULT 'pending',
  `refund_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_at` timestamp NULL DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `alert_sent` tinyint(1) NOT NULL DEFAULT 0,
  `alert_sent_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `online_payments`
--

INSERT INTO `online_payments` (`id`, `tenant_id`, `gateway`, `reference_type`, `reference_id`, `reference_number`, `transaction_id`, `gateway_order_id`, `gateway_payment_id`, `amount`, `currency`, `payer_name`, `payer_contact`, `payer_upi_id`, `payer_bank`, `status`, `refund_amount`, `paid_at`, `gateway_response`, `failure_reason`, `alert_sent`, `alert_sent_at`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'upi_direct', 'Trip', 1, 'TRP-2026-0001', 'UTR987654321', NULL, NULL, 15000.00, 'INR', 'Rahul Sharma', '9888888888', 'rahul@paytm', NULL, 'partially_refunded', 5000.00, '2026-04-13 09:30:00', NULL, NULL, 0, NULL, NULL, 2, '2026-04-18 01:32:19', '2026-04-18 04:01:54');

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `type` enum('login','forgot_password') NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `email`, `otp`, `type`, `is_used`, `expires_at`, `created_at`, `updated_at`) VALUES
(5, 'sachinve4@gmail.com', '586907', 'forgot_password', 1, '2026-04-06 09:38:03', '2026-04-06 04:02:51', '2026-04-06 04:08:03'),
(18, 'admin@shivtravels.com', '238138', 'login', 1, '2026-04-18 13:11:16', '2026-04-18 07:40:41', '2026-04-18 07:41:16'),
(19, 'sachinve4@gmail.com', '767122', 'login', 1, '2026-04-21 09:44:43', '2026-04-21 04:12:52', '2026-04-21 04:14:43');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_qrs`
--

CREATE TABLE `payment_qrs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `qr_type` enum('trip_payment','advance_collection','corporate_payment','general') NOT NULL DEFAULT 'general',
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `upi_id` varchar(255) NOT NULL,
  `payee_name` varchar(255) NOT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `transaction_note` varchar(255) DEFAULT NULL,
  `currency` varchar(255) NOT NULL DEFAULT 'INR',
  `qr_image_path` varchar(255) DEFAULT NULL,
  `upi_deep_link` varchar(255) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `send_alert` tinyint(1) NOT NULL DEFAULT 1,
  `alert_contact` varchar(15) DEFAULT NULL,
  `alert_sent` tinyint(1) NOT NULL DEFAULT 0,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_qrs`
--

INSERT INTO `payment_qrs` (`id`, `tenant_id`, `qr_type`, `reference_type`, `reference_id`, `reference_number`, `upi_id`, `payee_name`, `amount`, `transaction_note`, `currency`, `qr_image_path`, `upi_deep_link`, `expires_at`, `is_active`, `send_alert`, `alert_contact`, `alert_sent`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'trip_payment', 'Trip', 1, 'TRP-2026-0001', 'shivtravels@paytm', 'Shiv Travels', 25000.00, 'Trip TRP-2026-0001 payment', 'INR', NULL, 'upi://pay?pa=shivtravels%40paytm&pn=Shiv%2BTravels&cu=INR&am=25000&tn=Trip%2BTRP-2026-0001%2Bpayment', '2026-04-25 18:29:59', 0, 1, '7800060691', 1, 2, '2026-04-18 04:10:38', '2026-04-18 05:00:50');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(21, 'App\\Models\\User', 2, 'api-token', 'fe7cbea01cb457e2d3f080160fb44fcde62cfa8c02260b54e807afc0dcae57c8', '[\"*\"]', '2026-04-18 07:55:38', NULL, '2026-04-18 07:41:17', '2026-04-18 07:55:38'),
(22, 'App\\Models\\User', 1, 'api-token', '4e47980c66abccb76da6b8ef288145ca32eaa2c80131f3ec9ae4d56b82848518', '[\"*\"]', '2026-04-21 04:49:58', NULL, '2026-04-21 04:14:43', '2026-04-21 04:49:58');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('BJsRnK8JdB7T5jhdakOyEHSe8VjyDNKC5S7gzoYA', NULL, '127.0.0.1', 'PostmanRuntime/7.53.0', 'eyJfdG9rZW4iOiIzenhhZWh2bmlkaFA5cmtJTUNJOWVGelFOdnF4MThoT3Faalk4V25vIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776504805),
('MEj6r5sAadRfqJjv9Yuel1hhHGHWj9lI56G7Eatm', NULL, '127.0.0.1', 'PostmanRuntime/7.53.0', 'eyJfdG9rZW4iOiJja1JNVk9PNm5jTlNkS1NkSzl6UDVYS0tCaFU1bmVNS20zajMzdmVOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1775655748),
('MlNZEiPqDcKKwQ5npeJJ9T9jtix7w2JfSXORoEEx', NULL, '127.0.0.1', 'PostmanRuntime/7.53.0', 'eyJfdG9rZW4iOiJGYWMyNjV4YjFhZ1JhaUZwM2ZxUlZCWU84V09RcFR0UE1vM0J1YWI5IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1775466359),
('PItL0geG9GdwqLeQByGn5cyFetohb9zyMNzvzlqj', NULL, '127.0.0.1', 'PostmanRuntime/7.53.0', 'eyJfdG9rZW4iOiJhZkRDdmtmUHdTeFFIS0dzOGVRaERjMDduRzQzeFFFZElGR2loSjB4IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1775565152),
('swuBFbWjsTGiWphAt7ztuhJ7wFpd6Uw0mc8rOG55', NULL, '127.0.0.1', 'PostmanRuntime/7.53.0', 'eyJfdG9rZW4iOiJZcWFXWUxNc1YxNUlsRG82cmtPRW85akRudTRUeXRYYTNIeXJqYkxZIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776518719);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `staff_type` enum('driver','helper','office') NOT NULL,
  `license_number` varchar(255) DEFAULT NULL,
  `license_expiry` date DEFAULT NULL,
  `license_type` varchar(255) DEFAULT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `da_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bank_name` varchar(255) DEFAULT NULL,
  `bank_account` varchar(255) DEFAULT NULL,
  `bank_ifsc` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `tenant_id`, `user_id`, `name`, `phone`, `email`, `date_of_birth`, `date_of_joining`, `address`, `emergency_contact`, `emergency_contact_name`, `staff_type`, `license_number`, `license_expiry`, `license_type`, `basic_salary`, `da_per_day`, `hra`, `other_allowance`, `bank_name`, `bank_account`, `bank_ifsc`, `notes`, `is_available`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 4, 'Mohan Driver', '9111111111', NULL, NULL, NULL, NULL, NULL, NULL, 'driver', 'UP32-20200012345', '2029-04-03', NULL, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, 0, 1, '2026-04-03 10:43:32', '2026-04-03 11:30:39', NULL),
(2, 1, NULL, 'Mohan Kumar', '9111111111', 'mohan@example.com', '1990-05-15', '2022-01-01', 'Ram Nagar, Lucknow', '9222222222', 'Sohan Kumar', 'driver', 'UP32-20200012345', '2028-12-31', 'Heavy Vehicle', 18000.00, 300.00, 2000.00, 0.00, 'SBI', '1234567890', 'SBIN0001234', NULL, 1, 1, '2026-04-08 08:04:25', '2026-04-08 08:04:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `staff_advances`
--

CREATE TABLE `staff_advances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `advance_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `payment_mode` enum('cash','bank','upi') NOT NULL DEFAULT 'cash',
  `transaction_ref` varchar(255) DEFAULT NULL,
  `is_deducted` tinyint(1) NOT NULL DEFAULT 0,
  `salary_id` bigint(20) UNSIGNED DEFAULT NULL,
  `deducted_on` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_advances`
--

INSERT INTO `staff_advances` (`id`, `tenant_id`, `staff_id`, `amount`, `advance_date`, `reason`, `payment_mode`, `transaction_ref`, `is_deducted`, `salary_id`, `deducted_on`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 5000.00, '2026-04-08', 'Medical emergency', 'cash', NULL, 1, 1, '2026-04-30', NULL, 2, '2026-04-08 08:16:21', '2026-04-08 08:19:48');

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` enum('present','absent','half_day','on_trip','leave','holiday') NOT NULL DEFAULT 'present',
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `working_hours` decimal(5,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `marked_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_attendance`
--

INSERT INTO `staff_attendance` (`id`, `tenant_id`, `staff_id`, `date`, `status`, `check_in`, `check_out`, `working_hours`, `notes`, `marked_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-04-08', 'present', '09:00:00', '18:00:00', -9.00, NULL, 2, '2026-04-08 08:09:35', '2026-04-08 08:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `staff_da_logs`
--

CREATE TABLE `staff_da_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `da_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `trip_days` int(11) NOT NULL DEFAULT 1,
  `da_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `paid_on` date DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_da_logs`
--

INSERT INTO `staff_da_logs` (`id`, `tenant_id`, `staff_id`, `trip_id`, `da_amount`, `trip_days`, `da_per_day`, `extra_allowance`, `notes`, `status`, `paid_on`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 200.00, 2, 0.00, 200.00, NULL, 'paid', '2026-04-30', 2, '2026-04-08 08:13:53', '2026-04-08 08:19:48');

-- --------------------------------------------------------

--
-- Table structure for table `staff_documents`
--

CREATE TABLE `staff_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('aadhar','pan','license','photo','address_proof','bank_passbook','other') NOT NULL,
  `document_number` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_documents`
--

INSERT INTO `staff_documents` (`id`, `tenant_id`, `staff_id`, `document_type`, `document_number`, `document_path`, `expiry_date`, `is_verified`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'aadhar', '1234-5678-9012', 'tenants/1/staff-docs/1/staff-1-aadhar-1775656388.pdf', NULL, 0, NULL, 2, '2026-04-08 08:23:10', '2026-04-08 08:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `staff_salaries`
--

CREATE TABLE `staff_salaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `month` varchar(255) NOT NULL,
  `year` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `da_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `bonus` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_allowance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `absent_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_deduction` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_days` int(11) NOT NULL DEFAULT 0,
  `present_days` int(11) NOT NULL DEFAULT 0,
  `absent_days` int(11) NOT NULL DEFAULT 0,
  `half_days` int(11) NOT NULL DEFAULT 0,
  `trip_days` int(11) NOT NULL DEFAULT 0,
  `payment_status` enum('pending','paid','partial') NOT NULL DEFAULT 'pending',
  `payment_mode` enum('cash','bank','upi') DEFAULT NULL,
  `paid_on` date DEFAULT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_salaries`
--

INSERT INTO `staff_salaries` (`id`, `tenant_id`, `staff_id`, `month`, `year`, `basic_salary`, `hra`, `da_total`, `bonus`, `other_allowance`, `gross_salary`, `advance_deduction`, `absent_deduction`, `other_deduction`, `total_deduction`, `net_salary`, `total_days`, `present_days`, `absent_days`, `half_days`, `trip_days`, `payment_status`, `payment_mode`, `paid_on`, `transaction_ref`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-04', 2026, 0.00, 0.00, 200.00, 2000.00, 0.00, 2200.00, 5000.00, 0.00, 0.00, 5000.00, -2800.00, 30, 1, 0, 0, 0, 'paid', 'bank', '2026-04-30', 'TXN123456', 'April 2026 salary', 2, '2026-04-08 08:18:14', '2026-04-08 08:19:48');

-- --------------------------------------------------------

--
-- Table structure for table `template_logs`
--

CREATE TABLE `template_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `template_type` enum('invoice_gst','invoice_non_gst','letterhead','quotation','einvoice') NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `irn` varchar(255) DEFAULT NULL,
  `ack_number` varchar(255) DEFAULT NULL,
  `ack_date` timestamp NULL DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `einvoice_status` enum('not_uploaded','uploaded','failed','cancelled') NOT NULL DEFAULT 'not_uploaded',
  `einvoice_response` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_logs`
--

INSERT INTO `template_logs` (`id`, `tenant_id`, `template_type`, `reference_type`, `reference_id`, `reference_number`, `file_path`, `file_name`, `irn`, `ack_number`, `ack_date`, `qr_code_path`, `einvoice_status`, `einvoice_response`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'invoice_gst', 'Trip', 1, 'TRP-2026-0001', 'tenants/1/invoices/gst/invoice-gst-TRP-2026-0001.pdf', 'invoice-gst-TRP-2026-0001.pdf', NULL, NULL, NULL, NULL, 'not_uploaded', NULL, 2, '2026-04-14 23:14:27', '2026-04-14 23:14:27'),
(2, 1, 'invoice_non_gst', 'Trip', 1, 'TRP-2026-0001', 'tenants/1/invoices/non-gst/invoice-TRP-2026-0001.pdf', 'invoice-TRP-2026-0001.pdf', NULL, NULL, NULL, NULL, 'not_uploaded', NULL, 2, '2026-04-14 23:15:49', '2026-04-14 23:15:49'),
(3, 1, 'letterhead', NULL, NULL, NULL, 'tenants/1/letterheads/letterhead-20260415-044713.pdf', 'letterhead-20260415-044713.pdf', NULL, NULL, NULL, NULL, 'not_uploaded', NULL, 2, '2026-04-14 23:17:13', '2026-04-14 23:17:13'),
(4, 1, 'quotation', 'Lead', 1, 'LID-2026-0001', 'tenants/1/quotations/quotation-LID-2026-0001.pdf', 'quotation-LID-2026-0001.pdf', NULL, NULL, NULL, NULL, 'not_uploaded', NULL, 2, '2026-04-14 23:18:29', '2026-04-14 23:18:29'),
(5, 1, 'quotation', NULL, NULL, NULL, 'tenants/1/quotations/quotation-custom-20260415044953.pdf', 'quotation-custom-20260415044954.pdf', NULL, NULL, NULL, NULL, 'not_uploaded', NULL, 2, '2026-04-14 23:19:54', '2026-04-14 23:19:54'),
(6, 1, 'einvoice', 'Trip', 1, 'TRP-2026-0001', 'tenants/1/einvoices/einvoice-TRP-2026-0001.pdf', 'einvoice-TRP-2026-0001.pdf', 'a5c12d8f9e3b7a4c1d2e6f0a8b3c5d7e9f1a2b4c6d8e0f2a4b6c8d0e2f4a6b8', '112345678901234', '2026-04-14 23:23:53', 'tenants/1/einvoice-qr/qr-TRP-2026-0001.png', 'uploaded', '{\"success\":true,\"irn\":\"a5c12d8f9e3b7a4c1d2e6f0a8b3c5d7e9f1a2b4c6d8e0f2a4b6c8d0e2f4a6b8\",\"ack_number\":\"112345678901234\",\"ack_date\":\"2026-04-15 04:53:53\",\"signed_qr\":\"MOCK_QR_DATA_69df19e136bad\"}', 2, '2026-04-14 23:23:53', '2026-04-14 23:23:56');

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gstin` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `company_name`, `email`, `phone`, `gstin`, `address`, `logo_path`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Shiv Travels Lucknow', 'admin@shivtravels.com', '9876543210', '09AAAAA0000A1Z5', NULL, NULL, 1, '2026-04-03 10:43:31', '2026-04-04 02:44:15', NULL),
(2, 'New Travels Co', 'newadmin@travels.com', '9000000001', NULL, NULL, NULL, 1, '2026-04-03 10:47:17', '2026-04-03 10:47:17', NULL),
(3, 'Krishna Tours Varanasi', 'admin@krishnatours.com', '9123456789', '09BBBBB1111B2Z6', 'Sigra, Varanasi', NULL, 1, '2026-04-04 02:13:39', '2026-04-04 02:13:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `trip_number` varchar(255) NOT NULL,
  `trip_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `duration_days` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `trip_route` varchar(255) NOT NULL,
  `pickup_address` text NOT NULL,
  `destination_points` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`destination_points`)),
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_type` varchar(255) NOT NULL,
  `seating_capacity` int(10) UNSIGNED NOT NULL,
  `number_of_vehicles` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_contact` varchar(15) NOT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `helper_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_km` decimal(10,2) DEFAULT NULL,
  `end_km` decimal(10,2) DEFAULT NULL,
  `total_km` decimal(10,2) DEFAULT NULL,
  `km_grade` char(1) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `advance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `part_payment` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_gst` tinyint(1) NOT NULL DEFAULT 0,
  `gst_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `status` enum('scheduled','ongoing','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `duty_slip_path` varchar(255) DEFAULT NULL,
  `invoice_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `tenant_id`, `trip_number`, `trip_date`, `return_date`, `duration_days`, `trip_route`, `pickup_address`, `destination_points`, `vehicle_id`, `vehicle_type`, `seating_capacity`, `number_of_vehicles`, `customer_id`, `customer_name`, `customer_contact`, `driver_id`, `helper_id`, `start_km`, `end_km`, `total_km`, `km_grade`, `total_amount`, `advance_amount`, `part_payment`, `balance_amount`, `discount`, `is_gst`, `gst_percent`, `tax_amount`, `payment_status`, `status`, `duty_slip_path`, `invoice_path`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'TRP-2026-0001', '2026-04-10', NULL, 2, 'Lucknow → Delhi', 'Hazratganj, Lucknow', '[\"Kanpur\",\"Agra\",\"Delhi\"]', 1, 'bus', 32, 1, 1, 'Rahul Sharma', '9888888888', 1, NULL, 45000.00, 45680.00, 680.00, 'D', 28000.00, 0.00, 8000.00, 21400.00, 0.00, 1, 5.00, 1400.00, 'partial', 'ongoing', 'tenants/1/duty-slips/trip-1.pdf', 'tenants/1/invoices/non-gst/invoice-TRP-2026-0001.pdf', 'Updated: need extra vehicle', 2, '2026-04-03 11:30:39', '2026-04-14 23:15:49', NULL),
(2, 1, 'TRP-2026-0002', '2026-05-10', '2026-05-12', 2, 'Lucknow to Delhi', 'Hazratganj, Lucknow', '[\"Kanpur\",\"Agra\",\"Delhi\"]', 1, 'bus', 32, 1, 1, 'Rahul Sharma', '9888888888', 1, NULL, NULL, NULL, NULL, NULL, 28000.00, 10000.00, 0.00, 18900.00, 500.00, 1, 5.00, 1400.00, 'partial', 'scheduled', NULL, NULL, 'Confirmed booking', 2, '2026-04-06 06:21:05', '2026-04-06 06:21:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trip_payments`
--

CREATE TABLE `trip_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `trip_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` enum('advance','part','final') NOT NULL,
  `mode` enum('cash','online','cheque','upi') NOT NULL DEFAULT 'cash',
  `reference` varchar(100) DEFAULT NULL,
  `paid_on` date NOT NULL,
  `collected_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trip_payments`
--

INSERT INTO `trip_payments` (`id`, `tenant_id`, `trip_id`, `amount`, `type`, `mode`, `reference`, `paid_on`, `collected_by`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 8000.00, 'part', 'upi', 'UTR123456789', '2026-04-11', 'Ramesh Sharma', 'Second installment', 2, '2026-04-04 05:18:45', '2026-04-04 05:18:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `role` enum('superadmin','admin','operator','driver','accountant') NOT NULL DEFAULT 'operator',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `role`, `is_active`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'superadmin', 1, 'Super Admin', 'sachinve4@gmail.com', NULL, '$2y$12$38naGwGMoCYNgclSZ5MkTOjXpKE3GxvYF/Zd85GHcpmA7mRvt/1Sm', NULL, '2026-04-03 10:43:31', '2026-04-06 04:08:03'),
(2, 1, 'admin', 1, 'Ramesh Admin', 'admin@shivtravels.com', NULL, '$2y$12$0/8SS40f7ANjQNqYBiahouxO87hQ6mJces9sZnf6Q0ditXzLJetpe', NULL, '2026-04-03 10:43:32', '2026-04-03 10:43:32'),
(3, 1, 'operator', 1, 'Suresh Operator', 'operator@shivtravels.com', NULL, '$2y$12$GB8vDcMlSXUPcMLqVr4jbOkJt2bIcStN379qBTnFXgPeGN98en4ku', NULL, '2026-04-03 10:43:32', '2026-04-03 10:43:32'),
(4, 1, 'driver', 1, 'Mohan Driver', 'driver@shivtravels.com', NULL, '$2y$12$ApKwMwENOGofl.VuwvIALOIp7WVzOgFE9ui2jcz/wJZEfu5Aqc2pm', NULL, '2026-04-03 10:43:32', '2026-04-03 10:43:32'),
(5, 2, 'admin', 1, 'New Admin', 'newadmin@travels.com', NULL, '$2y$12$pfVL0JtWBT5SJNrk4jGzC.8IBpH7Eh1azw/IF33H0EATWRERRNL8a', NULL, '2026-04-03 10:47:18', '2026-04-03 10:47:18'),
(6, 3, 'admin', 1, 'Suresh Yadav', 'admin@krishnatours.com', NULL, '$2y$12$3zoogjI6kZ1ahxR74q3UJO0tzB9e29JXll/CiqwsZi4cqdquHl3t6', NULL, '2026-04-04 02:13:40', '2026-04-04 02:13:40');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `registration_number` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `seating_capacity` int(10) UNSIGNED NOT NULL,
  `make` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `fuel_type` enum('diesel','petrol','cng','electric') NOT NULL DEFAULT 'diesel',
  `current_km` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `tenant_id`, `registration_number`, `type`, `seating_capacity`, `make`, `model`, `fuel_type`, `current_km`, `is_available`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'UP32AB1234', 'bus', 32, 'Tata', 'LP 909', 'diesel', 45680.00, 0, 1, '2026-04-03 10:43:32', '2026-04-07 07:08:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_documents`
--

CREATE TABLE `vehicle_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `document_type` enum('rc','insurance','pollution','permit','fitness','tax','other') NOT NULL,
  `document_number` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date NOT NULL,
  `alert_before_days` int(10) UNSIGNED NOT NULL DEFAULT 30,
  `is_expired` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_documents`
--

INSERT INTO `vehicle_documents` (`id`, `tenant_id`, `vehicle_id`, `document_type`, `document_number`, `document_path`, `issue_date`, `expiry_date`, `alert_before_days`, `is_expired`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'insurance', 'POL123456789', 'tenants/1/vehicle-docs/1/doc-1-1775566348.pdf', '2025-04-01', '2027-05-02', 30, 0, NULL, 2, '2026-04-07 07:22:29', '2026-04-07 07:22:29');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_fuel_logs`
--

CREATE TABLE `vehicle_fuel_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `fuel_type` enum('diesel','petrol','cng','adblue','electric') NOT NULL DEFAULT 'diesel',
  `quantity_liters` decimal(8,2) NOT NULL,
  `price_per_liter` decimal(8,2) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `km_at_fill` decimal(10,2) NOT NULL,
  `km_since_last_fill` decimal(10,2) DEFAULT NULL,
  `fuel_efficiency` decimal(8,2) DEFAULT NULL,
  `fuel_station` varchar(255) DEFAULT NULL,
  `payment_mode` enum('cash','card','upi','account') NOT NULL DEFAULT 'cash',
  `bill_number` varchar(255) DEFAULT NULL,
  `bill_image` varchar(255) DEFAULT NULL,
  `filled_on` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_fuel_logs`
--

INSERT INTO `vehicle_fuel_logs` (`id`, `tenant_id`, `vehicle_id`, `fuel_type`, `quantity_liters`, `price_per_liter`, `total_cost`, `km_at_fill`, `km_since_last_fill`, `fuel_efficiency`, `fuel_station`, `payment_mode`, `bill_number`, `bill_image`, `filled_on`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'diesel', 80.00, 92.50, 7400.00, 45680.00, NULL, NULL, 'HP Petrol Pump, Lucknow', 'cash', 'BILL001', NULL, '2026-04-07', NULL, 2, '2026-04-07 07:08:44', '2026-04-07 07:08:44'),
(2, 1, 1, 'adblue', 10.00, 28.00, 280.00, 45680.00, 0.00, NULL, NULL, 'cash', NULL, NULL, '2026-04-07', 'AdBlue refill', 2, '2026-04-07 07:16:14', '2026-04-07 07:16:14');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_ledgers`
--

CREATE TABLE `vehicle_ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `entry_type` enum('income','expense') NOT NULL,
  `category` enum('trip_income','fuel','maintenance','repair','spare_part','document','driver_da','toll','other_income','other_expense') NOT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `entry_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_ledgers`
--

INSERT INTO `vehicle_ledgers` (`id`, `tenant_id`, `vehicle_id`, `entry_type`, `category`, `reference_type`, `reference_id`, `description`, `amount`, `entry_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'expense', 'fuel', 'VehicleFuelLog', 1, 'Fuel (diesel) - 80.00L', 7400.00, '2026-04-07', NULL, 2, '2026-04-07 07:08:44', '2026-04-07 07:08:44'),
(2, 1, 1, 'expense', 'fuel', 'VehicleFuelLog', 2, 'Fuel (adblue) - 10.00L', 280.00, '2026-04-07', NULL, 2, '2026-04-07 07:16:14', '2026-04-07 07:16:14'),
(3, 1, 1, 'expense', 'maintenance', 'VehicleMaintenanceLog', 1, 'Full Service - Oil change + Filter (service)', 3000.00, '2026-04-05', NULL, 2, '2026-04-07 07:18:01', '2026-04-07 07:18:01'),
(4, 1, 1, 'expense', 'repair', 'VehicleMaintenanceLog', 2, 'Brake pad replacement (repair)', 2300.00, '2026-04-06', NULL, 2, '2026-04-07 07:19:17', '2026-04-07 07:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_maintenance_logs`
--

CREATE TABLE `vehicle_maintenance_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `maintenance_type` enum('repair','service','lubricant','spare_part','tyre','battery','other') NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `labour_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `parts_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `km_at_service` decimal(10,2) DEFAULT NULL,
  `next_service_km` decimal(10,2) DEFAULT NULL,
  `next_service_date` date DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `vendor_contact` varchar(255) DEFAULT NULL,
  `bill_number` varchar(255) DEFAULT NULL,
  `bill_image` varchar(255) DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'completed',
  `service_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_maintenance_logs`
--

INSERT INTO `vehicle_maintenance_logs` (`id`, `tenant_id`, `vehicle_id`, `maintenance_type`, `title`, `description`, `labour_cost`, `parts_cost`, `total_cost`, `km_at_service`, `next_service_km`, `next_service_date`, `vendor_name`, `vendor_contact`, `bill_number`, `bill_image`, `status`, `service_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'service', 'Full Service - Oil change + Filter', 'Engine oil changed, oil filter replaced, air filter cleaned', 800.00, 2200.00, 3000.00, 45000.00, 50000.00, '2026-07-01', 'Tata Motors Service, Lucknow', '9876543210', 'SRV001', NULL, 'completed', '2026-04-05', NULL, 2, '2026-04-07 07:18:01', '2026-04-07 07:18:01'),
(2, 1, 1, 'repair', 'Brake pad replacement', NULL, 500.00, 1800.00, 2300.00, NULL, NULL, NULL, 'Sharma Auto Works', NULL, NULL, NULL, 'completed', '2026-04-06', NULL, 2, '2026-04-07 07:19:17', '2026-04-07 07:19:17');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_spare_parts`
--

CREATE TABLE `vehicle_spare_parts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED DEFAULT NULL,
  `part_name` varchar(255) NOT NULL,
  `part_number` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity_in_stock` int(11) NOT NULL DEFAULT 0,
  `minimum_stock_alert` int(11) NOT NULL DEFAULT 2,
  `unit` varchar(255) NOT NULL DEFAULT 'piece',
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `condition` enum('good','fair','needs_replacement') NOT NULL DEFAULT 'good',
  `last_replaced_on` date DEFAULT NULL,
  `km_at_replacement` decimal(10,2) DEFAULT NULL,
  `replacement_interval_km` decimal(10,2) DEFAULT NULL,
  `vendor_name` varchar(255) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_spare_parts`
--

INSERT INTO `vehicle_spare_parts` (`id`, `tenant_id`, `vehicle_id`, `part_name`, `part_number`, `category`, `quantity_in_stock`, `minimum_stock_alert`, `unit`, `unit_price`, `total_value`, `condition`, `last_replaced_on`, `km_at_replacement`, `replacement_interval_km`, `vendor_name`, `is_available`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Engine Oil', 'OIL-15W40', 'engine', 10, 3, 'liter', 450.00, 4500.00, 'good', NULL, NULL, NULL, 'Castrol Dealer', 1, NULL, 2, '2026-04-07 07:24:18', '2026-04-07 07:24:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `cash_book_entries`
--
ALTER TABLE `cash_book_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cash_book_entries_created_by_foreign` (`created_by`),
  ADD KEY `cash_book_entries_tenant_id_entry_date_index` (`tenant_id`,`entry_date`),
  ADD KEY `cash_book_entries_tenant_id_category_index` (`tenant_id`,`category`);

--
-- Indexes for table `corporates`
--
ALTER TABLE `corporates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `corporates_tenant_id_foreign` (`tenant_id`),
  ADD KEY `corporates_created_by_foreign` (`created_by`);

--
-- Indexes for table `corporate_duties`
--
ALTER TABLE `corporate_duties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `corporate_duties_duty_number_unique` (`duty_number`),
  ADD KEY `corporate_duties_tenant_id_foreign` (`tenant_id`),
  ADD KEY `corporate_duties_corporate_id_foreign` (`corporate_id`),
  ADD KEY `corporate_duties_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `corporate_duties_driver_id_foreign` (`driver_id`),
  ADD KEY `corporate_duties_helper_id_foreign` (`helper_id`),
  ADD KEY `corporate_duties_created_by_foreign` (`created_by`);

--
-- Indexes for table `corporate_fines`
--
ALTER TABLE `corporate_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `corporate_fines_tenant_id_foreign` (`tenant_id`),
  ADD KEY `corporate_fines_corporate_id_foreign` (`corporate_id`),
  ADD KEY `corporate_fines_duty_id_foreign` (`duty_id`),
  ADD KEY `corporate_fines_payment_id_foreign` (`payment_id`),
  ADD KEY `corporate_fines_created_by_foreign` (`created_by`);

--
-- Indexes for table `corporate_payments`
--
ALTER TABLE `corporate_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `corporate_payments_invoice_number_unique` (`invoice_number`),
  ADD KEY `corporate_payments_tenant_id_foreign` (`tenant_id`),
  ADD KEY `corporate_payments_corporate_id_foreign` (`corporate_id`),
  ADD KEY `corporate_payments_created_by_foreign` (`created_by`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_categories_tenant_id_name_unique` (`tenant_id`,`name`),
  ADD KEY `inventory_categories_created_by_foreign` (`created_by`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_items_category_id_foreign` (`category_id`),
  ADD KEY `inventory_items_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `inventory_items_created_by_foreign` (`created_by`),
  ADD KEY `inventory_items_tenant_id_item_type_index` (`tenant_id`,`item_type`),
  ADD KEY `inventory_items_tenant_id_category_id_index` (`tenant_id`,`category_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_transactions_item_id_foreign` (`item_id`),
  ADD KEY `inventory_transactions_created_by_foreign` (`created_by`),
  ADD KEY `inventory_transactions_tenant_id_item_id_index` (`tenant_id`,`item_id`),
  ADD KEY `inventory_transactions_tenant_id_transaction_type_index` (`tenant_id`,`transaction_type`),
  ADD KEY `inventory_transactions_tenant_id_transaction_date_index` (`tenant_id`,`transaction_date`);

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
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `leads_lead_number_unique` (`lead_number`),
  ADD KEY `leads_tenant_id_foreign` (`tenant_id`),
  ADD KEY `leads_customer_id_foreign` (`customer_id`),
  ADD KEY `leads_converted_trip_id_foreign` (`converted_trip_id`),
  ADD KEY `leads_created_by_foreign` (`created_by`),
  ADD KEY `leads_assigned_to_foreign` (`assigned_to`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `online_payments`
--
ALTER TABLE `online_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `online_payments_transaction_id_unique` (`transaction_id`),
  ADD KEY `online_payments_created_by_foreign` (`created_by`),
  ADD KEY `online_payments_tenant_id_status_index` (`tenant_id`,`status`),
  ADD KEY `online_payments_tenant_id_gateway_index` (`tenant_id`,`gateway`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `otps_email_type_index` (`email`,`type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_qrs`
--
ALTER TABLE `payment_qrs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_qrs_tenant_id_foreign` (`tenant_id`),
  ADD KEY `payment_qrs_created_by_foreign` (`created_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_tenant_id_foreign` (`tenant_id`),
  ADD KEY `staff_user_id_foreign` (`user_id`);

--
-- Indexes for table `staff_advances`
--
ALTER TABLE `staff_advances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_advances_tenant_id_foreign` (`tenant_id`),
  ADD KEY `staff_advances_staff_id_foreign` (`staff_id`),
  ADD KEY `staff_advances_salary_id_foreign` (`salary_id`),
  ADD KEY `staff_advances_created_by_foreign` (`created_by`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_attendance_staff_id_date_unique` (`staff_id`,`date`),
  ADD KEY `staff_attendance_tenant_id_foreign` (`tenant_id`),
  ADD KEY `staff_attendance_marked_by_foreign` (`marked_by`);

--
-- Indexes for table `staff_da_logs`
--
ALTER TABLE `staff_da_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_da_logs_tenant_id_foreign` (`tenant_id`),
  ADD KEY `staff_da_logs_staff_id_foreign` (`staff_id`),
  ADD KEY `staff_da_logs_trip_id_foreign` (`trip_id`),
  ADD KEY `staff_da_logs_created_by_foreign` (`created_by`);

--
-- Indexes for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_documents_tenant_id_foreign` (`tenant_id`),
  ADD KEY `staff_documents_staff_id_foreign` (`staff_id`),
  ADD KEY `staff_documents_created_by_foreign` (`created_by`);

--
-- Indexes for table `staff_salaries`
--
ALTER TABLE `staff_salaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_salaries_staff_id_month_year_unique` (`staff_id`,`month`,`year`),
  ADD KEY `staff_salaries_tenant_id_foreign` (`tenant_id`),
  ADD KEY `staff_salaries_created_by_foreign` (`created_by`);

--
-- Indexes for table `template_logs`
--
ALTER TABLE `template_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `template_logs_created_by_foreign` (`created_by`),
  ADD KEY `template_logs_tenant_id_template_type_index` (`tenant_id`,`template_type`),
  ADD KEY `template_logs_reference_type_reference_id_index` (`reference_type`,`reference_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tenants_email_unique` (`email`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trips_trip_number_unique` (`trip_number`),
  ADD KEY `trips_tenant_id_foreign` (`tenant_id`),
  ADD KEY `trips_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `trips_customer_id_foreign` (`customer_id`),
  ADD KEY `trips_driver_id_foreign` (`driver_id`),
  ADD KEY `trips_helper_id_foreign` (`helper_id`),
  ADD KEY `trips_created_by_foreign` (`created_by`);

--
-- Indexes for table `trip_payments`
--
ALTER TABLE `trip_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trip_payments_tenant_id_foreign` (`tenant_id`),
  ADD KEY `trip_payments_trip_id_foreign` (`trip_id`),
  ADD KEY `trip_payments_created_by_foreign` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vehicles_registration_number_unique` (`registration_number`),
  ADD KEY `vehicles_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `vehicle_documents`
--
ALTER TABLE `vehicle_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_documents_tenant_id_foreign` (`tenant_id`),
  ADD KEY `vehicle_documents_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `vehicle_documents_created_by_foreign` (`created_by`);

--
-- Indexes for table `vehicle_fuel_logs`
--
ALTER TABLE `vehicle_fuel_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_fuel_logs_tenant_id_foreign` (`tenant_id`),
  ADD KEY `vehicle_fuel_logs_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `vehicle_fuel_logs_created_by_foreign` (`created_by`);

--
-- Indexes for table `vehicle_ledgers`
--
ALTER TABLE `vehicle_ledgers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_ledgers_tenant_id_foreign` (`tenant_id`),
  ADD KEY `vehicle_ledgers_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `vehicle_ledgers_created_by_foreign` (`created_by`);

--
-- Indexes for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_maintenance_logs_tenant_id_foreign` (`tenant_id`),
  ADD KEY `vehicle_maintenance_logs_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `vehicle_maintenance_logs_created_by_foreign` (`created_by`);

--
-- Indexes for table `vehicle_spare_parts`
--
ALTER TABLE `vehicle_spare_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_spare_parts_tenant_id_foreign` (`tenant_id`),
  ADD KEY `vehicle_spare_parts_vehicle_id_foreign` (`vehicle_id`),
  ADD KEY `vehicle_spare_parts_created_by_foreign` (`created_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cash_book_entries`
--
ALTER TABLE `cash_book_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `corporates`
--
ALTER TABLE `corporates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `corporate_duties`
--
ALTER TABLE `corporate_duties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `corporate_fines`
--
ALTER TABLE `corporate_fines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `corporate_payments`
--
ALTER TABLE `corporate_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `online_payments`
--
ALTER TABLE `online_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `payment_qrs`
--
ALTER TABLE `payment_qrs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff_advances`
--
ALTER TABLE `staff_advances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_da_logs`
--
ALTER TABLE `staff_da_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_documents`
--
ALTER TABLE `staff_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff_salaries`
--
ALTER TABLE `staff_salaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `template_logs`
--
ALTER TABLE `template_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trip_payments`
--
ALTER TABLE `trip_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle_documents`
--
ALTER TABLE `vehicle_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle_fuel_logs`
--
ALTER TABLE `vehicle_fuel_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicle_ledgers`
--
ALTER TABLE `vehicle_ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicle_spare_parts`
--
ALTER TABLE `vehicle_spare_parts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cash_book_entries`
--
ALTER TABLE `cash_book_entries`
  ADD CONSTRAINT `cash_book_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cash_book_entries_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `corporates`
--
ALTER TABLE `corporates`
  ADD CONSTRAINT `corporates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `corporates_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `corporate_duties`
--
ALTER TABLE `corporate_duties`
  ADD CONSTRAINT `corporate_duties_corporate_id_foreign` FOREIGN KEY (`corporate_id`) REFERENCES `corporates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `corporate_duties_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `corporate_duties_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `corporate_duties_helper_id_foreign` FOREIGN KEY (`helper_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `corporate_duties_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `corporate_duties_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `corporate_fines`
--
ALTER TABLE `corporate_fines`
  ADD CONSTRAINT `corporate_fines_corporate_id_foreign` FOREIGN KEY (`corporate_id`) REFERENCES `corporates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `corporate_fines_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `corporate_fines_duty_id_foreign` FOREIGN KEY (`duty_id`) REFERENCES `corporate_duties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `corporate_fines_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `corporate_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `corporate_fines_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `corporate_payments`
--
ALTER TABLE `corporate_payments`
  ADD CONSTRAINT `corporate_payments_corporate_id_foreign` FOREIGN KEY (`corporate_id`) REFERENCES `corporates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `corporate_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `corporate_payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_categories`
--
ALTER TABLE `inventory_categories`
  ADD CONSTRAINT `inventory_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_categories_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `inventory_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_items_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_items_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inventory_transactions_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_converted_trip_id_foreign` FOREIGN KEY (`converted_trip_id`) REFERENCES `trips` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `leads_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `online_payments`
--
ALTER TABLE `online_payments`
  ADD CONSTRAINT `online_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `online_payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_qrs`
--
ALTER TABLE `payment_qrs`
  ADD CONSTRAINT `payment_qrs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payment_qrs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `staff_advances`
--
ALTER TABLE `staff_advances`
  ADD CONSTRAINT `staff_advances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `staff_advances_salary_id_foreign` FOREIGN KEY (`salary_id`) REFERENCES `staff_salaries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `staff_advances_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_advances_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD CONSTRAINT `staff_attendance_marked_by_foreign` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `staff_attendance_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_attendance_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_da_logs`
--
ALTER TABLE `staff_da_logs`
  ADD CONSTRAINT `staff_da_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `staff_da_logs_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_da_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_da_logs_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_documents`
--
ALTER TABLE `staff_documents`
  ADD CONSTRAINT `staff_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `staff_documents_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_documents_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_salaries`
--
ALTER TABLE `staff_salaries`
  ADD CONSTRAINT `staff_salaries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `staff_salaries_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_salaries_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `template_logs`
--
ALTER TABLE `template_logs`
  ADD CONSTRAINT `template_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `template_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `trips_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `trips_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trips_helper_id_foreign` FOREIGN KEY (`helper_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trips_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`);

--
-- Constraints for table `trip_payments`
--
ALTER TABLE `trip_payments`
  ADD CONSTRAINT `trip_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `trip_payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trip_payments_trip_id_foreign` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_documents`
--
ALTER TABLE `vehicle_documents`
  ADD CONSTRAINT `vehicle_documents_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicle_documents_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_documents_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_fuel_logs`
--
ALTER TABLE `vehicle_fuel_logs`
  ADD CONSTRAINT `vehicle_fuel_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicle_fuel_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_fuel_logs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_ledgers`
--
ALTER TABLE `vehicle_ledgers`
  ADD CONSTRAINT `vehicle_ledgers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicle_ledgers_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_ledgers_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_maintenance_logs`
--
ALTER TABLE `vehicle_maintenance_logs`
  ADD CONSTRAINT `vehicle_maintenance_logs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicle_maintenance_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_maintenance_logs_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicle_spare_parts`
--
ALTER TABLE `vehicle_spare_parts`
  ADD CONSTRAINT `vehicle_spare_parts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vehicle_spare_parts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vehicle_spare_parts_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
