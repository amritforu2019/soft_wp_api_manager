# 📖 INSTALLATION GUIDE

## Complete Setup Instructions for SMS/WhatsApp API Manager

---

## ✅ Step-by-Step Installation

### 1️⃣ Copy Project Files

Copy all files to your XAMPP htdocs directory:
```
C:\xampp\htdocs\soft_wp_api_manager\
```

### 2️⃣ Import Database

1. Start XAMPP (Apache + MySQL)
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Click "New" to create database
4. Database name: `sms_api_manager`
5. Click "Create"
6. Select the `sms_api_manager` database
7. Click "Import" tab
8. Click "Choose File" and select `database.sql`
9. Click "Go" to import

### 3️⃣ Verify Database Connection

The default configuration is in `config/db_connect.php`:
- Host: localhost
- Username: root
- Password: (empty)
- Database: sms_api_manager

### 4️⃣ Access Admin Panel

1. Open browser
2. Go to: `http://localhost/soft_wp_api_manager/login.php`
3. Login with:
   - **Username**: `admin`
   - **Password**: `admin123`

### 5️⃣ Add Your First Customer

1. Click "Customers" in sidebar
2. Click "Add New Customer"
3. Fill in details:
   - Customer Name: Test Customer
   - Email: test@example.com
   - Mobile: 1234567890
   - API Type: Type 1
   - Status: Active
   - Credit Limit: 1000
   - Priority: Normal
4. Click "Save Customer"
5. **Copy the generated API Key** - customers will use this!

### 6️⃣ Configure API Providers

Edit these files and add your real API credentials:

**File**: `api/Type1Api.php`
```php
private $apiUrl = 'https://your-api-provider.com/send';
private $apiKey = 'YOUR_ACTUAL_API_KEY';
```

**File**: `api/Type2Api.php`
```php
private $apiUrl = 'https://your-second-provider.com/v1/sms';
private $apiKey = 'YOUR_ACTUAL_API_KEY';
```

**File**: `api/Type3Api.php`
```php
private $apiUrl = 'https://your-third-provider.com/whatsapp/send';
private $apiKey = 'YOUR_ACTUAL_API_KEY';
```

### 7️⃣ Test Customer API

Use PowerShell to test:

```powershell
$body = @{
    api_key = "PASTE_CUSTOMER_API_KEY_HERE"
    mobile_number = "1234567890"
    message_text = "Test message from API"
    priority = "normal"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost/soft_wp_api_manager/api/send_message.php" -Method Post -Body $body -ContentType "application/json"
```

### 8️⃣ Setup Cron Job (Message Processing)

#### For Windows - Task Scheduler:

1. Press `Win + R`, type `taskschd.msc`, press Enter
2. Click "Create Basic Task"
3. Name: `SMS API Cron`
4. Description: `Process pending SMS messages`
5. Click "Next"
6. Trigger: Daily
7. Click "Next"
8. Start date: Today
9. Recur every: 1 days
10. Click "Next"
11. Action: "Start a program"
12. Click "Next"
13. Program/script: `C:\xampp\php\php.exe`
14. Add arguments: `C:\xampp\htdocs\soft_wp_api_manager\cron_send.php`
15. Click "Next" then "Finish"
16. Right-click the task → Properties
17. Go to "Triggers" tab → Edit
18. Check "Repeat task every" → Select "1 minute"
19. For a duration of: "Indefinitely"
20. Click "OK"

#### Manual Test:

Open PowerShell in project directory:
```powershell
cd C:\xampp\htdocs\soft_wp_api_manager
php cron_send.php
```

---

## 🎯 Quick Start Guide

### Admin Panel URLs:

- **Login**: http://localhost/soft_wp_api_manager/login.php
- **Dashboard**: http://localhost/soft_wp_api_manager/index.php
- **Customers**: http://localhost/soft_wp_api_manager/customers.php
- **Messages**: http://localhost/soft_wp_api_manager/messages.php
- **API Logs**: http://localhost/soft_wp_api_manager/api_logs.php
- **Settings**: http://localhost/soft_wp_api_manager/settings.php

### Customer API Endpoint:

**URL**: `http://localhost/soft_wp_api_manager/api/send_message.php`

**Method**: POST

**Headers**: `Content-Type: application/json`

**Body**:
```json
{
    "api_key": "customer_api_key_here",
    "mobile_number": "1234567890",
    "message_text": "Your message here",
    "priority": "normal"
}
```

---

## 🔧 Troubleshooting

### Problem: Can't login

**Solution**:
1. Check MySQL is running in XAMPP
2. Verify database `sms_api_manager` exists
3. Check `admin_users` table has data
4. Try default credentials: admin / admin123

### Problem: Messages stuck in "pending"

**Solution**:
1. Check if cron job is running
2. Run manually: `php cron_send.php`
3. Check Settings → Cron Status = "Enabled"
4. View API Logs for errors

### Problem: API returns "Invalid API key"

**Solution**:
1. Go to Customers page
2. Click Edit on customer
3. Copy the API Key exactly
4. Use it in your API request

### Problem: Customer has no credits

**Solution**:
1. Go to Customers → Edit Customer
2. Increase "Credit Limit"
3. Save changes

### Problem: Messages fail to send

**Solution**:
1. Check API Logs page for errors
2. Verify API credentials in `api/Type1Api.php`, etc.
3. Check customer Status is "Active"
4. Ensure customer has available credits

---

## 📊 Understanding the Flow

```
Customer API Request
    ↓
Message Queue (pending status)
    ↓
Cron Job Runs (every minute)
    ↓
Validates Customer (active, has credits)
    ↓
Sends via API Provider (Type1/2/3)
    ↓
Logs Request/Response
    ↓
Updates Message Status (sent/failed)
    ↓
Creates Delivery Report
```

---

## 🔐 Security Notes

1. **Change default password** after first login
2. Use strong passwords for new admin users
3. Keep API keys secure
4. Don't expose database credentials
5. Use HTTPS in production

---

## 📁 File Structure

```
soft_wp_api_manager/
├── 📁 api/              → API handlers & customer endpoint
├── 📁 config/           → Database configuration
├── 📁 includes/         → Header, footer, auth
├── 📄 index.php         → Dashboard
├── 📄 login.php         → Login page
├── 📄 customers.php     → Customer management
├── 📄 messages.php      → Message queue
├── 📄 settings.php      → System settings
├── 📄 cron_send.php     → Cron job script
└── 📄 database.sql      → Database schema
```

---

## ✨ Features Overview

### 1. Dashboard
- Total customers, messages statistics
- Pending/sent/failed counts
- Recent messages table

### 2. Customer Management
- Add/Edit/Delete customers
- Auto-generate API keys
- Set credit limits
- Priority levels (High/Normal)
- Status control (Active/Suspended/Expired)

### 3. Message Queue
- View all messages
- Filter by status/customer
- See delivery status
- Retry count tracking

### 4. API Logs
- Complete request/response logging
- HTTP status codes
- Error tracking

### 5. Delivery Reports
- Track message delivery
- API message IDs
- Delivery timestamps

### 6. Settings
- Enable/disable cron
- Set messages per cron run
- Configure default API
- Credit alert limits

---

## 🚀 Next Steps

After installation:

1. ✅ Login to admin panel
2. ✅ Add your first customer
3. ✅ Configure API provider credentials
4. ✅ Test the customer API endpoint
5. ✅ Setup cron job for auto-processing
6. ✅ Monitor dashboard and logs

---

## 📞 Need Help?

Check these in order:
1. Dashboard for statistics
2. Messages page for queue status
3. API Logs for request/response details
4. Delivery Reports for final status
5. Settings to verify cron is enabled

---

**Happy Messaging! 🎉**
