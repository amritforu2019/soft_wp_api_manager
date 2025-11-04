-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 04, 2025 at 10:48 AM
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
-- Database: `sms_api_manager`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `full_name`, `email`, `status`, `created_at`, `updated_at`) VALUES
(2, 'admin', '$2y$10$xTouzM44TYmJnAlzbCjkjujsiHYh52EOlxPAeK61/SfNSPfZbuR6.', 'System Administrator', 'admin@example.com', 'active', '2025-11-04 10:46:38', '2025-11-04 10:51:10');

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

CREATE TABLE `api_logs` (
  `id` bigint(20) NOT NULL,
  `message_id` bigint(20) DEFAULT NULL,
  `api_type` enum('type1','type2','type3') DEFAULT 'type1',
  `request_url` text DEFAULT NULL,
  `request_data` text DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `status_code` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `api_key` varchar(100) NOT NULL COMMENT 'API key for customer authentication',
  `api_type` enum('type1','type2','type3') DEFAULT 'type1',
  `customer_api_key` varchar(255) DEFAULT NULL COMMENT 'Customer own WhatsApp/SMS API key',
  `use_own_api` tinyint(1) DEFAULT 0 COMMENT '1=Use customer API, 0=Use system default API',
  `status` enum('active','suspended','expired') DEFAULT 'active',
  `credit_limit` int(11) DEFAULT 0,
  `used_credits` int(11) DEFAULT 0,
  `priority_level` tinyint(1) DEFAULT 1 COMMENT '1=Normal,2=High',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_reports`
--

CREATE TABLE `delivery_reports` (
  `id` bigint(20) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `api_message_id` varchar(100) DEFAULT NULL,
  `delivery_status` enum('delivered','failed','waiting','unknown') DEFAULT 'waiting',
  `report_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `mobile_number` varchar(20) NOT NULL,
  `message_text` text NOT NULL,
  `api_type` enum('type1','type2','type3') DEFAULT 'type1',
  `status` enum('pending','processing','sent','failed','hold') DEFAULT 'pending',
  `delivery_status` enum('waiting','delivered','failed','unknown') DEFAULT 'waiting',
  `priority` enum('high','normal','low') DEFAULT 'normal',
  `retry_count` tinyint(1) DEFAULT 0,
  `error_message` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `created_at`) VALUES
(1, 'default_api', 'type1', '2025-11-04 10:36:08'),
(2, 'cron_status', 'enabled', '2025-11-04 10:36:08'),
(3, 'credit_alert_limit', '50', '2025-11-04 10:36:08'),
(4, 'messages_per_cron', '50', '2025-11-04 10:36:08'),
(5, 'type1_api_url', 'https://api-type1.example.com/send', '2025-11-04 11:08:41'),
(6, 'type1_api_key', '178052ca18e41bb711ec6b34832c53a6c102f54a470d4a705742b17dfee512dc', '2025-11-04 11:08:41'),
(7, 'type2_api_url', 'https://api-type2.example.com/v1/sms', '2025-11-04 11:08:41'),
(8, 'type2_api_key', 'JYCEYLjAt98K6RoG4Arb', '2025-11-04 11:08:41'),
(9, 'type3_api_url', 'https://api-type3.example.com/whatsapp/send', '2025-11-04 11:08:41'),
(10, 'type3_api_key', 'YOUR_TYPE3_DEFAULT_API_KEY', '2025-11-04 11:08:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `api_logs`
--
ALTER TABLE `api_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- Indexes for table `delivery_reports`
--
ALTER TABLE `delivery_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_id` (`message_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `status` (`status`),
  ADD KEY `priority` (`priority`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `api_logs`
--
ALTER TABLE `api_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery_reports`
--
ALTER TABLE `delivery_reports`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
