<?php
require_once 'SmsApiInterface.php';

class Type2Api implements SmsApiInterface {
    private $apiUrl = 'https://api-type2.example.com/v1/sms';
    private $apiKey = 'YOUR_TYPE2_API_KEY';
    
    public function sendMessage($mobile, $message, $customerApiKey = null, $useCustomerApi = false) {
        // Use customer's own API key if provided, otherwise use system default
        $finalApiKey = ($useCustomerApi && !empty($customerApiKey)) ? $customerApiKey : $this->apiKey;
        
        // Prepare API request (Type 2 format)
        $postData = json_encode([
            'apikey' => $finalApiKey,
            'to' => $mobile,
            'text' => $message
        ]);
        
        // Initialize cURL with JSON
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Return response
        return [
            'success' => ($httpCode == 200 || $httpCode == 201),
            'response' => $response,
            'status_code' => $httpCode,
            'error' => $error,
            'request_url' => $this->apiUrl,
            'request_data' => $postData,
            'api_used' => $useCustomerApi ? 'customer_api' : 'system_default'
        ];
    }
    
    public function checkDelivery($messageId) {
        // Implement delivery status check for Type 2 API
        
        return [
            'status' => 'waiting',
            'message_id' => $messageId
        ];
    }
}
?>
