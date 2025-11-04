<?php
/**
 * Customer API Endpoint - Send Message
 * Customers POST to this endpoint to queue messages
 */

header('Content-Type: application/json');
require_once '../config/db_connect.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Response array
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

// Validate required fields
if (!isset($data['api_key']) || !isset($data['mobile_number']) || !isset($data['message_text'])) {
    $response['message'] = 'Missing required fields: api_key, mobile_number, message_text';
    echo json_encode($response);
    exit;
}

$api_key = sanitize_input($data['api_key']);
$mobile_number = sanitize_input($data['mobile_number']);
$message_text = sanitize_input($data['message_text']);
$priority = isset($data['priority']) ? sanitize_input($data['priority']) : 'normal';

// Validate API key and get customer
$stmt = $conn->prepare("SELECT id, customer_name, api_type, status, credit_limit, used_credits FROM customers WHERE api_key = ? LIMIT 1");
$stmt->bind_param("s", $api_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $response['message'] = 'Invalid API key';
    echo json_encode($response);
    exit;
}

$customer = $result->fetch_assoc();
$stmt->close();

// Check customer status
if ($customer['status'] != 'active') {
    $response['message'] = 'Account is ' . $customer['status'] . '. Please contact support.';
    echo json_encode($response);
    exit;
}

// Check credit limit
if ($customer['used_credits'] >= $customer['credit_limit']) {
    $response['message'] = 'Credit limit exceeded. Please recharge your account.';
    echo json_encode($response);
    exit;
}

// Validate priority
if (!in_array($priority, ['high', 'normal', 'low'])) {
    $priority = 'normal';
}

// Insert message into queue
$stmt = $conn->prepare("INSERT INTO messages (customer_id, mobile_number, message_text, api_type, status, priority) VALUES (?, ?, ?, ?, 'pending', ?)");
$status = 'pending';
$stmt->bind_param("issss", $customer['id'], $mobile_number, $message_text, $customer['api_type'], $priority);

if ($stmt->execute()) {
    $message_id = $stmt->insert_id;
    
    $response['success'] = true;
    $response['message'] = 'Message queued successfully';
    $response['data'] = [
        'message_id' => $message_id,
        'customer_name' => $customer['customer_name'],
        'mobile_number' => $mobile_number,
        'status' => 'pending',
        'priority' => $priority,
        'credits_remaining' => ($customer['credit_limit'] - $customer['used_credits'] - 1)
    ];
} else {
    $response['message'] = 'Failed to queue message: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response, JSON_PRETTY_PRINT);
?>
