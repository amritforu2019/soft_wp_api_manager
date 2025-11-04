<?php
$page_title = "System Settings";
require_once 'config/db_connect.php';
require_once 'includes/header.php';

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $default_api = sanitize_input($_POST['default_api']);
    $cron_status = sanitize_input($_POST['cron_status']);
    $credit_alert_limit = intval($_POST['credit_alert_limit']);
    $messages_per_cron = intval($_POST['messages_per_cron']);
    
    // Update settings
    $conn->query("UPDATE system_settings SET setting_value='$default_api' WHERE setting_key='default_api'");
    $conn->query("UPDATE system_settings SET setting_value='$cron_status' WHERE setting_key='cron_status'");
    $conn->query("UPDATE system_settings SET setting_value='$credit_alert_limit' WHERE setting_key='credit_alert_limit'");
    $conn->query("UPDATE system_settings SET setting_value='$messages_per_cron' WHERE setting_key='messages_per_cron'");
    
    $success = 'Settings updated successfully!';
}

// Get current settings
$settings = [];
$result = $conn->query("SELECT * FROM system_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear"></i> System Settings
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="default_api" class="form-label">Default API Type</label>
                            <select class="form-select" id="default_api" name="default_api">
                                <option value="type1" <?php echo ($settings['default_api'] ?? '') == 'type1' ? 'selected' : ''; ?>>Type 1</option>
                                <option value="type2" <?php echo ($settings['default_api'] ?? '') == 'type2' ? 'selected' : ''; ?>>Type 2</option>
                                <option value="type3" <?php echo ($settings['default_api'] ?? '') == 'type3' ? 'selected' : ''; ?>>Type 3</option>
                            </select>
                            <small class="text-muted">Default API to use for new customers</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cron_status" class="form-label">Cron Job Status</label>
                            <select class="form-select" id="cron_status" name="cron_status">
                                <option value="enabled" <?php echo ($settings['cron_status'] ?? '') == 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="disabled" <?php echo ($settings['cron_status'] ?? '') == 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                            <small class="text-muted">Enable or disable message processing cron job</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="credit_alert_limit" class="form-label">Credit Alert Limit</label>
                            <input type="number" class="form-control" id="credit_alert_limit" name="credit_alert_limit" 
                                   value="<?php echo $settings['credit_alert_limit'] ?? 50; ?>" min="1">
                            <small class="text-muted">Alert when credits remaining are below this value</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="messages_per_cron" class="form-label">Messages Per Cron Run</label>
                            <input type="number" class="form-control" id="messages_per_cron" name="messages_per_cron" 
                                   value="<?php echo $settings['messages_per_cron'] ?? 50; ?>" min="1" max="1000">
                            <small class="text-muted">Maximum number of messages to process in each cron run</small>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Cron Setup Instructions -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-info-circle"></i> Cron Job Setup Instructions
                </div>
                <div class="card-body">
                    <h6>For Windows (Task Scheduler):</h6>
                    <ol>
                        <li>Open Task Scheduler</li>
                        <li>Create a new Basic Task</li>
                        <li>Set trigger to "Daily" and repeat every 1 minute</li>
                        <li>Action: Start a program</li>
                        <li>Program: <code>C:\xampp\php\php.exe</code></li>
                        <li>Arguments: <code>C:\xampp\htdocs\soft_wp_api_manager\cron_send.php</code></li>
                    </ol>
                    
                    <h6 class="mt-3">For Linux (Crontab):</h6>
                    <pre class="bg-light p-2">* * * * * /usr/bin/php /path/to/soft_wp_api_manager/cron_send.php >> /var/log/cron_send.log 2>&1</pre>
                    
                    <h6 class="mt-3">Manual Test:</h6>
                    <pre class="bg-light p-2">php cron_send.php</pre>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle"></i> System Information
                </div>
                <div class="card-body">
                    <h6>Database Info</h6>
                    <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                    <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                    
                    <hr>
                    
                    <h6>Statistics</h6>
                    <?php
                    $total_customers = $conn->query("SELECT COUNT(*) as count FROM customers")->fetch_assoc()['count'];
                    $total_messages = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];
                    $pending_messages = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status='pending'")->fetch_assoc()['count'];
                    ?>
                    <p><strong>Total Customers:</strong> <?php echo $total_customers; ?></p>
                    <p><strong>Total Messages:</strong> <?php echo $total_messages; ?></p>
                    <p><strong>Pending Messages:</strong> <?php echo $pending_messages; ?></p>
                    
                    <hr>
                    
                    <h6>PHP Version</h6>
                    <p><?php echo phpversion(); ?></p>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <i class="bi bi-exclamation-triangle"></i> API Configuration
                </div>
                <div class="card-body">
                    <p class="mb-2">Remember to configure your API keys in:</p>
                    <ul class="mb-0">
                        <li><code>api/Type1Api.php</code></li>
                        <li><code>api/Type2Api.php</code></li>
                        <li><code>api/Type3Api.php</code></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
