# 🔧 TROUBLESHOOTING GUIDE

## Login Issues - admin/admin123 Not Working

---

## 🚨 Quick Fix Methods

### Method 1: Use Password Reset Utility (EASIEST)

1. Open in browser: `http://localhost/soft_wp_api_manager/reset_password.php`
2. Fill in the form:
   - Username: `admin`
   - New Password: `admin123`
   - Confirm Password: `admin123`
3. Click "Reset/Create Password"
4. Try logging in again
5. **IMPORTANT**: Delete `reset_password.php` after use!

---

### Method 2: Run SQL Script in phpMyAdmin

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database `sms_api_manager`
3. Click "SQL" tab
4. Copy and paste this SQL:

```sql
UPDATE `admin_users` 
SET `password` = '$2y$10$8K1p/a0dL3LKznjr5.B.C.vUKx8F5RxPF5P3bI5xXPPJLJqQJfDuS' 
WHERE `username` = 'admin';
```

5. Click "Go"
6. Try logging in with admin/admin123

**Alternative**: You can also run the `fix_admin_password.sql` file:
- In phpMyAdmin, click "Import"
- Choose file: `fix_admin_password.sql`
- Click "Go"

---

### Method 3: Re-import Database

1. Open phpMyAdmin
2. Select database `sms_api_manager`
3. Click "Operations" tab
4. Scroll down and click "Drop the database"
5. Create new database `sms_api_manager`
6. Import the updated `database.sql` file
7. Try login again with admin/admin123

---

## 🔍 Verify the Issue

### Check if admin user exists:

1. Open phpMyAdmin
2. Go to database `sms_api_manager`
3. Click on table `admin_users`
4. Click "Browse"
5. You should see a user with username "admin"

### If admin user is missing:

Run this SQL in phpMyAdmin:

```sql
INSERT INTO `admin_users` (`username`, `password`, `full_name`, `email`, `status`) 
VALUES ('admin', '$2y$10$8K1p/a0dL3LKznjr5.B.C.vUKx8F5RxPF5P3bI5xXPPJLJqQJfDuS', 'System Administrator', 'admin@example.com', 'active');
```

---

## 🐛 Other Common Login Issues

### Issue: "Invalid username or password" error

**Possible Causes:**
1. Wrong password hash in database
2. User status is not "active"
3. Database not connected

**Solutions:**
1. Use Method 1 (reset_password.php) to reset password
2. Check user status in phpMyAdmin (should be "active")
3. Verify database connection in `config/db_connect.php`

---

### Issue: Blank page or error after login

**Possible Causes:**
1. PHP errors
2. Session issues
3. Missing files

**Solutions:**
1. Enable error display in PHP:
   - Edit `login.php` and add at top:
   ```php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```
2. Check if `index.php` exists
3. Clear browser cookies and cache

---

### Issue: Database connection error

**Error Message:** "Connection failed: ..."

**Solutions:**
1. Start MySQL in XAMPP Control Panel
2. Check database exists: `sms_api_manager`
3. Verify credentials in `config/db_connect.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'sms_api_manager');
   ```

---

### Issue: Table 'admin_users' doesn't exist

**Solution:**
1. Database was not imported correctly
2. Re-import `database.sql` file
3. Make sure you selected the correct database before importing

---

## 📋 Step-by-Step Login Test

### Test Checklist:

1. ✅ XAMPP Apache is running
2. ✅ XAMPP MySQL is running
3. ✅ Database `sms_api_manager` exists
4. ✅ Table `admin_users` exists
5. ✅ Admin user exists in table
6. ✅ Admin user status = 'active'
7. ✅ Password hash is correct
8. ✅ Login page loads: `http://localhost/soft_wp_api_manager/login.php`

---

## 🔐 Password Hash Information

The correct password hash for **"admin123"** is:
```
$2y$10$8K1p/a0dL3LKznjr5.B.C.vUKx8F5RxPF5P3bI5xXPPJLJqQJfDuS
```

This uses PHP's `password_hash()` function with bcrypt (PASSWORD_DEFAULT).

**Common Mistake:** Using wrong hash or plain text password in database.

---

## 💡 Create Your Own Password Hash

If you want to use a different password, use this PHP code:

```php
<?php
$password = "your_new_password";
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password Hash: " . $hash;
?>
```

Save as `generate_hash.php`, run in browser, copy the hash, and update database.

---

## 🛠️ Manual Database Check

### Verify admin_users table structure:

```sql
DESCRIBE admin_users;
```

Should show:
- id (INT)
- username (VARCHAR)
- password (VARCHAR 255)
- full_name (VARCHAR)
- email (VARCHAR)
- status (ENUM)
- created_at (DATETIME)
- updated_at (DATETIME)

### Check admin user data:

```sql
SELECT * FROM admin_users WHERE username = 'admin';
```

Should return one row with:
- username: admin
- status: active
- password: (long hash starting with $2y$10$)

---

## 📞 Still Having Issues?

### Enable Debug Mode:

Add to top of `login.php`:

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
```

This will show any PHP errors that might be preventing login.

### Check PHP Error Logs:

Location: `C:\xampp\php\logs\php_error_log`

Look for any errors related to database connection or password verification.

### Test Database Connection:

Create file `test_db.php`:

```php
<?php
require_once 'config/db_connect.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Database connected successfully!<br>";
    
    $result = $conn->query("SELECT * FROM admin_users WHERE username = 'admin'");
    if ($result && $result->num_rows > 0) {
        echo "Admin user found!<br>";
        $user = $result->fetch_assoc();
        echo "Username: " . $user['username'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        echo "Password hash exists: " . (strlen($user['password']) > 0 ? 'YES' : 'NO');
    } else {
        echo "Admin user NOT found!";
    }
}
?>
```

Open: `http://localhost/soft_wp_api_manager/test_db.php`

---

## ✅ Success Checklist

After fixing, you should be able to:

- ✅ Open `http://localhost/soft_wp_api_manager/login.php`
- ✅ Enter username: `admin`
- ✅ Enter password: `admin123`
- ✅ Click Login
- ✅ Redirect to Dashboard (`index.php`)
- ✅ See "Welcome, System Administrator" in top right

---

## 🔒 Security Reminder

After fixing login:

1. ✅ Change default password from admin123
2. ✅ Delete `reset_password.php` if you used it
3. ✅ Delete `test_db.php` if you created it
4. ✅ Delete `generate_hash.php` if you created it
5. ✅ Delete `fix_admin_password.sql` after use

---

## 📝 Summary of Files Created for Troubleshooting

1. **reset_password.php** - Web interface to reset passwords
2. **fix_admin_password.sql** - SQL script to fix password
3. **TROUBLESHOOTING.md** - This file

---

**Need more help?** Check the error messages and follow the steps above carefully.
