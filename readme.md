# SMS/WhatsApp API Manager System

A complete Master WhatsApp/SMS API management system built with core PHP, Bootstrap 5, and MySQL. This system allows you to manage multiple customers, each with their own API keys, credits, and message queues.

## 🌟 Features

Got it 👍 — you want to build a complete Master WhatsApp/SMS API management system, where:

Each customer has their own WhatsApp/SMS API key, limits, and usage details.

You have 3 different API providers (API types) that can be used dynamically.

Customers post an SMS request to your API endpoint,
→ your system stores it in DB,
→ a cron job sends messages from your side (based on priority).

You need reports, delivery status, and customer-wise control (active/inactive, credit limit, expired API, etc.).

Let’s break this down into a clear architecture and module plan 👇
🧩 System Modules Overview
1️⃣ Customer Management
Field	Description
id	Unique customer ID
name	Customer/Company name
mobile	Contact number
email	Email address
api_key	Customer’s WhatsApp/SMS API key
api_type	Type of API (e.g. Type1, Type2, Type3)
status	active / suspended / expired
credit_limit	Maximum messages allowed
used_credits	Messages already sent
priority_level	For cron-based sending priority

✅ Admin can:

Add/Edit/Delete customers

View usage, credits, status

Suspend or block users

2️⃣ SMS / WhatsApp Message Queue Table
Field	Description
id	Message ID
customer_id	Linked to customer table
mobile_number	Receiver number
message_text	Message body
api_type	API to use
status	pending / sent / failed / delivered
priority	high / normal / low
created_at	When message added
sent_at	When message sent
delivery_status	delivered / failed / waiting
retry_count	Number of retry attempts

✅ Messages are first stored when customer posts to your master API.
✅ Cron job runs every minute (or defined interval) to:

Pick messages by priority

Check customer’s credit, API validity, suspension

Send via correct API

Update status and delivery info

3️⃣ API Integration Module

You’ll integrate your 3 WhatsApp/SMS APIs like:

api_type_1: Example API (e.g. Gupshup / GreenAPI / UltraMSG)

api_type_2: Example API

api_type_3: Example API

Each type has its own send logic & response parser.

✅ Design pattern:

class ApiFactory {
  public static function getApiHandler($type) {
    switch($type) {
      case 'type1': return new Type1Api();
      case 'type2': return new Type2Api();
      case 'type3': return new Type3Api();
    }
  }
}


Each class implements:

interface SmsApiInterface {
  public function sendMessage($mobile, $message, $customerApiKey);
  public function checkDelivery($messageId);
}

4️⃣ Cron Job for Sending

Cron runs e.g. every 1 min:

php artisan schedule:run


Fetch pending messages by priority.

Validate:

Customer active?

Credit available?

API valid?

If all OK → send → update status + decrement credits.

Else → mark as failed or hold.

5️⃣ Delivery Report Handling

Cron or webhook for each API checks delivery.

Update delivery_status in DB.

6️⃣ Customer Dashboard (Optional)

Customers can:

View messages sent

Delivery reports

Remaining credits

API key and expiry

7️⃣ Admin Dashboard

Admin can:

Manage customers

See total messages sent / failed / pending

Assign credits

Suspend users

Switch default API

Monitor API health

8️⃣ System Alerts

Notify customer if API expired.

Notify when credit is < 10%.

Pause sending automatically when:

Credit exhausted

Suspended/Expired

🗃️ Database Tables Summary

customers

messages

api_logs

delivery_reports

system_settings

⚙️ Tech Stack Recommendation

Backend: Laravel (for queue, scheduler, and modular structure)

DB: MySQL

Frontend (optional): React.js or simple AdminLTE

Cron: Laravel Scheduler

Queue system: Laravel Queue (Redis or Database driver)

🔒 Security & Reliability

JWT authentication for customer API

Rate limiting to avoid spam

Logs for all sent/failed messages

Option to resend failed SMS


🧩 Database Structure (Core PHP Compatible)
🧱 customers – Customer Master

Stores all customer-related details, limits, and status.

CREATE TABLE `customers` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `api_key` VARCHAR(100) NOT NULL,
  `api_type` ENUM('type1', 'type2', 'type3') DEFAULT 'type1',
  `status` ENUM('active', 'suspended', 'expired') DEFAULT 'active',
  `credit_limit` INT(11) DEFAULT 0,
  `used_credits` INT(11) DEFAULT 0,
  `priority_level` TINYINT(1) DEFAULT 1 COMMENT '1=Normal,2=High',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

🧱 messages – Message Queue Table

Stores all incoming SMS/WhatsApp requests before and after sending.

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
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

🧱 api_logs – Store API Requests & Responses (for debugging)
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

🧱 delivery_reports – Store delivery confirmation from API
CREATE TABLE `delivery_reports` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `message_id` BIGINT(20) NOT NULL,
  `api_message_id` VARCHAR(100) DEFAULT NULL,
  `delivery_status` ENUM('delivered', 'failed', 'waiting', 'unknown') DEFAULT 'waiting',
  `report_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

🧱 system_settings – Global Configs for APIs, Cron, etc.
CREATE TABLE `system_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


Example rows:

default_api → type1

cron_status → enabled

credit_alert_limit → 50

🔄 Flow Summary
1️⃣ Customer Posts SMS

POST URL: /api/send_message.php
Request:

{
  "api_key": "CUST123KEY",
  "mobile_number": "9876543210",
  "message_text": "Hello Test Message"
}


Flow:

Verify API key

Check credits & customer status

Store message in messages (status = pending)

2️⃣ Cron Job (e.g. cron_send.php)

Runs every minute:

Fetch messages with status='pending' (limit 50)

Check customer status & credit

Send using respective API (type1/type2/type3)

Update:

status='sent' or failed

Decrease credit

Insert log in api_logs

Add to delivery_reports

3️⃣ Delivery Update

From API callback or another cron:

Update delivery_reports & messages.delivery_status

4️⃣ Credit & Suspension Control

If used_credits >= credit_limit → set status='suspended'

If customer manually paused → no sending

If API expired → hold messages

5️⃣ Reporting (Admin Panel)

You can easily query:

-- Total messages per customer
SELECT c.customer_name, COUNT(m.id) as total_sent
FROM messages m
JOIN customers c ON m.customer_id = c.id
WHERE m.status='sent'
GROUP BY c.id;


Would you like me to prepare the core PHP sample files next (like send_message.php, cron_send.php, and db_connect.php with prepared statements and safe flow)?
That would give you a full working base structure.