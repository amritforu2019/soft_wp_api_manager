# 🔑 Customer API Key Feature - Documentation

## Overview

The system now supports **two modes of API operation**:

1. **System Default API** - Uses the system's configured API keys (Type1, Type2, Type3)
2. **Customer's Own API** - Customer can use their own WhatsApp/SMS API account

---

## 🎯 How It Works

### For Each Customer:

- **Option 1: Use System Default API** (Original behavior)
  - Customer gets authentication `api_key` to call your API
  - Your system uses its own WhatsApp/SMS provider API keys to send messages
  - Customer pays you per message

- **Option 2: Use Customer's Own API** (New feature)
  - Customer provides their own WhatsApp/SMS provider API key
  - Your system routes messages using customer's API key
  - Customer pays their provider directly
  - You can still charge for usage/management

---

## 📊 Database Changes

### New Fields in `customers` Table:

| Field | Type | Description |
|-------|------|-------------|
| `customer_api_key` | VARCHAR(255) | Customer's own WhatsApp/SMS API key |
| `use_own_api` | TINYINT(1) | 1 = Use customer API, 0 = Use system default |

---

## 🔧 Installation & Migration

### For New Installations:
Simply import the updated `database.sql` file - it includes all new fields.

### For Existing Installations:
Run the migration script in phpMyAdmin:

```sql
USE `sms_api_manager`;

ALTER TABLE `customers` 
ADD COLUMN `customer_api_key` VARCHAR(255) DEFAULT NULL COMMENT 'Customer own WhatsApp/SMS API key' AFTER `api_type`,
ADD COLUMN `use_own_api` TINYINT(1) DEFAULT 0 COMMENT '1=Use customer API, 0=Use system default API' AFTER `customer_api_key`;
```

Or import: `migration_customer_api.sql`

---

## 📝 Admin Panel Usage

### Adding a New Customer:

1. Go to **Customers** → **Add New Customer**
2. Fill in basic details (Name, Email, Mobile, etc.)
3. Select **API Type** (type1, type2, or type3)
4. **Toggle "Customer has their own API key"** if applicable
5. If enabled:
   - Enter customer's WhatsApp/SMS API key
   - This key will be used instead of system default
6. Click **Save Customer**

### Editing Existing Customer:

1. Go to **Customers** → Click **Edit** on any customer
2. Scroll to **"Customer's Own API Configuration"**
3. Toggle the switch to enable/disable
4. Update customer's API key if needed
5. Click **Update Customer**

### Viewing API Usage:

On the **Customers** page, the **"Own API"** column shows:
- ✅ **Yes** (Green badge) = Using customer's own API
- ❌ **No** (Gray badge) = Using system default API

---

## 🔄 How Messages Are Sent

### Message Flow with Customer API:

```
Customer sends request to your API
    ↓
Message queued in database
    ↓
Cron job picks up message
    ↓
Check: Does customer have own API? (use_own_api = 1)
    ├─ YES → Use customer_api_key
    └─ NO  → Use system default API (Type1/2/3)
    ↓
Send via WhatsApp/SMS provider
    ↓
Log response
    ↓
Update status
```

### Code Logic:

```php
// In cron_send.php
$useCustomerApi = ($customer['use_own_api'] == 1 && !empty($customer['customer_api_key']));
$apiKeyToUse = $useCustomerApi ? $customer['customer_api_key'] : null;

$result = $apiHandler->sendMessage(
    $mobile,
    $message,
    $apiKeyToUse,
    $useCustomerApi
);
```

---

## 🎨 API Handler Changes

All API handlers (`Type1Api.php`, `Type2Api.php`, `Type3Api.php`) now accept:

```php
public function sendMessage($mobile, $message, $customerApiKey = null, $useCustomerApi = false)
{
    // Use customer's API key if provided
    $finalApiKey = ($useCustomerApi && !empty($customerApiKey)) 
        ? $customerApiKey 
        : $this->apiKey; // System default
    
    // Send message using finalApiKey
}
```

---

## 📊 API Logs

API logs now include which API was used:

```json
{
    "success": true,
    "status_code": 200,
    "api_used": "customer_api",  // or "system_default"
    "response": "..."
}
```

---

## 💼 Business Use Cases

### Use Case 1: Reseller Model
- You provide the infrastructure
- Customer uses your system default API
- You charge per message
- **Setting:** `use_own_api = 0`

### Use Case 2: White Label / Self-Service
- Customer has own WhatsApp Business API
- They want centralized management
- They pay their provider directly
- You charge for platform/management
- **Setting:** `use_own_api = 1`

### Use Case 3: Hybrid
- Some customers use your API
- Some customers use their own API
- Flexible pricing models
- **Setting:** Mix of both

---

## 🔐 Security Considerations

### Customer API Keys:
- Stored in database (consider encryption for production)
- Only visible to admin users
- Customer can't see via API
- Validated before use

### Validation Flow:
1. Check if customer is active
2. Check credit limits
3. Verify API key exists if `use_own_api = 1`
4. Send message
5. Log all attempts

---

## 🧪 Testing

### Test Customer with Own API:

1. **Create Test Customer:**
   - Name: Test Customer
   - API Type: type1
   - Enable: "Customer has their own API key"
   - Customer API Key: `TEST_API_KEY_123`

2. **Send Test Message:**
```bash
php cron_send.php
```

3. **Check API Logs:**
   - Verify `request_data` contains customer's API key
   - Check `api_used` field

### Test Customer with System Default:

1. **Create Test Customer:**
   - Name: Default Test
   - API Type: type1
   - Leave "Own API" unchecked

2. **Send Test Message**
3. **Verify:** System default API key is used

---

## 📄 API Documentation Update

### Customer API Endpoint (`api/send_message.php`):

**No changes required!** The endpoint works the same way:

```json
POST /api/send_message.php
{
    "api_key": "customer_authentication_key",
    "mobile_number": "1234567890",
    "message_text": "Hello",
    "priority": "normal"
}
```

The system automatically determines which WhatsApp/SMS API to use based on customer settings.

---

## 🎯 Benefits

### For System Admins:
✅ Flexibility in customer onboarding  
✅ Support multiple business models  
✅ Better cost management  
✅ Easier scaling  

### For Customers:
✅ Can use existing WhatsApp Business API  
✅ Direct billing with provider  
✅ Full control over API account  
✅ Centralized message management  

---

## 📊 Reporting

### View API Usage by Type:

```sql
SELECT 
    c.customer_name,
    c.use_own_api,
    c.api_type,
    COUNT(m.id) as total_messages,
    SUM(CASE WHEN m.status = 'sent' THEN 1 ELSE 0 END) as sent_messages
FROM customers c
LEFT JOIN messages m ON c.id = m.customer_id
GROUP BY c.id;
```

### Customer API vs System Default Stats:

```sql
SELECT 
    CASE WHEN use_own_api = 1 THEN 'Customer API' ELSE 'System Default' END as api_mode,
    COUNT(*) as total_customers,
    SUM(used_credits) as total_messages_sent
FROM customers
GROUP BY use_own_api;
```

---

## 🔧 Troubleshooting

### Issue: Customer API not working

**Check:**
1. Is `use_own_api` = 1?
2. Is `customer_api_key` not empty?
3. Is the API key valid?
4. Check API logs for error messages

### Issue: Still using system default

**Verify:**
- Toggle is enabled in customer edit page
- Database field `use_own_api` = 1
- `customer_api_key` is not NULL

### Issue: Messages failing

**Debug:**
1. Check API logs table
2. Look at `error_message` in messages table
3. Verify customer's API key is correct
4. Test API key independently

---

## 🎓 Example Scenarios

### Scenario 1: Migrate Customer to Own API

```sql
-- Enable customer's own API
UPDATE customers 
SET use_own_api = 1, 
    customer_api_key = 'CUSTOMER_ACTUAL_API_KEY'
WHERE id = 123;

-- Verify
SELECT id, customer_name, use_own_api, customer_api_key 
FROM customers 
WHERE id = 123;
```

### Scenario 2: Revert to System Default

```sql
-- Disable customer's API
UPDATE customers 
SET use_own_api = 0, 
    customer_api_key = NULL
WHERE id = 123;
```

---

## 📚 Files Modified

1. `database.sql` - Added new fields
2. `migration_customer_api.sql` - Migration script
3. `add_customer.php` - Added customer API fields
4. `edit_customer.php` - Added customer API fields
5. `customers.php` - Display "Own API" status
6. `cron_send.php` - Logic to use customer/system API
7. `api/Type1Api.php` - Support customer API key
8. `api/Type2Api.php` - Support customer API key
9. `api/Type3Api.php` - Support customer API key
10. `api/SmsApiInterface.php` - Updated interface

---

## ✅ Success Checklist

After implementation:

- [✅] Database migration completed
- [✅] Can create customer with own API
- [✅] Can edit customer API settings
- [✅] Toggle works in add/edit forms
- [✅] "Own API" badge shows in customer list
- [✅] Cron sends using correct API key
- [✅] API logs show which API was used
- [✅] Messages send successfully with both modes

---

## 🚀 Next Steps

1. **Import migration SQL** if existing installation
2. **Test with dummy customer**
3. **Configure real API keys** for testing
4. **Update customer accounts** as needed
5. **Monitor API logs** for verification

---

**Questions or issues?** Check the API logs and message error_message field for debugging.

---

*Feature implemented: November 2025*
