-- Quick Fix: Reset Admin Password to 'admin123'
-- Run this in phpMyAdmin SQL tab if login is not working

-- Method 1: Update existing admin user
UPDATE `admin_users` 
SET `password` = '$2y$10$8K1p/a0dL3LKznjr5.B.C.vUKx8F5RxPF5P3bI5xXPPJLJqQJfDuS' 
WHERE `username` = 'admin';

-- Method 2: If admin user doesn't exist, create it
INSERT IGNORE INTO `admin_users` (`username`, `password`, `full_name`, `email`, `status`) 
VALUES ('admin', '$2y$10$8K1p/a0dL3LKznjr5.B.C.vUKx8F5RxPF5P3bI5xXPPJLJqQJfDuS', 'System Administrator', 'admin@example.com', 'active');

-- Verify the admin user
SELECT id, username, full_name, status FROM admin_users WHERE username = 'admin';

-- Note: Password hash is for 'admin123'
