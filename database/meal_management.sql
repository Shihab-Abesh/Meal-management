-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2026 at 01:55 PM
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
-- Database: `meal_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `module` varchar(40) NOT NULL,
  `action` varchar(60) NOT NULL,
  `description` varchar(500) NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `module`, `action`, `description`, `subject_type`, `subject_id`, `client_id`, `properties`, `ip_address`, `created_at`) VALUES
(1, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:11:11'),
(2, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-06 09:12:18'),
(3, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 09:12:29'),
(4, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:13:04'),
(5, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:23:56'),
(6, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 09:26:43'),
(7, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:29:17'),
(8, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 09:31:44'),
(9, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:35:00'),
(10, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 09:38:39'),
(11, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:39:57'),
(12, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:52:50'),
(13, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-06 09:54:19'),
(14, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 09:54:45'),
(15, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:55:28'),
(16, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:56:04'),
(17, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 09:57:05'),
(18, 1, 'menu', 'menu_created', 'Created lunch menu for 25 oct 2026', 'App\\Models\\DailyMenu', 41, NULL, '{\"items\":5,\"price\":\"150.00\"}', '127.0.0.1', '2026-10-06 09:57:10'),
(19, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 10:08:55'),
(20, 5, 'meals', 'confirmed', 'Confirmed 8 meals: Lunch for XYZ Software Ltd. on 07 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-07\",\"meal_type\":\"lunch\",\"total\":8,\"added\":8,\"removed\":0,\"added_employee_ids\":[6,4,3,5,2,9,8,7],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-06 10:08:58'),
(21, 5, 'meals', 'confirmed', 'Confirmed 1 meals: Dinner for XYZ Software Ltd. on 07 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-07\",\"meal_type\":\"dinner\",\"total\":1,\"added\":1,\"removed\":0,\"added_employee_ids\":[6],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-06 10:08:58'),
(22, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 10:09:45'),
(23, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-06 10:11:37'),
(24, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-06 10:12:17'),
(25, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 10:13:38'),
(26, 1, 'meals', 'edited', 'Edited meals (9 now): Lunch for ABC Bank Ltd. on 05 Oct 2026', 'App\\Models\\Client', 2, 2, '{\"date\":\"2026-10-05\",\"meal_type\":\"lunch\",\"total\":9,\"added\":0,\"removed\":1,\"added_employee_ids\":[],\"removed_employee_ids\":[11],\"override\":true}', '127.0.0.1', '2026-10-06 10:13:42'),
(27, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 10:14:02'),
(28, 1, 'meals', 'edited', 'Edited meals (10 now): Lunch for ABC Bank Ltd. on 05 Oct 2026', 'App\\Models\\Client', 2, 2, '{\"date\":\"2026-10-05\",\"meal_type\":\"lunch\",\"total\":10,\"added\":1,\"removed\":0,\"added_employee_ids\":[11],\"removed_employee_ids\":[],\"override\":true}', '127.0.0.1', '2026-10-06 10:14:07'),
(29, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-06 11:09:44'),
(30, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:19:33'),
(31, 1, 'deliveries', 'rider_assigned', 'Order ORD-261007-00038: assigned to Rahim Uddin', 'App\\Models\\Order', 38, 1, '{\"rider_id\":1,\"previous_rider_id\":null,\"from\":\"confirmed\"}', '127.0.0.1', '2026-10-07 01:19:37'),
(32, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 01:19:40'),
(33, 7, 'deliveries', 'status_changed', 'Order ORD-261007-00038: Assigned → Picked Up', 'App\\Models\\Order', 38, 1, '{\"from\":\"assigned\",\"to\":\"picked_up\"}', '127.0.0.1', '2026-10-07 01:19:43'),
(34, 7, 'deliveries', 'status_changed', 'Order ORD-261007-00038: Picked Up → On The Way', 'App\\Models\\Order', 38, 1, '{\"from\":\"picked_up\",\"to\":\"on_the_way\"}', '127.0.0.1', '2026-10-07 01:19:45'),
(35, 7, 'deliveries', 'status_changed', 'Order ORD-261007-00038: On The Way → Delivered', 'App\\Models\\Order', 38, 1, '{\"from\":\"on_the_way\",\"to\":\"delivered\"}', '127.0.0.1', '2026-10-07 01:19:47'),
(36, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:19:50'),
(37, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:20:08'),
(38, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 01:22:46'),
(39, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 01:23:48'),
(40, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 01:24:06'),
(41, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:25:26'),
(42, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 01:28:13'),
(43, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 01:29:21'),
(44, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 01:29:39'),
(45, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:31:14'),
(46, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:31:38'),
(47, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:32:15'),
(48, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:32:52'),
(49, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 01:35:23'),
(50, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 01:36:24'),
(51, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 01:36:42'),
(52, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:52:49'),
(53, 1, 'billing', 'bill_generated', 'Bill BILL-2610-00005 for XYZ Software Ltd.: 53 meals, 6780.00 (01 Oct 2026 – 06 Oct 2026)', 'App\\Models\\Bill', 5, 1, '{\"total_meals\":53,\"total_amount\":\"6780.00\"}', '127.0.0.1', '2026-10-07 01:53:00'),
(54, 1, 'billing', 'payment_added', 'Payment of 1000.00 (bKash) received from XYZ Software Ltd. for bill BILL-2610-00005', 'App\\Models\\Payment', 3, 1, '{\"bill_id\":5,\"amount\":\"1000.00\",\"method\":\"bkash\",\"due_after\":\"5780.00\"}', '127.0.0.1', '2026-10-07 01:53:30'),
(55, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:54:33'),
(56, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:55:49'),
(57, 1, 'billing', 'payment_added', 'Payment of 500.00 (bKash) received from XYZ Software Ltd. for bill BILL-2610-00005', 'App\\Models\\Payment', 4, 1, '{\"bill_id\":5,\"amount\":\"500.00\",\"method\":\"bkash\",\"due_after\":\"5280.00\"}', '127.0.0.1', '2026-10-07 01:55:56'),
(58, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 01:57:33'),
(59, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 01:58:05'),
(60, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 02:02:34'),
(61, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 02:03:04'),
(62, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 02:06:09'),
(63, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 02:10:41'),
(64, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 02:11:11'),
(65, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 02:13:15'),
(66, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 02:30:34'),
(67, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 05:30:47'),
(68, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 05:33:00'),
(69, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 05:33:35'),
(70, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 05:34:59'),
(71, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 05:37:50'),
(72, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 05:38:20'),
(73, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 05:39:05'),
(74, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 05:40:33'),
(75, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 05:41:15'),
(76, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 05:51:00'),
(77, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 06:03:30'),
(78, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 06:05:05'),
(79, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 06:10:32'),
(80, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 06:13:56'),
(81, 1, 'orders', 'created', 'Order ORD-261007-00111 created: 3 meals (ABC Bank Ltd. · Lunch · 07 Oct 2026)', 'App\\Models\\Order', 111, 2, '{\"quantity\":3,\"amount\":\"390.00\",\"status\":\"confirmed\"}', '127.0.0.1', '2026-10-07 07:36:27'),
(82, 1, 'meals', 'confirmed', 'Confirmed 3 meals: Lunch for ABC Bank Ltd. on 07 Oct 2026', 'App\\Models\\Client', 2, 2, '{\"date\":\"2026-10-07\",\"meal_type\":\"lunch\",\"total\":3,\"added\":3,\"removed\":0,\"added_employee_ids\":[11,16,14],\"removed_employee_ids\":[],\"override\":true}', '127.0.0.1', '2026-10-07 07:36:27'),
(83, 1, 'auth', 'logout', 'Vendor Admin signed out', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 07:47:13'),
(84, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 07:48:04'),
(85, 5, 'orders', 'updated', 'Order ORD-261007-00037 updated from the meal sheet: 1 → 3 meals', 'App\\Models\\Order', 37, 1, '{\"old\":{\"quantity\":1,\"amount\":\"140.00\",\"status\":\"confirmed\"},\"new\":{\"quantity\":3,\"amount\":\"420.00\",\"status\":\"confirmed\"}}', '127.0.0.1', '2026-10-07 07:49:54'),
(86, 5, 'meals', 'edited', 'Edited meals (3 now): Dinner for XYZ Software Ltd. on 07 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-07\",\"meal_type\":\"dinner\",\"total\":3,\"added\":2,\"removed\":0,\"added_employee_ids\":[3,5],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 07:49:54'),
(87, 5, 'orders', 'created', 'Order ORD-261008-00112 created: 3 meals (XYZ Software Ltd. · Lunch · 08 Oct 2026)', 'App\\Models\\Order', 112, 1, '{\"quantity\":3,\"amount\":\"360.00\",\"status\":\"pending\"}', '127.0.0.1', '2026-10-07 07:51:12'),
(88, 5, 'meals', 'confirmed', 'Confirmed 3 meals: Lunch for XYZ Software Ltd. on 08 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-08\",\"meal_type\":\"lunch\",\"total\":3,\"added\":3,\"removed\":0,\"added_employee_ids\":[9,8,7],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 07:51:12'),
(89, 5, 'orders', 'updated', 'Order ORD-261008-00112 updated from the meal sheet: 3 → 9 meals', 'App\\Models\\Order', 112, 1, '{\"old\":{\"quantity\":3,\"amount\":\"360.00\",\"status\":\"pending\"},\"new\":{\"quantity\":9,\"amount\":\"1080.00\",\"status\":\"pending\"}}', '127.0.0.1', '2026-10-07 07:51:39'),
(90, 5, 'meals', 'edited', 'Edited meals (9 now): Lunch for XYZ Software Ltd. on 08 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-08\",\"meal_type\":\"lunch\",\"total\":9,\"added\":6,\"removed\":0,\"added_employee_ids\":[2,5,3,4,6,1],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 07:51:39'),
(91, 5, 'employees', 'status_changed', 'Activated employee Mehedi Hasan (EMP-010)', 'App\\Models\\Employee', 10, 1, '{\"status\":\"active\"}', '127.0.0.1', '2026-10-07 07:52:19'),
(92, 5, 'orders', 'updated', 'Order ORD-261008-00112 updated from the meal sheet: 9 → 10 meals', 'App\\Models\\Order', 112, 1, '{\"old\":{\"quantity\":9,\"amount\":\"1080.00\",\"status\":\"pending\"},\"new\":{\"quantity\":10,\"amount\":\"1200.00\",\"status\":\"pending\"}}', '127.0.0.1', '2026-10-07 07:52:38'),
(93, 5, 'meals', 'edited', 'Edited meals (10 now): Lunch for XYZ Software Ltd. on 08 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-08\",\"meal_type\":\"lunch\",\"total\":10,\"added\":1,\"removed\":0,\"added_employee_ids\":[10],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 07:52:38'),
(94, 5, 'auth', 'logout', 'Tanvir Ahmed signed out', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 07:53:14'),
(95, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 07:53:25'),
(96, 1, 'orders', 'status_changed', 'Order ORD-261008-00112: Pending → Preparing', 'App\\Models\\Order', 112, 1, '{\"from\":\"pending\",\"to\":\"preparing\"}', '127.0.0.1', '2026-10-07 07:54:52'),
(97, 1, 'orders', 'status_changed', 'Order ORD-261008-00112: Preparing → Ready', 'App\\Models\\Order', 112, 1, '{\"from\":\"preparing\",\"to\":\"ready\"}', '127.0.0.1', '2026-10-07 07:55:04'),
(98, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:35:06'),
(99, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:35:12'),
(100, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:36:06'),
(101, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:36:11'),
(102, 5, 'orders', 'created', 'Order ORD-261012-00113 created: 10 meals (XYZ Software Ltd. · Lunch · 12 Oct 2026)', 'App\\Models\\Order', 113, 1, '{\"quantity\":10,\"amount\":\"1200.00\",\"status\":\"pending\"}', '127.0.0.1', '2026-10-07 08:36:14'),
(103, 5, 'meals', 'confirmed', 'Confirmed 10 meals: Lunch for XYZ Software Ltd. on 12 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-12\",\"meal_type\":\"lunch\",\"total\":10,\"added\":10,\"removed\":0,\"added_employee_ids\":[1,6,4,3,10,5,2,9,8,7],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 08:36:14'),
(104, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:37:21'),
(105, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:37:25'),
(106, 5, 'orders', 'created', 'Order ORD-261013-00114 created: 10 meals (XYZ Software Ltd. · Lunch · 13 Oct 2026)', 'App\\Models\\Order', 114, 1, '{\"quantity\":10,\"amount\":\"1200.00\",\"status\":\"pending\"}', '127.0.0.1', '2026-10-07 08:37:29'),
(107, 5, 'meals', 'confirmed', 'Confirmed 10 meals: Lunch for XYZ Software Ltd. on 13 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-13\",\"meal_type\":\"lunch\",\"total\":10,\"added\":10,\"removed\":0,\"added_employee_ids\":[1,6,4,3,10,5,2,9,8,7],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 08:37:29'),
(108, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:38:28'),
(109, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:39:18'),
(110, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:40:06'),
(111, 1, 'menu', 'menu_created', 'Created lunch menu for 10 oct 2026', 'App\\Models\\DailyMenu', 42, NULL, '{\"items\":4,\"price\":\"150.00\"}', '127.0.0.1', '2026-10-07 08:40:09'),
(112, 1, 'menu', 'week_copied', 'Copied 11 menus from the week of 03 Oct to the week of 10 Oct 2026', NULL, NULL, NULL, '{\"from\":\"2026-10-03\",\"to\":\"2026-10-10\",\"copied\":11}', '127.0.0.1', '2026-10-07 08:40:11'),
(113, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:40:45'),
(114, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 08:43:10'),
(115, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:43:54'),
(116, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 08:44:21'),
(117, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:44:53'),
(118, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:45:19'),
(119, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 08:47:18'),
(120, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:48:16'),
(121, 7, 'auth', 'login', 'Rahim Uddin signed in', 'App\\Models\\User', 7, NULL, NULL, '127.0.0.1', '2026-10-07 08:48:45'),
(122, 1, 'auth', 'logout', 'Vendor Admin signed out', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:53:47'),
(123, 2, 'auth', 'login', 'Vendor Staff signed in', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 08:53:56'),
(124, 2, 'auth', 'logout', 'Vendor Staff signed out', 'App\\Models\\User', 2, NULL, NULL, '127.0.0.1', '2026-10-07 08:54:07'),
(125, 5, 'auth', 'login', 'Tanvir Ahmed signed in', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:54:22'),
(126, 5, 'orders', 'created', 'Order ORD-261008-00115 created: 1 meals (XYZ Software Ltd. · Dinner · 08 Oct 2026)', 'App\\Models\\Order', 115, 1, '{\"quantity\":1,\"amount\":\"140.00\",\"status\":\"pending\"}', '127.0.0.1', '2026-10-07 08:54:35'),
(127, 5, 'meals', 'confirmed', 'Confirmed 1 meals: Dinner for XYZ Software Ltd. on 08 Oct 2026', 'App\\Models\\Client', 1, 1, '{\"date\":\"2026-10-08\",\"meal_type\":\"dinner\",\"total\":1,\"added\":1,\"removed\":0,\"added_employee_ids\":[6],\"removed_employee_ids\":[],\"override\":false}', '127.0.0.1', '2026-10-07 08:54:35'),
(128, 5, 'auth', 'logout', 'Tanvir Ahmed signed out', 'App\\Models\\User', 5, 1, NULL, '127.0.0.1', '2026-10-07 08:54:46'),
(129, 1, 'auth', 'login', 'Vendor Admin signed in', 'App\\Models\\User', 1, NULL, NULL, '127.0.0.1', '2026-10-07 08:54:53'),
(130, 1, 'orders', 'status_changed', 'Order ORD-261008-00115: Pending → Confirmed', 'App\\Models\\Order', 115, 1, '{\"from\":\"pending\",\"to\":\"confirmed\"}', '127.0.0.1', '2026-10-07 12:22:12'),
(131, 1, 'orders', 'status_changed', 'Order ORD-261008-00115: Confirmed → Ready', 'App\\Models\\Order', 115, 1, '{\"from\":\"confirmed\",\"to\":\"ready\"}', '127.0.0.1', '2026-10-07 12:22:16'),
(132, 1, 'orders', 'status_changed', 'Order ORD-261007-00111: Confirmed → Ready', 'App\\Models\\Order', 111, 2, '{\"from\":\"confirmed\",\"to\":\"ready\"}', '127.0.0.1', '2026-10-07 12:22:59'),
(133, 1, 'deliveries', 'rider_assigned', 'Order ORD-261007-00111: assigned to Rahim Uddin', 'App\\Models\\Order', 111, 2, '{\"rider_id\":1,\"previous_rider_id\":null,\"from\":\"ready\"}', '127.0.0.1', '2026-10-07 12:23:22'),
(134, 1, 'deliveries', 'status_changed', 'Order ORD-261007-00111: Assigned → Picked Up', 'App\\Models\\Order', 111, 2, '{\"from\":\"assigned\",\"to\":\"picked_up\"}', '127.0.0.1', '2026-10-07 12:24:00'),
(135, 1, 'deliveries', 'status_changed', 'Order ORD-261007-00111: Picked Up → On The Way', 'App\\Models\\Order', 111, 2, '{\"from\":\"picked_up\",\"to\":\"on_the_way\"}', '127.0.0.1', '2026-10-07 12:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bill_number` varchar(30) DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `lunch_meals` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `dinner_meals` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_meals` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `lines` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`lines`)),
  `notes` varchar(500) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`id`, `bill_number`, `client_id`, `period_start`, `period_end`, `lunch_meals`, `dinner_meals`, `total_meals`, `total_amount`, `paid_amount`, `due_amount`, `status`, `issue_date`, `due_date`, `lines`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BILL-2610-00001', 1, '2026-09-01', '2026-09-30', 106, 69, 175, 22380.00, 22380.00, 0.00, 'paid', '2026-10-02', '2026-10-17', '[{\"meal_type\":\"lunch\",\"unit_price\":\"120.00\",\"quantity\":106,\"amount\":\"12720.00\"},{\"meal_type\":\"dinner\",\"unit_price\":\"140.00\",\"quantity\":69,\"amount\":\"9660.00\"}]', NULL, 1, '2026-10-07 01:51:35', '2026-10-07 01:51:35'),
(2, 'BILL-2610-00002', 2, '2026-09-01', '2026-09-30', 132, 0, 132, 17160.00, 8580.00, 8580.00, 'partially_paid', '2026-10-02', '2026-10-17', '[{\"meal_type\":\"lunch\",\"unit_price\":\"130.00\",\"quantity\":132,\"amount\":\"17160.00\"}]', NULL, 1, '2026-10-07 01:51:35', '2026-10-07 01:51:35'),
(3, 'BILL-2610-00003', 3, '2026-09-01', '2026-09-30', 156, 95, 251, 37650.00, 0.00, 37650.00, 'unpaid', '2026-10-02', '2026-10-17', '[{\"meal_type\":\"lunch\",\"unit_price\":\"150.00\",\"quantity\":156,\"amount\":\"23400.00\"},{\"meal_type\":\"dinner\",\"unit_price\":\"150.00\",\"quantity\":95,\"amount\":\"14250.00\"}]', NULL, 1, '2026-10-07 01:51:35', '2026-10-07 01:51:35'),
(4, 'BILL-2610-00004', 4, '2026-09-01', '2026-09-30', 179, 0, 179, 22260.00, 0.00, 22260.00, 'unpaid', '2026-10-02', '2026-10-17', '[{\"meal_type\":\"lunch\",\"unit_price\":\"120.00\",\"quantity\":153,\"amount\":\"18360.00\"},{\"meal_type\":\"lunch\",\"unit_price\":\"150.00\",\"quantity\":26,\"amount\":\"3900.00\"}]', NULL, 1, '2026-10-07 01:51:35', '2026-10-07 01:51:35');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('meal-manager-cache-2121221|::1', 'i:1;', 1791275938),
('meal-manager-cache-2121221|::1:timer', 'i:1791275938;', 1791275938),
('meal-manager-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1791376035),
('meal-manager-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1791376035;', 1791376035),
('meal-manager-cache-902ba3cda1883801594b6e1b452790cc53948fda', 'i:3;', 1791336043),
('meal-manager-cache-902ba3cda1883801594b6e1b452790cc53948fda:timer', 'i:1791336043;', 1791336043),
('meal-manager-cache-ac3478d69a3c81fa62e60f5c3696165a4e5e6ac4', 'i:1;', 1791363335),
('meal-manager-cache-ac3478d69a3c81fa62e60f5c3696165a4e5e6ac4:timer', 'i:1791363335;', 1791363335),
('meal-manager-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:1;', 1791363297),
('meal-manager-cache-da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1791363297;', 1791363297);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(100) NOT NULL,
  `delivery_address` varchar(500) DEFAULT NULL,
  `employee_count` int(10) UNSIGNED DEFAULT NULL,
  `lunch_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `dinner_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `office_start_time` time DEFAULT NULL,
  `lunch_cutoff_time` time DEFAULT NULL,
  `dinner_cutoff_time` time DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `city`, `delivery_address`, `employee_count`, `lunch_enabled`, `dinner_enabled`, `office_start_time`, `lunch_cutoff_time`, `dinner_cutoff_time`, `special_instructions`, `status`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'XYZ Software Ltd.', 'Tanvir Ahmed', '01711000000', 'office0@example.com', 'House 10, Road 5, Gulshan', 'Dhaka', '5th floor reception, House 10, Road 5, Gulshan', 20, 1, 1, '09:00:00', '10:00:00', '16:00:00', 'Call the reception on arrival.', 'active', NULL, NULL, '2026-10-06 09:10:59', '2026-10-06 09:10:59', NULL),
(2, 'ABC Bank Ltd.', 'Nusrat Jahan', '01711000001', 'office1@example.com', 'House 11, Road 5, Gulshan', 'Dhaka', NULL, 30, 1, 0, '09:00:00', '10:00:00', NULL, NULL, 'active', NULL, NULL, '2026-10-06 09:11:00', '2026-10-06 09:11:00', NULL),
(3, 'Tech Solutions Ltd.', 'Rakib Hasan', '01711000002', 'office2@example.com', 'House 12, Road 5, Gulshan', 'Dhaka', NULL, 40, 1, 1, '09:00:00', '10:00:00', '16:00:00', NULL, 'active', NULL, NULL, '2026-10-06 09:11:00', '2026-10-06 09:11:00', NULL),
(4, 'Digital Bangladesh Ltd.', 'Farhana Akter', '01711000003', 'office3@example.com', 'House 13, Road 5, Gulshan', 'Chattogram', NULL, 50, 1, 0, '09:00:00', '10:00:00', NULL, NULL, 'active', NULL, NULL, '2026-10-06 09:11:00', '2026-10-06 09:11:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_menu_prices`
--

CREATE TABLE `client_menu_prices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `meal_type` varchar(20) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `effective_from` date NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_menu_prices`
--

INSERT INTO `client_menu_prices` (`id`, `client_id`, `meal_type`, `price`, `effective_from`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'lunch', 120.00, '2026-08-01', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(2, 1, 'dinner', 140.00, '2026-08-01', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(3, 2, 'lunch', 130.00, '2026-08-01', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(4, 3, 'lunch', 150.00, '2026-08-01', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(5, 3, 'dinner', 150.00, '2026-08-01', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `daily_menus`
--

CREATE TABLE `daily_menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `menu_date` date NOT NULL,
  `meal_type` varchar(20) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_menus`
--

INSERT INTO `daily_menus` (`id`, `menu_date`, `meal_type`, `price`, `notes`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, '2026-09-16', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(2, '2026-09-16', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(3, '2026-09-17', 'lunch', 150.00, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(4, '2026-09-17', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(5, '2026-09-19', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(6, '2026-09-19', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(7, '2026-09-20', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(8, '2026-09-20', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(9, '2026-09-21', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(10, '2026-09-21', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(11, '2026-09-22', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(12, '2026-09-22', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(13, '2026-09-23', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(14, '2026-09-23', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(15, '2026-09-24', 'lunch', 150.00, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(16, '2026-09-24', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(17, '2026-09-26', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(18, '2026-09-26', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(19, '2026-09-27', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(20, '2026-09-27', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(21, '2026-09-28', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(22, '2026-09-28', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(23, '2026-09-29', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(24, '2026-09-29', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(25, '2026-09-30', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(26, '2026-09-30', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(27, '2026-10-01', 'lunch', 150.00, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(28, '2026-10-01', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(29, '2026-10-03', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(30, '2026-10-03', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(31, '2026-10-04', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(32, '2026-10-04', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(33, '2026-10-05', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(34, '2026-10-05', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(35, '2026-10-06', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(36, '2026-10-06', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(37, '2026-10-07', 'lunch', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(38, '2026-10-07', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(39, '2026-10-08', 'lunch', 150.00, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(40, '2026-10-08', 'dinner', NULL, NULL, NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42');

-- --------------------------------------------------------

--
-- Table structure for table `daily_menu_items`
--

CREATE TABLE `daily_menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `daily_menu_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_menu_items`
--

INSERT INTO `daily_menu_items` (`id`, `daily_menu_id`, `menu_item_id`, `sort_order`) VALUES
(1, 1, 1, 0),
(2, 1, 4, 1),
(3, 1, 9, 2),
(4, 1, 8, 3),
(5, 1, 10, 4),
(6, 2, 1, 0),
(7, 2, 4, 1),
(8, 2, 9, 2),
(9, 3, 3, 0),
(10, 3, 10, 1),
(11, 3, 11, 2),
(12, 4, 1, 0),
(13, 4, 7, 1),
(14, 4, 8, 2),
(15, 4, 9, 3),
(16, 5, 2, 0),
(17, 5, 5, 1),
(18, 5, 10, 2),
(19, 5, 12, 3),
(20, 6, 1, 0),
(21, 6, 7, 1),
(22, 6, 8, 2),
(23, 6, 9, 3),
(24, 7, 1, 0),
(25, 7, 6, 1),
(26, 7, 9, 2),
(27, 7, 8, 3),
(28, 8, 1, 0),
(29, 8, 4, 1),
(30, 8, 9, 2),
(31, 9, 1, 0),
(32, 9, 4, 1),
(33, 9, 9, 2),
(34, 9, 8, 3),
(35, 9, 10, 4),
(36, 10, 1, 0),
(37, 10, 7, 1),
(38, 10, 8, 2),
(39, 10, 9, 3),
(40, 11, 3, 0),
(41, 11, 10, 1),
(42, 11, 11, 2),
(43, 12, 1, 0),
(44, 12, 4, 1),
(45, 12, 9, 2),
(46, 13, 1, 0),
(47, 13, 7, 1),
(48, 13, 9, 2),
(49, 13, 8, 3),
(50, 14, 1, 0),
(51, 14, 7, 1),
(52, 14, 8, 2),
(53, 14, 9, 3),
(54, 15, 2, 0),
(55, 15, 5, 1),
(56, 15, 10, 2),
(57, 15, 12, 3),
(58, 16, 1, 0),
(59, 16, 4, 1),
(60, 16, 9, 2),
(61, 17, 1, 0),
(62, 17, 4, 1),
(63, 17, 9, 2),
(64, 17, 8, 3),
(65, 17, 10, 4),
(66, 18, 1, 0),
(67, 18, 4, 1),
(68, 18, 9, 2),
(69, 19, 3, 0),
(70, 19, 10, 1),
(71, 19, 11, 2),
(72, 20, 1, 0),
(73, 20, 7, 1),
(74, 20, 8, 2),
(75, 20, 9, 3),
(76, 21, 1, 0),
(77, 21, 7, 1),
(78, 21, 9, 2),
(79, 21, 8, 3),
(80, 22, 1, 0),
(81, 22, 4, 1),
(82, 22, 9, 2),
(83, 23, 2, 0),
(84, 23, 5, 1),
(85, 23, 10, 2),
(86, 23, 12, 3),
(87, 24, 1, 0),
(88, 24, 7, 1),
(89, 24, 8, 2),
(90, 24, 9, 3),
(91, 25, 1, 0),
(92, 25, 6, 1),
(93, 25, 9, 2),
(94, 25, 8, 3),
(95, 26, 1, 0),
(96, 26, 4, 1),
(97, 26, 9, 2),
(98, 27, 1, 0),
(99, 27, 4, 1),
(100, 27, 9, 2),
(101, 27, 8, 3),
(102, 27, 10, 4),
(103, 28, 1, 0),
(104, 28, 7, 1),
(105, 28, 8, 2),
(106, 28, 9, 3),
(107, 29, 1, 0),
(108, 29, 7, 1),
(109, 29, 9, 2),
(110, 29, 8, 3),
(111, 30, 1, 0),
(112, 30, 7, 1),
(113, 30, 8, 2),
(114, 30, 9, 3),
(115, 31, 2, 0),
(116, 31, 5, 1),
(117, 31, 10, 2),
(118, 31, 12, 3),
(119, 32, 1, 0),
(120, 32, 4, 1),
(121, 32, 9, 2),
(122, 33, 1, 0),
(123, 33, 6, 1),
(124, 33, 9, 2),
(125, 33, 8, 3),
(126, 34, 1, 0),
(127, 34, 7, 1),
(128, 34, 8, 2),
(129, 34, 9, 3),
(130, 35, 1, 0),
(131, 35, 4, 1),
(132, 35, 9, 2),
(133, 35, 8, 3),
(134, 35, 10, 4),
(135, 36, 1, 0),
(136, 36, 4, 1),
(137, 36, 9, 2),
(138, 37, 1, 0),
(139, 37, 6, 1),
(140, 37, 9, 2),
(141, 37, 8, 3),
(142, 38, 1, 0),
(143, 38, 7, 1),
(144, 38, 8, 2),
(145, 38, 9, 3),
(146, 39, 2, 0),
(147, 39, 5, 1),
(148, 39, 10, 2),
(149, 39, 12, 3),
(150, 40, 1, 0),
(151, 40, 4, 1),
(152, 40, 9, 2);

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `rider_id` bigint(20) UNSIGNED NOT NULL,
  `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `picked_up_at` timestamp NULL DEFAULT NULL,
  `on_the_way_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `rider_id`, `assigned_by`, `assigned_at`, `picked_up_at`, `on_the_way_at`, `delivered_at`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-09-15 11:40:00', '2026-09-15 12:50:00', '2026-09-15 12:55:00', '2026-09-15 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(2, 2, 2, 1, '2026-09-15 04:40:00', '2026-09-15 05:50:00', '2026-09-15 05:55:00', '2026-09-15 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(3, 3, 3, 1, '2026-09-16 11:40:00', '2026-09-16 12:50:00', '2026-09-16 12:55:00', '2026-09-16 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(4, 4, 1, 1, '2026-09-16 04:40:00', '2026-09-16 05:50:00', '2026-09-16 05:55:00', '2026-09-16 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(5, 5, 2, 1, '2026-09-17 11:40:00', '2026-09-17 12:50:00', '2026-09-17 12:55:00', '2026-09-17 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(6, 6, 3, 1, '2026-09-17 04:40:00', '2026-09-17 05:50:00', '2026-09-17 05:55:00', '2026-09-17 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(7, 7, 1, 1, '2026-09-19 11:40:00', '2026-09-19 12:50:00', '2026-09-19 12:55:00', '2026-09-19 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(8, 8, 2, 1, '2026-09-19 04:40:00', '2026-09-19 05:50:00', '2026-09-19 05:55:00', '2026-09-19 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(9, 9, 3, 1, '2026-09-20 11:40:00', '2026-09-20 12:50:00', '2026-09-20 12:55:00', '2026-09-20 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(10, 10, 1, 1, '2026-09-20 04:40:00', '2026-09-20 05:50:00', '2026-09-20 05:55:00', '2026-09-20 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(11, 11, 2, 1, '2026-09-21 11:40:00', '2026-09-21 12:50:00', '2026-09-21 12:55:00', '2026-09-21 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(12, 12, 3, 1, '2026-09-21 04:40:00', '2026-09-21 05:50:00', '2026-09-21 05:55:00', '2026-09-21 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(13, 13, 1, 1, '2026-09-22 11:40:00', '2026-09-22 12:50:00', '2026-09-22 12:55:00', '2026-09-22 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(14, 14, 2, 1, '2026-09-22 04:40:00', '2026-09-22 05:50:00', '2026-09-22 05:55:00', '2026-09-22 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(15, 15, 3, 1, '2026-09-23 11:40:00', '2026-09-23 12:50:00', '2026-09-23 12:55:00', '2026-09-23 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(16, 16, 1, 1, '2026-09-23 04:40:00', '2026-09-23 05:50:00', '2026-09-23 05:55:00', '2026-09-23 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(17, 17, 2, 1, '2026-09-24 11:40:00', '2026-09-24 12:50:00', '2026-09-24 12:55:00', '2026-09-24 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(18, 18, 3, 1, '2026-09-24 04:40:00', '2026-09-24 05:50:00', '2026-09-24 05:55:00', '2026-09-24 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(19, 19, 1, 1, '2026-09-26 11:40:00', '2026-09-26 12:50:00', '2026-09-26 12:55:00', '2026-09-26 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(20, 20, 2, 1, '2026-09-26 04:40:00', '2026-09-26 05:50:00', '2026-09-26 05:55:00', '2026-09-26 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(21, 21, 3, 1, '2026-09-27 11:40:00', '2026-09-27 12:50:00', '2026-09-27 12:55:00', '2026-09-27 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(22, 22, 1, 1, '2026-09-27 04:40:00', '2026-09-27 05:50:00', '2026-09-27 05:55:00', '2026-09-27 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(23, 23, 2, 1, '2026-09-28 11:40:00', '2026-09-28 12:50:00', '2026-09-28 12:55:00', '2026-09-28 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(24, 24, 3, 1, '2026-09-28 04:40:00', '2026-09-28 05:50:00', '2026-09-28 05:55:00', '2026-09-28 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(25, 25, 1, 1, '2026-09-29 11:40:00', '2026-09-29 12:50:00', '2026-09-29 12:55:00', '2026-09-29 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(26, 26, 2, 1, '2026-09-29 04:40:00', '2026-09-29 05:50:00', '2026-09-29 05:55:00', '2026-09-29 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(27, 27, 3, 1, '2026-09-30 11:40:00', '2026-09-30 12:50:00', '2026-09-30 12:55:00', '2026-09-30 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(28, 28, 1, 1, '2026-09-30 04:40:00', '2026-09-30 05:50:00', '2026-09-30 05:55:00', '2026-09-30 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(29, 29, 2, 1, '2026-10-01 11:40:00', '2026-10-01 12:50:00', '2026-10-01 12:55:00', '2026-10-01 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(30, 30, 3, 1, '2026-10-01 04:40:00', '2026-10-01 05:50:00', '2026-10-01 05:55:00', '2026-10-01 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(31, 31, 1, 1, '2026-10-03 11:40:00', '2026-10-03 12:50:00', '2026-10-03 12:55:00', '2026-10-03 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(32, 32, 2, 1, '2026-10-03 04:40:00', '2026-10-03 05:50:00', '2026-10-03 05:55:00', '2026-10-03 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(33, 33, 3, 1, '2026-10-04 11:40:00', '2026-10-04 12:50:00', '2026-10-04 12:55:00', '2026-10-04 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(34, 34, 1, 1, '2026-10-04 04:40:00', '2026-10-04 05:50:00', '2026-10-04 05:55:00', '2026-10-04 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(35, 35, 2, 1, '2026-10-05 11:40:00', '2026-10-05 12:50:00', '2026-10-05 12:55:00', '2026-10-05 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(36, 36, 3, 1, '2026-10-05 04:40:00', '2026-10-05 05:50:00', '2026-10-05 05:55:00', '2026-10-05 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(37, 39, 3, 1, '2026-09-15 04:40:00', '2026-09-15 05:50:00', '2026-09-15 05:55:00', '2026-09-15 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(38, 40, 1, 1, '2026-09-16 04:40:00', '2026-09-16 05:50:00', '2026-09-16 05:55:00', '2026-09-16 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(39, 41, 2, 1, '2026-09-17 04:40:00', '2026-09-17 05:50:00', '2026-09-17 05:55:00', '2026-09-17 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(40, 42, 3, 1, '2026-09-19 04:40:00', '2026-09-19 05:50:00', '2026-09-19 05:55:00', '2026-09-19 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(41, 43, 1, 1, '2026-09-20 04:40:00', '2026-09-20 05:50:00', '2026-09-20 05:55:00', '2026-09-20 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(42, 44, 2, 1, '2026-09-21 04:40:00', '2026-09-21 05:50:00', '2026-09-21 05:55:00', '2026-09-21 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(43, 45, 3, 1, '2026-09-22 04:40:00', '2026-09-22 05:50:00', '2026-09-22 05:55:00', '2026-09-22 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(44, 46, 1, 1, '2026-09-23 04:40:00', '2026-09-23 05:50:00', '2026-09-23 05:55:00', '2026-09-23 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(45, 47, 2, 1, '2026-09-24 04:40:00', '2026-09-24 05:50:00', '2026-09-24 05:55:00', '2026-09-24 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(46, 48, 3, 1, '2026-09-26 04:40:00', '2026-09-26 05:50:00', '2026-09-26 05:55:00', '2026-09-26 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(47, 49, 1, 1, '2026-09-27 04:40:00', '2026-09-27 05:50:00', '2026-09-27 05:55:00', '2026-09-27 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(48, 50, 2, 1, '2026-09-28 04:40:00', '2026-09-28 05:50:00', '2026-09-28 05:55:00', '2026-09-28 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(49, 51, 3, 1, '2026-09-29 04:40:00', '2026-09-29 05:50:00', '2026-09-29 05:55:00', '2026-09-29 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(50, 52, 1, 1, '2026-09-30 04:40:00', '2026-09-30 05:50:00', '2026-09-30 05:55:00', '2026-09-30 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(51, 53, 2, 1, '2026-10-01 04:40:00', '2026-10-01 05:50:00', '2026-10-01 05:55:00', '2026-10-01 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(52, 54, 3, 1, '2026-10-03 04:40:00', '2026-10-03 05:50:00', '2026-10-03 05:55:00', '2026-10-03 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(53, 55, 1, 1, '2026-10-04 04:40:00', '2026-10-04 05:50:00', '2026-10-04 05:55:00', '2026-10-04 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(54, 56, 2, 1, '2026-10-05 04:40:00', '2026-10-05 05:50:00', '2026-10-05 05:55:00', '2026-10-05 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(55, 57, 3, 1, '2026-09-15 11:40:00', '2026-09-15 12:50:00', '2026-09-15 12:55:00', '2026-09-15 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(56, 58, 1, 1, '2026-09-15 04:40:00', '2026-09-15 05:50:00', '2026-09-15 05:55:00', '2026-09-15 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(57, 59, 2, 1, '2026-09-16 11:40:00', '2026-09-16 12:50:00', '2026-09-16 12:55:00', '2026-09-16 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(58, 60, 3, 1, '2026-09-16 04:40:00', '2026-09-16 05:50:00', '2026-09-16 05:55:00', '2026-09-16 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(59, 61, 1, 1, '2026-09-17 11:40:00', '2026-09-17 12:50:00', '2026-09-17 12:55:00', '2026-09-17 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(60, 62, 2, 1, '2026-09-17 04:40:00', '2026-09-17 05:50:00', '2026-09-17 05:55:00', '2026-09-17 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(61, 63, 3, 1, '2026-09-19 11:40:00', '2026-09-19 12:50:00', '2026-09-19 12:55:00', '2026-09-19 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(62, 64, 1, 1, '2026-09-19 04:40:00', '2026-09-19 05:50:00', '2026-09-19 05:55:00', '2026-09-19 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(63, 65, 2, 1, '2026-09-20 11:40:00', '2026-09-20 12:50:00', '2026-09-20 12:55:00', '2026-09-20 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(64, 66, 3, 1, '2026-09-20 04:40:00', '2026-09-20 05:50:00', '2026-09-20 05:55:00', '2026-09-20 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(65, 67, 1, 1, '2026-09-21 11:40:00', '2026-09-21 12:50:00', '2026-09-21 12:55:00', '2026-09-21 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(66, 68, 2, 1, '2026-09-21 04:40:00', '2026-09-21 05:50:00', '2026-09-21 05:55:00', '2026-09-21 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(67, 69, 3, 1, '2026-09-22 11:40:00', '2026-09-22 12:50:00', '2026-09-22 12:55:00', '2026-09-22 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(68, 70, 1, 1, '2026-09-22 04:40:00', '2026-09-22 05:50:00', '2026-09-22 05:55:00', '2026-09-22 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(69, 71, 2, 1, '2026-09-23 11:40:00', '2026-09-23 12:50:00', '2026-09-23 12:55:00', '2026-09-23 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(70, 72, 3, 1, '2026-09-23 04:40:00', '2026-09-23 05:50:00', '2026-09-23 05:55:00', '2026-09-23 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(71, 73, 1, 1, '2026-09-24 11:40:00', '2026-09-24 12:50:00', '2026-09-24 12:55:00', '2026-09-24 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(72, 74, 2, 1, '2026-09-24 04:40:00', '2026-09-24 05:50:00', '2026-09-24 05:55:00', '2026-09-24 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(73, 75, 3, 1, '2026-09-26 11:40:00', '2026-09-26 12:50:00', '2026-09-26 12:55:00', '2026-09-26 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(74, 76, 1, 1, '2026-09-26 04:40:00', '2026-09-26 05:50:00', '2026-09-26 05:55:00', '2026-09-26 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(75, 77, 2, 1, '2026-09-27 11:40:00', '2026-09-27 12:50:00', '2026-09-27 12:55:00', '2026-09-27 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(76, 78, 3, 1, '2026-09-27 04:40:00', '2026-09-27 05:50:00', '2026-09-27 05:55:00', '2026-09-27 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(77, 79, 1, 1, '2026-09-28 11:40:00', '2026-09-28 12:50:00', '2026-09-28 12:55:00', '2026-09-28 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(78, 80, 2, 1, '2026-09-28 04:40:00', '2026-09-28 05:50:00', '2026-09-28 05:55:00', '2026-09-28 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(79, 81, 3, 1, '2026-09-29 11:40:00', '2026-09-29 12:50:00', '2026-09-29 12:55:00', '2026-09-29 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(80, 82, 1, 1, '2026-09-29 04:40:00', '2026-09-29 05:50:00', '2026-09-29 05:55:00', '2026-09-29 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(81, 83, 2, 1, '2026-09-30 11:40:00', '2026-09-30 12:50:00', '2026-09-30 12:55:00', '2026-09-30 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(82, 84, 3, 1, '2026-09-30 04:40:00', '2026-09-30 05:50:00', '2026-09-30 05:55:00', '2026-09-30 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(83, 85, 1, 1, '2026-10-01 11:40:00', '2026-10-01 12:50:00', '2026-10-01 12:55:00', '2026-10-01 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(84, 86, 2, 1, '2026-10-01 04:40:00', '2026-10-01 05:50:00', '2026-10-01 05:55:00', '2026-10-01 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(85, 87, 3, 1, '2026-10-03 11:40:00', '2026-10-03 12:50:00', '2026-10-03 12:55:00', '2026-10-03 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(86, 88, 1, 1, '2026-10-03 04:40:00', '2026-10-03 05:50:00', '2026-10-03 05:55:00', '2026-10-03 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(87, 89, 2, 1, '2026-10-04 11:40:00', '2026-10-04 12:50:00', '2026-10-04 12:55:00', '2026-10-04 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(88, 90, 3, 1, '2026-10-04 04:40:00', '2026-10-04 05:50:00', '2026-10-04 05:55:00', '2026-10-04 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(89, 91, 1, 1, '2026-10-05 11:40:00', '2026-10-05 12:50:00', '2026-10-05 12:55:00', '2026-10-05 13:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(90, 92, 2, 1, '2026-10-05 04:40:00', '2026-10-05 05:50:00', '2026-10-05 05:55:00', '2026-10-05 06:40:00', NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(91, 93, 3, 1, '2026-09-15 04:40:00', '2026-09-15 05:50:00', '2026-09-15 05:55:00', '2026-09-15 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(92, 94, 1, 1, '2026-09-16 04:40:00', '2026-09-16 05:50:00', '2026-09-16 05:55:00', '2026-09-16 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(93, 95, 2, 1, '2026-09-17 04:40:00', '2026-09-17 05:50:00', '2026-09-17 05:55:00', '2026-09-17 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(94, 96, 3, 1, '2026-09-19 04:40:00', '2026-09-19 05:50:00', '2026-09-19 05:55:00', '2026-09-19 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(95, 97, 1, 1, '2026-09-20 04:40:00', '2026-09-20 05:50:00', '2026-09-20 05:55:00', '2026-09-20 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(96, 98, 2, 1, '2026-09-21 04:40:00', '2026-09-21 05:50:00', '2026-09-21 05:55:00', '2026-09-21 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(97, 99, 3, 1, '2026-09-22 04:40:00', '2026-09-22 05:50:00', '2026-09-22 05:55:00', '2026-09-22 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(98, 100, 1, 1, '2026-09-23 04:40:00', '2026-09-23 05:50:00', '2026-09-23 05:55:00', '2026-09-23 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(99, 101, 2, 1, '2026-09-24 04:40:00', '2026-09-24 05:50:00', '2026-09-24 05:55:00', '2026-09-24 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(100, 102, 3, 1, '2026-09-26 04:40:00', '2026-09-26 05:50:00', '2026-09-26 05:55:00', '2026-09-26 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(101, 103, 1, 1, '2026-09-27 04:40:00', '2026-09-27 05:50:00', '2026-09-27 05:55:00', '2026-09-27 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(102, 104, 2, 1, '2026-09-28 04:40:00', '2026-09-28 05:50:00', '2026-09-28 05:55:00', '2026-09-28 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(103, 105, 3, 1, '2026-09-29 04:40:00', '2026-09-29 05:50:00', '2026-09-29 05:55:00', '2026-09-29 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(104, 106, 1, 1, '2026-09-30 04:40:00', '2026-09-30 05:50:00', '2026-09-30 05:55:00', '2026-09-30 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(105, 107, 2, 1, '2026-10-01 04:40:00', '2026-10-01 05:50:00', '2026-10-01 05:55:00', '2026-10-01 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(106, 108, 3, 1, '2026-10-03 04:40:00', '2026-10-03 05:50:00', '2026-10-03 05:55:00', '2026-10-03 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(107, 109, 1, 1, '2026-10-04 04:40:00', '2026-10-04 05:50:00', '2026-10-04 05:55:00', '2026-10-04 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(108, 110, 2, 1, '2026-10-05 04:40:00', '2026-10-05 05:50:00', '2026-10-05 05:55:00', '2026-10-05 06:40:00', NULL, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(109, 38, 1, 1, '2026-10-07 01:19:47', '2026-10-07 01:19:43', '2026-10-07 01:19:45', '2026-10-07 01:19:47', NULL, '2026-10-07 01:19:37', '2026-10-07 01:19:47'),
(110, 111, 1, 1, '2026-10-07 12:24:02', '2026-10-07 12:24:00', '2026-10-07 12:24:02', NULL, NULL, '2026-10-07 12:23:22', '2026-10-07 12:24:02');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `employee_code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `meal_preference` varchar(30) NOT NULL DEFAULT 'regular',
  `lunch_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `dinner_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `joining_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `client_id`, `employee_code`, `name`, `phone`, `email`, `department`, `designation`, `meal_preference`, `lunch_enabled`, `dinner_enabled`, `status`, `joining_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'EMP-001', 'Abdullah Al Mamun', '01910000000', NULL, 'Engineering', 'Manager', 'regular', 1, 0, 'active', '2026-04-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(2, 1, 'EMP-002', 'Rahim Uddin', '01910000001', NULL, 'Finance', 'Executive', 'regular', 1, 1, 'active', '2026-03-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(3, 1, 'EMP-003', 'Karim Hossain', '01910000002', NULL, 'Sales', 'Executive', 'regular', 1, 1, 'active', '2026-02-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(4, 1, 'EMP-004', 'Hasan Mahmud', '01910000003', NULL, 'Operations', 'Manager', 'vegetarian', 1, 0, 'active', '2026-01-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(5, 1, 'EMP-005', 'Nusrat Jahan', '01910000004', NULL, 'HR', 'Executive', 'regular', 1, 1, 'active', '2025-12-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(6, 1, 'EMP-006', 'Farhana Akter', '01910000005', NULL, 'Engineering', 'Executive', 'regular', 1, 1, 'active', '2025-11-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(7, 1, 'EMP-007', 'Tanvir Ahmed', '01910000006', NULL, 'Finance', 'Manager', 'regular', 1, 0, 'active', '2025-10-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(8, 1, 'EMP-008', 'Sadia Islam', '01910000007', NULL, 'Sales', 'Executive', 'regular', 1, 1, 'active', '2025-09-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(9, 1, 'EMP-009', 'Rakib Hasan', '01910000008', NULL, 'Operations', 'Executive', 'regular', 1, 1, 'active', '2025-08-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(10, 1, 'EMP-010', 'Mehedi Hasan', '01910000009', NULL, 'HR', 'Manager', 'regular', 1, 0, 'active', '2025-07-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-07 07:52:19'),
(11, 2, 'EMP-001', 'Abdullah Al Mamun', '01910001000', NULL, 'Engineering', 'Manager', 'regular', 1, 0, 'active', '2026-04-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(12, 2, 'EMP-002', 'Rahim Uddin', '01910001001', NULL, 'Finance', 'Executive', 'regular', 1, 0, 'active', '2026-03-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(13, 2, 'EMP-003', 'Karim Hossain', '01910001002', NULL, 'Sales', 'Executive', 'regular', 1, 0, 'active', '2026-02-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(14, 2, 'EMP-004', 'Hasan Mahmud', '01910001003', NULL, 'Operations', 'Manager', 'vegetarian', 1, 0, 'active', '2026-01-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(15, 2, 'EMP-005', 'Nusrat Jahan', '01910001004', NULL, 'HR', 'Executive', 'regular', 1, 0, 'active', '2025-12-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(16, 2, 'EMP-006', 'Farhana Akter', '01910001005', NULL, 'Engineering', 'Executive', 'regular', 1, 0, 'active', '2025-11-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(17, 2, 'EMP-007', 'Tanvir Ahmed', '01910001006', NULL, 'Finance', 'Manager', 'regular', 1, 0, 'active', '2025-10-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(18, 2, 'EMP-008', 'Sadia Islam', '01910001007', NULL, 'Sales', 'Executive', 'regular', 1, 0, 'active', '2025-09-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(19, 2, 'EMP-009', 'Rakib Hasan', '01910001008', NULL, 'Operations', 'Executive', 'regular', 1, 0, 'active', '2025-08-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(20, 2, 'EMP-010', 'Mehedi Hasan', '01910001009', NULL, 'HR', 'Manager', 'regular', 1, 0, 'active', '2025-07-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(21, 2, 'EMP-011', 'Sumaiya Rahman', '01910001010', NULL, 'Engineering', 'Executive', 'vegetarian', 1, 0, 'active', '2025-06-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(22, 2, 'EMP-012', 'Arif Chowdhury', '01910001011', NULL, 'Finance', 'Executive', 'regular', 1, 0, 'inactive', '2025-05-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(23, 3, 'EMP-001', 'Abdullah Al Mamun', '01910002000', NULL, 'Engineering', 'Manager', 'regular', 1, 0, 'active', '2026-04-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(24, 3, 'EMP-002', 'Rahim Uddin', '01910002001', NULL, 'Finance', 'Executive', 'regular', 1, 1, 'active', '2026-03-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(25, 3, 'EMP-003', 'Karim Hossain', '01910002002', NULL, 'Sales', 'Executive', 'regular', 1, 1, 'active', '2026-02-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(26, 3, 'EMP-004', 'Hasan Mahmud', '01910002003', NULL, 'Operations', 'Manager', 'vegetarian', 1, 0, 'active', '2026-01-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(27, 3, 'EMP-005', 'Nusrat Jahan', '01910002004', NULL, 'HR', 'Executive', 'regular', 1, 1, 'active', '2025-12-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(28, 3, 'EMP-006', 'Farhana Akter', '01910002005', NULL, 'Engineering', 'Executive', 'regular', 1, 1, 'active', '2025-11-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(29, 3, 'EMP-007', 'Tanvir Ahmed', '01910002006', NULL, 'Finance', 'Manager', 'regular', 1, 0, 'active', '2025-10-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(30, 3, 'EMP-008', 'Sadia Islam', '01910002007', NULL, 'Sales', 'Executive', 'regular', 1, 1, 'active', '2025-09-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(31, 3, 'EMP-009', 'Rakib Hasan', '01910002008', NULL, 'Operations', 'Executive', 'regular', 1, 1, 'active', '2025-08-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(32, 3, 'EMP-010', 'Mehedi Hasan', '01910002009', NULL, 'HR', 'Manager', 'regular', 1, 0, 'active', '2025-07-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(33, 3, 'EMP-011', 'Sumaiya Rahman', '01910002010', NULL, 'Engineering', 'Executive', 'vegetarian', 1, 1, 'active', '2025-06-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(34, 3, 'EMP-012', 'Arif Chowdhury', '01910002011', NULL, 'Finance', 'Executive', 'regular', 1, 1, 'active', '2025-05-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(35, 3, 'EMP-013', 'Jannatul Ferdous', '01910002012', NULL, 'Sales', 'Manager', 'regular', 1, 0, 'active', '2025-04-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(36, 3, 'EMP-014', 'Imran Kabir', '01910002013', NULL, 'Operations', 'Executive', 'regular', 1, 1, 'inactive', '2025-03-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(37, 4, 'EMP-001', 'Abdullah Al Mamun', '01910003000', NULL, 'Engineering', 'Manager', 'regular', 1, 0, 'active', '2026-04-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(38, 4, 'EMP-002', 'Rahim Uddin', '01910003001', NULL, 'Finance', 'Executive', 'regular', 1, 0, 'active', '2026-03-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(39, 4, 'EMP-003', 'Karim Hossain', '01910003002', NULL, 'Sales', 'Executive', 'regular', 1, 0, 'active', '2026-02-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(40, 4, 'EMP-004', 'Hasan Mahmud', '01910003003', NULL, 'Operations', 'Manager', 'vegetarian', 1, 0, 'active', '2026-01-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(41, 4, 'EMP-005', 'Nusrat Jahan', '01910003004', NULL, 'HR', 'Executive', 'regular', 1, 0, 'active', '2025-12-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(42, 4, 'EMP-006', 'Farhana Akter', '01910003005', NULL, 'Engineering', 'Executive', 'regular', 1, 0, 'active', '2025-11-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(43, 4, 'EMP-007', 'Tanvir Ahmed', '01910003006', NULL, 'Finance', 'Manager', 'regular', 1, 0, 'active', '2025-10-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(44, 4, 'EMP-008', 'Sadia Islam', '01910003007', NULL, 'Sales', 'Executive', 'regular', 1, 0, 'active', '2025-09-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(45, 4, 'EMP-009', 'Rakib Hasan', '01910003008', NULL, 'Operations', 'Executive', 'regular', 1, 0, 'active', '2025-08-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(46, 4, 'EMP-010', 'Mehedi Hasan', '01910003009', NULL, 'HR', 'Manager', 'regular', 1, 0, 'active', '2025-07-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(47, 4, 'EMP-011', 'Sumaiya Rahman', '01910003010', NULL, 'Engineering', 'Executive', 'vegetarian', 1, 0, 'active', '2025-06-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(48, 4, 'EMP-012', 'Arif Chowdhury', '01910003011', NULL, 'Finance', 'Executive', 'regular', 1, 0, 'active', '2025-05-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(49, 4, 'EMP-013', 'Jannatul Ferdous', '01910003012', NULL, 'Sales', 'Manager', 'regular', 1, 0, 'active', '2025-04-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(50, 4, 'EMP-014', 'Imran Kabir', '01910003013', NULL, 'Operations', 'Executive', 'regular', 1, 0, 'active', '2025-03-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(51, 4, 'EMP-015', 'Shakil Ahmed', '01910003014', NULL, 'HR', 'Executive', 'regular', 1, 0, 'active', '2025-02-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37'),
(52, 4, 'EMP-016', 'Taslima Begum', '01910003015', NULL, 'Engineering', 'Manager', 'regular', 1, 0, 'inactive', '2025-01-06', NULL, NULL, '2026-10-06 09:23:37', '2026-10-06 09:23:37');

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
-- Table structure for table `meal_entries`
--

CREATE TABLE `meal_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED NOT NULL,
  `meal_date` date NOT NULL,
  `meal_type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'confirmed',
  `unit_price` decimal(10,2) NOT NULL,
  `confirmed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `meal_entries`
--

INSERT INTO `meal_entries` (`id`, `client_id`, `employee_id`, `meal_date`, `meal_type`, `status`, `unit_price`, `confirmed_by`, `confirmed_at`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(2, 1, 3, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(3, 1, 4, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(4, 1, 5, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(5, 1, 6, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(6, 1, 7, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(7, 1, 9, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(8, 1, 2, '2026-09-15', 'dinner', 'confirmed', 140.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(9, 1, 3, '2026-09-15', 'dinner', 'confirmed', 140.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(10, 1, 5, '2026-09-15', 'dinner', 'confirmed', 140.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(11, 1, 6, '2026-09-15', 'dinner', 'confirmed', 140.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(12, 1, 9, '2026-09-15', 'dinner', 'confirmed', 140.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(13, 1, 1, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(14, 1, 3, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(15, 1, 4, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(16, 1, 5, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(17, 1, 6, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(18, 1, 7, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(19, 1, 8, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(20, 1, 3, '2026-09-16', 'dinner', 'confirmed', 140.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(21, 1, 5, '2026-09-16', 'dinner', 'confirmed', 140.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(22, 1, 6, '2026-09-16', 'dinner', 'confirmed', 140.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(23, 1, 8, '2026-09-16', 'dinner', 'confirmed', 140.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(24, 1, 1, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(25, 1, 2, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(26, 1, 4, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(27, 1, 5, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(28, 1, 6, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(29, 1, 7, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(30, 1, 8, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(31, 1, 9, '2026-09-17', 'lunch', 'confirmed', 120.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(32, 1, 2, '2026-09-17', 'dinner', 'confirmed', 140.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(33, 1, 5, '2026-09-17', 'dinner', 'confirmed', 140.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(34, 1, 6, '2026-09-17', 'dinner', 'confirmed', 140.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(35, 1, 8, '2026-09-17', 'dinner', 'confirmed', 140.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(36, 1, 9, '2026-09-17', 'dinner', 'confirmed', 140.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(37, 1, 1, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(38, 1, 2, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(39, 1, 3, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(40, 1, 4, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(41, 1, 6, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(42, 1, 7, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(43, 1, 8, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(44, 1, 9, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(45, 1, 2, '2026-09-19', 'dinner', 'confirmed', 140.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(46, 1, 3, '2026-09-19', 'dinner', 'confirmed', 140.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(47, 1, 6, '2026-09-19', 'dinner', 'confirmed', 140.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(48, 1, 8, '2026-09-19', 'dinner', 'confirmed', 140.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(49, 1, 9, '2026-09-19', 'dinner', 'confirmed', 140.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(50, 1, 1, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(51, 1, 2, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(52, 1, 3, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(53, 1, 4, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(54, 1, 5, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(55, 1, 7, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(56, 1, 8, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(57, 1, 9, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(58, 1, 2, '2026-09-20', 'dinner', 'confirmed', 140.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(59, 1, 3, '2026-09-20', 'dinner', 'confirmed', 140.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(60, 1, 5, '2026-09-20', 'dinner', 'confirmed', 140.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(61, 1, 8, '2026-09-20', 'dinner', 'confirmed', 140.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(62, 1, 9, '2026-09-20', 'dinner', 'confirmed', 140.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(63, 1, 1, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(64, 1, 2, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(65, 1, 3, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(66, 1, 4, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(67, 1, 5, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(68, 1, 6, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(69, 1, 8, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(70, 1, 9, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(71, 1, 2, '2026-09-21', 'dinner', 'confirmed', 140.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(72, 1, 3, '2026-09-21', 'dinner', 'confirmed', 140.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(73, 1, 5, '2026-09-21', 'dinner', 'confirmed', 140.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(74, 1, 6, '2026-09-21', 'dinner', 'confirmed', 140.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(75, 1, 8, '2026-09-21', 'dinner', 'confirmed', 140.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(76, 1, 9, '2026-09-21', 'dinner', 'confirmed', 140.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(77, 1, 2, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(78, 1, 3, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(79, 1, 4, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(80, 1, 5, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(81, 1, 6, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(82, 1, 7, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(83, 1, 9, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(84, 1, 2, '2026-09-22', 'dinner', 'confirmed', 140.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(85, 1, 3, '2026-09-22', 'dinner', 'confirmed', 140.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(86, 1, 5, '2026-09-22', 'dinner', 'confirmed', 140.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(87, 1, 6, '2026-09-22', 'dinner', 'confirmed', 140.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(88, 1, 9, '2026-09-22', 'dinner', 'confirmed', 140.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(89, 1, 1, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(90, 1, 3, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(91, 1, 4, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(92, 1, 5, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(93, 1, 6, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(94, 1, 7, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(95, 1, 8, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(96, 1, 3, '2026-09-23', 'dinner', 'confirmed', 140.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(97, 1, 5, '2026-09-23', 'dinner', 'confirmed', 140.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(98, 1, 6, '2026-09-23', 'dinner', 'confirmed', 140.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(99, 1, 8, '2026-09-23', 'dinner', 'confirmed', 140.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(100, 1, 1, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(101, 1, 2, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(102, 1, 4, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(103, 1, 5, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(104, 1, 6, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(105, 1, 7, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(106, 1, 8, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(107, 1, 9, '2026-09-24', 'lunch', 'confirmed', 120.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(108, 1, 2, '2026-09-24', 'dinner', 'confirmed', 140.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(109, 1, 5, '2026-09-24', 'dinner', 'confirmed', 140.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(110, 1, 6, '2026-09-24', 'dinner', 'confirmed', 140.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(111, 1, 8, '2026-09-24', 'dinner', 'confirmed', 140.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(112, 1, 9, '2026-09-24', 'dinner', 'confirmed', 140.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(113, 1, 1, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(114, 1, 2, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(115, 1, 3, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(116, 1, 4, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(117, 1, 6, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(118, 1, 7, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(119, 1, 8, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(120, 1, 9, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(121, 1, 2, '2026-09-26', 'dinner', 'confirmed', 140.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(122, 1, 3, '2026-09-26', 'dinner', 'confirmed', 140.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(123, 1, 6, '2026-09-26', 'dinner', 'confirmed', 140.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(124, 1, 8, '2026-09-26', 'dinner', 'confirmed', 140.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(125, 1, 9, '2026-09-26', 'dinner', 'confirmed', 140.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(126, 1, 1, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(127, 1, 2, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(128, 1, 3, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(129, 1, 4, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(130, 1, 5, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(131, 1, 7, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(132, 1, 8, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(133, 1, 9, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(134, 1, 2, '2026-09-27', 'dinner', 'confirmed', 140.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(135, 1, 3, '2026-09-27', 'dinner', 'confirmed', 140.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(136, 1, 5, '2026-09-27', 'dinner', 'confirmed', 140.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(137, 1, 8, '2026-09-27', 'dinner', 'confirmed', 140.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(138, 1, 9, '2026-09-27', 'dinner', 'confirmed', 140.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(139, 1, 1, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(140, 1, 2, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(141, 1, 3, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(142, 1, 4, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(143, 1, 5, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(144, 1, 6, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(145, 1, 8, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(146, 1, 9, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(147, 1, 2, '2026-09-28', 'dinner', 'confirmed', 140.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(148, 1, 3, '2026-09-28', 'dinner', 'confirmed', 140.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(149, 1, 5, '2026-09-28', 'dinner', 'confirmed', 140.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(150, 1, 6, '2026-09-28', 'dinner', 'confirmed', 140.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(151, 1, 8, '2026-09-28', 'dinner', 'confirmed', 140.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(152, 1, 9, '2026-09-28', 'dinner', 'confirmed', 140.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(153, 1, 2, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(154, 1, 3, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(155, 1, 4, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(156, 1, 5, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(157, 1, 6, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(158, 1, 7, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(159, 1, 9, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(160, 1, 2, '2026-09-29', 'dinner', 'confirmed', 140.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(161, 1, 3, '2026-09-29', 'dinner', 'confirmed', 140.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(162, 1, 5, '2026-09-29', 'dinner', 'confirmed', 140.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(163, 1, 6, '2026-09-29', 'dinner', 'confirmed', 140.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(164, 1, 9, '2026-09-29', 'dinner', 'confirmed', 140.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(165, 1, 1, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(166, 1, 3, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(167, 1, 4, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(168, 1, 5, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(169, 1, 6, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(170, 1, 7, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(171, 1, 8, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(172, 1, 3, '2026-09-30', 'dinner', 'confirmed', 140.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(173, 1, 5, '2026-09-30', 'dinner', 'confirmed', 140.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(174, 1, 6, '2026-09-30', 'dinner', 'confirmed', 140.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(175, 1, 8, '2026-09-30', 'dinner', 'confirmed', 140.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(176, 1, 1, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(177, 1, 2, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(178, 1, 4, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(179, 1, 5, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(180, 1, 6, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(181, 1, 7, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(182, 1, 8, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(183, 1, 9, '2026-10-01', 'lunch', 'confirmed', 120.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(184, 1, 2, '2026-10-01', 'dinner', 'confirmed', 140.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(185, 1, 5, '2026-10-01', 'dinner', 'confirmed', 140.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(186, 1, 6, '2026-10-01', 'dinner', 'confirmed', 140.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(187, 1, 8, '2026-10-01', 'dinner', 'confirmed', 140.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(188, 1, 9, '2026-10-01', 'dinner', 'confirmed', 140.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(189, 1, 1, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(190, 1, 2, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(191, 1, 3, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(192, 1, 4, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(193, 1, 6, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(194, 1, 7, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(195, 1, 8, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(196, 1, 9, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(197, 1, 2, '2026-10-03', 'dinner', 'confirmed', 140.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(198, 1, 3, '2026-10-03', 'dinner', 'confirmed', 140.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(199, 1, 6, '2026-10-03', 'dinner', 'confirmed', 140.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(200, 1, 8, '2026-10-03', 'dinner', 'confirmed', 140.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(201, 1, 9, '2026-10-03', 'dinner', 'confirmed', 140.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(202, 1, 1, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(203, 1, 2, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(204, 1, 3, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(205, 1, 4, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(206, 1, 5, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(207, 1, 7, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(208, 1, 8, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(209, 1, 9, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(210, 1, 2, '2026-10-04', 'dinner', 'confirmed', 140.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(211, 1, 3, '2026-10-04', 'dinner', 'confirmed', 140.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(212, 1, 5, '2026-10-04', 'dinner', 'confirmed', 140.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(213, 1, 8, '2026-10-04', 'dinner', 'confirmed', 140.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(214, 1, 9, '2026-10-04', 'dinner', 'confirmed', 140.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(215, 1, 1, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(216, 1, 2, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(217, 1, 3, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(218, 1, 4, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(219, 1, 5, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(220, 1, 6, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(221, 1, 8, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(222, 1, 9, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(223, 1, 2, '2026-10-05', 'dinner', 'confirmed', 140.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(224, 1, 3, '2026-10-05', 'dinner', 'confirmed', 140.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(225, 1, 5, '2026-10-05', 'dinner', 'confirmed', 140.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(226, 1, 6, '2026-10-05', 'dinner', 'confirmed', 140.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(227, 1, 8, '2026-10-05', 'dinner', 'confirmed', 140.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(228, 1, 9, '2026-10-05', 'dinner', 'confirmed', 140.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(229, 2, 12, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(230, 2, 13, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(231, 2, 14, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(232, 2, 15, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(233, 2, 16, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(234, 2, 17, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(235, 2, 19, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(236, 2, 20, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(237, 2, 21, '2026-09-15', 'lunch', 'confirmed', 130.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(238, 2, 11, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(239, 2, 13, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(240, 2, 14, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(241, 2, 15, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(242, 2, 16, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(243, 2, 17, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(244, 2, 18, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(245, 2, 20, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(246, 2, 21, '2026-09-16', 'lunch', 'confirmed', 130.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(247, 2, 11, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(248, 2, 12, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(249, 2, 14, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(250, 2, 15, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(251, 2, 16, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(252, 2, 17, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(253, 2, 18, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(254, 2, 19, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(255, 2, 21, '2026-09-17', 'lunch', 'confirmed', 130.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(256, 2, 11, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(257, 2, 12, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(258, 2, 13, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(259, 2, 14, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(260, 2, 16, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(261, 2, 17, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(262, 2, 18, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(263, 2, 19, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(264, 2, 20, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(265, 2, 21, '2026-09-19', 'lunch', 'confirmed', 130.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(266, 2, 11, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(267, 2, 12, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(268, 2, 13, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(269, 2, 14, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(270, 2, 15, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(271, 2, 17, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(272, 2, 18, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(273, 2, 19, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(274, 2, 20, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(275, 2, 21, '2026-09-20', 'lunch', 'confirmed', 130.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(276, 2, 11, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(277, 2, 12, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(278, 2, 13, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(279, 2, 14, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(280, 2, 15, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(281, 2, 16, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(282, 2, 18, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(283, 2, 19, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(284, 2, 20, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(285, 2, 21, '2026-09-21', 'lunch', 'confirmed', 130.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(286, 2, 12, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(287, 2, 13, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(288, 2, 14, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(289, 2, 15, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(290, 2, 16, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(291, 2, 17, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(292, 2, 19, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(293, 2, 20, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(294, 2, 21, '2026-09-22', 'lunch', 'confirmed', 130.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(295, 2, 11, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(296, 2, 13, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(297, 2, 14, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(298, 2, 15, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(299, 2, 16, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(300, 2, 17, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(301, 2, 18, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(302, 2, 20, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(303, 2, 21, '2026-09-23', 'lunch', 'confirmed', 130.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(304, 2, 11, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(305, 2, 12, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(306, 2, 14, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(307, 2, 15, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(308, 2, 16, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(309, 2, 17, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(310, 2, 18, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(311, 2, 19, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(312, 2, 21, '2026-09-24', 'lunch', 'confirmed', 130.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(313, 2, 11, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(314, 2, 12, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(315, 2, 13, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(316, 2, 14, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(317, 2, 16, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(318, 2, 17, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(319, 2, 18, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(320, 2, 19, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(321, 2, 20, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(322, 2, 21, '2026-09-26', 'lunch', 'confirmed', 130.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(323, 2, 11, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(324, 2, 12, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(325, 2, 13, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(326, 2, 14, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(327, 2, 15, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(328, 2, 17, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(329, 2, 18, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(330, 2, 19, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(331, 2, 20, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(332, 2, 21, '2026-09-27', 'lunch', 'confirmed', 130.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(333, 2, 11, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(334, 2, 12, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(335, 2, 13, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(336, 2, 14, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(337, 2, 15, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(338, 2, 16, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(339, 2, 18, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(340, 2, 19, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(341, 2, 20, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(342, 2, 21, '2026-09-28', 'lunch', 'confirmed', 130.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(343, 2, 12, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(344, 2, 13, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(345, 2, 14, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(346, 2, 15, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(347, 2, 16, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(348, 2, 17, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(349, 2, 19, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(350, 2, 20, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(351, 2, 21, '2026-09-29', 'lunch', 'confirmed', 130.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(352, 2, 11, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(353, 2, 13, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(354, 2, 14, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(355, 2, 15, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(356, 2, 16, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(357, 2, 17, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(358, 2, 18, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(359, 2, 20, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(360, 2, 21, '2026-09-30', 'lunch', 'confirmed', 130.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(361, 2, 11, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(362, 2, 12, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(363, 2, 14, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(364, 2, 15, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(365, 2, 16, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(366, 2, 17, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(367, 2, 18, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(368, 2, 19, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(369, 2, 21, '2026-10-01', 'lunch', 'confirmed', 130.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(370, 2, 11, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(371, 2, 12, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(372, 2, 13, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(373, 2, 14, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00');
INSERT INTO `meal_entries` (`id`, `client_id`, `employee_id`, `meal_date`, `meal_type`, `status`, `unit_price`, `confirmed_by`, `confirmed_at`, `updated_by`, `created_at`, `updated_at`) VALUES
(374, 2, 16, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(375, 2, 17, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(376, 2, 18, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(377, 2, 19, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(378, 2, 20, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(379, 2, 21, '2026-10-03', 'lunch', 'confirmed', 130.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(380, 2, 11, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(381, 2, 12, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(382, 2, 13, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(383, 2, 14, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(384, 2, 15, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(385, 2, 17, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(386, 2, 18, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(387, 2, 19, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(388, 2, 20, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(389, 2, 21, '2026-10-04', 'lunch', 'confirmed', 130.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(390, 2, 11, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-06 10:14:07', 1, '2026-10-05 03:30:00', '2026-10-06 10:14:07'),
(391, 2, 12, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(392, 2, 13, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(393, 2, 14, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(394, 2, 15, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(395, 2, 16, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(396, 2, 18, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(397, 2, 19, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(398, 2, 20, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(399, 2, 21, '2026-10-05', 'lunch', 'confirmed', 130.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(400, 3, 24, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(401, 3, 25, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(402, 3, 26, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(403, 3, 27, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(404, 3, 28, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(405, 3, 29, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(406, 3, 31, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(407, 3, 32, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(408, 3, 33, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(409, 3, 34, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(410, 3, 35, '2026-09-15', 'lunch', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(411, 3, 24, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(412, 3, 25, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(413, 3, 27, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(414, 3, 28, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(415, 3, 31, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(416, 3, 33, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(417, 3, 34, '2026-09-15', 'dinner', 'confirmed', 150.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(418, 3, 23, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(419, 3, 25, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(420, 3, 26, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(421, 3, 27, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(422, 3, 28, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(423, 3, 29, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(424, 3, 30, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(425, 3, 32, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(426, 3, 33, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(427, 3, 34, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(428, 3, 35, '2026-09-16', 'lunch', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(429, 3, 25, '2026-09-16', 'dinner', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(430, 3, 27, '2026-09-16', 'dinner', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(431, 3, 28, '2026-09-16', 'dinner', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(432, 3, 30, '2026-09-16', 'dinner', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(433, 3, 33, '2026-09-16', 'dinner', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(434, 3, 34, '2026-09-16', 'dinner', 'confirmed', 150.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(435, 3, 23, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(436, 3, 24, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(437, 3, 26, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(438, 3, 27, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(439, 3, 28, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(440, 3, 29, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(441, 3, 30, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(442, 3, 31, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(443, 3, 33, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(444, 3, 34, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(445, 3, 35, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(446, 3, 24, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(447, 3, 27, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(448, 3, 28, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(449, 3, 30, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(450, 3, 31, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(451, 3, 33, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(452, 3, 34, '2026-09-17', 'dinner', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(453, 3, 23, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(454, 3, 24, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(455, 3, 25, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(456, 3, 26, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(457, 3, 28, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(458, 3, 29, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(459, 3, 30, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(460, 3, 31, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(461, 3, 32, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(462, 3, 33, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(463, 3, 35, '2026-09-19', 'lunch', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(464, 3, 24, '2026-09-19', 'dinner', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(465, 3, 25, '2026-09-19', 'dinner', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(466, 3, 28, '2026-09-19', 'dinner', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(467, 3, 30, '2026-09-19', 'dinner', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(468, 3, 31, '2026-09-19', 'dinner', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(469, 3, 33, '2026-09-19', 'dinner', 'confirmed', 150.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(470, 3, 23, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(471, 3, 24, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(472, 3, 25, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(473, 3, 26, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(474, 3, 27, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(475, 3, 29, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(476, 3, 30, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(477, 3, 31, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(478, 3, 32, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(479, 3, 33, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(480, 3, 34, '2026-09-20', 'lunch', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(481, 3, 24, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(482, 3, 25, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(483, 3, 27, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(484, 3, 30, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(485, 3, 31, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(486, 3, 33, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(487, 3, 34, '2026-09-20', 'dinner', 'confirmed', 150.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(488, 3, 23, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(489, 3, 24, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(490, 3, 25, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(491, 3, 26, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(492, 3, 27, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(493, 3, 28, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(494, 3, 30, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(495, 3, 31, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(496, 3, 32, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(497, 3, 33, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(498, 3, 34, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(499, 3, 35, '2026-09-21', 'lunch', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(500, 3, 24, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(501, 3, 25, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(502, 3, 27, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(503, 3, 28, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(504, 3, 30, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(505, 3, 31, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(506, 3, 33, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(507, 3, 34, '2026-09-21', 'dinner', 'confirmed', 150.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(508, 3, 24, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(509, 3, 25, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(510, 3, 26, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(511, 3, 27, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(512, 3, 28, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(513, 3, 29, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(514, 3, 31, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(515, 3, 32, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(516, 3, 33, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(517, 3, 34, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(518, 3, 35, '2026-09-22', 'lunch', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(519, 3, 24, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(520, 3, 25, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(521, 3, 27, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(522, 3, 28, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(523, 3, 31, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(524, 3, 33, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(525, 3, 34, '2026-09-22', 'dinner', 'confirmed', 150.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(526, 3, 23, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(527, 3, 25, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(528, 3, 26, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(529, 3, 27, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(530, 3, 28, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(531, 3, 29, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(532, 3, 30, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(533, 3, 32, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(534, 3, 33, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(535, 3, 34, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(536, 3, 35, '2026-09-23', 'lunch', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(537, 3, 25, '2026-09-23', 'dinner', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(538, 3, 27, '2026-09-23', 'dinner', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(539, 3, 28, '2026-09-23', 'dinner', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(540, 3, 30, '2026-09-23', 'dinner', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(541, 3, 33, '2026-09-23', 'dinner', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(542, 3, 34, '2026-09-23', 'dinner', 'confirmed', 150.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(543, 3, 23, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(544, 3, 24, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(545, 3, 26, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(546, 3, 27, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(547, 3, 28, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(548, 3, 29, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(549, 3, 30, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(550, 3, 31, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(551, 3, 33, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(552, 3, 34, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(553, 3, 35, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(554, 3, 24, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(555, 3, 27, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(556, 3, 28, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(557, 3, 30, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(558, 3, 31, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(559, 3, 33, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(560, 3, 34, '2026-09-24', 'dinner', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(561, 3, 23, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(562, 3, 24, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(563, 3, 25, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(564, 3, 26, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(565, 3, 28, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(566, 3, 29, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(567, 3, 30, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(568, 3, 31, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(569, 3, 32, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(570, 3, 33, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(571, 3, 35, '2026-09-26', 'lunch', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(572, 3, 24, '2026-09-26', 'dinner', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(573, 3, 25, '2026-09-26', 'dinner', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(574, 3, 28, '2026-09-26', 'dinner', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(575, 3, 30, '2026-09-26', 'dinner', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(576, 3, 31, '2026-09-26', 'dinner', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(577, 3, 33, '2026-09-26', 'dinner', 'confirmed', 150.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(578, 3, 23, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(579, 3, 24, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(580, 3, 25, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(581, 3, 26, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(582, 3, 27, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(583, 3, 29, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(584, 3, 30, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(585, 3, 31, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(586, 3, 32, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(587, 3, 33, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(588, 3, 34, '2026-09-27', 'lunch', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(589, 3, 24, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(590, 3, 25, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(591, 3, 27, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(592, 3, 30, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(593, 3, 31, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(594, 3, 33, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(595, 3, 34, '2026-09-27', 'dinner', 'confirmed', 150.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(596, 3, 23, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(597, 3, 24, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(598, 3, 25, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(599, 3, 26, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(600, 3, 27, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(601, 3, 28, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(602, 3, 30, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(603, 3, 31, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(604, 3, 32, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(605, 3, 33, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(606, 3, 34, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(607, 3, 35, '2026-09-28', 'lunch', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(608, 3, 24, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(609, 3, 25, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(610, 3, 27, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(611, 3, 28, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(612, 3, 30, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(613, 3, 31, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(614, 3, 33, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(615, 3, 34, '2026-09-28', 'dinner', 'confirmed', 150.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(616, 3, 24, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(617, 3, 25, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(618, 3, 26, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(619, 3, 27, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(620, 3, 28, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(621, 3, 29, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(622, 3, 31, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(623, 3, 32, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(624, 3, 33, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(625, 3, 34, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(626, 3, 35, '2026-09-29', 'lunch', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(627, 3, 24, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(628, 3, 25, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(629, 3, 27, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(630, 3, 28, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(631, 3, 31, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(632, 3, 33, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(633, 3, 34, '2026-09-29', 'dinner', 'confirmed', 150.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(634, 3, 23, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(635, 3, 25, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(636, 3, 26, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(637, 3, 27, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(638, 3, 28, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(639, 3, 29, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(640, 3, 30, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(641, 3, 32, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(642, 3, 33, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(643, 3, 34, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(644, 3, 35, '2026-09-30', 'lunch', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(645, 3, 25, '2026-09-30', 'dinner', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(646, 3, 27, '2026-09-30', 'dinner', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(647, 3, 28, '2026-09-30', 'dinner', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(648, 3, 30, '2026-09-30', 'dinner', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(649, 3, 33, '2026-09-30', 'dinner', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(650, 3, 34, '2026-09-30', 'dinner', 'confirmed', 150.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(651, 3, 23, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(652, 3, 24, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(653, 3, 26, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(654, 3, 27, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(655, 3, 28, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(656, 3, 29, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(657, 3, 30, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(658, 3, 31, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(659, 3, 33, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(660, 3, 34, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(661, 3, 35, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(662, 3, 24, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(663, 3, 27, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(664, 3, 28, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(665, 3, 30, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(666, 3, 31, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(667, 3, 33, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(668, 3, 34, '2026-10-01', 'dinner', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(669, 3, 23, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(670, 3, 24, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(671, 3, 25, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(672, 3, 26, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(673, 3, 28, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(674, 3, 29, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(675, 3, 30, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(676, 3, 31, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(677, 3, 32, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(678, 3, 33, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(679, 3, 35, '2026-10-03', 'lunch', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(680, 3, 24, '2026-10-03', 'dinner', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(681, 3, 25, '2026-10-03', 'dinner', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(682, 3, 28, '2026-10-03', 'dinner', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(683, 3, 30, '2026-10-03', 'dinner', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(684, 3, 31, '2026-10-03', 'dinner', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(685, 3, 33, '2026-10-03', 'dinner', 'confirmed', 150.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(686, 3, 23, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(687, 3, 24, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(688, 3, 25, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(689, 3, 26, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(690, 3, 27, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(691, 3, 29, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(692, 3, 30, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(693, 3, 31, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(694, 3, 32, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(695, 3, 33, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(696, 3, 34, '2026-10-04', 'lunch', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(697, 3, 24, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(698, 3, 25, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(699, 3, 27, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(700, 3, 30, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(701, 3, 31, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(702, 3, 33, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(703, 3, 34, '2026-10-04', 'dinner', 'confirmed', 150.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(704, 3, 23, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(705, 3, 24, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(706, 3, 25, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(707, 3, 26, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(708, 3, 27, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(709, 3, 28, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(710, 3, 30, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(711, 3, 31, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(712, 3, 32, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(713, 3, 33, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(714, 3, 34, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(715, 3, 35, '2026-10-05', 'lunch', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(716, 3, 24, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(717, 3, 25, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(718, 3, 27, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(719, 3, 28, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(720, 3, 30, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(721, 3, 31, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(722, 3, 33, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(723, 3, 34, '2026-10-05', 'dinner', 'confirmed', 150.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(724, 4, 38, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(725, 4, 39, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(726, 4, 40, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(727, 4, 41, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(728, 4, 42, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(729, 4, 43, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(730, 4, 45, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(731, 4, 46, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(732, 4, 47, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(733, 4, 48, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(734, 4, 49, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(735, 4, 50, '2026-09-15', 'lunch', 'confirmed', 120.00, 1, '2026-09-15 03:30:00', NULL, '2026-09-15 03:30:00', '2026-09-15 03:30:00'),
(736, 4, 37, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(737, 4, 39, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(738, 4, 40, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(739, 4, 41, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(740, 4, 42, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(741, 4, 43, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(742, 4, 44, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(743, 4, 46, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00');
INSERT INTO `meal_entries` (`id`, `client_id`, `employee_id`, `meal_date`, `meal_type`, `status`, `unit_price`, `confirmed_by`, `confirmed_at`, `updated_by`, `created_at`, `updated_at`) VALUES
(744, 4, 47, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(745, 4, 48, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(746, 4, 49, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(747, 4, 50, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(748, 4, 51, '2026-09-16', 'lunch', 'confirmed', 120.00, 1, '2026-09-16 03:30:00', NULL, '2026-09-16 03:30:00', '2026-09-16 03:30:00'),
(749, 4, 37, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(750, 4, 38, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(751, 4, 40, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(752, 4, 41, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(753, 4, 42, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(754, 4, 43, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(755, 4, 44, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(756, 4, 45, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(757, 4, 47, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(758, 4, 48, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(759, 4, 49, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(760, 4, 50, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(761, 4, 51, '2026-09-17', 'lunch', 'confirmed', 150.00, 1, '2026-09-17 03:30:00', NULL, '2026-09-17 03:30:00', '2026-09-17 03:30:00'),
(762, 4, 37, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(763, 4, 38, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(764, 4, 39, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(765, 4, 40, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(766, 4, 42, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(767, 4, 43, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(768, 4, 44, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(769, 4, 45, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(770, 4, 46, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(771, 4, 47, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(772, 4, 49, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(773, 4, 50, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(774, 4, 51, '2026-09-19', 'lunch', 'confirmed', 120.00, 1, '2026-09-19 03:30:00', NULL, '2026-09-19 03:30:00', '2026-09-19 03:30:00'),
(775, 4, 37, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(776, 4, 38, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(777, 4, 39, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(778, 4, 40, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(779, 4, 41, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(780, 4, 43, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(781, 4, 44, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(782, 4, 45, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(783, 4, 46, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(784, 4, 47, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(785, 4, 48, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(786, 4, 50, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(787, 4, 51, '2026-09-20', 'lunch', 'confirmed', 120.00, 1, '2026-09-20 03:30:00', NULL, '2026-09-20 03:30:00', '2026-09-20 03:30:00'),
(788, 4, 37, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(789, 4, 38, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(790, 4, 39, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(791, 4, 40, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(792, 4, 41, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(793, 4, 42, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(794, 4, 44, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(795, 4, 45, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(796, 4, 46, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(797, 4, 47, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(798, 4, 48, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(799, 4, 49, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(800, 4, 51, '2026-09-21', 'lunch', 'confirmed', 120.00, 1, '2026-09-21 03:30:00', NULL, '2026-09-21 03:30:00', '2026-09-21 03:30:00'),
(801, 4, 38, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(802, 4, 39, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(803, 4, 40, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(804, 4, 41, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(805, 4, 42, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(806, 4, 43, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(807, 4, 45, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(808, 4, 46, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(809, 4, 47, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(810, 4, 48, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(811, 4, 49, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(812, 4, 50, '2026-09-22', 'lunch', 'confirmed', 120.00, 1, '2026-09-22 03:30:00', NULL, '2026-09-22 03:30:00', '2026-09-22 03:30:00'),
(813, 4, 37, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(814, 4, 39, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(815, 4, 40, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(816, 4, 41, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(817, 4, 42, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(818, 4, 43, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(819, 4, 44, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(820, 4, 46, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(821, 4, 47, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(822, 4, 48, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(823, 4, 49, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(824, 4, 50, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(825, 4, 51, '2026-09-23', 'lunch', 'confirmed', 120.00, 1, '2026-09-23 03:30:00', NULL, '2026-09-23 03:30:00', '2026-09-23 03:30:00'),
(826, 4, 37, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(827, 4, 38, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(828, 4, 40, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(829, 4, 41, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(830, 4, 42, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(831, 4, 43, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(832, 4, 44, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(833, 4, 45, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(834, 4, 47, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(835, 4, 48, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(836, 4, 49, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(837, 4, 50, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(838, 4, 51, '2026-09-24', 'lunch', 'confirmed', 150.00, 1, '2026-09-24 03:30:00', NULL, '2026-09-24 03:30:00', '2026-09-24 03:30:00'),
(839, 4, 37, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(840, 4, 38, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(841, 4, 39, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(842, 4, 40, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(843, 4, 42, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(844, 4, 43, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(845, 4, 44, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(846, 4, 45, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(847, 4, 46, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(848, 4, 47, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(849, 4, 49, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(850, 4, 50, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(851, 4, 51, '2026-09-26', 'lunch', 'confirmed', 120.00, 1, '2026-09-26 03:30:00', NULL, '2026-09-26 03:30:00', '2026-09-26 03:30:00'),
(852, 4, 37, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(853, 4, 38, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(854, 4, 39, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(855, 4, 40, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(856, 4, 41, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(857, 4, 43, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(858, 4, 44, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(859, 4, 45, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(860, 4, 46, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(861, 4, 47, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(862, 4, 48, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(863, 4, 50, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(864, 4, 51, '2026-09-27', 'lunch', 'confirmed', 120.00, 1, '2026-09-27 03:30:00', NULL, '2026-09-27 03:30:00', '2026-09-27 03:30:00'),
(865, 4, 37, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(866, 4, 38, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(867, 4, 39, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(868, 4, 40, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(869, 4, 41, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(870, 4, 42, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(871, 4, 44, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(872, 4, 45, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(873, 4, 46, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(874, 4, 47, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(875, 4, 48, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(876, 4, 49, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(877, 4, 51, '2026-09-28', 'lunch', 'confirmed', 120.00, 1, '2026-09-28 03:30:00', NULL, '2026-09-28 03:30:00', '2026-09-28 03:30:00'),
(878, 4, 38, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(879, 4, 39, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(880, 4, 40, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(881, 4, 41, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(882, 4, 42, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(883, 4, 43, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(884, 4, 45, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(885, 4, 46, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(886, 4, 47, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(887, 4, 48, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(888, 4, 49, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(889, 4, 50, '2026-09-29', 'lunch', 'confirmed', 120.00, 1, '2026-09-29 03:30:00', NULL, '2026-09-29 03:30:00', '2026-09-29 03:30:00'),
(890, 4, 37, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(891, 4, 39, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(892, 4, 40, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(893, 4, 41, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(894, 4, 42, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(895, 4, 43, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(896, 4, 44, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(897, 4, 46, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(898, 4, 47, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(899, 4, 48, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(900, 4, 49, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(901, 4, 50, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(902, 4, 51, '2026-09-30', 'lunch', 'confirmed', 120.00, 1, '2026-09-30 03:30:00', NULL, '2026-09-30 03:30:00', '2026-09-30 03:30:00'),
(903, 4, 37, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(904, 4, 38, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(905, 4, 40, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(906, 4, 41, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(907, 4, 42, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(908, 4, 43, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(909, 4, 44, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(910, 4, 45, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(911, 4, 47, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(912, 4, 48, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(913, 4, 49, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(914, 4, 50, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(915, 4, 51, '2026-10-01', 'lunch', 'confirmed', 150.00, 1, '2026-10-01 03:30:00', NULL, '2026-10-01 03:30:00', '2026-10-01 03:30:00'),
(916, 4, 37, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(917, 4, 38, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(918, 4, 39, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(919, 4, 40, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(920, 4, 42, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(921, 4, 43, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(922, 4, 44, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(923, 4, 45, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(924, 4, 46, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(925, 4, 47, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(926, 4, 49, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(927, 4, 50, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(928, 4, 51, '2026-10-03', 'lunch', 'confirmed', 120.00, 1, '2026-10-03 03:30:00', NULL, '2026-10-03 03:30:00', '2026-10-03 03:30:00'),
(929, 4, 37, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(930, 4, 38, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(931, 4, 39, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(932, 4, 40, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(933, 4, 41, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(934, 4, 43, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(935, 4, 44, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(936, 4, 45, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(937, 4, 46, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(938, 4, 47, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(939, 4, 48, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(940, 4, 50, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(941, 4, 51, '2026-10-04', 'lunch', 'confirmed', 120.00, 1, '2026-10-04 03:30:00', NULL, '2026-10-04 03:30:00', '2026-10-04 03:30:00'),
(942, 4, 37, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(943, 4, 38, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(944, 4, 39, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(945, 4, 40, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(946, 4, 41, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(947, 4, 42, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(948, 4, 44, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(949, 4, 45, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(950, 4, 46, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(951, 4, 47, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(952, 4, 48, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(953, 4, 49, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(954, 4, 51, '2026-10-05', 'lunch', 'confirmed', 120.00, 1, '2026-10-05 03:30:00', NULL, '2026-10-05 03:30:00', '2026-10-05 03:30:00'),
(955, 1, 6, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(956, 1, 4, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(957, 1, 3, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(958, 1, 5, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(959, 1, 2, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(960, 1, 9, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(961, 1, 8, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(962, 1, 7, '2026-10-07', 'lunch', 'confirmed', 120.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(963, 1, 6, '2026-10-07', 'dinner', 'confirmed', 140.00, 5, '2026-10-06 10:08:58', 5, '2026-10-06 10:08:58', '2026-10-06 10:08:58'),
(964, 2, 11, '2026-10-07', 'lunch', 'confirmed', 130.00, 1, '2026-10-07 07:36:27', 1, '2026-10-07 07:36:27', '2026-10-07 07:36:27'),
(965, 2, 16, '2026-10-07', 'lunch', 'confirmed', 130.00, 1, '2026-10-07 07:36:27', 1, '2026-10-07 07:36:27', '2026-10-07 07:36:27'),
(966, 2, 14, '2026-10-07', 'lunch', 'confirmed', 130.00, 1, '2026-10-07 07:36:27', 1, '2026-10-07 07:36:27', '2026-10-07 07:36:27'),
(967, 1, 3, '2026-10-07', 'dinner', 'confirmed', 140.00, 5, '2026-10-07 07:49:54', 5, '2026-10-07 07:49:54', '2026-10-07 07:49:54'),
(968, 1, 5, '2026-10-07', 'dinner', 'confirmed', 140.00, 5, '2026-10-07 07:49:54', 5, '2026-10-07 07:49:54', '2026-10-07 07:49:54'),
(969, 1, 9, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:12', 5, '2026-10-07 07:51:12', '2026-10-07 07:51:12'),
(970, 1, 8, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:12', 5, '2026-10-07 07:51:12', '2026-10-07 07:51:12'),
(971, 1, 7, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:12', 5, '2026-10-07 07:51:12', '2026-10-07 07:51:12'),
(972, 1, 2, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:39', 5, '2026-10-07 07:51:39', '2026-10-07 07:51:39'),
(973, 1, 5, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:39', 5, '2026-10-07 07:51:39', '2026-10-07 07:51:39'),
(974, 1, 3, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:39', 5, '2026-10-07 07:51:39', '2026-10-07 07:51:39'),
(975, 1, 4, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:39', 5, '2026-10-07 07:51:39', '2026-10-07 07:51:39'),
(976, 1, 6, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:39', 5, '2026-10-07 07:51:39', '2026-10-07 07:51:39'),
(977, 1, 1, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:51:39', 5, '2026-10-07 07:51:39', '2026-10-07 07:51:39'),
(978, 1, 10, '2026-10-08', 'lunch', 'confirmed', 120.00, 5, '2026-10-07 07:52:38', 5, '2026-10-07 07:52:38', '2026-10-07 07:52:38'),
(999, 1, 6, '2026-10-08', 'dinner', 'confirmed', 140.00, 5, '2026-10-07 08:54:35', 5, '2026-10-07 08:54:35', '2026-10-07 08:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(30) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `default_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `category`, `description`, `default_price`, `status`, `image_path`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Plain Rice', 'main_course', 'Steamed white rice', 30.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(2, 'Polao', 'main_course', 'Fragrant rice with ghee', 45.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(3, 'Chicken Biryani', 'main_course', 'Kacchi-style chicken biryani', 120.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(4, 'Chicken Curry', 'protein', 'Home-style chicken curry', 70.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(5, 'Beef Bhuna', 'protein', NULL, 90.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(6, 'Rui Fish Curry', 'protein', NULL, 80.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(7, 'Egg Curry', 'protein', NULL, 35.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(8, 'Mixed Vegetable', 'side_dish', 'Seasonal vegetables', 25.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(9, 'Dal', 'side_dish', 'Yellow lentil soup', 15.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(10, 'Salad', 'side_dish', NULL, 10.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(11, 'Borhani', 'beverage', 'Spiced yoghurt drink', 30.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42'),
(12, 'Firni', 'dessert', 'Rice pudding', 35.00, 'active', NULL, NULL, '2026-10-06 09:52:42', '2026-10-06 09:52:42');

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
(4, '2026_10_06_000001_create_vendor_profiles_table', 1),
(5, '2026_10_06_000002_create_clients_table', 2),
(6, '2026_10_06_000003_add_client_id_to_users_table', 2),
(7, '2026_10_06_000004_create_activity_logs_table', 2),
(8, '2026_10_06_000005_create_employees_table', 3),
(9, '2026_10_06_000006_create_settings_table', 4),
(10, '2026_10_06_000007_create_menu_tables', 4),
(11, '2026_10_06_000008_create_meal_entries_table', 5),
(12, '2026_10_06_000009_create_order_tables', 6),
(13, '2026_10_07_000010_create_billing_tables', 7),
(14, '2026_10_07_000011_create_notifications_table', 8);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('01a11592-5c5a-7048-ad05-6c367f2c781f', 'App\\Notifications\\Vendor\\ClientOrderPlaced', 'App\\Models\\User', 1, '{\"kind\":\"order_placed\",\"title\":\"New dinner order from XYZ Software Ltd.\",\"body\":\"1 meals for Thu, 08 Oct \\u00b7 ORD-261008-00115 \\u00b7 by Tanvir Ahmed\",\"url\":\"\\/orders\\/115\",\"order_id\":115,\"client_id\":1,\"client\":\"XYZ Software Ltd.\",\"meal_type\":\"dinner\",\"date\":\"2026-10-08\",\"quantity\":1,\"from_quantity\":null}', '2026-10-07 08:54:56', '2026-10-07 08:54:35', '2026-10-07 08:54:56'),
('01a11592-5c66-7321-91d0-b7b81808af31', 'App\\Notifications\\Vendor\\ClientOrderPlaced', 'App\\Models\\User', 2, '{\"kind\":\"order_placed\",\"title\":\"New dinner order from XYZ Software Ltd.\",\"body\":\"1 meals for Thu, 08 Oct \\u00b7 ORD-261008-00115 \\u00b7 by Tanvir Ahmed\",\"url\":\"\\/orders\\/115\",\"order_id\":115,\"client_id\":1,\"client\":\"XYZ Software Ltd.\",\"meal_type\":\"dinner\",\"date\":\"2026-10-08\",\"quantity\":1,\"from_quantity\":null}', NULL, '2026-10-07 08:54:35', '2026-10-07 08:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(30) DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `order_date` date NOT NULL,
  `meal_type` varchar(20) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `delivery_address` varchar(500) NOT NULL,
  `delivery_time` time DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `client_id`, `order_date`, `meal_type`, `quantity`, `amount`, `status`, `delivery_address`, `delivery_time`, `notes`, `cancel_reason`, `cancelled_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'ORD-260915-00001', 1, '2026-09-15', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(2, 'ORD-260915-00002', 1, '2026-09-15', 'lunch', 7, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(3, 'ORD-260916-00003', 1, '2026-09-16', 'dinner', 4, 560.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(4, 'ORD-260916-00004', 1, '2026-09-16', 'lunch', 7, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(5, 'ORD-260917-00005', 1, '2026-09-17', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(6, 'ORD-260917-00006', 1, '2026-09-17', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(7, 'ORD-260919-00007', 1, '2026-09-19', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(8, 'ORD-260919-00008', 1, '2026-09-19', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(9, 'ORD-260920-00009', 1, '2026-09-20', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(10, 'ORD-260920-00010', 1, '2026-09-20', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(11, 'ORD-260921-00011', 1, '2026-09-21', 'dinner', 6, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(12, 'ORD-260921-00012', 1, '2026-09-21', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(13, 'ORD-260922-00013', 1, '2026-09-22', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(14, 'ORD-260922-00014', 1, '2026-09-22', 'lunch', 7, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(15, 'ORD-260923-00015', 1, '2026-09-23', 'dinner', 4, 560.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(16, 'ORD-260923-00016', 1, '2026-09-23', 'lunch', 7, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(17, 'ORD-260924-00017', 1, '2026-09-24', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(18, 'ORD-260924-00018', 1, '2026-09-24', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(19, 'ORD-260926-00019', 1, '2026-09-26', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(20, 'ORD-260926-00020', 1, '2026-09-26', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(21, 'ORD-260927-00021', 1, '2026-09-27', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(22, 'ORD-260927-00022', 1, '2026-09-27', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(23, 'ORD-260928-00023', 1, '2026-09-28', 'dinner', 6, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(24, 'ORD-260928-00024', 1, '2026-09-28', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(25, 'ORD-260929-00025', 1, '2026-09-29', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(26, 'ORD-260929-00026', 1, '2026-09-29', 'lunch', 7, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(27, 'ORD-260930-00027', 1, '2026-09-30', 'dinner', 4, 560.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(28, 'ORD-260930-00028', 1, '2026-09-30', 'lunch', 7, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(29, 'ORD-261001-00029', 1, '2026-10-01', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(30, 'ORD-261001-00030', 1, '2026-10-01', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(31, 'ORD-261003-00031', 1, '2026-10-03', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(32, 'ORD-261003-00032', 1, '2026-10-03', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(33, 'ORD-261004-00033', 1, '2026-10-04', 'dinner', 5, 700.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(34, 'ORD-261004-00034', 1, '2026-10-04', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(35, 'ORD-261005-00035', 1, '2026-10-05', 'dinner', 6, 840.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(36, 'ORD-261005-00036', 1, '2026-10-05', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(37, 'ORD-261007-00037', 1, '2026-10-07', 'dinner', 3, 420.00, 'confirmed', '5th floor reception, House 10, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 07:49:54'),
(38, 'ORD-261007-00038', 1, '2026-10-07', 'lunch', 8, 960.00, 'delivered', '5th floor reception, House 10, Road 5, Gulshan', '12:45:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:19:47'),
(39, 'ORD-260915-00039', 2, '2026-09-15', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(40, 'ORD-260916-00040', 2, '2026-09-16', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(41, 'ORD-260917-00041', 2, '2026-09-17', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(42, 'ORD-260919-00042', 2, '2026-09-19', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(43, 'ORD-260920-00043', 2, '2026-09-20', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(44, 'ORD-260921-00044', 2, '2026-09-21', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(45, 'ORD-260922-00045', 2, '2026-09-22', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(46, 'ORD-260923-00046', 2, '2026-09-23', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(47, 'ORD-260924-00047', 2, '2026-09-24', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(48, 'ORD-260926-00048', 2, '2026-09-26', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(49, 'ORD-260927-00049', 2, '2026-09-27', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(50, 'ORD-260928-00050', 2, '2026-09-28', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(51, 'ORD-260929-00051', 2, '2026-09-29', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(52, 'ORD-260930-00052', 2, '2026-09-30', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(53, 'ORD-261001-00053', 2, '2026-10-01', 'lunch', 9, 1170.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(54, 'ORD-261003-00054', 2, '2026-10-03', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(55, 'ORD-261004-00055', 2, '2026-10-04', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(56, 'ORD-261005-00056', 2, '2026-10-05', 'lunch', 10, 1300.00, 'delivered', 'House 11, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(57, 'ORD-260915-00057', 3, '2026-09-15', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(58, 'ORD-260915-00058', 3, '2026-09-15', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(59, 'ORD-260916-00059', 3, '2026-09-16', 'dinner', 6, 900.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(60, 'ORD-260916-00060', 3, '2026-09-16', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(61, 'ORD-260917-00061', 3, '2026-09-17', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(62, 'ORD-260917-00062', 3, '2026-09-17', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(63, 'ORD-260919-00063', 3, '2026-09-19', 'dinner', 6, 900.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(64, 'ORD-260919-00064', 3, '2026-09-19', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(65, 'ORD-260920-00065', 3, '2026-09-20', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(66, 'ORD-260920-00066', 3, '2026-09-20', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(67, 'ORD-260921-00067', 3, '2026-09-21', 'dinner', 8, 1200.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(68, 'ORD-260921-00068', 3, '2026-09-21', 'lunch', 12, 1800.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(69, 'ORD-260922-00069', 3, '2026-09-22', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(70, 'ORD-260922-00070', 3, '2026-09-22', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(71, 'ORD-260923-00071', 3, '2026-09-23', 'dinner', 6, 900.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(72, 'ORD-260923-00072', 3, '2026-09-23', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(73, 'ORD-260924-00073', 3, '2026-09-24', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(74, 'ORD-260924-00074', 3, '2026-09-24', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(75, 'ORD-260926-00075', 3, '2026-09-26', 'dinner', 6, 900.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(76, 'ORD-260926-00076', 3, '2026-09-26', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(77, 'ORD-260927-00077', 3, '2026-09-27', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(78, 'ORD-260927-00078', 3, '2026-09-27', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(79, 'ORD-260928-00079', 3, '2026-09-28', 'dinner', 8, 1200.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(80, 'ORD-260928-00080', 3, '2026-09-28', 'lunch', 12, 1800.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(81, 'ORD-260929-00081', 3, '2026-09-29', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(82, 'ORD-260929-00082', 3, '2026-09-29', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(83, 'ORD-260930-00083', 3, '2026-09-30', 'dinner', 6, 900.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(84, 'ORD-260930-00084', 3, '2026-09-30', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(85, 'ORD-261001-00085', 3, '2026-10-01', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(86, 'ORD-261001-00086', 3, '2026-10-01', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(87, 'ORD-261003-00087', 3, '2026-10-03', 'dinner', 6, 900.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(88, 'ORD-261003-00088', 3, '2026-10-03', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(89, 'ORD-261004-00089', 3, '2026-10-04', 'dinner', 7, 1050.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(90, 'ORD-261004-00090', 3, '2026-10-04', 'lunch', 11, 1650.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(91, 'ORD-261005-00091', 3, '2026-10-05', 'dinner', 8, 1200.00, 'delivered', 'House 12, Road 5, Gulshan', '20:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(92, 'ORD-261005-00092', 3, '2026-10-05', 'lunch', 12, 1800.00, 'delivered', 'House 12, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(93, 'ORD-260915-00093', 4, '2026-09-15', 'lunch', 12, 1440.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(94, 'ORD-260916-00094', 4, '2026-09-16', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(95, 'ORD-260917-00095', 4, '2026-09-17', 'lunch', 13, 1950.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(96, 'ORD-260919-00096', 4, '2026-09-19', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(97, 'ORD-260920-00097', 4, '2026-09-20', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(98, 'ORD-260921-00098', 4, '2026-09-21', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(99, 'ORD-260922-00099', 4, '2026-09-22', 'lunch', 12, 1440.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(100, 'ORD-260923-00100', 4, '2026-09-23', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(101, 'ORD-260924-00101', 4, '2026-09-24', 'lunch', 13, 1950.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(102, 'ORD-260926-00102', 4, '2026-09-26', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(103, 'ORD-260927-00103', 4, '2026-09-27', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(104, 'ORD-260928-00104', 4, '2026-09-28', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(105, 'ORD-260929-00105', 4, '2026-09-29', 'lunch', 12, 1440.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(106, 'ORD-260930-00106', 4, '2026-09-30', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(107, 'ORD-261001-00107', 4, '2026-10-01', 'lunch', 13, 1950.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(108, 'ORD-261003-00108', 4, '2026-10-03', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(109, 'ORD-261004-00109', 4, '2026-10-04', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(110, 'ORD-261005-00110', 4, '2026-10-05', 'lunch', 13, 1560.00, 'delivered', 'House 13, Road 5, Gulshan', '13:00:00', NULL, NULL, NULL, 1, '2026-10-07 01:18:38', '2026-10-07 01:18:38'),
(111, 'ORD-261007-00111', 2, '2026-10-07', 'lunch', 3, 390.00, 'on_the_way', 'House 11, Road 5, Gulshan', NULL, NULL, NULL, NULL, 1, '2026-10-07 07:36:27', '2026-10-07 12:24:02'),
(112, 'ORD-261008-00112', 1, '2026-10-08', 'lunch', 10, 1200.00, 'ready', '5th floor reception, House 10, Road 5, Gulshan', NULL, NULL, NULL, NULL, 5, '2026-10-07 07:51:12', '2026-10-07 07:55:03'),
(115, 'ORD-261008-00115', 1, '2026-10-08', 'dinner', 1, 140.00, 'ready', '5th floor reception, House 10, Road 5, Gulshan', NULL, NULL, NULL, NULL, 5, '2026-10-07 08:54:35', '2026-10-07 12:22:16');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `sort_order` smallint(5) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `name`, `quantity`, `sort_order`) VALUES
(1, 111, 1, 'Plain Rice', 3, 0),
(2, 111, 6, 'Rui Fish Curry', 3, 1),
(3, 111, 9, 'Dal', 3, 2),
(4, 111, 8, 'Mixed Vegetable', 3, 3),
(5, 37, 1, 'Plain Rice', 3, 0),
(6, 37, 7, 'Egg Curry', 3, 1),
(7, 37, 8, 'Mixed Vegetable', 3, 2),
(8, 37, 9, 'Dal', 3, 3),
(17, 112, 2, 'Polao', 10, 0),
(18, 112, 5, 'Beef Bhuna', 10, 1),
(19, 112, 10, 'Salad', 10, 2),
(20, 112, 12, 'Firni', 10, 3),
(21, 115, 1, 'Plain Rice', 1, 0),
(22, 115, 4, 'Chicken Curry', 1, 1),
(23, 115, 9, 'Dal', 1, 2);

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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `bill_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` varchar(20) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `bill_id`, `client_id`, `payment_date`, `amount`, `method`, `reference`, `note`, `received_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '2026-10-05', 22380.00, 'bank', 'BANK-TRX-1001', NULL, 1, '2026-10-07 01:51:35', '2026-10-07 01:51:35', NULL),
(2, 2, 2, '2026-10-05', 8580.00, 'bkash', 'BK8F3K2L9Q', NULL, 1, '2026-10-07 01:51:35', '2026-10-07 01:51:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `nid` varchar(30) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `emergency_contact` varchar(150) DEFAULT NULL,
  `vehicle_type` varchar(30) NOT NULL,
  `vehicle_number` varchar(30) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riders`
--

INSERT INTO `riders` (`id`, `user_id`, `name`, `phone`, `nid`, `address`, `emergency_contact`, `vehicle_type`, `vehicle_number`, `joining_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, 'Rahim Uddin', '01800000001', NULL, 'Mirpur 10, Dhaka', 'Family - 01719000000', 'motorcycle', 'DHA-METRO-HA-11-2233', '2026-02-07', 'active', NULL, NULL, '2026-10-07 01:18:36', '2026-10-07 01:18:37'),
(2, NULL, 'Karim Mia', '01800000002', NULL, 'Mirpur 11, Dhaka', 'Family - 01719000001', 'bicycle', NULL, '2026-01-07', 'active', NULL, NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37'),
(3, NULL, 'Sohel Rana', '01800000003', NULL, 'Mirpur 12, Dhaka', 'Family - 01719000002', 'van', 'DHA-METRO-CHA-15-4455', '2025-12-07', 'active', NULL, NULL, '2026-10-07 01:18:37', '2026-10-07 01:18:37');

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
('RrUzfJEQmkQ20XVD1pY6I2yEmnfCzFeFEMZuH6CV', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiUG9Wbm9jM1V1bmM3bnhyRzJCZnZiZjVnUDhZcjZIaXE2dG43UXJpbSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiYzI3ZmM2YzgwZGIzZDA3YjdjY2YwYjM1MGRkMDQyNzYyOWViMzYwYTg2ZjA5MGYyOWExMWZiMmIzMWM0MTg5MCI7czo3OiJpbmVydGlhIjthOjA6e31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czozOToiaHR0cDovLzEyNy4wLjAuODAvYXBwL3B1YmxpYy9kZWxpdmVyaWVzIjtzOjU6InJvdXRlIjtzOjE2OiJkZWxpdmVyaWVzLmluZGV4Ijt9fQ==', 1791375975);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` varchar(30) NOT NULL,
  `client_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `notification_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`notification_preferences`)),
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `client_id`, `status`, `permissions`, `notification_preferences`, `email_verified_at`, `password`, `last_login_at`, `last_login_ip`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Vendor Admin', 'admin@example.com', '01700000000', 'vendor_admin', NULL, 'active', NULL, NULL, '2026-10-06 07:49:24', '$2y$12$OuVfY4opbFgMTRtegQY44enWGS5t2R3LC3i/jwrR1hrPFLxqLQsuy', '2026-10-07 08:54:53', '127.0.0.1', 'NLAq6WTSQnwALg8EgdefsQJZBlgYdXXaKbMNPhI6S78KuSgyRMf3t1t2j3OQ', '2026-10-06 07:49:24', '2026-10-07 08:54:53', NULL),
(2, 'Vendor Staff', 'staff@example.com', '01700000001', 'vendor_staff', NULL, 'active', NULL, NULL, '2026-10-06 07:49:25', '$2y$12$b5agjEnYtkDBCnxoMNqn/.SJqrC14icxxu5by5IgQcZ.254XbW6eK', '2026-10-07 08:53:56', '127.0.0.1', NULL, '2026-10-06 07:49:25', '2026-10-07 08:53:56', NULL),
(5, 'Tanvir Ahmed', 'xyz@example.com', NULL, 'client_admin', 1, 'active', NULL, NULL, '2026-10-06 09:11:00', '$2y$12$FQ8CRsTrRMThZI2z.fA3HeuWMOej7QYzLSMqf0kHSWIdWrp9bjQyu', '2026-10-07 08:54:22', '127.0.0.1', NULL, '2026-10-06 09:11:00', '2026-10-07 08:54:22', NULL),
(6, 'Nusrat Jahan', 'abcbank@example.com', NULL, 'client_admin', 2, 'active', NULL, NULL, '2026-10-06 09:11:00', '$2y$12$EytmthkYzu.u0jwXuuC.v.yKuVLXrREJkXzUVVUYflDy63sVt6PQO', NULL, NULL, NULL, '2026-10-06 09:11:00', '2026-10-06 09:11:00', NULL),
(7, 'Rahim Uddin', NULL, '01800000001', 'rider', NULL, 'active', NULL, NULL, NULL, '$2y$12$gEUO9wuXfXD0jh3kF75md.ARPXGcdzDJbpDUf44Sa51GFmP18yLcG', '2026-10-07 08:48:45', '127.0.0.1', NULL, '2026-10-07 01:18:37', '2026-10-07 08:48:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vendor_profiles`
--

CREATE TABLE `vendor_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `organization_name` varchar(150) DEFAULT NULL,
  `owner_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `address` varchar(500) NOT NULL,
  `city` varchar(100) NOT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `vat_tin` varchar(50) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendor_profiles`
--

INSERT INTO `vendor_profiles` (`id`, `business_name`, `organization_name`, `owner_name`, `phone`, `email`, `address`, `city`, `logo_path`, `business_type`, `vat_tin`, `status`, `created_at`, `updated_at`) VALUES
(1, 'ABC Catering', 'ABC Catering Ltd.', 'Business Owner', '01700000000', 'info@example.com', 'House 1, Road 1', 'Dhaka', NULL, 'Catering Service', NULL, 'active', '2026-10-06 07:49:24', '2026-10-06 08:03:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`),
  ADD KEY `activity_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  ADD KEY `activity_logs_module_created_at_index` (`module`,`created_at`),
  ADD KEY `activity_logs_client_id_created_at_index` (`client_id`,`created_at`),
  ADD KEY `activity_logs_created_at_index` (`created_at`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bills_client_id_period_start_unique` (`client_id`,`period_start`),
  ADD UNIQUE KEY `bills_bill_number_unique` (`bill_number`),
  ADD KEY `bills_created_by_foreign` (`created_by`),
  ADD KEY `bills_client_id_period_end_index` (`client_id`,`period_end`),
  ADD KEY `bills_status_due_date_index` (`status`,`due_date`),
  ADD KEY `bills_period_start_index` (`period_start`);

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
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clients_name_unique` (`name`),
  ADD KEY `clients_created_by_foreign` (`created_by`),
  ADD KEY `clients_city_index` (`city`),
  ADD KEY `clients_status_index` (`status`);

--
-- Indexes for table `client_menu_prices`
--
ALTER TABLE `client_menu_prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_menu_prices_client_id_meal_type_effective_from_unique` (`client_id`,`meal_type`,`effective_from`),
  ADD KEY `client_menu_prices_created_by_foreign` (`created_by`);

--
-- Indexes for table `daily_menus`
--
ALTER TABLE `daily_menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_menus_menu_date_meal_type_unique` (`menu_date`,`meal_type`),
  ADD KEY `daily_menus_created_by_foreign` (`created_by`),
  ADD KEY `daily_menus_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `daily_menu_items`
--
ALTER TABLE `daily_menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_menu_items_daily_menu_id_menu_item_id_unique` (`daily_menu_id`,`menu_item_id`),
  ADD KEY `daily_menu_items_menu_item_id_index` (`menu_item_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `deliveries_order_id_unique` (`order_id`),
  ADD KEY `deliveries_assigned_by_foreign` (`assigned_by`),
  ADD KEY `deliveries_rider_id_assigned_at_index` (`rider_id`,`assigned_at`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employees_client_id_employee_code_unique` (`client_id`,`employee_code`),
  ADD KEY `employees_created_by_foreign` (`created_by`),
  ADD KEY `employees_client_id_status_name_index` (`client_id`,`status`,`name`),
  ADD KEY `employees_client_id_department_index` (`client_id`,`department`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `meal_entries`
--
ALTER TABLE `meal_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `meal_entries_employee_id_meal_date_meal_type_unique` (`employee_id`,`meal_date`,`meal_type`),
  ADD KEY `meal_entries_confirmed_by_foreign` (`confirmed_by`),
  ADD KEY `meal_entries_updated_by_foreign` (`updated_by`),
  ADD KEY `meal_entries_client_id_meal_date_meal_type_status_index` (`client_id`,`meal_date`,`meal_type`,`status`),
  ADD KEY `meal_entries_meal_date_status_index` (`meal_date`,`status`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_items_name_unique` (`name`),
  ADD KEY `menu_items_created_by_foreign` (`created_by`),
  ADD KEY `menu_items_category_index` (`category`),
  ADD KEY `menu_items_status_index` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_created_at_index` (`notifiable_type`,`notifiable_id`,`created_at`),
  ADD KEY `notifications_notifiable_type_notifiable_id_read_at_index` (`notifiable_type`,`notifiable_id`,`read_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_client_id_order_date_meal_type_unique` (`client_id`,`order_date`,`meal_type`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_created_by_foreign` (`created_by`),
  ADD KEY `orders_order_date_status_index` (`order_date`,`status`),
  ADD KEY `orders_status_index` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_bill_id_foreign` (`bill_id`),
  ADD KEY `payments_received_by_foreign` (`received_by`),
  ADD KEY `payments_client_id_payment_date_index` (`client_id`,`payment_date`),
  ADD KEY `payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `riders_phone_unique` (`phone`),
  ADD UNIQUE KEY `riders_user_id_unique` (`user_id`),
  ADD KEY `riders_created_by_foreign` (`created_by`),
  ADD KEY `riders_status_index` (`status`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_updated_by_foreign` (`updated_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD KEY `users_role_status_index` (`role`,`status`),
  ADD KEY `users_client_id_foreign` (`client_id`);

--
-- Indexes for table `vendor_profiles`
--
ALTER TABLE `vendor_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vendor_profiles_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `client_menu_prices`
--
ALTER TABLE `client_menu_prices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `daily_menus`
--
ALTER TABLE `daily_menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `daily_menu_items`
--
ALTER TABLE `daily_menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=204;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meal_entries`
--
ALTER TABLE `meal_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `vendor_profiles`
--
ALTER TABLE `vendor_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `bills_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `client_menu_prices`
--
ALTER TABLE `client_menu_prices`
  ADD CONSTRAINT `client_menu_prices_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `client_menu_prices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_menus`
--
ALTER TABLE `daily_menus`
  ADD CONSTRAINT `daily_menus_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `daily_menus_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `daily_menu_items`
--
ALTER TABLE `daily_menu_items`
  ADD CONSTRAINT `daily_menu_items_daily_menu_id_foreign` FOREIGN KEY (`daily_menu_id`) REFERENCES `daily_menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_menu_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`);

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `deliveries_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_rider_id_foreign` FOREIGN KEY (`rider_id`) REFERENCES `riders` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `meal_entries`
--
ALTER TABLE `meal_entries`
  ADD CONSTRAINT `meal_entries_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `meal_entries_confirmed_by_foreign` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `meal_entries_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `meal_entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_bill_id_foreign` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`),
  ADD CONSTRAINT `payments_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `riders`
--
ALTER TABLE `riders`
  ADD CONSTRAINT `riders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `riders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
