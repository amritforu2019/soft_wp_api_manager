<?php
/**
 * Cron Job - Process Pending Messages
 * Run this script every minute via cron/task scheduler
 * Command: php cron_send.php
 */

require_once 'config/db_connect.php';
require_once 'api/ApiFactory.php';

echo "=== SMS/WhatsApp Cron Job Started at " . date('Y-m-d H:i:s') . " ===\n";

// Check if cron is enabled
$cron_status = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key='cron_status'")->fetch_assoc();
if ($cron_status['setting_value'] != 'enabled') {
    echo "Cron is disabled in system settings.\n";
    exit;
}

// Get messages per cron setting
$messages_limit = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key='messages_per_cron'")->fetch_assoc();
$limit = intval($messages_limit['setting_value']);

// Fetch pending messages with priority
$query = "
    SELECT m.*, c.api_key, c.status as customer_status, c.credit_limit, c.used_credits,
           c.customer_api_key, c.use_own_api
    FROM messages m 
    JOIN customers c ON m.customer_id = c.id 
    WHERE m.status = 'pending' 
    ORDER BY 
        CASE m.priority 
            WHEN 'high' THEN 1 
            WHEN 'normal' THEN 2 
            WHEN 'low' THEN 3 
        END,
        m.created_at ASC 
    LIMIT $limit
";

$messages = $conn->query($query);
$processed = 0;
$sent = 0;
$failed = 0;

echo "Found " . $messages->num_rows . " pending messages to process.\n\n";

while ($message = $messages->fetch_assoc()) {
    $message_id = $message['id'];
    $customer_id = $message['customer_id'];
    
    echo "Processing Message ID: $message_id\n";
    
    // Validate customer status
    if ($message['customer_status'] != 'active') {
        echo "  - Customer is " . $message['customer_status'] . ". Setting message to HOLD.\n";
        $conn->query("UPDATE messages SET status='hold', error_message='Customer account is " . $message['customer_status'] . "' WHERE id=$message_id");
        $failed++;
        continue;
    }
    
    // Check credit limit
    if ($message['used_credits'] >= $message['credit_limit']) {
        echo "  - Credit limit exceeded. Setting message to HOLD.\n";
        $conn->query("UPDATE messages SET status='hold', error_message='Credit limit exceeded' WHERE id=$message_id");
        $failed++;
        continue;
    }
    
    // Update status to processing
    $conn->query("UPDATE messages SET status='processing' WHERE id=$message_id");
    
    try {
        // Get API handler
        $apiHandler = ApiFactory::getApiHandler($message['api_type']);
        
        // Determine which API key to use
        $useCustomerApi = ($message['use_own_api'] == 1 && !empty($message['customer_api_key']));
        $apiKeyToUse = $useCustomerApi ? $message['customer_api_key'] : null;
        
        // Send message
        echo "  - Sending via " . strtoupper($message['api_type']) . " (" . ($useCustomerApi ? "Customer's API" : "System Default") . ")...\n";
        $result = $apiHandler->sendMessage(
            $message['mobile_number'],
            $message['message_text'],
            $apiKeyToUse,
            $useCustomerApi
        );
        
        // Log API request/response
        $stmt = $conn->prepare("INSERT INTO api_logs (message_id, api_type, request_url, request_data, response_data, status_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $message_id, $message['api_type'], $result['request_url'], $result['request_data'], $result['response'], $result['status_code']);
        $stmt->execute();
        $stmt->close();
        
        if ($result['success']) {
            // Update message status to sent
            $sent_at = date('Y-m-d H:i:s');
            $conn->query("UPDATE messages SET status='sent', sent_at='$sent_at' WHERE id=$message_id");
            
            // Increment customer used credits
            $conn->query("UPDATE customers SET used_credits = used_credits + 1 WHERE id=$customer_id");
            
            // Create delivery report entry
            $conn->query("INSERT INTO delivery_reports (message_id, delivery_status) VALUES ($message_id, 'waiting')");
            
            echo "  - SUCCESS! Message sent.\n";
            $sent++;
        } else {
            // Update message status to failed
            $error_msg = $conn->real_escape_string($result['error'] ? $result['error'] : 'API returned error');
            $retry_count = $message['retry_count'] + 1;
            
            if ($retry_count >= 3) {
                $conn->query("UPDATE messages SET status='failed', retry_count=$retry_count, error_message='$error_msg' WHERE id=$message_id");
                echo "  - FAILED after 3 retries. Error: $error_msg\n";
            } else {
                $conn->query("UPDATE messages SET status='pending', retry_count=$retry_count, error_message='$error_msg' WHERE id=$message_id");
                echo "  - FAILED (retry $retry_count/3). Will retry. Error: $error_msg\n";
            }
            $failed++;
        }
        
    } catch (Exception $e) {
        echo "  - EXCEPTION: " . $e->getMessage() . "\n";
        $error_msg = $conn->real_escape_string($e->getMessage());
        $conn->query("UPDATE messages SET status='failed', error_message='$error_msg' WHERE id=$message_id");
        $failed++;
    }
    
    $processed++;
    echo "\n";
    
    // Small delay to avoid API rate limiting
    usleep(100000); // 0.1 second
}

echo "=== Cron Job Completed ===\n";
echo "Processed: $processed\n";
echo "Sent: $sent\n";
echo "Failed/Hold: $failed\n";
echo "Finished at " . date('Y-m-d H:i:s') . "\n";

$conn->close();
?>
