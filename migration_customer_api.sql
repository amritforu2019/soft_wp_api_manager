-- Migration Script: Add Customer API Key Support
-- Run this if you already have the database set up
-- This adds fields for customers to use their own API keys

USE `sms_api_manager`;

-- Add new columns to customers table
ALTER TABLE `customers` 
ADD COLUMN `customer_api_key` VARCHAR(255) DEFAULT NULL COMMENT 'Customer own WhatsApp/SMS API key' AFTER `api_type`,
ADD COLUMN `use_own_api` TINYINT(1) DEFAULT 0 COMMENT '1=Use customer API, 0=Use system default API' AFTER `customer_api_key`;

-- Add system API keys to settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('type1_api_url', 'https://api-type1.example.com/send'),
('type1_api_key', 'YOUR_TYPE1_DEFAULT_API_KEY'),
('type2_api_url', 'https://api-type2.example.com/v1/sms'),
('type2_api_key', 'YOUR_TYPE2_DEFAULT_API_KEY'),
('type3_api_url', 'https://api-type3.example.com/whatsapp/send'),
('type3_api_key', 'YOUR_TYPE3_DEFAULT_API_KEY')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);

-- Verify changes
SELECT 'Customers table updated successfully' as status;
DESCRIBE customers;

SELECT 'System settings updated' as status;
SELECT * FROM system_settings WHERE setting_key LIKE '%api%';
