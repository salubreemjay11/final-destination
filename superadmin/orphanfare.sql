-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Nov 25, 2025 at 05:53 PM
=======
-- Generation Time: Nov 22, 2025 at 12:58 PM
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `orphanfare`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_requests`
--

CREATE TABLE `access_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `requested_module` varchar(100) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `admin_id`, `user_id`, `notification_type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-15 06:11:45'),
(2, 7, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-15 06:11:45'),
(3, 8, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-15 06:11:45'),
(4, 9, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-15 06:11:45'),
(5, 1, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:11:05'),
(6, 7, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:11:05'),
(7, 8, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:11:05'),
(8, 9, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:11:05'),
(9, 1, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:12:02'),
(10, 7, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:12:02'),
(11, 8, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:12:02'),
(12, 9, 11, 'failed_attempts', 'Multiple Failed Login Attempts', 'User akawa@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-17 14:12:02'),
(13, 1, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-17 14:12:14'),
(14, 7, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-17 14:12:14'),
(15, 8, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-17 14:12:14'),
(16, 9, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-17 14:12:14'),
(17, 1, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:34'),
(18, 7, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:34'),
(19, 8, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:34'),
(20, 9, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:34'),
(21, 1, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:44'),
(22, 7, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:44'),
(23, 8, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:44'),
(24, 9, 8, 'failed_attempts', 'Multiple Failed Login Attempts', 'User admin@orphanfare.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:46:44'),
(25, 1, 8, 'account_lockout', 'Account Lockout Alert', 'User admin@orphanfare.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:46:48'),
(26, 7, 8, 'account_lockout', 'Account Lockout Alert', 'User admin@orphanfare.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:46:48'),
(27, 8, 8, 'account_lockout', 'Account Lockout Alert', 'User admin@orphanfare.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:46:48'),
(28, 9, 8, 'account_lockout', 'Account Lockout Alert', 'User admin@orphanfare.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:46:48'),
(29, 1, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:47:23'),
(30, 7, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:47:23'),
(31, 8, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:47:23'),
(32, 9, 11, 'account_lockout', 'Account Lockout Alert', 'User akawa@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:47:23'),
(33, 1, 1, 'failed_attempts', 'Multiple Failed Login Attempts', 'User superadmin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:49:02'),
(34, 7, 1, 'failed_attempts', 'Multiple Failed Login Attempts', 'User superadmin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:49:02'),
(35, 8, 1, 'failed_attempts', 'Multiple Failed Login Attempts', 'User superadmin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:49:02'),
(36, 9, 1, 'failed_attempts', 'Multiple Failed Login Attempts', 'User superadmin@orphanfare.com has 3 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:49:02'),
(37, 1, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:51:00'),
(38, 7, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:51:00'),
(39, 8, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:51:00'),
(40, 9, 14, 'failed_attempts', 'Multiple Failed Login Attempts', 'User com@gmail.com has 4 failed login attempts. Consider contacting the user.', 0, '2025-11-18 15:51:00'),
(41, 1, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:51:10'),
(42, 7, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:51:10'),
(43, 8, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:51:10'),
(44, 9, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 5 failed login attempts. Immediate attention required.', 0, '2025-11-18 15:51:10'),
(45, 1, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-23 09:20:58'),
(46, 7, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-23 09:20:58'),
(47, 8, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-23 09:20:58'),
(48, 9, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-23 09:20:58'),
(49, 15, 14, 'account_lockout', 'Account Lockout Alert', 'User com@gmail.com has been locked out after 6 failed login attempts. Immediate attention required.', 0, '2025-11-23 09:20:58');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 09:59:08'),
(2, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 09:59:46'),
(3, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:06:33'),
(4, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:08:00'),
(5, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:08:26'),
(6, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:08:28'),
(7, 1, 'create', 'Created user: admin (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:13:48'),
(8, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:21:37'),
(9, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:21:39'),
(10, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:22:30'),
(11, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:22:36'),
(12, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:27:51'),
(13, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:30:19'),
(14, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:30:21'),
(15, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:31:51'),
(16, 1, 'create', 'Created user: admin (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:36:58'),
(17, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:37:35'),
(18, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 10:41:44'),
(19, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 11:54:16'),
(20, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 11:55:09'),
(21, 1, 'create', 'Created user: emjay (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 11:57:40'),
(22, 9, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:00:42'),
(23, 9, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:00:44'),
(24, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:00:59'),
(25, 1, 'create', 'Created user: tacs (user)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:02:19'),
(26, 10, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:02:32'),
(27, 10, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:02:35'),
(28, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:03:12'),
(29, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:04:01'),
(30, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:08:42'),
(31, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:09:41'),
(32, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:09:48'),
(33, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:14:14'),
(34, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:14:18'),
(35, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:14:23'),
(36, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:14:46'),
(37, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:14:52'),
(38, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:16:48'),
(39, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:16:52'),
(40, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:17:20'),
(41, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:19:26'),
(42, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 12:19:48'),
(43, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:18:37'),
(44, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:18:53'),
(45, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:28:36'),
(46, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:35:35'),
(47, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:39:43'),
(48, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:39:51'),
(49, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:40:10'),
(50, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:40:17'),
(51, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:41:06'),
(52, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:41:11'),
(53, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:42:12'),
(54, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:45:16'),
(55, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 13:45:42'),
(56, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:05:49'),
(57, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:06:03'),
(58, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:08:26'),
(59, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:10:00'),
(60, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:10:04'),
(61, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:14:33'),
(62, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:36:03'),
(63, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:36:08'),
(64, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:36:23'),
(65, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:36:34'),
(66, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:54:43'),
(67, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:55:03'),
(68, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:57:54'),
(69, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 14:58:02'),
(70, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:00:26'),
(71, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:00:33'),
(72, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:00:47'),
(73, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:01:07'),
(74, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:27:32'),
(75, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:27:40'),
(76, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:34:45'),
(77, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:34:50'),
(78, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:51:33'),
(79, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:51:39'),
(80, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:51:50'),
(81, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:51:56'),
(82, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:52:35'),
(83, 1, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:52:40'),
(84, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:58:46'),
(85, 1, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:58:52'),
(86, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:59:00'),
(87, 1, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 15:59:07'),
(88, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:00:10'),
(89, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:00:22'),
(90, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:00:37'),
(91, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:00:46'),
(92, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:00:59'),
(93, 10, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:01:07'),
(94, 10, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:07:09'),
(95, 1, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:07:20'),
(96, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:09:34'),
(97, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:09:44'),
(98, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:10:05'),
(99, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-21 16:10:17'),
(100, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 16:31:25'),
(101, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 16:42:58'),
(102, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 16:43:03'),
(103, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 16:43:17'),
(104, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 16:49:44'),
(105, 8, 'Child Added', 'Child Added on children (ID: CH-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 17:34:15'),
(106, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 02:42:52'),
(107, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 14:00:11'),
(108, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 10:04:47'),
(109, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 17:12:23'),
(110, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 17:12:27'),
(111, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 03:23:38'),
(112, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:47:02'),
(113, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:47:08'),
(114, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:06:37'),
(115, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:42:40'),
(116, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:42:54'),
(117, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:45:27'),
(118, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:45:32'),
(119, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:45:40'),
(120, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:45:44'),
(121, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:49:33'),
(122, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:49:40'),
(123, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:52:43'),
(124, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:52:48'),
(125, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:54:16'),
(126, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 09:54:21'),
(127, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:22:31'),
(128, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:22:45'),
(129, 1, 'create', 'Created user: luh (Social Welfare Assistant)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:24:18'),
(130, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:24:45'),
(131, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:24:52'),
(132, 11, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:55:44'),
(133, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:56:59'),
(134, 11, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:57:37'),
(135, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:57:44'),
(136, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 10:59:54'),
(137, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:00:00'),
(138, 11, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:01:15'),
(139, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:01:22'),
(140, 1, 'create', 'Created user: yey (Social Worker)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:01:50'),
(141, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:01:56'),
(142, 12, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:02:01'),
(143, 12, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:07:27'),
(144, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:07:35'),
(145, 11, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:12:32'),
(146, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:12:39'),
(147, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:19:27'),
(148, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:23:00'),
(149, 1, 'create', 'Created user: com (Social Worker)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:23:40'),
(150, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:23:43'),
(151, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:23:49'),
(152, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:28:52'),
(153, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:29:03'),
(154, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:39:26'),
(155, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:39:43'),
(156, 11, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:40:06'),
(157, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:40:13'),
(158, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:41:15'),
(159, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:41:26'),
(160, 11, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:41:46'),
(161, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 11:41:55'),
(162, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:39:47'),
(163, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:40:12'),
(164, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:42:34'),
(165, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:42:56'),
(166, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:44:10'),
(167, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:44:23'),
(168, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:44:53'),
(169, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 13:44:58'),
(170, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 14:37:30'),
(171, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:40:22'),
(172, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:44:32'),
(173, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:44:47'),
(174, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:46:17'),
(175, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:46:24'),
(176, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:46:45'),
(177, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:46:51'),
(178, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:46:59'),
(179, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:47:08'),
(180, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 06:02:47'),
(181, 11, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 06:02:57'),
(182, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 06:58:09'),
(183, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 08:53:47'),
(184, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 08:57:13'),
(185, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 08:57:20'),
(186, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 08:57:59'),
(187, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 08:58:28'),
(188, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:40:53'),
(189, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:41:00'),
(190, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:55:07'),
(191, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:55:12'),
(192, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:56:06'),
(193, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:56:13'),
(194, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:57:21'),
(195, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:02:36'),
(196, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:07:09'),
(197, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:07:20'),
(198, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:07:30'),
(199, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:07:41'),
(200, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:07:59'),
(201, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:08:04'),
(202, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:08:20'),
(203, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:08:27'),
(204, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:08:42'),
(205, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:08:58'),
(206, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:09:15'),
(207, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:09:23'),
(208, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:10:21'),
(209, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:10:25'),
(210, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:15:29'),
(211, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:15:33'),
(212, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:25:21'),
(213, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:25:26'),
(214, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:33:38'),
(215, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:33:56'),
(216, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:34:03'),
(217, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:34:09'),
(218, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:54:49'),
(219, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:54:54'),
(220, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:55:04'),
(221, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:55:10'),
(222, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:55:24'),
(223, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:55:47'),
(224, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:55:58'),
(225, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:56:07'),
(226, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:56:13'),
(227, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:56:21'),
(228, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:56:31'),
(229, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:56:37'),
(230, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:56:55'),
(231, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:57:06'),
(232, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:57:18'),
(233, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:57:22'),
(234, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:15:47'),
(235, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:15:55'),
(236, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:16:29'),
(237, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 13:16:33'),
(238, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:02:02'),
(239, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:02:12'),
(240, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:02:23'),
(241, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:02:29'),
(242, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:09:01'),
(243, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:09:05'),
(244, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:09:18'),
(245, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:09:23'),
(246, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:16:35'),
(247, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:16:51'),
(248, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:16:59'),
(249, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:17:07'),
(250, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:27:15'),
(251, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:27:23'),
(252, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:27:38'),
(253, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:27:45'),
(254, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:07'),
(255, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:13');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(256, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:24'),
(257, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:32'),
(258, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:46'),
(259, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:28:54'),
(260, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:40:51'),
(261, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:40:55'),
(262, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:41:08'),
(263, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 14:41:20'),
(264, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 15:20:37'),
(265, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 15:20:43'),
(266, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 15:21:21'),
(267, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 15:21:26'),
(268, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 08:56:05'),
(269, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:12:12'),
(270, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:12:17'),
(271, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:12:29'),
(272, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:12:35'),
(273, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:30:36'),
(274, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:30:41'),
(275, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 13:40:17'),
(276, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 13:40:22'),
(277, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 12:49:48'),
(278, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 12:54:01'),
(279, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-03 12:54:08'),
(280, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 09:57:23'),
(281, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 10:16:51'),
(282, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 10:25:17'),
(283, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-04 10:26:54'),
(284, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:41:06'),
(285, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:41:12'),
(286, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:41:17'),
(287, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:41:36'),
(288, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:42:58'),
(289, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:43:03'),
(290, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:47:41'),
(291, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:47:48'),
(292, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:48:28'),
(293, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:48:34'),
(294, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:49:19'),
(295, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:49:33'),
(296, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 12:49:37'),
(297, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 13:54:11'),
(298, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 13:55:07'),
(299, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 08:19:16'),
(300, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 03:12:59'),
(301, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 11:02:40'),
(302, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 14:35:24'),
(303, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 14:35:30'),
(304, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 01:22:05'),
(305, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 01:22:33'),
(306, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 01:27:12'),
(307, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 01:27:18'),
(308, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 07:44:20'),
(309, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 07:44:25'),
(310, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 07:44:47'),
(311, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 07:44:53'),
(312, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:02:50'),
(313, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:03:04'),
(314, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:03:07'),
(315, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:03:22'),
(316, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:04:08'),
(317, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:04:13'),
(318, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:05:17'),
(319, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:05:25'),
(320, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:06:20'),
(321, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:06:26'),
(322, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:35:30'),
(323, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:35:35'),
(324, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:35:47'),
(325, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:35:52'),
(326, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:36:02'),
(327, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:36:07'),
(328, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:36:20'),
(329, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:36:25'),
(330, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:46:58'),
(331, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 08:47:05'),
(332, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-12 14:35:13'),
(333, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 02:09:13'),
(334, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-13 15:30:19'),
(335, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 13:47:11'),
(336, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 16:52:57'),
(337, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 16:53:06'),
(338, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 16:53:47'),
(339, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 01:15:26'),
(340, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:07:16'),
(341, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:07:21'),
(342, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:11:13'),
(343, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:11:20'),
(344, 11, 'security_alert', 'Multiple failed login attempts: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:11:45'),
(345, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:12:07'),
(346, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:15:08'),
(347, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:15:13'),
(348, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:15:27'),
(349, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:15:38'),
(350, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:20:13'),
(351, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:20:18'),
(352, 1, 'update', 'Updated user: com (Role: Social Worker -> user, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:35:02'),
(353, 1, 'update', 'Updated user: com (Role: user -> user, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:37:42'),
(354, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:38:21'),
(355, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:38:28'),
(356, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:38:35'),
(357, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 06:42:32'),
(358, 1, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 08:51:32'),
(359, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:11:12'),
(360, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:11:31'),
(361, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:13:30'),
(362, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:21:27'),
(363, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:21:38'),
(364, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:22:51'),
(365, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:23:55'),
(366, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:41:00'),
(367, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:41:07'),
(368, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:44:25'),
(369, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:44:33'),
(370, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:08:32'),
(371, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:08:38'),
(372, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:08:50'),
(373, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:08:55'),
(374, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:09:29'),
(375, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:09:52'),
(376, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:10:18'),
(377, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:10:53'),
(378, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:11:08'),
(379, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:11:52'),
(380, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:11:58'),
(381, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:12:25'),
(382, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:13:50'),
(383, 14, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:14:25'),
(384, 14, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:15:03'),
(385, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:15:23'),
(386, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:47:03'),
(387, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:47:12'),
(388, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:00:45'),
(389, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:00:54'),
(390, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:01:00'),
(391, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:07:36'),
(392, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-16 14:59:20'),
(393, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 02:52:16'),
(394, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 02:52:29'),
(395, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 04:46:43'),
(396, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 04:46:50'),
(397, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 13:32:09'),
(398, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 13:33:37'),
(399, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:10:08'),
(400, 14, 'security_alert', 'Multiple failed login attempts: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:11:05'),
(401, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:11:45'),
(402, 11, 'security_alert', 'Multiple failed login attempts: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:12:02'),
(403, 11, 'security_alert', 'Multiple failed login attempts: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:12:14'),
(404, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:12:45'),
(405, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:13:33'),
(406, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:13:49'),
(407, 9, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:14:15'),
(408, 9, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:14:26'),
(409, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:33:53'),
(410, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 05:55:19'),
(411, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 10:32:22'),
(412, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 10:32:37'),
(413, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 11:45:27'),
(414, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 11:45:34'),
(415, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 11:50:00'),
(416, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 11:50:07'),
(417, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:26:45'),
(418, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:26:51'),
(419, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:50:50'),
(420, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:52:13'),
(421, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:53:41'),
(422, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:53:47'),
(423, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 12:53:57'),
(424, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:00:17'),
(425, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:04:38'),
(426, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:50:55'),
(427, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:51:01'),
(428, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:56:37'),
(429, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:56:50'),
(430, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:57:26'),
(431, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 13:57:39'),
(432, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:07:20'),
(433, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:07:26'),
(434, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:07:42'),
(435, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:07:47'),
(436, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:08:14'),
(437, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:08:40'),
(438, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:08:53'),
(439, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:09:08'),
(440, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:09:13'),
(441, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:09:26'),
(442, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:09:37'),
(443, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:09:49'),
(444, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:09:54'),
(445, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:14:21'),
(446, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:14:27'),
(447, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 14:16:56'),
(448, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:46:19'),
(449, 8, 'security_alert', 'Multiple failed login attempts: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:46:34'),
(450, 8, 'security_alert', 'Multiple failed login attempts: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:46:44'),
(451, 8, 'security_alert', 'Multiple failed login attempts: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:46:48'),
(452, 11, 'security_alert', 'Multiple failed login attempts: 6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:47:23'),
(453, 1, 'security_alert', 'Multiple failed login attempts: 3', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:49:02'),
(454, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:49:25'),
(455, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:49:56'),
(456, 14, 'security_alert', 'Multiple failed login attempts: 4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:51:00'),
(457, 14, 'security_alert', 'Multiple failed login attempts: 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:51:10'),
(458, 1, 'login', 'User logged into the system', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:51:57'),
(459, 1, 'update', 'Updated user: admin (Role: admin -> admin, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:52:19'),
(460, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:52:42'),
(461, 1, 'update', 'Updated user: admin (Role: admin -> super_admin, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:52:57'),
(462, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:53:29'),
(463, 1, 'create', 'Created user: emjay11 (admin)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:54:27'),
(464, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:56:16'),
(465, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:56:59'),
(466, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:57:36'),
(467, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 16:26:51'),
(468, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 16:52:47'),
(469, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 16:53:03'),
(470, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:37:08'),
(471, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:37:14'),
(472, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:38:58'),
(473, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:03'),
(474, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:08:23'),
(475, 8, 'update', 'Updated user: admin (Role: super_admin -> admin, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:08:48'),
(476, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:08:58'),
(477, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:20:03'),
(478, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:07:08'),
(479, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:07:15'),
(480, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:08:53'),
(481, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:09:04'),
(482, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:09:09'),
(483, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:09:14'),
(484, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:09:24'),
(485, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:10:23'),
(486, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:15:59'),
(487, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:16:04'),
(488, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:16:30'),
(489, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:53:22'),
(490, 8, 'login_2fa_required', 'User login requires 2FA verification', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:53:31'),
(491, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 07:43:37'),
(492, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 07:44:14'),
(493, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 07:49:32'),
(494, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 08:24:32'),
(495, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 08:24:37'),
(496, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 08:28:13'),
(497, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 11:51:53'),
(498, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 11:56:35'),
(499, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 12:00:48'),
(500, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 12:00:53'),
(501, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 12:14:29'),
(502, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 13:18:31'),
(503, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 13:32:32'),
(504, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 13:32:42'),
(505, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 13:37:58'),
(506, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 03:11:21'),
(507, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 06:28:23'),
(508, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:50:15'),
(509, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:50:24');
INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
<<<<<<< HEAD
(510, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:51:16'),
(511, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 01:44:47'),
(512, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:51:06'),
(513, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:51:13'),
(514, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:51:45'),
(515, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:51:55'),
(516, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:52:02'),
(517, 1, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:52:31'),
(518, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:52:37'),
(519, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 02:59:21'),
(520, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:18:13'),
(521, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:18:20'),
(522, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:39:20'),
(523, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:39:25'),
(524, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:52:38'),
(525, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:53:48'),
(526, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:53:52'),
(527, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:56:39'),
(528, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:04:50'),
(529, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:05:55'),
(530, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:06:02'),
(531, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:06:41'),
(532, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:06:51'),
(533, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:37:05'),
(534, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:57:04'),
(535, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:58:15'),
(536, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 08:58:22'),
(537, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:05:39'),
(538, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:05:49'),
(539, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:09:55'),
(540, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:10:09'),
(541, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:18:12'),
(542, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:19:14'),
(543, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:20:55'),
(544, 14, 'security_alert', 'Multiple failed login attempts: 6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:20:58'),
(545, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:22:53'),
(546, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:24:53'),
(547, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:25:06'),
(548, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:47:33'),
(549, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:50:51'),
(550, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:50:59'),
(551, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:54:13'),
(552, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 09:55:04'),
(553, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 10:32:36'),
(554, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 10:32:42'),
(555, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:07:17'),
(556, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:07:21'),
(557, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:10:36'),
(558, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:29:27'),
(559, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:29:41'),
(560, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 13:12:13'),
(561, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 14:09:21'),
(562, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:06:42'),
(563, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:06:48'),
(564, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:06:58'),
(565, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:07:06'),
(566, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:07:17'),
(567, 1, 'update', 'Updated user: emjay11 (Role: admin -> Social Welfare Assistant, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:07:31'),
(568, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:07:45'),
(569, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:08:50'),
(570, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:08:54'),
(571, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:15:04'),
(572, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 02:50:03'),
(573, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 03:21:52'),
(574, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 03:52:22'),
(575, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 03:52:43'),
(576, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 03:52:49'),
(577, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 03:59:13'),
(578, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 04:32:17'),
(579, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 04:42:36'),
(580, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 04:42:40'),
(581, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 04:46:50'),
(582, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 04:46:59'),
(583, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 04:47:04'),
(584, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:00:13'),
(585, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:01:14'),
(586, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:01:20'),
(587, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:21:46'),
(588, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:31:57'),
(589, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:32:11'),
(590, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:32:30'),
(591, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:08:32'),
(592, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:44:13'),
(593, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:49:37'),
(594, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:49:41'),
(595, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:50:45'),
(596, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:24:32'),
(597, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:25:41'),
(598, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:25:53'),
(599, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:26:16'),
(600, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:11:07'),
(601, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:26:39'),
(602, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:59:03'),
(603, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:33:57'),
(604, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:44:27'),
(605, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:44:32'),
(606, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:44:49'),
(607, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:48:21'),
(608, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:48:25'),
(609, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:51:38'),
(610, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:54:04'),
(611, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:54:09'),
(612, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:54:59'),
(613, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:56:35'),
(614, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:56:42'),
(615, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:56:55'),
(616, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:57:03'),
(617, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:57:12'),
(618, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:02:48'),
(619, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:03:29'),
(620, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:03:36'),
(621, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:24:39'),
(622, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:48:18'),
(623, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:48:22'),
(624, 1, 'update', 'Updated user: emjay11 (Role: Social Welfare Assistant -> Social Worker, Status: active -> active)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:48:37'),
(625, 15, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:48:47'),
(626, 15, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:49:44'),
(627, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:49:51'),
(628, 8, 'logout', 'User logged out of the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:51:10'),
(629, 1, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:51:15');
=======
(510, 8, 'login', 'User logged into the system', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:51:16');
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

-- --------------------------------------------------------

--
-- Table structure for table `audit_log_admin`
--

CREATE TABLE `audit_log_admin` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log_admin`
--

INSERT INTO `audit_log_admin` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 8, 'Child Added', 'Child Added on children (ID: CH-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 17:53:26'),
(2, 8, 'Child Added', 'Child Added on children (ID: CH-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 18:01:47'),
(3, 8, 'Case Created', 'Case Created on cases (ID: CS-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-22 18:13:21'),
(4, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: NEW)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 03:07:18'),
(5, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: NEW)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 03:09:33'),
(6, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: NEW)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 03:21:01'),
(7, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: NEW)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 03:31:26'),
(8, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: NEW)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 03:40:33'),
(9, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: NEW)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 03:41:04'),
(10, 8, 'Protective Action Initiated', 'Protective Action Initiated on protective_actions (ID: ACT-20251023060039751)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 04:00:39'),
(11, 8, 'Child Added', 'Child Added on children (ID: CH-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 15:31:07'),
(12, 8, 'Case Created', 'Case Created on cases (ID: CS-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 15:31:07'),
(13, 8, 'Donation Recorded', 'Donation Recorded on donations (ID: DON-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 16:20:45'),
(14, 8, 'Inventory Item Added', 'Inventory Item Added on inventory (ID: INV-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-23 16:47:14'),
(15, 8, 'Child and Case Created', 'Child and Case Created on children_cases (ID: UC-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 10:31:08'),
(16, 8, 'Child and Case Created', 'Child and Case Created on children_cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 12:13:52'),
(17, 8, 'Foster Parent Added', 'Foster Parent Added on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 12:57:00'),
(18, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 14:06:37'),
(19, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 16:10:53'),
(20, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 16:12:19'),
(21, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 16:18:16'),
(22, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 16:18:19'),
(23, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 16:48:27'),
(24, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:26:39'),
(25, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:43:36'),
(26, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:44:10'),
(27, 8, 'Documents Uploaded', 'Documents Uploaded on documents (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 14:33:30'),
(28, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:59:58'),
(29, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 07:58:47'),
(30, 8, 'Evidence Photos Uploaded', 'Evidence Photos Uploaded on evidence_photos (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 07:59:16'),
(31, 8, 'Donation Recorded', 'Donation Recorded on donations (ID: DON-2025-124)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 10:04:52'),
(32, 8, 'Donation Recorded', 'Donation Recorded on donations (ID: DON-2025-125)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 10:05:01'),
(33, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 10:13:31'),
(34, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 10:14:48'),
(35, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 09:00:02'),
(36, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 09:00:10'),
(37, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:16:15'),
(38, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:16:59'),
(39, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:18:40'),
(40, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:31:05'),
(41, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:39:19'),
(42, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:39:23'),
(43, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-009)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:39:45'),
(44, 8, 'Event Status Updated', 'Event Status Updated on events (ID: EVT-2025-009)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:40:20'),
(45, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:10:32'),
(46, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:13:49'),
(47, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:14:57'),
(48, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 12:40:01'),
(49, 8, 'Child and Case Created', 'Child and Case Created on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 10:31:19'),
(50, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-009)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:24:06'),
(51, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 13:57:35'),
(52, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 13:57:58'),
(53, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-05 15:50:04'),
(54, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 14:40:21'),
(55, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 14:40:33'),
(56, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 14:41:35'),
(57, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 14:47:50'),
(58, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 14:48:00'),
(59, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 14:49:55'),
(60, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-06 17:04:02'),
(61, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:00:16'),
(62, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:10:47'),
(63, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:10:53'),
(64, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:22:41'),
(65, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:25:25'),
(66, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:38:48'),
(67, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:40:02'),
(68, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:52:45'),
(69, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 10:53:50'),
(70, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 11:08:57'),
(71, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 11:10:07'),
(72, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 11:21:21'),
(73, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 11:54:07'),
(74, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 11:56:49'),
(75, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 12:11:52'),
(76, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 12:12:11'),
(77, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:29:33'),
(78, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:29:55'),
(79, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:30:21'),
(80, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:31:32'),
(81, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:33:01'),
(82, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:34:14'),
(83, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 13:35:07'),
(84, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 14:21:30'),
(85, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 14:57:53'),
(86, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 15:10:19'),
(87, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 16:23:49'),
(88, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 16:52:30'),
(89, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-08 17:17:15'),
(90, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 03:29:40'),
(91, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 03:41:20'),
(92, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 03:41:27'),
(93, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 03:59:24'),
(94, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:01:09'),
(95, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:01:38'),
(96, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:11:13'),
(97, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:11:29'),
(98, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:28:57'),
(99, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:30:48'),
(100, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 04:58:33'),
(101, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 05:09:57'),
(102, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 05:44:39'),
(103, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 07:07:10'),
(104, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 08:04:24'),
(105, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 08:36:39'),
(106, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 08:37:26'),
(107, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 08:40:47'),
(108, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 08:41:12'),
(109, 1, 'Child and Case Created', 'Child and Case Created on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 08:43:58'),
(110, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 09:19:13'),
(111, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 09:20:06'),
(112, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 09:24:26'),
(113, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-09 09:27:52'),
(114, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 10:50:59'),
(115, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 10:53:45'),
(116, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 11:02:12'),
(117, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:02:12'),
(118, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:17:11'),
(119, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:18:45'),
(120, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:19:38'),
(121, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:21:46'),
(122, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:37:59'),
(123, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:38:40'),
(124, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:38:59'),
(125, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:49:24'),
(126, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:49:59'),
(127, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:51:35'),
(128, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:51:47'),
(129, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:52:23'),
(130, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:52:43'),
(131, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:53:49'),
(132, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:55:24'),
(133, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:55:40'),
(134, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:56:22'),
(135, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 15:57:31'),
(136, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 16:04:58'),
(137, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 16:06:42'),
(138, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 16:07:18'),
(139, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 16:56:30'),
(140, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 16:56:44'),
(141, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 17:00:41'),
(142, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 17:01:25'),
(143, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-10 17:01:42'),
(144, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 02:36:50'),
(145, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 02:37:19'),
(146, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 02:45:04'),
(147, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 03:09:00'),
(148, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 03:09:37'),
(149, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 03:54:44'),
(150, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:02:31'),
(151, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:03:27'),
(152, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:05:30'),
(153, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:05:36'),
(154, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:06:21'),
(155, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 07:34:57'),
(156, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 07:35:03'),
(157, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 10:05:59'),
(158, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 10:06:13'),
(159, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 10:13:02'),
(160, 1, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 10:15:07'),
(161, 1, 'Case Updated', 'Case Updated on cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 10:15:21'),
(162, 1, 'Legal Action Added', 'Legal Action Added on legal_actions (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 10:15:45'),
(163, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 12:43:41'),
(164, 1, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 12:43:53'),
(165, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:00:10'),
(166, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:00:33'),
(167, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:01:00'),
(168, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:02:29'),
(169, 8, 'Documents Uploaded', 'Documents Uploaded on documents (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:15:20'),
(170, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:46:52'),
(171, 8, 'Event Article Added', 'Event Article Added on event_articles (ID: EVT-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 15:56:41'),
(172, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 15:57:05'),
(173, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 16:31:09'),
(174, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 16:43:32'),
(175, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-014)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:02:32'),
(176, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-014)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:06:27'),
(177, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-015)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:11:40'),
(178, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-015)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:13:36'),
(179, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 18:30:18'),
(180, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 18:30:56'),
(181, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 19:08:47'),
(182, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 09:59:02'),
(183, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:16:46'),
(184, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:16:58'),
(185, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:28:59'),
(186, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:29:51'),
(187, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:30:02'),
(188, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 10:46:47'),
(189, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-016)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:08:46'),
(190, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-016)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:09:14'),
(191, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-017)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:09:54'),
(192, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-017)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:17:39'),
(193, 8, 'Meeting Requested', 'Meeting Requested on meeting_requests (ID: 1)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-16 19:07:12'),
(194, 8, 'Meeting Requested', 'Meeting Requested on meeting_requests (ID: 2)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-16 19:07:14'),
(195, 8, 'Meeting Requested', 'Meeting Requested on meeting_requests (ID: 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-16 19:08:06'),
(196, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:56:49'),
(197, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 14:57:07'),
(198, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-018)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:03:34'),
(199, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-019)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:04:51'),
(200, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 06:34:12'),
(201, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 07:45:34'),
(202, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 07:55:01'),
(203, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 08:21:10'),
(204, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 08:21:38'),
(205, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 08:28:19'),
(206, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:02:18'),
(207, 8, 'Foster Parent Added', 'Foster Parent Added on foster_parents (ID: FT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:15:52'),
(208, 8, 'Foster Parent Deleted', 'Foster Parent Deleted on foster_parents (ID: FT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:22:15'),
(209, 8, 'Foster Parent Deleted', 'Foster Parent Deleted on foster_parents (ID: FT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:22:20'),
(210, 8, 'Foster Parent Deleted', 'Foster Parent Deleted on foster_parents (ID: FT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:22:23'),
(211, 8, 'Foster Parent Deleted', 'Foster Parent Deleted on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:22:24'),
(212, 8, 'Foster Parent Added', 'Foster Parent Added on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:23:35'),
(213, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:23:59'),
(214, 8, 'Legal Action Added', 'Legal Action Added on legal_actions (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:45:58'),
(215, 8, 'Legal Action Added', 'Legal Action Added on legal_actions (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 09:59:41');
INSERT INTO `audit_log_admin` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(216, 8, 'Legal Action Added', 'Legal Action Added on legal_actions (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 10:22:03'),
(217, 8, 'Intervention Added', 'Intervention Added on social_services (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 10:22:09'),
(218, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 10:24:55'),
(219, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 10:25:28'),
(220, 15, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-018)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:57:57'),
(221, 15, 'Event Created', 'Event Created on events (ID: EVT-2025-021)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:37:31'),
(222, 15, 'Event Created', 'Event Created on events (ID: EVT-2025-022)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:37:54'),
(223, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-023)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:15'),
(224, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-024)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:17'),
(225, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-025)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:17'),
(226, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-026)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:18'),
(227, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-027)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:18'),
(228, 1, 'Event Created', 'Event Created on events (ID: EVT-2025-028)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:19'),
(229, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-021)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:48'),
(230, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-021)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:41:55'),
(231, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-021)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:46:48'),
(232, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-021)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:48:21'),
(233, 1, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:48:30'),
(234, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-022)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:09:08'),
(235, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-023)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:31:02'),
(236, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-029)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:40:27'),
(237, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-030)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:40:31'),
(238, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:42:44'),
(239, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:43:55'),
(240, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:50:04'),
(241, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:50:48'),
(242, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:19:50'),
(243, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:20:15'),
(244, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:20:20'),
(245, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:23:18'),
(246, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:23:36'),
(247, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:26:26'),
(248, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:27:09'),
(249, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:27:30'),
(250, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:47:49'),
(251, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:06:53'),
(252, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:10:47'),
(253, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:11:35'),
(254, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:24:22'),
(255, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:25:49'),
(256, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:57:43'),
(257, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:58:00'),
(258, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:10'),
(259, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:13'),
(260, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:17'),
(261, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:20'),
(262, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:22'),
(263, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:24'),
(264, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:09:19'),
(265, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:09:38'),
(266, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:24:44'),
(267, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:24:49'),
(268, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:25:07'),
(269, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:26:04'),
(270, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:30:30'),
(271, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:31:18'),
(272, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:31:35'),
(273, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:31:38'),
(274, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:40:41'),
(275, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:57:00'),
(276, 8, 'Foster Parent Deleted', 'Foster Parent Deleted on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:06:23'),
(277, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:21:02'),
(278, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 02:18:46'),
(279, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 02:19:05'),
(280, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 02:30:00'),
(281, 8, 'Case Updated', 'Case Updated on cases (ID: UC-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 02:30:22'),
(282, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-005)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 03:03:46'),
(283, 8, 'Case Added to Child', 'Case Added to Child on cases (ID: CASE-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-21 03:04:06'),
(284, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 06:33:53'),
(285, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:14:35'),
(286, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:15:01'),
(287, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:24:35'),
(288, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:25:19'),
(289, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:30:48'),
(290, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:30:53'),
(291, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:34:27'),
(292, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:34:41'),
(293, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:37:27'),
(294, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:48:41'),
(295, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:55:30'),
(296, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:59:08'),
(297, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:02:37'),
(298, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:07:09'),
(299, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:07:32'),
(300, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:07:32'),
(301, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:14:26'),
(302, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:15:05'),
(303, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-009)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:16:10'),
(304, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:33:02'),
(305, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:38:33'),
(306, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:02:24'),
(307, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:11:40'),
(308, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:19:20'),
(309, 8, 'Event Photos Uploaded', 'Event Photos Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:19:40'),
(310, 8, 'Documents Uploaded', 'Documents Uploaded on documents (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:20:11'),
(311, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:29:45'),
(312, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:37:08'),
(313, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:37:33'),
(314, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:40:24'),
(315, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:18:13'),
(316, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:24:28'),
(317, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:24:43'),
(318, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:24:53'),
(319, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:30:34'),
(320, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:36:39'),
(321, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:36:54'),
(322, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:22:00'),
<<<<<<< HEAD
(323, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:52:00'),
(324, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 01:45:23'),
(325, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-006)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 01:46:58'),
(326, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-007)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 01:49:28'),
(327, 8, 'Foster Parent Added', 'Foster Parent Added on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 03:13:39'),
(328, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 03:14:32'),
(329, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-001)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 03:15:05'),
(330, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 03:15:38'),
(331, 8, 'Foster Parent Added', 'Foster Parent Added on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 03:27:09'),
(332, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-008)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:34:33'),
(333, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-009)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:37:41'),
(334, 8, 'Case Added to Child', 'Case Added to Child on cases (ID: CASE-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 04:37:54'),
(335, 8, 'Case Updated', 'Case Updated on cases (ID: CASE-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 05:05:12'),
(336, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-010)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:30:09'),
(337, 8, 'Case Added to Child', 'Case Added to Child on cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 11:30:24'),
(338, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 14:09:56'),
(339, 8, 'Event Photo Uploaded', 'Event Photo Uploaded on events_gallery (ID: EVT-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 14:10:44'),
(340, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:33:37'),
(341, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:34:00'),
(342, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:35:04'),
(343, 8, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-011)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:37:03'),
(344, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:46:25'),
(345, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:47:00'),
(346, 8, 'Legal Action Added', 'Legal Action Added on legal_actions (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:57:14'),
(347, 8, 'Evidence Photos Uploaded', 'Evidence Photos Uploaded on evidence_photos (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:58:16'),
(348, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-014)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:59:06'),
(349, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-014)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:59:32'),
(350, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-015)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:59:48'),
(351, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-015)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:00:54'),
(352, 8, 'Event Deleted', 'Event Deleted on events (ID: EVT-2025-015)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:01:13'),
(353, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-016)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:01:31'),
(354, 8, 'Email Reminder Sent', 'Email Reminder Sent on events (ID: EVT-2025-016)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:01:59'),
(355, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:08:55'),
(356, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:44:51'),
(357, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:45:15'),
(358, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:49:29'),
(359, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:50:56'),
(360, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:24:43'),
(361, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:26:25'),
(362, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:27:07'),
(363, 8, 'Case Added to Child', 'Case Added to Child on cases (ID: CASE-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 07:52:14'),
(364, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:11:19'),
(365, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:11:49'),
(366, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-003)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 08:13:49'),
(367, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:26:53'),
(368, 8, 'Child and Case Created', 'Child and Case Created on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:32:02'),
(369, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:35:45'),
(370, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 14:59:19'),
(371, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:16:37'),
(372, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:26:44'),
(373, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:27:17'),
(374, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:28:31'),
(375, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:28:46'),
(376, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:34:10'),
(377, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:34:39'),
(378, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:35:05'),
(379, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:35:55'),
(380, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:36:53'),
(381, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:43:13'),
(382, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:43:27'),
(383, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:44:59'),
(384, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:55:16'),
(385, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 15:56:16'),
(386, 8, 'Foster Parent Updated', 'Foster Parent Updated on foster_parents (ID: FT-2025-002)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:02:57'),
(387, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: UC-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:31:53'),
(388, 8, 'Unified Record Updated', 'Unified Record Updated on children_cases (ID: CASE-2025-004)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:37:33'),
(389, 15, 'Child Record Created', 'Child Record Created on children_cases (ID: UC-2025-013)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 16:49:26');
=======
(323, 8, 'Event Created', 'Event Created on events (ID: EVT-2025-012)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:52:00');
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

-- --------------------------------------------------------

--
-- Table structure for table `calendar_availability`
--

CREATE TABLE `calendar_availability` (
  `id` int(11) NOT NULL,
  `unavailable_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `calendar_availability`
--

INSERT INTO `calendar_availability` (`id`, `unavailable_date`, `start_time`, `end_time`, `reason`, `created_by`, `created_at`) VALUES
(1, '2025-11-11', '21:40:00', '21:44:00', 'i dunno', 1, '2025-11-11 13:41:07'),
(2, '2025-11-12', '22:35:00', '22:35:00', 'i dunno', 1, '2025-11-11 13:45:27'),
(3, '2025-11-13', '22:11:00', '22:11:00', 'i dunno', 1, '2025-11-11 14:11:41'),
(7, '2025-11-17', '19:17:00', '13:17:00', '', 8, '2025-11-15 11:17:30');

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `case_type` varchar(100) NOT NULL,
  `child_name` varchar(100) NOT NULL,
  `child_age` int(11) NOT NULL,
  `child_gender` enum('Male','Female') NOT NULL,
  `current_location` text DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `educational_attention` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `reported_by` varchar(100) NOT NULL,
  `reporter_relation` varchar(100) DEFAULT NULL,
  `reporter_phone` varchar(20) DEFAULT NULL,
  `reporter_email` varchar(100) DEFAULT NULL,
  `expected_date` date NOT NULL,
  `description` text NOT NULL,
  `priority` enum('urgent','high','medium','low') DEFAULT 'medium',
  `social_worker` varchar(100) DEFAULT NULL,
  `current_status` varchar(50) DEFAULT NULL,
  `status` enum('Open','Under Investigation','Court Action Pending','Closed') DEFAULT 'Open',
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `linked_child_id` varchar(20) DEFAULT NULL,
  `cf_incident_date` date DEFAULT NULL,
  `cf_incident_location` varchar(255) DEFAULT NULL,
  `cf_perpetrator_name` varchar(100) DEFAULT NULL,
  `cf_perpetrator_relation` varchar(100) DEFAULT NULL,
  `cf_evidence_notes` text DEFAULT NULL,
  `cf_risk_level` varchar(50) DEFAULT NULL,
  `cf_case_priority` varchar(50) DEFAULT NULL,
  `cf_investigation_status` varchar(100) DEFAULT NULL,
  `cf_medical_history` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_id`, `case_type`, `child_name`, `child_age`, `child_gender`, `current_location`, `birth_date`, `birth_place`, `educational_attention`, `contact_number`, `reported_by`, `reporter_relation`, `reporter_phone`, `reporter_email`, `expected_date`, `description`, `priority`, `social_worker`, `current_status`, `status`, `created_date`, `created_by`, `created_at`, `updated_at`, `linked_child_id`, `cf_incident_date`, `cf_incident_location`, `cf_perpetrator_name`, `cf_perpetrator_relation`, `cf_evidence_notes`, `cf_risk_level`, `cf_case_priority`, `cf_investigation_status`, `cf_medical_history`) VALUES
(4, 'UC-2025-002', 'Physical Abuse', 'emjay', 15, 'Male', '442', '2025-10-24', NULL, NULL, 'sdasdasd', 'sdadasd', 'sdasdas', 'dasdasda', 'emjay@gamil.com', '2025-10-24', 'dasdasd', 'urgent', 'juan-cruz', NULL, 'Open', '2025-10-24 00:00:00', 8, '2025-10-24 12:13:52', '2025-11-08 13:29:55', 'UC-2025-002', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'UC-2025-003', 'Physical Abuse', '', 0, 'Male', '442', '2025-11-02', NULL, NULL, 'dasds', 'dsadasd', '', '', '', '2025-11-02', 'dasdasd', 'urgent', '', NULL, 'Open', '2025-11-02 00:00:00', 8, '2025-11-02 10:31:19', '2025-11-25 07:27:07', 'UC-2025-003', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'UC-2025-004', 'Physical Abuse', '', 5, 'Female', '44', '2025-11-09', NULL, NULL, 'dasds', 'dsadasd', 'dasd', 'dsadasdas', 'emjay@gamil.com', '2025-11-09', 'hi', 'urgent', '', NULL, 'Open', '2025-11-09 00:00:00', 1, '2025-11-09 08:43:58', '2025-11-21 02:30:22', 'UC-2025-004', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'CASE-2025-001', 'Sexual Abuse', '', 12, 'Male', '44', '2025-11-29', NULL, NULL, 'sdasdasd', 'dsadasd', 'dasdasd', 'dsadasdas', 'emjay@gamil.com', '2025-11-21', 'dasdasdasdasdd', 'urgent', 'juan-cruz', NULL, 'Open', '2025-11-21 00:00:00', 8, '2025-11-21 03:04:06', '2025-11-21 03:06:04', 'UC-2025-005', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'CASE-2025-002', 'Sexual Abuse', '', 12, 'Male', 'DASDASDASD', '2025-11-23', NULL, NULL, 'dasdasd', '', '', '', '', '2025-11-23', 'sdasdasd', 'urgent', '', NULL, 'Open', '2025-11-23 12:37:54', 8, '2025-11-23 04:37:54', '2025-11-23 05:05:12', 'UC-2025-009', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'CASE-2025-003', '', '', 0, '', '', NULL, NULL, NULL, '', '', '', '', '', '2025-11-25', '', '', '', NULL, 'Open', '2025-11-23 19:30:24', 8, '2025-11-23 11:30:24', '2025-11-25 08:13:49', 'UC-2025-010', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'CASE-2025-004', '', '', 0, 'Male', 'dasdsasdas', '2025-11-25', NULL, NULL, 'dasdas', '', '', '', '', '2025-11-25', '', '', '', NULL, 'Open', '2025-11-25 15:52:14', 8, '2025-11-25 07:52:14', '2025-11-25 16:37:33', 'UC-2025-011', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Kingdom');

-- --------------------------------------------------------

--
-- Table structure for table `children`
--

CREATE TABLE `children` (
  `id` int(11) NOT NULL,
  `child_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `entry_date` date NOT NULL,
  `status` enum('Adoptable','Adopted','In Care','Reintegrated') DEFAULT 'In Care',
  `address` text DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `birth_place` varchar(255) DEFAULT NULL,
  `educational_attainment` varchar(100) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `monthly_income` varchar(50) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `family_composition` text DEFAULT NULL,
  `problem_presented` text DEFAULT NULL,
  `assessment_recommendation` text DEFAULT NULL,
  `health_status` varchar(100) DEFAULT 'Good',
  `allergies` text DEFAULT NULL,
  `emergency_contact` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `problem_description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `linked_case_id` varchar(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `cf_educational_level` varchar(100) DEFAULT NULL,
  `cf_religion` varchar(50) DEFAULT NULL,
  `cf_special_needs` text DEFAULT NULL,
  `cf_hobbies` text DEFAULT NULL,
  `cf_school_name` varchar(255) DEFAULT NULL,
  `cf_grade_level` varchar(50) DEFAULT NULL,
  `cf_favorite_color` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `children`
--

INSERT INTO `children` (`id`, `child_id`, `name`, `age`, `gender`, `date_of_birth`, `entry_date`, `status`, `address`, `civil_status`, `birth_place`, `educational_attainment`, `occupation`, `monthly_income`, `religion`, `family_composition`, `problem_presented`, `assessment_recommendation`, `health_status`, `allergies`, `emergency_contact`, `contact_phone`, `problem_description`, `notes`, `photo_path`, `created_at`, `updated_at`, `linked_case_id`, `created_by`, `cf_educational_level`, `cf_religion`, `cf_special_needs`, `cf_hobbies`, `cf_school_name`, `cf_grade_level`, `cf_favorite_color`) VALUES
(6, 'UC-2025-003', '', 0, 'Male', '2025-11-02', '2025-11-02', 'In Care', '442', '', '', '', '', '', '', NULL, '', '', 'Good', 'asdasd', 'asdasd', 'dasds', 'asdasd', 'dsadasd', 'uploads/children/UC-2025-003.jpg', '2025-11-02 10:31:19', '2025-11-25 08:14:06', 'UC-2025-003', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'red'),
(7, 'UC-2025-004', '', 5, 'Female', '2025-11-09', '2025-11-11', 'Adopted', '44', 'Married', 'pasig city', 'College Level', 'Professional', '26516', 'catholic', '[{\"name\":\"gio\",\"relationship\":\"brother\",\"age\":\"19\",\"sex\":\"male\",\"civil_status\":\"single\",\"educational_attainment\":\"college level\",\"occupation_income\":\"15611\"}]', 'hello', 'asdasdasdsad', 'Good', 'water', 'dasdas', 'dasds', 'dasdas', 'dasdads', 'uploads/children/UC-2025-004_1763203607.jpg', '2025-11-09 08:43:58', '2025-11-21 02:19:05', 'UC-2025-004', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, 'UC-2025-005', '', 12, 'Male', '2025-11-29', '2025-11-21', 'In Care', '44', 'Single', 'pasig city', 'College Level', 'Professional', '26516', 'catholic', NULL, 'fasddsfsadffd', 'faddfdaddfdad', 'Good', 'sdfd', 'dasdasd', 'sdasdasd', 'adfafasddsdfdsasfd', 'fadsdfdasdffasdsfd', 'public/placeholder.jpg', '2025-11-21 03:03:46', '2025-11-21 03:04:06', 'CASE-2025-001', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, 'UC-2025-006', '', 12, 'Male', NULL, '2025-11-23', 'In Care', '4442', 'Married', 'dsaddasd', 'College Level', 'none', '4342', 'cayholic', NULL, 'asdasdas', 'dsadasdasd', 'Good', 'xsxSDSD', 'dsadasddsdasdasdasdasd', '09564456', 'dasdasdasdasd', 'sadasdasdasdas', 'uploads/children/UC-2025-006_1763862418.png', '2025-11-23 01:46:58', '2025-11-23 01:46:58', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, 'UC-2025-007', '', 1, 'Male', '2025-11-23', '2025-11-23', 'In Care', '4442', 'Married', 'pasig', 'College Level', 'none', '4342', 'cayholic', NULL, 'asdasdasds', 'sadasdasd', 'Good', 'dsdasdassdsa', 'sdasdasd', '09564456', 'dsadasdasdasd', 'dsadasdasdas', 'public/placeholder.jpg', '2025-11-23 01:49:28', '2025-11-23 01:49:28', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, 'UC-2025-008', '', 12, 'Male', '2025-11-23', '2025-11-23', 'Adopted', 'DASDASDASD', '', 'dasdasdasd', '', 'dasdasd', 'asdas', 'dasdasdas', NULL, 'dsad', 'asdasd', 'Good', 'dasdasdasd', 'asdasd', 'dasdasd', 'dasdasdasd', 'sdasdasdas', 'public/placeholder.jpg', '2025-11-23 04:34:33', '2025-11-23 04:34:33', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, 'UC-2025-009', '', 12, 'Male', '2025-11-23', '2025-11-23', 'Adopted', 'DASDASDASD', '', 'dasdasdasd', '', 'dasdasd', 'asdas', 'dasdasdas', NULL, 'dsad', 'asdasd', 'Good', 'dasdasdasd', 'asdasd', 'dasdasd', 'dasdasdasd', 'sdasdasdas', 'public/placeholder.jpg', '2025-11-23 04:37:41', '2025-11-23 04:37:54', 'CASE-2025-002', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, 'UC-2025-010', '', 12, 'Female', '2025-11-23', '2025-11-23', 'In Care', 'dasadasds', 'Divorced', 'dasdsdasdds', 'No Formal Education', 'dsasdasdsad', 'sadasdasdsad', 'sadasdasds', NULL, 'das', 'asdasdssads', 'Good', 'dsasdasdd', 'dasdasdds', 'dadsdasd', 'asdasdsasd', 'dasdsasds', 'public/placeholder.jpg', '2025-11-23 11:30:09', '2025-11-25 05:30:02', 'CASE-2025-003', 8, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, 'UC-2025-011', '', 0, 'Male', '2025-11-25', '2025-11-25', 'In Care', 'dasdsasdas', 'Single', 'dasdasd', 'College Level', 'dasdasdas', 'sadas', 'asdasdsas', NULL, 'dasdasd', 'dassdasdasd', 'Good', 'dasdasdasd', 'sadsadsdasd', 'dasdas', 'asdasdsads', '', 'public/placeholder.jpg', '2025-11-25 05:37:03', '2025-11-25 16:37:33', NULL, 8, NULL, NULL, NULL, NULL, NULL, NULL, 'yellow'),
(45, 'UC-2025-012', 'justine', 0, 'Male', '2025-11-25', '2025-11-25', 'In Care', 'dasdsasdas', '', '', '', '', '', '', NULL, '', '', 'Good', 'dasdasdasd', 'sadsadsdasd', 'dasdas', 'asdasdsads', '', 'public/placeholder.jpg', '2025-11-25 14:32:02', '2025-11-25 16:31:53', 'UC-2025-012', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'green'),
(46, 'UC-2025-013', '', 12, 'Male', '2025-11-28', '2025-11-25', 'In Care', '442', 'Single', 'pasig city', 'College Level', 'Professional', '40000', 'Catholic', NULL, 'daffdfda', 'fdadfdaafdadfd', 'Good', 'dsasadasds', 'asdasdsasds', 'sdasdasd', 'dsaddsasds', 'asdsasdassd', 'public/placeholder.jpg', '2025-11-25 16:49:26', '2025-11-25 16:49:26', NULL, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `custom_fields`
--

CREATE TABLE `custom_fields` (
  `id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` enum('text','textarea','number','date','select','checkbox','radio') NOT NULL,
  `field_options` text DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `placeholder_text` varchar(255) DEFAULT NULL,
  `default_value` text DEFAULT NULL,
  `help_text` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `field_order` int(11) DEFAULT 0,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `custom_fields`
--

INSERT INTO `custom_fields` (`id`, `field_name`, `field_label`, `field_type`, `field_options`, `module`, `placeholder_text`, `default_value`, `help_text`, `is_required`, `field_order`, `display_order`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(23, 'favorite_color', 'Favorite Color ', 'textarea', '[]', 'children', '', '', '', 0, 0, 0, 1, 1, '2025-11-25 06:50:33', '2025-11-25 06:50:33'),
(25, 'favorite_anime', 'anime', 'textarea', '[]', 'donations', 'Enter child\'s favorite anime', '', '', 0, 0, 0, 1, 1, '2025-11-25 15:51:31', '2025-11-25 15:51:31'),
(26, 'favorite_anime', 'anime', 'select', '{\"RED\":\"RED\",\"Kingdom\":\"Kindom\"}', 'foster', '', '', '', 0, 0, 0, 1, 1, '2025-11-25 15:54:35', '2025-11-25 16:02:39'),
(27, 'medical_history', 'medical', 'checkbox', '{\"RED\":\"RED\",\"Kingdom\":\"Kindom\"}', 'cases', '', '', '', 0, 0, 0, 1, 1, '2025-11-25 16:08:57', '2025-11-25 16:08:57');

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_groups`
--

CREATE TABLE `custom_field_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_label` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_group_assignments`
--

CREATE TABLE `custom_field_group_assignments` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `custom_field_values`
--

CREATE TABLE `custom_field_values` (
  `id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `record_id` varchar(50) NOT NULL,
  `record_type` varchar(50) NOT NULL,
  `field_value` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Document','Photo') DEFAULT 'Document',
  `file_path` varchar(255) NOT NULL,
  `date_uploaded` date NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `case_id`, `name`, `type`, `file_path`, `date_uploaded`, `uploaded_by`, `created_at`) VALUES
(1, 'UC-2025-002', '01f7cef8-1c1a-4f53-9e6b-3c0372103228.jpg', 'Photo', 'uploads/cases/UC-2025-002/68fcdfba87536_01f7cef8-1c1a-4f53-9e6b-3c0372103228.jpg', '2025-10-25', 'admin', '2025-10-25 14:33:30'),
(2, 'UC-2025-004', '01f7cef8-1c1a-4f53-9e6b-3c0372103228.jpg', 'Photo', 'uploads/cases/UC-2025-004/6917397801f12_01f7cef8-1c1a-4f53-9e6b-3c0372103228.jpg', '2025-11-14', 'admin', '2025-11-14 14:15:20'),
(3, 'UC-2025-003', 'IMG_20220615_133800.jpg', 'Photo', 'uploads/cases/UC-2025-003/6921804b0fcf0_IMG_20220615_133800.jpg', '2025-11-22', 'admin', '2025-11-22 09:20:11');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donation_id` varchar(20) NOT NULL,
  `donor_name` varchar(255) NOT NULL,
  `donor_contact` varchar(50) DEFAULT NULL,
  `donor_email` varchar(255) DEFAULT NULL,
  `donation_type` enum('Goods','Services','Other') NOT NULL,
  `description` text NOT NULL,
  `date_received` date NOT NULL,
  `status` enum('Received','Completed') NOT NULL DEFAULT 'Received',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cf_donation_value` decimal(10,2) DEFAULT NULL,
  `cf_receipt_number` varchar(100) DEFAULT NULL,
  `cf_purpose` varchar(255) DEFAULT NULL,
  `cf_acknowledgement_sent` tinyint(1) DEFAULT 0,
  `cf_favorite_anime` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donation_id`, `donor_name`, `donor_contact`, `donor_email`, `donation_type`, `description`, `date_received`, `status`, `notes`, `created_at`, `updated_at`, `cf_donation_value`, `cf_receipt_number`, `cf_purpose`, `cf_acknowledgement_sent`, `cf_favorite_anime`) VALUES
(7, 'DON-20251031-110909-', 'jenny', '021614', 'salubreemjay@gmail.com', 'Other', 'asdasdasdasd', '2025-10-31', 'Received', 'dasdasdasd', '2025-10-31 10:09:09', '2025-11-18 16:45:22', NULL, NULL, NULL, 0, NULL),
(8, 'DON-20251101-100551-', 'yey', '021614', 'salubreemjay@gmail.com', 'Services', 'asdasdasd', '2025-11-01', 'Received', 'dasdasdas', '2025-11-01 09:05:51', '2025-11-18 16:45:22', NULL, NULL, NULL, 0, NULL),
(10, 'DON-20251111-030911-', 'gio', '021614', 'salubreemjay@gmail.com', 'Goods', 'sadsasds', '2025-11-11', 'Received', 'dsadassd', '2025-11-11 02:09:11', '2025-11-18 16:45:22', NULL, NULL, NULL, 0, NULL),
(13, 'DON-20251111-121332-', 'emjay', '021614', 'salubreemjay@gmail.com', 'Goods', 'dsadasdasdasd', '2025-11-11', 'Received', 'dasdasdas', '2025-11-11 11:13:32', '2025-11-18 16:50:55', NULL, NULL, NULL, 0, NULL),
(14, 'DON-20251115-101953-', 'Anonymous Donor', '', '', 'Goods', 'sdasdasdasd', '2025-11-15', 'Completed', 'dsadasdas', '2025-11-15 09:19:53', '2025-11-18 16:49:51', NULL, NULL, NULL, 0, NULL),
(15, 'DON-20251119-024108-', 'Anonymous Donor', '021614', 'salubre@gmail.con', 'Goods', 'sdadad', '2025-11-19', 'Received', '', '2025-11-19 01:41:08', '2025-11-19 01:41:08', NULL, NULL, NULL, 0, NULL),
(16, 'DON-20251123-040617-', 'tacs', '4323423423', '', 'Goods', 'asfsdfsf', '2025-11-23', 'Received', '', '2025-11-23 03:06:17', '2025-11-23 03:06:17', NULL, NULL, NULL, 0, NULL),
(20, 'DON-20251125-164758-', 'Anonymous Donor', '', '', 'Services', 'fasaddasdas', '2025-11-25', 'Received', 'dasddadsdasdsasdasd', '2025-11-25 15:47:58', '2025-11-25 15:47:58', NULL, NULL, NULL, 0, NULL),
(21, 'DON-20251125-165200-', 'Anonymous Donor', '', '', 'Goods', 'dsdasdadsdasd', '2025-11-25', 'Received', '', '2025-11-25 15:52:00', '2025-11-25 15:52:00', NULL, NULL, NULL, 0, 'anime');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `event_id` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `status` enum('sent','failed') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `email_address`, `event_id`, `subject`, `sent_at`, `status`) VALUES
(1, 'salubreemjay@gmail.com', 'TEST-001', '? Orphanfare Meeting: Test Meeting - System Check - Oct 24, 2025 at 5:42 PM', '2025-10-24 23:42:42', 'sent'),
(2, 'olivertabar.7@gmail.com', 'TEST-001', '? Orphanfare Meeting: Test Meeting - System Check - Oct 24, 2025 at 5:53 PM', '2025-10-24 23:53:10', 'sent'),
(3, 'salubreemjay@gmail.com', 'EVT-2025-002', '? Orphanfare Meeting: nyek - Oct 25, 2025 at 12:30 AM', '2025-10-25 00:12:19', 'sent'),
(4, 'olivertabar.7@gmail.com', 'EVT-2025-002', '? Orphanfare Meeting: nyek - Oct 25, 2025 at 12:30 AM', '2025-10-25 00:18:16', 'sent'),
(5, 'olivertabar.7@gmail.com', 'EVT-2025-002', '? Orphanfare Meeting: nyek - Oct 25, 2025 at 12:30 AM', '2025-10-25 00:18:19', 'sent'),
(6, 'salubreemjay@gmail.com', 'TEST-001', '? Orphanfare Meeting: Test Meeting - System Check - Oct 25, 2025 at 6:39 AM', '2025-10-25 12:39:15', 'sent'),
(7, 'salubreemjay@gmail.com', 'EVT-2025-004', '? Orphanfare Meeting: wow - Oct 25, 2025 at 2:30 PM', '2025-10-25 12:43:36', 'sent'),
(8, 'salubreemjay11@gmail.com', 'EVT-2025-004', '? Orphanfare Meeting: wow - Oct 25, 2025 at 2:30 PM', '2025-10-25 12:44:10', 'sent'),
(9, 'salubreemjay@gmail.com', 'EVT-2025-013', '? Orphanfare Meeting: emjay - Nov 15, 2025 at 10:00 PM', '2025-11-15 03:08:47', 'sent'),
(10, 'salubreemjay@gmail.com', 'EVT-2025-015', '? Orphanfare Meeting: lah - Dec 5, 2025 at 1:59 PM', '2025-11-25 14:00:54', 'sent'),
(11, 'salubreemjay@gmail.com', 'EVT-2025-016', '? Orphanfare Meeting: lah - Dec 5, 2025 at 2:01 PM', '2025-11-25 14:01:59', 'sent');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `event_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `assigned_to` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cf_time_gold` date DEFAULT NULL,
  `cf_radio_buttons` varchar(255) DEFAULT NULL,
  `cf_phone_number` varchar(20) DEFAULT NULL,
  `cf_u_rl` varchar(255) DEFAULT NULL,
  `cf_email_mail` varchar(255) DEFAULT NULL,
  `cf_favorite_anime` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events`
--

<<<<<<< HEAD
INSERT INTO `events` (`id`, `event_id`, `title`, `description`, `event_type`, `event_date`, `event_time`, `location`, `assigned_to`, `notes`, `status`, `is_active`, `created_by`, `created_at`, `updated_at`, `cf_time_gold`, `cf_radio_buttons`, `cf_phone_number`, `cf_u_rl`, `cf_email_mail`, `cf_favorite_anime`) VALUES
(37, 'EVT-2025-001', 'nyek', '', 'home_visit', '2025-11-19', '10:24:00', 'storage room', 'dsasdas', '', 'Completed', 0, 8, '2025-11-19 02:24:01', '2025-11-19 04:01:24', NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'EVT-2025-002', 'home', '', 'home_visit', '2025-11-21', '12:12:00', 'storage room', '', '', 'Scheduled', 0, 8, '2025-11-19 04:08:50', '2025-11-19 04:09:19', NULL, NULL, NULL, NULL, NULL, NULL),
(55, 'EVT-2025-003', 'home', '', 'home_visit', '2025-11-28', '12:13:00', 'storage room', 'dsasdas', '', 'Scheduled', 1, 8, '2025-11-19 04:09:38', '2025-11-19 04:09:38', NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'EVT-2025-004', 'nyek ', '', 'home_visit', '2025-11-29', '12:25:00', '', '', '', 'Scheduled', 0, 8, '2025-11-19 04:22:42', '2025-11-19 04:25:07', NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'EVT-2025-005', 'nyek ', '', 'home_visit', '2025-11-29', '12:25:00', '', '', '', 'Scheduled', 0, 8, '2025-11-19 04:24:44', '2025-11-19 04:31:38', NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'EVT-2025-006', 'nyek ', '', 'home_visit', '2025-11-29', '12:25:00', '', '', '', 'Scheduled', 0, 8, '2025-11-19 04:24:49', '2025-11-19 05:21:02', NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'EVT-2025-007', 'yey', '', 'home_visit', '2025-11-19', '12:43:00', 'storage room', '', '', 'Completed', 1, 8, '2025-11-19 04:40:41', '2025-11-19 04:46:21', NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'EVT-2025-008', 'yet', '', 'home_visit', '2025-11-29', '17:00:00', '', '', '', 'Scheduled', 1, 8, '2025-11-19 04:57:00', '2025-11-19 04:57:00', NULL, NULL, NULL, NULL, NULL, NULL),
(61, 'EVT-2025-009', 'hello', '', 'home_visit', '2025-11-22', '16:20:00', '', '', '', 'Completed', 1, 8, '2025-11-22 08:16:10', '2025-11-22 08:32:50', NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'EVT-2025-010', 'yes', '', 'home_visit', '2025-12-04', '17:40:00', '', '', '', 'Scheduled', 1, 8, '2025-11-22 09:40:24', '2025-11-22 09:40:24', NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'EVT-2025-011', 'dance', '', 'home_visit', '2025-12-05', '19:25:00', 'storage room', '', '', 'Scheduled', 1, 8, '2025-11-22 11:22:00', '2025-11-22 11:22:00', NULL, NULL, NULL, NULL, NULL, NULL),
(64, 'EVT-2025-012', 'home', '', 'going_home', '2025-11-22', '19:55:00', '', '', '', 'Completed', 1, 8, '2025-11-22 11:52:00', '2025-11-22 11:56:06', NULL, NULL, NULL, NULL, NULL, NULL),
(65, 'EVT-2025-013', 'hello', '', 'home_visit', '2025-11-23', '13:09:00', 'storage room', '', '', 'Completed', 1, 8, '2025-11-23 14:09:56', '2025-11-23 14:09:56', NULL, NULL, NULL, NULL, NULL, NULL),
(66, 'EVT-2025-014', 'lah', '', 'home_visit', '2025-12-05', '13:03:00', '', '', '', 'Scheduled', 0, 8, '2025-11-25 05:59:06', '2025-11-25 05:59:32', NULL, NULL, NULL, NULL, NULL, NULL),
(67, 'EVT-2025-015', 'lah', '', 'home_visit', '2025-12-05', '13:59:00', '', '', '', 'Scheduled', 0, 8, '2025-11-25 05:59:48', '2025-11-25 06:01:13', NULL, NULL, NULL, NULL, NULL, NULL),
(68, 'EVT-2025-016', 'lah', 'asddasdasd', 'home_visit', '2025-12-05', '14:01:00', 'storage room', 'dsasdas', 'dasdasdasd', 'Scheduled', 1, 8, '2025-11-25 06:01:31', '2025-11-25 06:01:31', NULL, NULL, NULL, NULL, NULL, NULL);
=======
INSERT INTO `events` (`id`, `event_id`, `title`, `description`, `event_type`, `event_date`, `event_time`, `location`, `assigned_to`, `notes`, `status`, `is_active`, `created_by`, `created_at`, `updated_at`, `cf_time_management`, `cf_time_gold`, `cf_radio_buttons`, `cf_phone_number`, `cf_u_rl`, `cf_email_mail`, `cf_favorite_anime`) VALUES
(37, 'EVT-2025-001', 'nyek', '', 'home_visit', '2025-11-19', '10:24:00', 'storage room', 'dsasdas', '', 'Completed', 0, 8, '2025-11-19 02:24:01', '2025-11-19 04:01:24', '', NULL, NULL, NULL, NULL, NULL, NULL),
(54, 'EVT-2025-002', 'home', '', 'home_visit', '2025-11-21', '12:12:00', 'storage room', '', '', 'Scheduled', 0, 8, '2025-11-19 04:08:50', '2025-11-19 04:09:19', '', NULL, NULL, NULL, NULL, NULL, NULL),
(55, 'EVT-2025-003', 'home', '', 'home_visit', '2025-11-28', '12:13:00', 'storage room', 'dsasdas', '', 'Scheduled', 1, 8, '2025-11-19 04:09:38', '2025-11-19 04:09:38', '', NULL, NULL, NULL, NULL, NULL, NULL),
(56, 'EVT-2025-004', 'nyek ', '', 'home_visit', '2025-11-29', '12:25:00', '', '', '', 'Scheduled', 0, 8, '2025-11-19 04:22:42', '2025-11-19 04:25:07', '', NULL, NULL, NULL, NULL, NULL, NULL),
(57, 'EVT-2025-005', 'nyek ', '', 'home_visit', '2025-11-29', '12:25:00', '', '', '', 'Scheduled', 0, 8, '2025-11-19 04:24:44', '2025-11-19 04:31:38', '', NULL, NULL, NULL, NULL, NULL, NULL),
(58, 'EVT-2025-006', 'nyek ', '', 'home_visit', '2025-11-29', '12:25:00', '', '', '', 'Scheduled', 0, 8, '2025-11-19 04:24:49', '2025-11-19 05:21:02', '', NULL, NULL, NULL, NULL, NULL, NULL),
(59, 'EVT-2025-007', 'yey', '', 'home_visit', '2025-11-19', '12:43:00', 'storage room', '', '', 'Completed', 1, 8, '2025-11-19 04:40:41', '2025-11-19 04:46:21', '', NULL, NULL, NULL, NULL, NULL, NULL),
(60, 'EVT-2025-008', 'yet', '', 'home_visit', '2025-11-29', '17:00:00', '', '', '', 'Scheduled', 1, 8, '2025-11-19 04:57:00', '2025-11-19 04:57:00', '', NULL, NULL, NULL, NULL, NULL, NULL),
(61, 'EVT-2025-009', 'hello', '', 'home_visit', '2025-11-22', '16:20:00', '', '', '', 'Completed', 1, 8, '2025-11-22 08:16:10', '2025-11-22 08:32:50', '', NULL, NULL, NULL, NULL, NULL, NULL),
(62, 'EVT-2025-010', 'yes', '', 'home_visit', '2025-12-04', '17:40:00', '', '', '', 'Scheduled', 1, 8, '2025-11-22 09:40:24', '2025-11-22 09:40:24', '', NULL, NULL, NULL, NULL, NULL, NULL),
(63, 'EVT-2025-011', 'dance', '', 'home_visit', '2025-12-05', '19:25:00', 'storage room', '', '', 'Scheduled', 1, 8, '2025-11-22 11:22:00', '2025-11-22 11:22:00', '', NULL, NULL, NULL, NULL, NULL, NULL),
(64, 'EVT-2025-012', 'home', '', 'going_home', '2025-11-22', '19:55:00', '', '', '', 'Completed', 1, 8, '2025-11-22 11:52:00', '2025-11-22 11:56:06', '', NULL, NULL, NULL, NULL, NULL, NULL);
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

-- --------------------------------------------------------

--
-- Table structure for table `events_gallery`
--

CREATE TABLE `events_gallery` (
  `id` int(11) NOT NULL,
  `event_id` varchar(20) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `caption` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `events_gallery`
--

INSERT INTO `events_gallery` (`id`, `event_id`, `image_path`, `caption`, `description`, `uploaded_by`, `created_at`) VALUES
(28, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763802144.jpeg', '', '', 8, '2025-11-22 09:02:24'),
(29, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763802700.jpg', '', '', 8, '2025-11-22 09:11:40'),
(30, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763803160_0.jpg', '', '', 8, '2025-11-22 09:19:20'),
(31, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763803180_0.jpeg', '', '', 8, '2025-11-22 09:19:40'),
(32, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763803785.jpeg', '', '', 8, '2025-11-22 09:29:45'),
(33, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763804228.jpg', '', '', 8, '2025-11-22 09:37:08'),
(34, 'EVT-2025-008', 'uploads/schedule/gallery/event_EVT-2025-008_1763804253.jpg', '', '', 8, '2025-11-22 09:37:33'),
(35, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763806692.jpeg', '', '', 8, '2025-11-22 10:18:12'),
(36, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807068.jpg', '', '', 8, '2025-11-22 10:24:28'),
(37, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807083.jpg', '', '', 8, '2025-11-22 10:24:43'),
(38, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807093.jpeg', '', '', 8, '2025-11-22 10:24:53'),
(39, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807434.jpg', '', '', 8, '2025-11-22 10:30:34'),
(40, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807799.jpeg', '', '', 8, '2025-11-22 10:36:39'),
<<<<<<< HEAD
(41, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807814.jpg', 'this is meeting picture', 'dsadasdasd', 8, '2025-11-22 10:36:54'),
(42, 'EVT-2025-011', 'uploads/schedule/gallery/event_EVT-2025-011_1763862323.png', '', '', 8, '2025-11-23 01:45:23'),
(43, 'EVT-2025-011', 'uploads/schedule/gallery/event_EVT-2025-011_1763867738.png', '', '', 8, '2025-11-23 03:15:38'),
(44, 'EVT-2025-013', 'uploads/schedule/gallery/event_EVT-2025-013_1763907044.jpeg', '', '', 8, '2025-11-23 14:10:44');
=======
(41, 'EVT-2025-010', 'uploads/schedule/gallery/event_EVT-2025-010_1763807814.jpg', 'this is meeting picture', 'dsadasdasd', 8, '2025-11-22 10:36:54');
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

-- --------------------------------------------------------

--
-- Table structure for table `event_articles`
--

CREATE TABLE `event_articles` (
  `id` int(11) NOT NULL,
  `event_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE `event_types` (
  `id` int(11) NOT NULL,
  `type_key` varchar(50) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `icon` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `visible_to` longtext DEFAULT NULL CHECK (json_valid(`visible_to`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_types`
--

INSERT INTO `event_types` (`id`, `type_key`, `type_name`, `icon`, `is_active`, `created_at`, `visible_to`) VALUES
(1, 'home_visit', 'Home Visit', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-house-door\" viewBox=\"0 0 16 16\"><path d=\"M8.354 1.146a.5.5 0 0 0-.708 0l-6 6A.5.5 0 0 0 1.5 7.5v7a.5.5 0 0 0 .5.5h4.5a.5.5 0 0 0 .5-.5v-4h2v4a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 .5-.5v-7a.5.5 0 0 0-.146-.354L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293zM2.5 14V7.707l5.5-5.5 5.5 5.5V14H10v-4a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v4z\"/></svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\",\"admin\",\"Social Worker\"]'),
(2, 'meeting', 'Meeting', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-people\" viewBox=\"0 0 16 16\"><path d=\"M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.5.5 0 0 1 7 12.5c0-1.665.5-2.986 1-3.74.478-.768 1.048-1.227 1.5-1.227s1.022.459 1.5 1.227c.5.754 1 2.075 1 3.74a.5.5 0 0 1-.5.5zM6 12a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3A.5.5 0 0 1 6 12m-1-1.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5\"/></svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]'),
(3, 'team_building', 'Team Building', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-microsoft-teams\" viewBox=\"0 0 16 16\">   <path d=\"M9.186 4.797a2.42 2.42 0 1 0-2.86-2.448h1.178c.929 0 1.682.753 1.682 1.682zm-4.295 7.738h2.613c.929 0 1.682-.753 1.682-1.682V5.58h2.783a.7.7 0 0 1 .682.716v4.294a4.197 4.197 0 0 1-4.093 4.293c-1.618-.04-3-.99-3.667-2.35Zm10.737-9.372a1.674 1.674 0 1 1-3.349 0 1.674 1.674 0 0 1 3.349 0m-2.238 9.488-.12-.002a5.2 5.2 0 0 0 .381-2.07V6.306a1.7 1.7 0 0 0-.15-.725h1.792c.39 0 .707.317.707.707v3.765a2.6 2.6 0 0 1-2.598 2.598z\"/>   <path d=\"M.682 3.349h6.822c.377 0 .682.305.682.682v6.822a.68.68 0 0 1-.682.682H.682A.68.68 0 0 1 0 10.853V4.03c0-.377.305-.682.682-.682Zm5.206 2.596v-.72h-3.59v.72h1.357V9.66h.87V5.945z\"/> </svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]'),
(4, 'staff_training', 'Staff Training', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-person-arms-up\" viewBox=\"0 0 16 16\">   <path d=\"M8 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3\"/>   <path d=\"m5.93 6.704-.846 8.451a.768.768 0 0 0 1.523.203l.81-4.865a.59.59 0 0 1 1.165 0l.81 4.865a.768.768 0 0 0 1.523-.203l-.845-8.451A1.5 1.5 0 0 1 10.5 5.5L13 2.284a.796.796 0 0 0-1.239-.998L9.634 3.84a.7.7 0 0 1-.33.235c-.23.074-.665.176-1.304.176-.64 0-1.074-.102-1.305-.176a.7.7 0 0 1-.329-.235L4.239 1.286a.796.796 0 0 0-1.24.998l2.5 3.216c.317.316.475.758.43 1.204Z\"/> </svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]'),
(5, 'financial', 'Financial Review', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-bank\" viewBox=\"0 0 16 16\">   <path d=\"m8 0 6.61 3h.89a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5H15v7a.5.5 0 0 1 .485.38l.5 2a.498.498 0 0 1-.485.62H.5a.498.498 0 0 1-.485-.62l.5-2A.5.5 0 0 1 1 13V6H.5a.5.5 0 0 1-.5-.5v-2A.5.5 0 0 1 .5 3h.89zM3.777 3h8.447L8 1zM2 6v7h1V6zm2 0v7h2.5V6zm3.5 0v7h1V6zm2 0v7H12V6zM13 6v7h1V6zm2-1V4H1v1zm-.39 9H1.39l-.25 1h13.72z\"/> </svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]'),
(6, 'orientation', 'Orientation', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-megaphone\" viewBox=\"0 0 16 16\">   <path d=\"M13 2.5a1.5 1.5 0 0 1 3 0v11a1.5 1.5 0 0 1-3 0v-.214c-2.162-1.241-4.49-1.843-6.912-2.083l.405 2.712A1 1 0 0 1 5.51 15.1h-.548a1 1 0 0 1-.916-.599l-1.85-3.49-.202-.003A2.014 2.014 0 0 1 0 9V7a2.02 2.02 0 0 1 1.992-2.013 75 75 0 0 0 2.483-.075c3.043-.154 6.148-.849 8.525-2.199zm1 0v11a.5.5 0 0 0 1 0v-11a.5.5 0 0 0-1 0m-1 1.35c-2.344 1.205-5.209 1.842-8 2.033v4.233q.27.015.537.036c2.568.189 5.093.744 7.463 1.993zm-9 6.215v-4.13a95 95 0 0 1-1.992.052A1.02 1.02 0 0 0 1 7v2c0 .55.448 1.002 1.006 1.009A61 61 0 0 1 4 10.065m-.657.975 1.609 3.037.01.024h.548l-.002-.014-.443-2.966a68 68 0 0 0-1.722-.082z\"/> </svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]'),
<<<<<<< HEAD
(7, 'calamity_duty', 'Calamity Duty', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-tsunami\" viewBox=\"0 0 16 16\">\n  <path d=\"M.036 12.314a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.757-.703a.5.5 0 0 1-.278-.65m0 2a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.757-.703a.5.5 0 0 1-.278-.65M2.662 8.08c-.456 1.063-.994 2.098-1.842 2.804a.5.5 0 0 1-.64-.768c.652-.544 1.114-1.384 1.564-2.43.14-.328.281-.68.427-1.044.302-.754.624-1.559 1.01-2.308C3.763 3.2 4.528 2.105 5.7 1.299 6.877.49 8.418 0 10.5 0c1.463 0 2.511.4 3.179 1.058.67.66.893 1.518.819 2.302-.074.771-.441 1.516-1.02 1.965a1.88 1.88 0 0 1-1.904.27c-.65.642-.907 1.679-.71 2.614C11.076 9.215 11.784 10 13 10h2.5a.5.5 0 0 1 0 1H13c-1.784 0-2.826-1.215-3.114-2.585-.232-1.1.005-2.373.758-3.284L10.5 5.06l-.777.388a.5.5 0 0 1-.447 0l-1-.5a.5.5 0 0 1 .447-.894l.777.388.776-.388a.5.5 0 0 1 .447 0l1 .5.034.018c.44.264.81.195 1.108-.036.328-.255.586-.729.637-1.27.05-.529-.1-1.076-.525-1.495s-1.19-.77-2.477-.77c-1.918 0-3.252.448-4.232 1.123C5.283 2.8 4.61 3.738 4.07 4.79c-.365.71-.655 1.433-.945 2.16-.15.376-.301.753-.463 1.13\"/>\n</svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]');
=======
(7, 'calamity_duty', 'Calamity Duty', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-tsunami\" viewBox=\"0 0 16 16\">\n  <path d=\"M.036 12.314a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.757-.703a.5.5 0 0 1-.278-.65m0 2a.5.5 0 0 1 .65-.278l1.757.703a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.014-.406a2.5 2.5 0 0 1 1.857 0l1.015.406a1.5 1.5 0 0 0 1.114 0l1.757-.703a.5.5 0 1 1 .372.928l-1.758.703a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.014-.406a1.5 1.5 0 0 0-1.114 0l-1.015.406a2.5 2.5 0 0 1-1.857 0l-1.757-.703a.5.5 0 0 1-.278-.65M2.662 8.08c-.456 1.063-.994 2.098-1.842 2.804a.5.5 0 0 1-.64-.768c.652-.544 1.114-1.384 1.564-2.43.14-.328.281-.68.427-1.044.302-.754.624-1.559 1.01-2.308C3.763 3.2 4.528 2.105 5.7 1.299 6.877.49 8.418 0 10.5 0c1.463 0 2.511.4 3.179 1.058.67.66.893 1.518.819 2.302-.074.771-.441 1.516-1.02 1.965a1.88 1.88 0 0 1-1.904.27c-.65.642-.907 1.679-.71 2.614C11.076 9.215 11.784 10 13 10h2.5a.5.5 0 0 1 0 1H13c-1.784 0-2.826-1.215-3.114-2.585-.232-1.1.005-2.373.758-3.284L10.5 5.06l-.777.388a.5.5 0 0 1-.447 0l-1-.5a.5.5 0 0 1 .447-.894l.777.388.776-.388a.5.5 0 0 1 .447 0l1 .5.034.018c.44.264.81.195 1.108-.036.328-.255.586-.729.637-1.27.05-.529-.1-1.076-.525-1.495s-1.19-.77-2.477-.77c-1.918 0-3.252.448-4.232 1.123C5.283 2.8 4.61 3.738 4.07 4.79c-.365.71-.655 1.433-.945 2.16-.15.376-.301.753-.463 1.13\"/>\n</svg>', 1, '2025-11-18 12:14:26', '[\"super_admin\"]'),
(9, 'going_home', 'Home', '<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"currentColor\" class=\"bi bi-123\" viewBox=\"0 0 16 16\">\n  <path d=\"M2.873 11.297V4.142H1.699L0 5.379v1.137l1.64-1.18h.06v5.961zm3.213-5.09v-.063c0-.618.44-1.169 1.196-1.169.676 0 1.174.44 1.174 1.106 0 .624-.42 1.101-.807 1.526L4.99 10.553v.744h4.78v-.99H6.643v-.069L8.41 8.252c.65-.724 1.237-1.332 1.237-2.27C9.646 4.849 8.723 4 7.308 4c-1.573 0-2.36 1.064-2.36 2.15v.057zm6.559 1.883h.786c.823 0 1.374.481 1.379 1.179.01.707-.55 1.216-1.421 1.21-.77-.005-1.326-.419-1.379-.953h-1.095c.042 1.053.938 1.918 2.464 1.918 1.478 0 2.642-.839 2.62-2.144-.02-1.143-.922-1.651-1.551-1.714v-.063c.535-.09 1.347-.66 1.326-1.678-.026-1.053-.933-1.855-2.359-1.845-1.5.005-2.317.88-2.348 1.898h1.116c.032-.498.498-.944 1.206-.944.703 0 1.206.435 1.206 1.07.005.64-.504 1.106-1.2 1.106h-.75z\"/>\n</svg>', 1, '2025-11-22 11:50:59', '[\"super_admin\",\"admin\",\"Social Worker\",\"Social Welfare Assistant\",\"user\"]');
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

-- --------------------------------------------------------

--
-- Table structure for table `event_type_visibility`
--

CREATE TABLE `event_type_visibility` (
  `id` int(11) NOT NULL,
  `event_type_id` int(11) DEFAULT NULL,
  `role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `event_type_visibility`
--

INSERT INTO `event_type_visibility` (`id`, `event_type_id`, `role`) VALUES
(2, 1, 'admin'),
(4, 1, 'Social Welfare Assistant'),
(3, 1, 'Social Worker'),
(1, 1, 'super_admin'),
(5, 1, 'user'),
(7, 2, 'admin'),
(6, 2, 'super_admin'),
(9, 3, 'admin'),
(8, 3, 'super_admin'),
(11, 4, 'admin'),
(10, 4, 'super_admin'),
(13, 5, 'admin'),
(12, 5, 'super_admin'),
(15, 6, 'admin'),
(14, 6, 'super_admin'),
(17, 7, 'admin'),
(16, 7, 'super_admin');

-- --------------------------------------------------------

--
-- Table structure for table `evidence_photos`
--

CREATE TABLE `evidence_photos` (
  `id` int(11) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_date` date NOT NULL,
  `uploaded_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `evidence_photos`
--

INSERT INTO `evidence_photos` (`id`, `case_id`, `name`, `file_path`, `uploaded_date`, `uploaded_by`, `created_at`) VALUES
(0, 'UC-2025-002', '01f7cef8-1c1a-4f53-9e6b-3c0372103228.jpg', 'uploads/cases/UC-2025-002/evidence/69046c54a13ed_01f7cef8-1c1a-4f53-9e6b-3c0372103228.jpg', '2025-10-31', 'admin', '2025-10-31 07:59:16'),
(0, 'CASE-2025-003', 'IMG_20250505_104601_508.jpg', 'uploads/cases/CASE-2025-003/evidence/69254578c52f1_IMG_20250505_104601_508.jpg', '2025-11-25', 'admin', '2025-11-25 05:58:16');

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(11) NOT NULL,
  `field_group_id` int(11) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('text','textarea','number','email','url','select','checkbox','wysiwyg','image','file') NOT NULL,
  `instructions` text DEFAULT NULL,
  `required` tinyint(1) DEFAULT 0,
  `default_value` text DEFAULT NULL,
  `options` text DEFAULT NULL,
  `placeholder` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `field_groups`
--

CREATE TABLE `field_groups` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `post_type` varchar(100) NOT NULL,
  `position` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `field_values`
--

CREATE TABLE `field_values` (
  `id` int(11) NOT NULL,
  `field_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foster_documents`
--

CREATE TABLE `foster_documents` (
  `id` int(11) NOT NULL,
  `foster_id` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Photo','Document','Certificate','Assessment') DEFAULT 'Document',
  `file_path` varchar(500) NOT NULL,
  `date_uploaded` date NOT NULL,
  `uploaded_by` varchar(100) DEFAULT 'System',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `foster_documents`
--

INSERT INTO `foster_documents` (`id`, `foster_id`, `name`, `type`, `file_path`, `date_uploaded`, `uploaded_by`, `description`, `created_at`, `updated_at`) VALUES
(1, 'FT-2025-001', '488624001_2038651839961850_2853282580795075679_n.JPG', 'Photo', 'uploads/foster/FT-2025-001/691c3611c2d25_488624001_2038651839961850_2853282580795075679_n.JPG', '2025-11-18', 'admin', NULL, '2025-11-18 09:02:09', '2025-11-18 09:02:09');

-- --------------------------------------------------------

--
-- Table structure for table `foster_parents`
--

CREATE TABLE `foster_parents` (
  `id` int(11) NOT NULL,
  `foster_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `educational_attainment` varchar(100) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `civil_status` varchar(50) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `salary_multiplier` varchar(20) DEFAULT NULL,
  `monthly_income` varchar(50) DEFAULT NULL,
  `income_source` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive','Pending','Approved','Rejected') DEFAULT 'Pending',
  `family_planning` text DEFAULT NULL,
  `adoption_awareness` text DEFAULT NULL,
  `parenting_approach` text DEFAULT NULL,
  `age_preference` varchar(50) DEFAULT NULL,
  `gender_preference` varchar(20) DEFAULT NULL,
  `interests` text DEFAULT NULL,
  `personality_traits` text DEFAULT NULL,
  `experience_level` varchar(50) DEFAULT NULL,
  `problem_presented` text DEFAULT NULL,
  `assessment_recommendation` text DEFAULT NULL,
  `family_composition` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`family_composition`)),
  `assessment_date` date DEFAULT NULL,
  `social_worker_name` varchar(100) DEFAULT NULL,
  `psychological_evaluation` varchar(50) DEFAULT NULL,
  `psychologist_notes` text DEFAULT NULL,
  `overall_assessment` varchar(50) DEFAULT NULL,
  `dswd_referral_date` date DEFAULT NULL,
  `capacity` int(11) DEFAULT 1,
  `current_children` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cf_home_ownership` varchar(50) DEFAULT NULL,
  `cf_years_at_address` int(11) DEFAULT NULL,
  `cf_previous_experience` text DEFAULT NULL,
  `cf_references` text DEFAULT NULL,
  `cf_emergency_contact` varchar(100) DEFAULT NULL,
  `cf_favorite_anime` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `foster_parents`
--

INSERT INTO `foster_parents` (`id`, `foster_id`, `name`, `age`, `birth_date`, `birth_place`, `educational_attainment`, `religion`, `gender`, `civil_status`, `contact_number`, `email`, `address`, `occupation`, `salary_multiplier`, `monthly_income`, `income_source`, `status`, `family_planning`, `adoption_awareness`, `parenting_approach`, `age_preference`, `gender_preference`, `interests`, `personality_traits`, `experience_level`, `problem_presented`, `assessment_recommendation`, `family_composition`, `assessment_date`, `social_worker_name`, `psychological_evaluation`, `psychologist_notes`, `overall_assessment`, `dswd_referral_date`, `capacity`, `current_children`, `notes`, `photo_path`, `created_at`, `updated_at`, `cf_home_ownership`, `cf_years_at_address`, `cf_previous_experience`, `cf_references`, `cf_emergency_contact`, `cf_favorite_anime`) VALUES
(0, 'FT-2025-001', 'justines', 15, '2025-10-24', 'pasig city', 'college', 'catholic', 'Male', 'Single', '215616', 'superadmin@orphanfare.com', 'sadassdasd', 'Professional', '15615', '26516', 'ghost project', 'Pending', 'dasda', 'dasdasdasd', 'asdasdas', '0-10 years', 'Male', 'music', NULL, 'First-time', 'dasdasdas', 'dasdasdsad', NULL, '2025-10-24', 'System Administrator', 'Completed', 'dasdadas', 'Recommended', '2025-10-24', 1, 0, 'asdasdas', NULL, '2025-10-24 12:57:00', '2025-11-23 03:15:05', NULL, NULL, NULL, NULL, NULL, NULL),
(0, 'FT-2025-002', 'emjay', 54, '2025-11-23', 'pasig', 'college', 'catholic', 'Male', 'Single', '23432435', 'admin@orphanfare.com', '442', 'none', '4324', '4342', 'it', 'Pending', 'asddasd', 'dsadasdasdasd', 'dsadasdasdasd', NULL, 'No Preference', NULL, NULL, 'First-time', 'dsadasdasdsadasdas', 'dasdasdasdas', NULL, '2025-11-23', 'sdadasdasdasdas', 'Pending', 'dsadasda', 'Recommended', '2025-11-23', 1, 0, 'dasdasdas', NULL, '2025-11-23 03:13:39', '2025-11-25 16:02:57', NULL, NULL, NULL, NULL, NULL, 'RED'),
(0, 'FT-2025-002', 'emjay', 54, '2025-11-23', 'pasig', 'college', 'catholic', 'Male', 'Single', '23432435', 'admin@orphanfare.com', '442', 'none', '4324', '4342', 'it', 'Pending', 'asddasd', 'dsadasdasdasd', 'dsadasdasdasd', NULL, 'No Preference', NULL, NULL, 'First-time', 'dsadasdasdsadasdas', 'dasdasdasdas', NULL, '2025-11-23', 'sdadasdasdasdas', 'Pending', 'dsadasda', 'Recommended', '2025-11-23', 1, 0, 'dasdasdas', NULL, '2025-11-23 03:27:09', '2025-11-25 16:02:57', NULL, NULL, NULL, NULL, NULL, 'RED');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `item_id` varchar(20) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 10,
  `unit` varchar(20) DEFAULT 'pcs',
  `location` varchar(100) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `last_restocked` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cf_unit_cost` decimal(10,2) DEFAULT NULL,
  `cf_expiry_date` date DEFAULT NULL,
  `cf_batch_number` varchar(100) DEFAULT NULL,
  `cf_supplier_contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `item_id`, `item_name`, `category`, `quantity`, `min_stock_level`, `unit`, `location`, `supplier`, `last_restocked`, `notes`, `created_at`, `updated_at`, `cf_unit_cost`, `cf_expiry_date`, `cf_batch_number`, `cf_supplier_contact`) VALUES
(0, 'INV-2025-001', 'emjay', 'Clothing', 50, 10, 'pcs', 'storage room', 'tacs', '2025-10-24', 'dasdasd', '2025-10-23 16:47:14', '2025-10-23 16:47:14', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `legal_actions`
--

CREATE TABLE `legal_actions` (
  `id` int(11) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `type` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled') DEFAULT 'Scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `legal_actions`
--

INSERT INTO `legal_actions` (`id`, `case_id`, `type`, `date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'UC-2025-004', 'trial court', '2025-11-11', 'Scheduled', 'this is it', '2025-11-11 10:15:45', '2025-11-11 10:15:45'),
(2, 'UC-2025-004', 'Court Proceedings & Hearings', '2025-11-18', 'Scheduled', 'adasdasd', '2025-11-18 09:45:58', '2025-11-18 09:45:58'),
(3, 'UC-2025-004', 'Court Proceedings & Hearings', '2025-11-18', 'Scheduled', 'adasdasd', '2025-11-18 09:59:41', '2025-11-18 09:59:41'),
(4, 'UC-2025-003', 'Quasi-Judicial & Administrative Proceedings', '2025-11-21', 'Scheduled', 'dasdasdasdasd', '2025-11-18 10:22:03', '2025-11-18 10:22:03'),
(5, 'CASE-2025-003', 'Court Proceedings & Hearings', '2025-11-28', 'Scheduled', 'fdfsd', '2025-11-25 05:57:14', '2025-11-25 05:57:14');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_requests`
--

CREATE TABLE `meeting_requests` (
  `id` int(11) NOT NULL,
  `foster_id` varchar(20) NOT NULL,
  `child_id` varchar(20) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `requested_at` datetime NOT NULL,
  `scheduled_date` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `meeting_requests`
--

INSERT INTO `meeting_requests` (`id`, `foster_id`, `child_id`, `requested_by`, `status`, `requested_at`, `scheduled_date`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'FT-2025-001', 'UC-2025-003', 8, 'pending', '2025-11-17 03:07:12', NULL, NULL, '2025-11-16 19:07:12', '2025-11-16 19:07:12'),
(2, 'FT-2025-001', 'UC-2025-003', 8, 'pending', '2025-11-17 03:07:14', NULL, NULL, '2025-11-16 19:07:14', '2025-11-16 19:07:14'),
(3, 'FT-2025-001', 'UC-2025-003', 8, 'pending', '2025-11-17 03:08:06', NULL, NULL, '2025-11-16 19:08:06', '2025-11-16 19:08:06');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('event','schedule','system','alert','info','success') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `related_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `module` varchar(100) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  `can_create` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `role`, `module`, `can_view`, `can_edit`, `can_delete`, `can_create`) VALUES
(176, 'super_admin', 'dashboard', 1, 1, 1, 1),
(177, 'super_admin', 'child_management', 1, 1, 1, 1),
(178, 'super_admin', 'case_management', 1, 1, 1, 1),
(179, 'super_admin', 'donation', 1, 1, 1, 1),
(180, 'super_admin', 'inventory', 1, 1, 1, 1),
(181, 'super_admin', 'foster_info', 1, 1, 1, 1),
(182, 'super_admin', 'schedule', 1, 1, 1, 1),
(183, 'super_admin', 'reports', 1, 1, 1, 1),
(184, 'super_admin', 'settings', 1, 1, 1, 1),
(185, 'admin', 'dashboard', 1, 1, 1, 1),
(186, 'admin', 'child_management', 1, 1, 1, 1),
(187, 'admin', 'case_management', 1, 1, 1, 1),
(188, 'admin', 'donation', 1, 1, 1, 1),
(189, 'admin', 'inventory', 1, 1, 1, 1),
(190, 'admin', 'foster_info', 1, 1, 1, 1),
(191, 'admin', 'schedule', 1, 1, 1, 1),
(192, 'admin', 'reports', 1, 1, 1, 1),
(193, 'admin', 'settings', 1, 1, 1, 1),
(194, 'Social Worker', 'dashboard', 1, 1, 1, 1),
(195, 'Social Worker', 'child_management', 1, 1, 1, 1),
(196, 'Social Worker', 'case_management', 1, 1, 1, 1),
(197, 'Social Worker', 'donation', 1, 1, 1, 1),
(198, 'Social Worker', 'inventory', 1, 1, 1, 1),
(199, 'Social Worker', 'foster_info', 1, 1, 1, 1),
(200, 'Social Worker', 'schedule', 1, 1, 1, 1),
(201, 'Social Worker', 'reports', 1, 1, 1, 1),
(202, 'Social Worker', 'settings', 0, 0, 0, 0),
(203, 'Social Welfare Assistant', 'dashboard', 1, 0, 0, 1),
(204, 'Social Welfare Assistant', 'child_management', 1, 0, 0, 0),
(205, 'Social Welfare Assistant', 'case_management', 1, 0, 0, 0),
(206, 'Social Welfare Assistant', 'donation', 1, 0, 0, 0),
(207, 'Social Welfare Assistant', 'inventory', 1, 0, 0, 0),
(208, 'Social Welfare Assistant', 'foster_info', 1, 0, 0, 0),
(209, 'Social Welfare Assistant', 'schedule', 1, 1, 1, 1),
(210, 'Social Welfare Assistant', 'reports', 1, 0, 0, 0),
(211, 'Social Welfare Assistant', 'settings', 0, 0, 0, 0),
(212, 'user', 'dashboard', 1, 1, 1, 1),
(213, 'user', 'schedule', 1, 1, 1, 1),
(220, 'super_admin', 'custom_fields', 1, 1, 1, 1),
(226, 'super_admin', 'user_management', 1, 1, 1, 1),
(227, 'admin', 'user_management', 1, 1, 1, 1),
(228, 'Social Worker', 'user_management', 0, 0, 0, 0),
(229, 'Social Welfare Assistant', 'user_management', 0, 0, 0, 0),
(230, 'user', 'user_management', 0, 0, 0, 0),
(231, 'user', 'settings', 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `protective_actions`
--

CREATE TABLE `protective_actions` (
  `id` int(11) NOT NULL,
  `action_id` varchar(50) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `case_type` varchar(100) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `priority` varchar(20) NOT NULL,
  `justification` text NOT NULL,
  `notifications` text DEFAULT NULL,
  `coordinating_officer` varchar(100) NOT NULL,
  `case_description` text DEFAULT NULL,
  `followup_date` datetime DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` varchar(20) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `protective_actions`
--

INSERT INTO `protective_actions` (`id`, `action_id`, `case_id`, `case_type`, `action_type`, `priority`, `justification`, `notifications`, `coordinating_officer`, `case_description`, `followup_date`, `created_by`, `created_at`, `status`) VALUES
(1, 'ACT-20251023060039751', 'CS-2025-001', 'Physical Abuse', 'Safety Plan Implementation', 'urgent', 'dasdasd', 'Supervisor, Legal Department, Law Enforcement, Child Protection Unit', 'Officer Mike Johnson', 'dasdas', '2025-10-23 11:41:00', 8, '2025-10-23 12:00:39', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_requests`
--

CREATE TABLE `role_change_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_role_value` varchar(50) NOT NULL,
  `requested_role_value` varchar(50) NOT NULL,
  `request_reason` text DEFAULT NULL,
  `request_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by_user` int(11) DEFAULT NULL,
  `reviewed_at_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `role_change_requests`
--

INSERT INTO `role_change_requests` (`id`, `user_id`, `current_role_value`, `requested_role_value`, `request_reason`, `request_status`, `reviewed_by_user`, `reviewed_at_time`, `created_at`, `updated_at`) VALUES
(1, 15, 'Social Welfare Assistant', 'admin', '', 'pending', NULL, NULL, '2025-11-25 04:42:31', '2025-11-25 04:42:31'),
(2, 15, 'Social Welfare Assistant', 'Social Worker', '', 'pending', NULL, NULL, '2025-11-25 04:46:56', '2025-11-25 04:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `role_change_requests_new`
--

CREATE TABLE `role_change_requests_new` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `current_user_role` varchar(50) NOT NULL,
  `requested_role` varchar(50) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `can_view` tinyint(1) DEFAULT 0,
  `can_create` tinyint(1) DEFAULT 0,
  `can_edit` tinyint(1) DEFAULT 0,
  `can_delete` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission_key`, `can_view`, `can_create`, `can_edit`, `can_delete`, `created_at`) VALUES
(1, 'super_admin', 'schedule_view', 1, 1, 1, 1, '2025-11-18 17:41:10'),
(2, 'admin', 'schedule_view', 1, 1, 1, 1, '2025-11-18 17:41:10'),
(3, 'Social Worker', 'schedule_view', 1, 1, 1, 0, '2025-11-18 17:41:10'),
(4, 'Social Welfare Assistant', 'schedule_view', 1, 0, 0, 0, '2025-11-18 17:41:10'),
(5, 'user', 'schedule_view', 1, 0, 0, 0, '2025-11-18 17:41:10');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_activities`
--

CREATE TABLE `schedule_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` enum('event_created','event_updated','event_deleted','status_changed','email_sent') NOT NULL,
  `event_id` varchar(20) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `schedule_activities`
--

INSERT INTO `schedule_activities` (`id`, `user_id`, `action_type`, `event_id`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 8, 'event_created', 'EVT-2025-003', 'Event: yahoo | Type: staff_training', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-24 16:48:27'),
(2, 8, 'event_created', 'EVT-2025-004', 'Event: wow | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:26:39'),
(3, 8, 'email_sent', 'EVT-2025-004', 'Recipients: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:36:50'),
(4, 8, 'email_sent', 'EVT-2025-004', 'Recipients: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:37:22'),
(5, 8, 'email_sent', 'EVT-2025-004', 'Recipients: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:38:20'),
(6, 8, 'email_sent', 'EVT-2025-004', 'Recipients: 0', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:40:08'),
(7, 8, 'email_sent', 'EVT-2025-004', 'Recipients: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:43:36'),
(8, 8, 'email_sent', 'EVT-2025-004', 'Recipients: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-25 04:44:10'),
(9, 8, 'event_created', 'EVT-2025-005', 'Event: yey | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-27 05:59:58'),
(10, 8, 'event_deleted', 'EVT-2025-003', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 10:13:31'),
(11, 8, 'event_deleted', 'EVT-2025-005', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-10-31 10:14:48'),
(12, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 09:00:02'),
(13, 8, 'event_deleted', 'EVT-2025-001', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 09:00:10'),
(14, 8, 'event_deleted', 'EVT-2025-002', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:16:15'),
(15, 8, 'event_created', 'EVT-2025-006', 'Event: meet | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:16:59'),
(16, 8, 'event_created', 'EVT-2025-007', 'Event: meet | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:18:40'),
(17, 8, 'event_created', 'EVT-2025-008', 'Event: meet | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:31:05'),
(18, 8, 'event_deleted', 'EVT-2025-007', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:39:19'),
(19, 8, 'event_deleted', 'EVT-2025-008', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:39:23'),
(20, 8, 'event_created', 'EVT-2025-009', 'Event: jikj | Type: meeting', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:39:45'),
(21, 8, 'status_changed', 'EVT-2025-009', 'New status: Completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-01 11:40:20'),
(22, 8, 'event_deleted', 'EVT-2025-009', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-02 11:24:06'),
(23, 1, 'event_created', 'EVT-2025-010', 'Event: salubre | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 03:54:44'),
(24, 1, 'event_deleted', 'EVT-2025-010', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:02:31'),
(25, 1, 'event_deleted', 'EVT-2025-006', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:03:27'),
(26, 1, 'event_created', 'EVT-2025-010', 'Event: salubre | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:05:30'),
(27, 1, 'event_deleted', 'EVT-2025-010', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:05:36'),
(28, 1, 'event_created', 'EVT-2025-011', 'Event: gio | Type: staff_training', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-11 04:06:21'),
(29, 8, 'event_created', 'EVT-2025-012', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:00:10'),
(30, 8, 'event_deleted', 'EVT-2025-012', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:00:33'),
(31, 8, 'event_created', 'EVT-2025-013', 'Event: emjay | Type: meeting', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:01:00'),
(32, 8, '', 'EVT-2025-011', 'Photo: this is meeting picture | Event: EVT-2025-011', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:02:29'),
(33, 8, '', 'EVT-2025-011', 'Photo: this is meeting picture | Event: EVT-2025-011', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 14:46:52'),
(34, 8, '', 'EVT-2025-013', 'Article: title | Event: EVT-2025-013', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 15:56:41'),
(35, 8, '', 'EVT-2025-011', 'Photos: 1 | Event: EVT-2025-011', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 15:57:05'),
(36, 8, '', 'EVT-2025-013', 'Photo: this is meeting picture | Event: EVT-2025-013', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 16:43:32'),
(37, 8, 'event_created', 'EVT-2025-014', 'Event: Jenny | Type: meeting', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:02:32'),
(38, 8, 'event_deleted', 'EVT-2025-014', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:06:27'),
(39, 8, 'event_created', 'EVT-2025-015', 'Event: dasd | Type: meeting', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:11:40'),
(40, 8, 'event_deleted', 'EVT-2025-015', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 17:13:36'),
(41, 8, 'email_sent', 'EVT-2025-013', 'Recipients: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-14 19:08:47'),
(42, 8, 'event_created', 'EVT-2025-016', 'Event: jus | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:08:46'),
(43, 8, 'event_deleted', 'EVT-2025-016', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:09:14'),
(44, 8, 'event_created', 'EVT-2025-017', 'Event: jus | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:09:54'),
(45, 8, 'event_deleted', 'EVT-2025-017', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-15 11:17:39'),
(46, 8, 'event_created', 'EVT-2025-018', 'Event: yey | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:03:34'),
(47, 8, 'event_created', 'EVT-2025-019', 'Event: no | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-17 17:04:51'),
(48, 15, '', 'EVT-2025-018', 'Photo: this is meeting picture | Event: EVT-2025-018', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 15:57:57'),
(49, 15, 'event_created', 'EVT-2025-021', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:37:31'),
(50, 15, 'event_created', 'EVT-2025-022', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:37:54'),
(51, 1, 'event_created', 'EVT-2025-023', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:15'),
(52, 1, 'event_created', 'EVT-2025-024', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:17'),
(53, 1, 'event_created', 'EVT-2025-025', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:17'),
(54, 1, 'event_created', 'EVT-2025-026', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:18'),
(55, 1, 'event_created', 'EVT-2025-027', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:18'),
(56, 1, 'event_created', 'EVT-2025-028', 'Event: emjay | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:19'),
(57, 1, 'event_deleted', 'EVT-2025-021', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:39:48'),
(58, 1, 'event_deleted', 'EVT-2025-021', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:41:55'),
(59, 1, 'event_deleted', 'EVT-2025-021', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:46:48'),
(60, 1, 'event_deleted', 'EVT-2025-021', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:48:21'),
(61, 1, 'event_deleted', 'EVT-2025-011', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-18 17:48:30'),
(62, 8, 'event_deleted', 'EVT-2025-022', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:09:08'),
(63, 8, 'event_deleted', 'EVT-2025-023', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:31:02'),
(64, 8, 'event_created', 'EVT-2025-029', 'Event: nyek | Type: calamity_duty', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:40:27'),
(65, 8, 'event_created', 'EVT-2025-030', 'Event: nyek | Type: calamity_duty', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:40:31'),
(66, 8, 'event_created', 'EVT-2025-001', 'Event: nyek | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:42:44'),
(67, 8, 'event_deleted', 'EVT-2025-001', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:43:55'),
(68, 8, 'event_created', 'EVT-2025-002', 'Event: nyek | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:50:04'),
(69, 8, 'event_created', 'EVT-2025-003', 'Event: how | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 01:50:48'),
(70, 8, 'event_deleted', 'EVT-2025-002', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:19:50'),
(71, 8, 'event_deleted', 'EVT-2025-003', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:20:15'),
(72, 8, 'event_deleted', 'EVT-2025-003', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:20:20'),
(73, 8, 'event_deleted', 'EVT-2025-002', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:23:18'),
(74, 8, 'event_deleted', 'EVT-2025-001', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:23:36'),
(75, 8, 'event_created', 'EVT-2025-002', 'Event: nyek | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:26:26'),
(76, 8, 'event_created', 'EVT-2025-003', 'Event: home | Type: meeting', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:27:09'),
(77, 8, 'event_deleted', 'EVT-2025-003', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:27:30'),
(78, 8, 'event_created', 'EVT-2025-004', 'Event: home | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 02:47:49'),
(79, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:06:53'),
(80, 8, '', 'EVT-2025-001', 'Photo: this is meeting picture | Event: EVT-2025-001', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:10:47'),
(81, 8, 'event_deleted', 'EVT-2025-002', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:11:35'),
(82, 8, 'event_deleted', 'EVT-2025-006', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:24:22'),
(83, 8, 'event_deleted', 'EVT-2025-005', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:25:49'),
(84, 8, 'event_created', 'EVT-2025-004', 'Event: home | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:57:43'),
(85, 8, 'event_created', 'EVT-2025-005', 'Event: home | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 03:58:00'),
(86, 8, 'event_deleted', 'EVT-2025-008', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:10'),
(87, 8, 'event_deleted', 'EVT-2025-007', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:13'),
(88, 8, 'event_deleted', 'EVT-2025-005', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:17'),
(89, 8, 'event_deleted', 'EVT-2025-003', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:20'),
(90, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:22'),
(91, 8, 'event_deleted', 'EVT-2025-006', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:03:24'),
(92, 8, 'event_deleted', 'EVT-2025-002', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:09:19'),
(93, 8, 'event_created', 'EVT-2025-003', 'Event: home | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:09:38'),
(94, 8, 'event_created', 'EVT-2025-005', 'Event: nyek  | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:24:44'),
(95, 8, 'event_created', 'EVT-2025-006', 'Event: nyek  | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:24:49'),
(96, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:25:07'),
(97, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:26:04'),
(98, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:30:30'),
(99, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:31:18'),
(100, 8, 'event_deleted', 'EVT-2025-004', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:31:35'),
(101, 8, 'event_deleted', 'EVT-2025-005', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:31:38'),
(102, 8, 'event_created', 'EVT-2025-007', 'Event: yey | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:40:41'),
(103, 8, 'event_created', 'EVT-2025-008', 'Event: yet | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 04:57:00'),
(104, 8, 'event_deleted', 'EVT-2025-006', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-19 05:21:02'),
(105, 8, '', 'EVT-2025-007', 'Photo: this is meeting picture | Event: EVT-2025-007', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 06:33:53'),
(106, 8, '', 'EVT-2025-008', 'Photos: 1 | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:14:36'),
(107, 8, '', 'EVT-2025-008', 'Photos: 1 | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:15:01'),
(108, 8, '', 'EVT-2025-003', 'Photo: this is meeting picture | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:24:35'),
(109, 8, '', 'EVT-2025-003', 'Photo: this is meeting picture | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:25:19'),
(110, 8, '', 'EVT-2025-003', 'Photos: 1 | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:30:48'),
(111, 8, '', 'EVT-2025-003', 'Photos: 1 | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:30:53'),
(112, 8, '', 'EVT-2025-003', 'Photos: 1 | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:34:27'),
(113, 8, '', 'EVT-2025-008', 'Photos: 1 | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:34:41'),
(114, 8, '', 'EVT-2025-008', 'Photos: 1 | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:37:27'),
(115, 8, '', 'EVT-2025-003', 'Photos: 1 | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:48:41'),
(116, 8, '', 'EVT-2025-003', 'Photo: this is meeting | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:55:30'),
(117, 8, '', 'EVT-2025-003', 'Photo:  | Event: EVT-2025-003', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 07:59:08'),
(118, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:02:37'),
(119, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:07:09'),
(120, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:07:32'),
(121, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:07:32'),
(122, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:14:26'),
(123, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:15:05'),
(124, 8, 'event_created', 'EVT-2025-009', 'Event: hello | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:16:10'),
(125, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:33:02'),
(126, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 08:38:33'),
(127, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:02:24'),
(128, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:11:40'),
(129, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:29:45'),
(130, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:37:08'),
(131, 8, '', 'EVT-2025-008', 'Photo:  | Event: EVT-2025-008', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:37:33'),
(132, 8, 'event_created', 'EVT-2025-010', 'Event: yes | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 09:40:24'),
(133, 8, '', 'EVT-2025-010', 'Photo:  | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:18:13'),
(134, 8, '', 'EVT-2025-010', 'Photo:  | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:24:28'),
(135, 8, '', 'EVT-2025-010', 'Photo:  | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:24:43'),
(136, 8, '', 'EVT-2025-010', 'Photo:  | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:24:53'),
(137, 8, '', 'EVT-2025-010', 'Photo:  | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:30:34'),
(138, 8, '', 'EVT-2025-010', 'Photo:  | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:36:39'),
(139, 8, '', 'EVT-2025-010', 'Photo: this is meeting picture | Event: EVT-2025-010', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 10:36:54'),
(140, 8, 'event_created', 'EVT-2025-011', 'Event: dance | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:22:00'),
<<<<<<< HEAD
(141, 8, 'event_created', 'EVT-2025-012', 'Event: home | Type: going_home', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:52:00'),
(142, 8, '', 'EVT-2025-011', 'Photo:  | Event: EVT-2025-011', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 01:45:23'),
(143, 8, '', 'EVT-2025-011', 'Photo:  | Event: EVT-2025-011', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 03:15:38'),
(144, 8, 'event_created', 'EVT-2025-013', 'Event: hello | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 14:09:56'),
(145, 8, '', 'EVT-2025-013', 'Photo:  | Event: EVT-2025-013', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-23 14:10:44'),
(146, 8, 'event_created', 'EVT-2025-014', 'Event: lah | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:59:06'),
(147, 8, 'event_deleted', 'EVT-2025-014', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:59:32'),
(148, 8, 'event_created', 'EVT-2025-015', 'Event: lah | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 05:59:48'),
(149, 8, 'email_sent', 'EVT-2025-015', 'Recipients: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:00:54'),
(150, 8, 'event_deleted', 'EVT-2025-015', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:01:13'),
(151, 8, 'event_created', 'EVT-2025-016', 'Event: lah | Type: home_visit', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:01:32'),
(152, 8, 'email_sent', 'EVT-2025-016', 'Recipients: 1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 06:01:59');
=======
(141, 8, 'event_created', 'EVT-2025-012', 'Event: home | Type: going_home', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-22 11:52:00');
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

-- --------------------------------------------------------

--
-- Table structure for table `sms_contacts`
--

CREATE TABLE `sms_contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `event_id` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `status` enum('sent','failed') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `social_services`
--

CREATE TABLE `social_services` (
  `id` int(11) NOT NULL,
  `case_id` varchar(20) NOT NULL,
  `type` varchar(100) NOT NULL,
  `date_started` date NOT NULL,
  `status` enum('Ongoing','Completed','Cancelled') DEFAULT 'Ongoing',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_services`
--

INSERT INTO `social_services` (`id`, `case_id`, `type`, `date_started`, `status`, `details`, `created_at`, `updated_at`) VALUES
(1, 'UC-2025-003', 'Family & Community Support', '2025-11-18', 'Ongoing', 'xczxczxczxczx', '2025-11-18 10:22:09', '2025-11-18 10:22:09');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, '1', 'min_password_length', '2025-10-21 13:54:36', '2025-10-21 13:54:36'),
(2, '0', 'require_special_chars', '2025-10-21 13:54:36', '2025-10-21 13:54:36'),
(4, 'min_password_length', '10', '2025-10-21 14:05:20', '2025-11-15 06:51:17'),
(5, 'require_special_chars', '1', '2025-10-21 14:05:20', '2025-10-21 14:05:20'),
(6, 'require_numbers', '1', '2025-10-21 14:05:20', '2025-10-21 14:05:20'),
(7, 'require_uppercase', '1', '2025-10-21 14:05:20', '2025-11-15 06:39:20'),
(8, 'two_factor_auth', '0', '2025-10-21 14:05:20', '2025-10-21 14:05:20'),
(9, 'session_timeout', '30 minutes', '2025-10-21 14:05:20', '2025-11-15 09:09:38'),
(10, 'organization_name', 'Orphanfare Children\\\'s Home', '2025-10-21 14:22:02', '2025-11-15 07:08:50'),
(11, 'contact_email', 'admin@orphanfare.org', '2025-10-21 14:22:02', '2025-10-21 14:22:02'),
(12, 'phone_number', '+63 (969) 164-5421', '2025-10-21 14:22:02', '2025-10-21 14:22:02'),
(14, 'email_notifications', '1', '2025-11-01 15:06:24', '2025-11-01 15:06:24'),
(15, 'medical_alerts', '1', '2025-11-01 15:06:24', '2025-11-01 15:06:24'),
(16, 'case_updates', '0', '2025-11-01 15:06:24', '2025-11-01 15:06:24'),
(17, 'inventory_alerts', '1', '2025-11-01 15:06:24', '2025-11-01 15:06:24'),
(22, 'require_lowercase', '1', '2025-11-11 09:21:59', '2025-11-11 09:21:59'),
(23, 'max_login_attempts', '3', '2025-11-11 09:21:59', '2025-11-15 09:11:55'),
(24, 'lockout_attempts', '5', '2025-11-11 09:21:59', '2025-11-11 09:21:59'),
(26, 'superadmin_2fa_required', '0', '2025-11-15 06:41:48', '2025-11-15 06:41:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','user','Social Worker','Social Welfare Assistant') DEFAULT 'user',
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_verified` tinyint(1) DEFAULT 0,
  `two_factor_backup_codes` text DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_password_change` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `account_locked` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `updated_at`, `two_factor_secret`, `two_factor_enabled`, `two_factor_verified`, `two_factor_backup_codes`, `last_login`, `last_password_change`, `failed_attempts`, `account_locked`, `is_active`) VALUES
<<<<<<< HEAD
(1, 'superadmin', 'superadmin@orphanfare.com', '$2y$10$1ta75myHWaNw9yPDdZUcR.s6bqyJsW1dUcIabdVoT5NV4za/SIC9C', 'super_admin', 'active', '2025-10-20 15:59:41', '2025-11-25 16:51:15', NULL, 0, 0, NULL, '2025-11-25 16:51:15', '2025-11-11 08:48:40', 0, 0, 1),
(7, 'superuser', 'superuser@orphanfare.com', '$2y$10$Nn5wIMgk6XVC0HVVG.TMCebPweQFrlp8zgfYqZlQ3BPMhY1Qr.jmu', 'super_admin', 'active', '2025-10-21 10:30:53', '2025-10-21 10:30:53', NULL, 0, 0, NULL, NULL, '2025-11-11 08:48:40', 0, 0, 1),
(8, 'admin', 'admin@orphanfare.com', '$2y$10$td0sFX/r2hB8z/Z09C1ht.iea67Mylk1j5raSykVDpbCG9A1OnkJa', 'admin', 'active', '2025-10-21 10:36:58', '2025-11-25 16:49:51', NULL, 0, 0, NULL, '2025-11-25 16:49:51', '2025-11-11 08:48:40', 0, 0, 1),
=======
(1, 'superadmin', 'superadmin@orphanfare.com', '$2y$10$1ta75myHWaNw9yPDdZUcR.s6bqyJsW1dUcIabdVoT5NV4za/SIC9C', 'super_admin', 'active', '2025-10-20 15:59:41', '2025-11-22 11:50:24', NULL, 0, 0, NULL, '2025-11-22 11:50:24', '2025-11-11 08:48:40', 0, 0, 1),
(7, 'superuser', 'superuser@orphanfare.com', '$2y$10$Nn5wIMgk6XVC0HVVG.TMCebPweQFrlp8zgfYqZlQ3BPMhY1Qr.jmu', 'super_admin', 'active', '2025-10-21 10:30:53', '2025-10-21 10:30:53', NULL, 0, 0, NULL, NULL, '2025-11-11 08:48:40', 0, 0, 1),
(8, 'admin', 'admin@orphanfare.com', '$2y$10$td0sFX/r2hB8z/Z09C1ht.iea67Mylk1j5raSykVDpbCG9A1OnkJa', 'admin', 'active', '2025-10-21 10:36:58', '2025-11-22 11:51:16', NULL, 0, 0, NULL, '2025-11-22 11:51:16', '2025-11-11 08:48:40', 0, 0, 1),
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12
(9, 'emjay', 'salubreemjay@gmail.com', '$2y$10$sab1GE8wxIkXiyUNK53t3egcnqPVQycNWAFnV207hWkkImzp49.1e', 'admin', 'active', '2025-10-21 11:57:40', '2025-11-17 14:14:15', NULL, 0, 0, NULL, '2025-11-17 14:14:15', '2025-11-11 08:48:40', 0, 0, 1),
(10, 'tacs', 'admin@carwash.com', '$2y$10$yexoDibRd3rDYlucYBXdDuxZUwMXaRx6VhysyfRf0v1zDNbbwjp1m', 'user', 'active', '2025-10-21 12:02:19', '2025-11-25 14:58:37', NULL, 0, 0, NULL, '2025-11-25 14:58:37', '2025-11-11 08:48:40', 2, 0, 1),
(11, 'luh', 'akawa@gmail.com', '$2y$10$K09DGa8DkQmH57baJrNOreddh3/vpjgOyKPNjqcLTKG2LQBTvYFOG', 'user', 'active', '2025-10-25 10:24:18', '2025-11-18 15:47:23', NULL, 0, 0, NULL, '2025-11-18 15:47:23', '2025-11-11 08:48:40', 6, 1, 1),
(12, 'yey', 'emjaysalubre11@yahoo.com.ph', '$2y$10$Lpeb0tsdEnANk79KeOe3V.0DSx0l74Rt.bnoWahxfEly6geLZu9/m', 'user', 'active', '2025-10-25 11:01:50', '2025-11-17 14:11:25', NULL, 0, 0, NULL, '2025-11-17 14:11:25', '2025-11-11 08:48:40', 2, 0, 1),
(13, 'socialworker', 'socialworker@orphanfare.com', '$2y$10$examplepasswordhash', 'Social Worker', 'active', '2025-10-25 11:04:28', '2025-10-25 11:28:34', NULL, 0, 0, NULL, NULL, '2025-11-11 08:48:40', 0, 0, 1),
(14, 'com', 'com@gmail.com', '$2y$10$zRP/jofiYKXNjpNtOMDab.p8Eb3bnG4Qc5Ozl4O7AmqUZ5OmFGchy', 'user', 'active', '2025-10-25 11:23:40', '2025-11-23 09:20:58', NULL, 0, 0, NULL, '2025-11-23 09:20:58', '2025-11-15 10:13:48', 6, 1, 1),
(15, 'emjay11', 'emjay@email.com', '$2y$10$8iAtDcppg/gSFw8f1hE1befT7eGafiRDdkGh7MZbZir1wALw72WVm', 'Social Worker', 'active', '2025-11-18 15:54:27', '2025-11-25 16:48:47', NULL, 0, 0, NULL, '2025-11-25 16:48:47', '2025-11-18 15:54:27', 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `type` varchar(50) DEFAULT 'info',
  `related_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_role_requests`
--

CREATE TABLE `user_role_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_role` varchar(50) NOT NULL,
  `to_role` varchar(50) NOT NULL,
  `request_reason` text DEFAULT NULL,
  `request_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE `user_settings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_requests`
--
ALTER TABLE `access_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_log_admin`
--
ALTER TABLE `audit_log_admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendar_availability`
--
ALTER TABLE `calendar_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date` (`unavailable_date`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_id` (`case_id`),
  ADD KEY `idx_cases_linked_child` (`linked_child_id`),
  ADD KEY `idx_cases_created_by` (`created_by`);

--
-- Indexes for table `children`
--
ALTER TABLE `children`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_id` (`child_id`),
  ADD KEY `idx_children_linked_case` (`linked_case_id`),
  ADD KEY `idx_children_created_by` (`created_by`);

--
-- Indexes for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `custom_field_groups`
--
ALTER TABLE `custom_field_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `custom_field_group_assignments`
--
ALTER TABLE `custom_field_group_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_group_field` (`group_id`,`field_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_field_value` (`field_id`,`record_id`,`record_type`),
  ADD KEY `idx_record` (`record_id`,`record_type`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `donation_id` (`donation_id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`);

--
-- Indexes for table `events_gallery`
--
ALTER TABLE `events_gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_articles`
--
ALTER TABLE `event_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_key` (`type_key`);

--
-- Indexes for table `event_type_visibility`
--
ALTER TABLE `event_type_visibility`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_visibility` (`event_type_id`,`role`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_group_id` (`field_group_id`);

--
-- Indexes for table `field_groups`
--
ALTER TABLE `field_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `field_values`
--
ALTER TABLE `field_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `foster_documents`
--
ALTER TABLE `foster_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_foster_id` (`foster_id`),
  ADD KEY `idx_date_uploaded` (`date_uploaded`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_id` (`item_id`);

--
-- Indexes for table `legal_actions`
--
ALTER TABLE `legal_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `meeting_requests`
--
ALTER TABLE `meeting_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_foster_id` (`foster_id`),
  ADD KEY `idx_child_id` (`child_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `requested_by` (`requested_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_module` (`role`,`module`);

--
-- Indexes for table `protective_actions`
--
ALTER TABLE `protective_actions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_id` (`action_id`),
  ADD KEY `idx_case_id` (`case_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `role_change_requests`
--
ALTER TABLE `role_change_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_change_requests_new`
--
ALTER TABLE `role_change_requests_new`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission_key`);

--
-- Indexes for table `schedule_activities`
--
ALTER TABLE `schedule_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `sms_contacts`
--
ALTER TABLE `sms_contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_services`
--
ALTER TABLE `social_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_role_requests`
--
ALTER TABLE `user_role_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_setting` (`user_id`,`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_requests`
--
ALTER TABLE `access_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=630;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=511;
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

--
-- AUTO_INCREMENT for table `audit_log_admin`
--
ALTER TABLE `audit_log_admin`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=390;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=324;
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

--
-- AUTO_INCREMENT for table `calendar_availability`
--
ALTER TABLE `calendar_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `children`
--
ALTER TABLE `children`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `custom_fields`
--
ALTER TABLE `custom_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `custom_field_groups`
--
ALTER TABLE `custom_field_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_group_assignments`
--
ALTER TABLE `custom_field_group_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

--
-- AUTO_INCREMENT for table `events_gallery`
--
ALTER TABLE `events_gallery`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

--
-- AUTO_INCREMENT for table `event_articles`
--
ALTER TABLE `event_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `event_type_visibility`
--
ALTER TABLE `event_type_visibility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_groups`
--
ALTER TABLE `field_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_values`
--
ALTER TABLE `field_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `foster_documents`
--
ALTER TABLE `foster_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `legal_actions`
--
ALTER TABLE `legal_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `meeting_requests`
--
ALTER TABLE `meeting_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=237;

--
-- AUTO_INCREMENT for table `protective_actions`
--
ALTER TABLE `protective_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `role_change_requests`
--
ALTER TABLE `role_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_change_requests_new`
--
ALTER TABLE `role_change_requests_new`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `schedule_activities`
--
ALTER TABLE `schedule_activities`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;
>>>>>>> 46e0a86ce4c0788de605b002b4020d9cce540d12

--
-- AUTO_INCREMENT for table `sms_contacts`
--
ALTER TABLE `sms_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `social_services`
--
ALTER TABLE `social_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_role_requests`
--
ALTER TABLE `user_role_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_settings`
--
ALTER TABLE `user_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_requests`
--
ALTER TABLE `access_requests`
  ADD CONSTRAINT `access_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `admin_notifications_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `custom_fields`
--
ALTER TABLE `custom_fields`
  ADD CONSTRAINT `custom_fields_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_field_groups`
--
ALTER TABLE `custom_field_groups`
  ADD CONSTRAINT `custom_field_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_field_group_assignments`
--
ALTER TABLE `custom_field_group_assignments`
  ADD CONSTRAINT `custom_field_group_assignments_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `custom_field_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `custom_field_group_assignments_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `custom_field_values`
--
ALTER TABLE `custom_field_values`
  ADD CONSTRAINT `custom_field_values_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `events_gallery`
--
ALTER TABLE `events_gallery`
  ADD CONSTRAINT `events_gallery_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_articles`
--
ALTER TABLE `event_articles`
  ADD CONSTRAINT `event_articles_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_type_visibility`
--
ALTER TABLE `event_type_visibility`
  ADD CONSTRAINT `event_type_visibility_ibfk_1` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fields`
--
ALTER TABLE `fields`
  ADD CONSTRAINT `fields_ibfk_1` FOREIGN KEY (`field_group_id`) REFERENCES `field_groups` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `field_values`
--
ALTER TABLE `field_values`
  ADD CONSTRAINT `field_values_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `meeting_requests`
--
ALTER TABLE `meeting_requests`
  ADD CONSTRAINT `meeting_requests_ibfk_1` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_settings`
--
ALTER TABLE `user_settings`
  ADD CONSTRAINT `user_settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
