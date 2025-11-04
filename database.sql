-- SMS/WhatsApp API Manager Database Schema
-- Create Database
CREATE DATABASE IF NOT EXISTS `sms_api_manager` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sms_api_manager`;

-- Table: admin_users (for login authentication)
CREATE TABLE `admin_users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `status`) 
VALUES ('admin', '$2y$10$8K1p/a0dL3LKznjr5.B.C.vUKx8F5RxPF5P3bI5xXPPJLJqQJfDuS', 'System Administrator', 'admin@example.com', 'active');

-- Table: customers – Customer Master
CREATE TABLE `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `api_key` VARCHAR(100) NOT NULL COMMENT 'API key for customer authentication',
  `api_type` ENUM('type1', 'type2', 'type3') DEFAULT 'type1',
  `customer_api_key` VARCHAR(255) DEFAULT NULL COMMENT 'Customer own WhatsApp/SMS API key',
  `use_own_api` TINYINT(1) DEFAULT 0 COMMENT '1=Use customer API, 0=Use system default API',
  `status` ENUM('active', 'suspended', 'expired') DEFAULT 'active',
  `credit_limit` INT(11) DEFAULT 0,
  `used_credits` INT(11) DEFAULT 0,
  `priority_level` TINYINT(1) DEFAULT 1 COMMENT '1=Normal,2=High',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: messages – Message Queue Table
CREATE TABLE `messages` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `mobile_number` VARCHAR(20) NOT NULL,
  `message_text` TEXT NOT NULL,
  `api_type` ENUM('type1', 'type2', 'type3') DEFAULT 'type1',
  `status` ENUM('pending', 'processing', 'sent', 'failed', 'hold') DEFAULT 'pending',
  `delivery_status` ENUM('waiting', 'delivered', 'failed', 'unknown') DEFAULT 'waiting',
  `priority` ENUM('high', 'normal', 'low') DEFAULT 'normal',
  `retry_count` TINYINT(1) DEFAULT 0,
  `error_message` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `sent_at` DATETIME DEFAULT NULL,
  `delivered_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  KEY `priority` (`priority`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: api_logs – Store API Requests & Responses
CREATE TABLE `api_logs` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `message_id` BIGINT(20) DEFAULT NULL,
  `api_type` ENUM('type1', 'type2', 'type3') DEFAULT 'type1',
  `request_url` TEXT,
  `request_data` TEXT,
  `response_data` TEXT,
  `status_code` VARCHAR(10) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: delivery_reports – Store delivery confirmation from API
CREATE TABLE `delivery_reports` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `message_id` BIGINT(20) NOT NULL,
  `api_message_id` VARCHAR(100) DEFAULT NULL,
  `delivery_status` ENUM('delivered', 'failed', 'waiting', 'unknown') DEFAULT 'waiting',
  `report_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: system_settings – Global Configs for APIs, Cron, etc.
CREATE TABLE `system_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('default_api', 'type1'),
('cron_status', 'enabled'),
('credit_alert_limit', '50'),
('messages_per_cron', '50');
