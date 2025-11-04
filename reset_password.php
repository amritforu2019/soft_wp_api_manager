<?php
/**
 * Admin Password Reset Utility
 * This script helps you reset or create admin passwords
 * 
 * USAGE: Run this file in your browser once, then DELETE it for security
 * URL: http://localhost/soft_wp_api_manager/reset_password.php
 */

require_once 'config/db_connect.php';

$message = '';
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($new_password)) {
        $message = 'Username and password are required';
    } elseif ($new_password !== $confirm_password) {
        $message = 'Passwords do not match';
    } else {
        // Hash the password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing user
            $stmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $hashed_password, $username);
            if ($stmt->execute()) {
                $success = true;
                $message = "Password updated successfully for user: $username";
            } else {
                $message = "Error updating password: " . $stmt->error;
            }
        } else {
            // Create new user
            $full_name = sanitize_input($_POST['full_name'] ?? $username);
            $email = sanitize_input($_POST['email'] ?? '');
            
            $stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $email);
            if ($stmt->execute()) {
                $success = true;
                $message = "New admin user created successfully: $username";
            } else {
                $message = "Error creating user: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// Display current admin users
$users = $conn->query("SELECT id, username, full_name, email, status, created_at FROM admin_users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 50px 0; }
        .card { box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .danger-zone { background: #fff3cd; border: 2px solid #ffc107; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                
                <!-- Warning -->
                <div class="alert alert-danger shadow">
                    <h4>⚠️ SECURITY WARNING</h4>
                    <p class="mb-0"><strong>Delete this file immediately after use!</strong><br>
                    This script can create or reset admin passwords and should not be accessible in production.</p>
                </div>

                <!-- Reset Form -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🔑 Reset/Create Admin Password</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> alert-dismissible fade show">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Username *</label>
                                <input type="text" name="username" class="form-control" required 
                                       placeholder="admin" value="admin">
                                <small class="text-muted">If exists, password will be reset. If not, new user will be created.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">New Password *</label>
                                    <input type="password" name="new_password" class="form-control" required 
                                           placeholder="Enter new password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <input type="password" name="confirm_password" class="form-control" required 
                                           placeholder="Confirm password">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Full Name (for new users)</label>
                                <input type="text" name="full_name" class="form-control" 
                                       placeholder="System Administrator">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email (for new users)</label>
                                <input type="email" name="email" class="form-control" 
                                       placeholder="admin@example.com">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Reset/Create Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Current Users -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">👥 Current Admin Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Quick Fix -->
                <div class="card mt-4 danger-zone">
                    <h5>🚀 Quick Fix for Default Login</h5>
                    <p>To reset the default admin password to <code>admin123</code>, use these values:</p>
                    <ul>
                        <li><strong>Username:</strong> admin</li>
                        <li><strong>Password:</strong> admin123</li>
                        <li><strong>Confirm:</strong> admin123</li>
                    </ul>
                    <p class="mb-0"><small>Then click "Reset/Create Password" button above.</small></p>
                </div>

                <!-- Delete Instructions -->
                <div class="alert alert-warning mt-4">
                    <h5>🗑️ After You're Done:</h5>
                    <ol class="mb-0">
                        <li>Test login at: <a href="login.php" target="_blank">login.php</a></li>
                        <li><strong>DELETE this file:</strong> <code>reset_password.php</code></li>
                        <li>Never leave this file on a production server!</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
