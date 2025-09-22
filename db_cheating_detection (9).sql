-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2025 at 06:48 AM
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
-- Database: `db_cheating_detection`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`, `profile_picture`) VALUES
(3, 'admin1', 'admin1@gmail.com', '$2y$10$hZL.QCfQb.jcpLhSw48qLu3ieeSsvbW0NnCn0RDCkOorGbeuHcPPa', '2025-07-25 13:11:47', '2025-07-25 13:16:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `answers`
--

CREATE TABLE `answers` (
  `id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` text NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `answers`
--

INSERT INTO `answers` (`id`, `question_id`, `answer_text`, `is_correct`) VALUES
(66, 49, '2', 1),
(67, 50, '5', 1),
(68, 51, 'cam', 1),
(69, 52, 'test', 1),
(70, 53, 'test1', 1),
(71, 54, 'test2', 1),
(72, 55, 'test3', 1),
(73, 56, 'asd', 1),
(74, 57, 'test5', 1),
(75, 58, 'test6', 1),
(76, 59, 'test7', 1),
(77, 60, 'test8', 1),
(78, 61, 'test9', 1),
(79, 62, 'test10', 1),
(80, 63, 'test11', 1),
(81, 64, 'test12', 1),
(82, 65, 'test13', 1),
(83, 66, 'test13', 1),
(84, 67, 'test14', 1),
(85, 68, 'test15', 1),
(86, 69, 'test16', 1),
(87, 70, 'test17', 1),
(88, 71, 'test18', 1),
(89, 72, 'test19', 1),
(90, 73, 'test20', 1),
(91, 74, 'test21', 1),
(92, 75, 'test22', 1),
(93, 76, 'test23', 1),
(94, 77, 'test24', 1);

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `admin_id`, `action`, `old_value`, `new_value`, `changed_at`) VALUES
(4, 3, 'Updated system settings', '{\"id\":\"1\",\"default_exam_duration\":\"1\",\"cheating_detection_threshold\":\"0.8\",\"password_min_length\":\"8\",\"password_require_special\":\"1\",\"session_timeout\":\"30\",\"email_alert_cheating\":\"1\",\"email_alert_exam_closure\":\"1\",\"created_at\":\"2025-09-03 06:48:33\",\"updated_at\":\"2025-09-03 06:48:33\"}', '{\"default_exam_duration\":1,\"cheating_detection_threshold\":0.8,\"password_min_length\":8,\"password_require_special\":1,\"session_timeout\":30,\"email_alert_cheating\":1,\"email_alert_exam_closure\":1}', '2025-09-02 22:54:14'),
(5, 3, 'Updated system settings', '{\"id\":\"1\",\"default_exam_duration\":\"1\",\"cheating_detection_threshold\":\"0.8\",\"password_min_length\":\"8\",\"password_require_special\":\"1\",\"session_timeout\":\"30\",\"email_alert_cheating\":\"1\",\"email_alert_exam_closure\":\"1\",\"created_at\":\"2025-09-03 06:48:33\",\"updated_at\":\"2025-09-03 06:48:33\"}', '{\"default_exam_duration\":60,\"cheating_detection_threshold\":0.8,\"password_min_length\":8,\"password_require_special\":1,\"session_timeout\":30,\"email_alert_cheating\":1,\"email_alert_exam_closure\":1}', '2025-09-02 22:54:22'),
(6, 3, 'Updated system settings', '{\"id\":\"1\",\"default_exam_duration\":\"60\",\"cheating_detection_threshold\":\"0.8\",\"password_min_length\":\"8\",\"password_require_special\":\"1\",\"session_timeout\":\"30\",\"email_alert_cheating\":\"1\",\"email_alert_exam_closure\":\"1\",\"created_at\":\"2025-09-03 06:48:33\",\"updated_at\":\"2025-09-03 06:54:22\"}', '{\"default_exam_duration\":1,\"cheating_detection_threshold\":0.8,\"password_min_length\":8,\"password_require_special\":1,\"session_timeout\":30,\"email_alert_cheating\":1,\"email_alert_exam_closure\":1}', '2025-09-02 22:54:32'),
(7, 3, 'Updated system settings', '{\"id\":\"1\",\"default_exam_duration\":\"1\",\"cheating_detection_threshold\":\"0.8\",\"password_min_length\":\"8\",\"password_require_special\":\"1\",\"session_timeout\":\"30\",\"email_alert_cheating\":\"1\",\"email_alert_exam_closure\":\"1\",\"created_at\":\"2025-09-03 06:48:33\",\"updated_at\":\"2025-09-03 06:54:32\"}', '{\"default_exam_duration\":60,\"cheating_detection_threshold\":0.8,\"password_min_length\":8,\"password_require_special\":1,\"session_timeout\":30,\"email_alert_cheating\":1,\"email_alert_exam_closure\":1}', '2025-09-02 23:26:37');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` varchar(50) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `timestamp`, `user_id`, `user_name`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(11, '2025-09-02 23:33:12', 'admin1@gmail.com', 'admin1@gmail.com', 'SETTINGS_CHANGED', 'Updated system settings', '::1', NULL, '2025-09-02 23:33:12'),
(12, '2025-09-02 23:34:08', 'admin1@gmail.com', 'admin1@gmail.com', 'SETTINGS_CHANGED', 'Updated system settings', '::1', NULL, '2025-09-02 23:34:08'),
(13, '2025-09-05 07:49:33', 'admin1@gmail.com', 'admin1@gmail.com', 'SETTINGS_CHANGED', 'Updated system settings', '::1', NULL, '2025-09-05 07:49:33'),
(14, '2025-09-05 08:00:41', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 30', '::1', NULL, '2025-09-05 08:00:41'),
(15, '2025-09-05 08:02:23', 'BULK_ROLE_CHANGE', '', 'BULK_ROLE_CHANGED', 'Changed role to proctor for users: 1', '::1', NULL, '2025-09-05 08:02:23'),
(16, '2025-09-05 08:02:35', 'BULK_ROLE_CHANGE', '', 'BULK_ROLE_CHANGED', 'Changed role to student for users: , 1', '::1', NULL, '2025-09-05 08:02:35'),
(17, '2025-09-05 08:02:42', 'BULK_ROLE_CHANGE', '', 'BULK_ROLE_CHANGED', 'Changed role to proctor for users: , 1', '::1', NULL, '2025-09-05 08:02:42'),
(18, '2025-09-05 08:02:48', 'BULK_ROLE_CHANGE', '', 'BULK_ROLE_CHANGED', 'Changed role to student for users: , 1', '::1', NULL, '2025-09-05 08:02:48'),
(19, '2025-09-05 08:03:04', 'BULK_DELETE', '', 'BULK_USER_DELETED', 'Deleted users: , 1', '::1', NULL, '2025-09-05 08:03:04'),
(20, '2025-09-05 08:03:16', '', 'ehh', 'USER_CREATED', 'Created new user ', '::1', NULL, '2025-09-05 08:03:16'),
(21, '2025-09-05 08:04:00', 'SYSTEM', NULL, 'BACKUP_CREATED', 'Database backup created: backups/backup_2025-09-05_10-04-00.json', '::1', NULL, '2025-09-05 08:04:00'),
(22, '2025-09-05 08:04:54', 'SYSTEM', NULL, 'BACKUP_DELETED', 'Backup deleted: backup_2025-09-05_10-04-00.json', '::1', NULL, '2025-09-05 08:04:54'),
(23, '2025-09-09 19:32:33', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 34', '::1', NULL, '2025-09-09 19:32:33'),
(24, '2025-09-09 19:32:40', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 33', '::1', NULL, '2025-09-09 19:32:40'),
(25, '2025-09-09 19:32:41', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 32', '::1', NULL, '2025-09-09 19:32:41'),
(26, '2025-09-09 19:32:42', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 31', '::1', NULL, '2025-09-09 19:32:42'),
(27, '2025-09-09 19:33:08', '3', '3', 'CHEATING_DETECTED', 'Marked cheating for exam ID 35', '::1', NULL, '2025-09-09 19:33:08'),
(28, '2025-09-09 19:33:26', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 35', '::1', NULL, '2025-09-09 19:33:26'),
(29, '2025-09-09 19:34:00', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 36', '::1', NULL, '2025-09-09 19:34:00'),
(30, '2025-09-09 19:34:42', '3', '3', 'CHEATING_DETECTED', 'Marked cheating for exam ID 36', '::1', NULL, '2025-09-09 19:34:42'),
(31, '2025-09-09 19:35:49', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 35', '::1', NULL, '2025-09-09 19:35:49'),
(32, '2025-09-09 19:35:57', 'UNKNOWN', 'UNKNOWN', 'DELETE_EXAM', 'Deleted exam ID 36', '::1', NULL, '2025-09-09 19:35:57'),
(33, '2025-09-09 19:36:14', '3', '3', 'CHEATING_DETECTED', 'Marked cheating for exam ID 37', '::1', NULL, '2025-09-09 19:36:14'),
(34, '2025-09-09 19:36:21', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 37', '::1', NULL, '2025-09-09 19:36:21'),
(35, '2025-09-09 19:36:58', 'SYSTEM', NULL, 'BACKUP_CREATED', 'Database backup created: backups/backup_2025-09-09_21-36-58.json', '::1', NULL, '2025-09-09 19:36:58'),
(36, '2025-09-09 19:37:01', 'SYSTEM', NULL, 'BACKUP_DELETED', 'Backup deleted: backup_2025-09-09_21-36-58.json', '::1', NULL, '2025-09-09 19:37:01'),
(37, '2025-09-09 19:37:35', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 38', '::1', NULL, '2025-09-09 19:37:35'),
(38, '2025-09-09 19:43:19', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 39', '::1', NULL, '2025-09-09 19:43:19'),
(39, '2025-09-10 14:40:13', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 40', '::1', NULL, '2025-09-10 14:40:13'),
(40, '2025-09-10 14:41:02', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 41', '::1', NULL, '2025-09-10 14:41:02'),
(41, '2025-09-10 14:43:59', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 42', '::1', NULL, '2025-09-10 14:43:59'),
(42, '2025-09-10 14:48:42', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 43', '::1', NULL, '2025-09-10 14:48:42'),
(43, '2025-09-10 15:01:52', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 44', '::1', NULL, '2025-09-10 15:01:52'),
(44, '2025-09-10 15:06:25', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 45', '::1', NULL, '2025-09-10 15:06:25'),
(45, '2025-09-10 15:08:01', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 46', '::1', NULL, '2025-09-10 15:08:01'),
(46, '2025-09-10 15:08:52', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 47', '::1', NULL, '2025-09-10 15:08:52'),
(47, '2025-09-10 15:19:21', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 48', '::1', NULL, '2025-09-10 15:19:21'),
(48, '2025-09-10 15:20:19', '3', '3', 'CHEATING_DETECTED', 'Marked cheating for exam ID 38', '::1', NULL, '2025-09-10 15:20:19'),
(49, '2025-09-10 15:23:14', '3', '3', 'CHEATING_DETECTED', 'Marked cheating for exam ID 49', '::1', NULL, '2025-09-10 15:23:14'),
(50, '2025-09-10 15:27:07', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 50', '::1', NULL, '2025-09-10 15:27:07'),
(51, '2025-09-13 04:07:14', '3', '3', 'CHEATING_DETECTED', 'Marked cheating for exam ID 51', '::1', NULL, '2025-09-13 04:07:14'),
(52, '2025-09-13 04:08:02', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 52', '::1', NULL, '2025-09-13 04:08:02'),
(53, '2025-09-13 04:08:35', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 53', '::1', NULL, '2025-09-13 04:08:35'),
(54, '2025-09-13 04:12:45', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 55', '::1', NULL, '2025-09-13 04:12:45'),
(55, '2025-09-13 04:15:02', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 56', '::1', NULL, '2025-09-13 04:15:02'),
(56, '2025-09-13 04:18:04', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 57', '::1', NULL, '2025-09-13 04:18:04'),
(57, '2025-09-13 04:20:19', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 58', '::1', NULL, '2025-09-13 04:20:19'),
(58, '2025-09-13 04:24:05', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 59', '::1', NULL, '2025-09-13 04:24:05'),
(59, '2025-09-13 04:26:26', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 60', '::1', NULL, '2025-09-13 04:26:26'),
(60, '2025-09-13 04:27:51', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 61', '::1', NULL, '2025-09-13 04:27:51'),
(61, '2025-09-13 04:28:23', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 62', '::1', NULL, '2025-09-13 04:28:23'),
(62, '2025-09-13 04:29:50', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 63', '::1', NULL, '2025-09-13 04:29:50'),
(63, '2025-09-13 04:33:02', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 64', '::1', NULL, '2025-09-13 04:33:02'),
(64, '2025-09-13 04:35:12', '202202002', '202202002', 'CHEATING_DETECTED', 'Marked cheating for exam ID 65', '::1', NULL, '2025-09-13 04:35:12');

-- --------------------------------------------------------

--
-- Table structure for table `cheating_log`
--

CREATE TABLE `cheating_log` (
  `id` int(11) NOT NULL,
  `studentNo` varchar(50) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `screenshot` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cheating_log`
--

INSERT INTO `cheating_log` (`id`, `studentNo`, `exam_id`, `timestamp`, `screenshot`) VALUES
(1, '202202002', 23, '2025-09-02 06:44:21', NULL),
(3, '202202002', 22, '2025-09-02 06:47:28', NULL),
(4, '3', 24, '2025-09-02 06:48:57', NULL),
(6, '3', 23, '2025-09-02 06:51:08', NULL),
(7, '3', 22, '2025-09-02 06:51:11', NULL),
(8, '202202002', 24, '2025-09-02 06:53:38', NULL),
(10, '202202002', 25, '2025-09-02 06:55:01', NULL),
(12, '202202002', 26, '2025-09-02 07:01:47', NULL),
(13, '202202002', 27, '2025-09-02 07:02:01', NULL),
(36, '3', 37, '2025-09-10 03:36:14', NULL),
(37, '202202002', 37, '2025-09-10 03:36:21', NULL),
(38, '202202002', 38, '2025-09-10 03:37:35', NULL),
(40, '202202002', 39, '2025-09-10 03:43:19', NULL),
(42, '202202002', 40, '2025-09-10 22:40:13', NULL),
(44, '202202002', 41, '2025-09-10 22:41:02', NULL),
(46, '202202002', 42, '2025-09-10 22:43:59', NULL),
(47, '202202002', 43, '2025-09-10 22:48:42', NULL),
(48, '202202002', 44, '2025-09-10 23:01:52', NULL),
(49, '202202002', 45, '2025-09-10 23:06:25', NULL),
(51, '202202002', 46, '2025-09-10 23:08:01', NULL),
(54, '202202002', 47, '2025-09-10 23:08:52', NULL),
(57, '202202002', 48, '2025-09-10 23:19:21', NULL),
(60, '3', 38, '2025-09-10 23:20:19', NULL),
(63, '3', 49, '2025-09-10 23:23:14', NULL),
(66, '202202002', 50, '2025-09-10 23:27:07', 'screenshots/screenshot_202202002_50_1757518032.png'),
(69, '3', 51, '2025-09-13 12:07:14', 'screenshots/screenshot_3_51_1757736439.png'),
(71, '202202002', 52, '2025-09-13 12:08:02', 'screenshots/screenshot_202202002_52_1757736488.png'),
(73, '202202002', 53, '2025-09-13 12:08:35', 'screenshots/screenshot_202202002_53_1757736544.png'),
(77, '202202002', 55, '2025-09-13 12:12:45', 'screenshots/screenshot_202202002_55_1757736787.png'),
(80, '202202002', 56, '2025-09-13 12:15:02', 'screenshots/screenshot_202202002_56_1757736934.png'),
(83, '202202002', 57, '2025-09-13 12:18:04', 'screenshots/screenshot_202202002_57_1757737090.png'),
(85, '202202002', 58, '2025-09-13 12:20:18', 'screenshots/screenshot_202202002_58_1757737249.png'),
(87, '202202002', 59, '2025-09-13 12:24:05', 'screenshots/screenshot_202202002_59_1757737445.png'),
(88, '202202002', 60, '2025-09-13 12:26:26', 'screenshots/screenshot_202202002_60_1757737592.png'),
(90, '202202002', 61, '2025-09-13 12:27:51', 'screenshots/screenshot_202202002_61_1757737676.png'),
(93, '202202002', 62, '2025-09-13 12:28:23', 'screenshots/screenshot_202202002_62_1757737709.png'),
(95, '202202002', 63, '2025-09-13 12:29:50', 'screenshots/screenshot_202202002_63_1757737796.png'),
(97, '202202002', 64, '2025-09-13 12:33:02', 'screenshots/screenshot_202202002_64_1757737987.png'),
(100, '202202002', 65, '2025-09-13 12:35:12', 'screenshots/screenshot_202202002_65_1757738117.png');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `subject`, `title`, `created_at`, `status`) VALUES
(37, '1', '1', '2025-09-09 19:36:07', 'open'),
(38, '5', '5', '2025-09-09 19:37:25', 'open'),
(39, 'cam', 'cam', '2025-09-09 19:43:03', 'open'),
(40, 'test', 'test', '2025-09-10 14:40:00', 'open'),
(41, 'test1', 'test1', '2025-09-10 14:40:51', 'open'),
(42, 'test2', 'test2', '2025-09-10 14:43:26', 'open'),
(43, 'test3', 'test3', '2025-09-10 14:48:20', 'open'),
(44, 'asd', 'asd', '2025-09-10 15:01:11', 'open'),
(45, 'test5', 'test5', '2025-09-10 15:02:38', 'open'),
(46, 'test6', 'test6', '2025-09-10 15:07:46', 'open'),
(47, 'test7', 'test7', '2025-09-10 15:08:30', 'open'),
(48, 'test8', 'test8', '2025-09-10 15:19:01', 'open'),
(49, 'test9', 'test9', '2025-09-10 15:22:00', 'open'),
(50, 'test10', 'test10', '2025-09-10 15:26:23', 'open'),
(51, 'test11', 'test11', '2025-09-13 04:06:16', 'open'),
(52, 'test12', 'test12', '2025-09-13 04:07:43', 'open'),
(53, 'test13', 'test13', '2025-09-13 04:08:25', 'open'),
(54, 'test13', 'test13', '2025-09-13 04:12:05', 'open'),
(55, 'test14', 'test14', '2025-09-13 04:12:33', 'open'),
(56, 'test15', 'test15', '2025-09-13 04:14:49', 'open'),
(57, 'test16', 'test16', '2025-09-13 04:17:06', 'open'),
(58, 'test17', 'test17', '2025-09-13 04:19:59', 'open'),
(59, 'test18', 'test18', '2025-09-13 04:23:19', 'open'),
(60, 'test19', 'test19', '2025-09-13 04:25:49', 'open'),
(61, 'test20', 'test20', '2025-09-13 04:27:36', 'open'),
(62, 'test21', 'test21', '2025-09-13 04:28:02', 'open'),
(63, 'test22', 'test22', '2025-09-13 04:29:06', 'open'),
(64, 'test23', 'test23', '2025-09-13 04:32:34', 'open'),
(65, 'test24', 'test24', '2025-09-13 04:34:16', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `exam_answers`
--

CREATE TABLE `exam_answers` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `title`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 'cheating', 'Cheating Detected', 'Multiple cheating incidents detected in exam EX001', 0, '2025-09-03 01:08:22', '2025-09-03 01:08:22'),
(2, 'system', 'System Maintenance', 'Scheduled maintenance will occur tonight at 2 AM', 0, '2025-09-03 01:08:22', '2025-09-03 01:08:22'),
(3, 'exam', 'Exam Completed', 'Exam EX002 has been completed by all students', 0, '2025-09-03 01:08:22', '2025-09-03 01:08:22'),
(4, 'user', 'New User Registration', 'New student registered: john.doe@example.com', 0, '2025-09-03 01:08:22', '2025-09-03 01:08:22');

-- --------------------------------------------------------

--
-- Table structure for table `proctors`
--

CREATE TABLE `proctors` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `proctors`
--

INSERT INTO `proctors` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`, `profile_picture`) VALUES
(3, 'proctor 1', 'proctor1@gmail.com', '$2y$10$mWBq8kvgatnpZK0gl3F5f.uv6Nu1dSvp51a57/6MgCM/30bA2JOJy', '2025-07-27 11:07:17', '2025-07-27 11:07:17', NULL),
(4, 'proctor1', 'p1@gmail.com', '$2y$10$yzfmtcArFERDROuJm1rIYe5oOW2ZC8eUvE1FcDp7lDKWgcfnLPQQG', '2025-07-28 07:25:40', '2025-07-28 07:25:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `correct_answer` text NOT NULL,
  `question_type` varchar(50) NOT NULL DEFAULT 'short-answer',
  `options_json` text DEFAULT NULL,
  `correct_answer_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `exam_id`, `question_text`, `correct_answer`, `question_type`, `options_json`, `correct_answer_json`) VALUES
(49, 37, '2', '', 'short-answer', NULL, NULL),
(50, 38, '5 5', '', 'short-answer', NULL, NULL),
(51, 39, 'cam', '', 'short-answer', NULL, NULL),
(52, 40, 'test', '', 'short-answer', NULL, NULL),
(53, 41, 'test1', '', 'short-answer', NULL, NULL),
(54, 42, 'test2', '', 'short-answer', NULL, NULL),
(55, 43, 'test3', '', 'short-answer', NULL, NULL),
(56, 44, 'asd', '', 'short-answer', NULL, NULL),
(57, 45, 'test5', '', 'short-answer', NULL, NULL),
(58, 46, 'test6', '', 'short-answer', NULL, NULL),
(59, 47, 'test7', '', 'short-answer', NULL, NULL),
(60, 48, 'test8', '', 'short-answer', NULL, NULL),
(61, 49, 'test9', '', 'short-answer', NULL, NULL),
(62, 50, 'test10', '', 'short-answer', NULL, NULL),
(63, 51, 'test11', '', 'short-answer', NULL, NULL),
(64, 52, 'test12', '', 'short-answer', NULL, NULL),
(65, 53, 'test13', '', 'short-answer', NULL, NULL),
(66, 54, 'test13', '', 'short-answer', NULL, NULL),
(67, 55, 'test14', '', 'short-answer', NULL, NULL),
(68, 56, 'test15', '', 'short-answer', NULL, NULL),
(69, 57, 'test16', '', 'short-answer', NULL, NULL),
(70, 58, 'test17', '', 'short-answer', NULL, NULL),
(71, 59, 'test18', '', 'short-answer', NULL, NULL),
(72, 60, 'test19', '', 'short-answer', NULL, NULL),
(73, 61, 'test20', '', 'short-answer', NULL, NULL),
(74, 62, 'test21', '', 'short-answer', NULL, NULL),
(75, 63, 'test22', '', 'short-answer', NULL, NULL),
(76, 64, 'test23', '', 'short-answer', NULL, NULL),
(77, 65, 'test24', '', 'short-answer', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `studentNo` varchar(255) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `score` int(11) NOT NULL,
  `taken_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_results`
--

INSERT INTO `student_results` (`id`, `name`, `studentNo`, `exam_id`, `section`, `score`, `taken_at`) VALUES
(68, NULL, '202202002', 37, 'BSIT4B', 1, '2025-09-09 19:36:21');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `default_exam_duration` int(11) NOT NULL DEFAULT 60,
  `cheating_detection_threshold` float NOT NULL DEFAULT 0.8,
  `password_min_length` int(11) NOT NULL DEFAULT 8,
  `password_require_special` tinyint(1) NOT NULL DEFAULT 1,
  `session_timeout` int(11) NOT NULL DEFAULT 30,
  `email_alert_cheating` tinyint(1) NOT NULL DEFAULT 1,
  `email_alert_exam_closure` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `default_exam_duration`, `cheating_detection_threshold`, `password_min_length`, `password_require_special`, `session_timeout`, `email_alert_cheating`, `email_alert_exam_closure`, `created_at`, `updated_at`) VALUES
(1, 5, 0.8, 8, 1, 30, 1, 1, '2025-09-02 22:48:33', '2025-09-05 07:49:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `studentNo` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `homeAddress` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `sex` enum('male','female','other') NOT NULL,
  `role` enum('proctor','student') NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`studentNo`, `password`, `name`, `age`, `email`, `homeAddress`, `birthdate`, `sex`, `role`, `section`, `created_at`, `updated_at`, `profile_picture`) VALUES
('', '$2y$10$MWQnQTcadQCcycM0RY.Hy.KTTYzYv2gpUQzv.9jWn1olSqWXdKfz2', 'ehh', 0, 'ehh@gmail.com', '', '0000-00-00', 'male', 'student', '', '2025-09-05 08:03:16', '2025-09-05 08:03:16', NULL),
('202202002', '$2y$10$bqg8cEIG5VwgRzaur1QUGuGSTAq4CQ2TiMobBSuaXkU/1oF.oH99C', 'Allan John Balanon', 21, 'balanonkokoy@gmail.com', 'muzon,malabon', '2004-02-04', 'male', 'student', 'BSIT4B', '2025-07-27 11:05:07', '2025-07-29 02:04:20', 'uploads/profile_pictures/202202002_1753754660.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `answers`
--
ALTER TABLE `answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `cheating_log`
--
ALTER TABLE `cheating_log`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cheating` (`studentNo`,`exam_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_answers`
--
ALTER TABLE `exam_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_exam_id` (`exam_id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_question_id` (`question_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `proctors`
--
ALTER TABLE `proctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `student_results_ibfk_1` (`studentNo`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`studentNo`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `answers`
--
ALTER TABLE `answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `cheating_log`
--
ALTER TABLE `cheating_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `exam_answers`
--
ALTER TABLE `exam_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `proctors`
--
ALTER TABLE `proctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `answers`
--
ALTER TABLE `answers`
  ADD CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_results`
--
ALTER TABLE `student_results`
  ADD CONSTRAINT `student_results_ibfk_1` FOREIGN KEY (`studentNo`) REFERENCES `users` (`studentNo`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_results_ibfk_2` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
