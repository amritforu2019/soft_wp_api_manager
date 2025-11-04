<?php
require_once 'SmsApiInterface.php';

class Type3Api implements SmsApiInterface {
    private $apiUrl = 'https://api-type3.example.com/whatsapp/send';
    private $apiKey = 'YOUR_TYPE3_API_KEY';
    
    public function sendMessage($mobile, $message, $customerApiKey = null, $useCustomerApi = false) {
        // Use customer's own API key if provided, otherwise use system default
        $finalApiKey = ($useCustomerApi && !empty($customerApiKey)) ? $customerApiKey : $this->apiKey;
        
        // Prepare API request (Type 3 WhatsApp format)
        $postData = [
            'token' => $finalApiKey,
            'phone' => $mobile,
            'body' => $message
        ];
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Return response
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'response' => $response,
            'status_code' => $httpCode,
            'error' => $error,
            'request_url' => $this->apiUrl,
            'request_data' => json_encode($postData),
            'api_used' => $useCustomerApi ? 'customer_api' : 'system_default'
        ];
    }
    
    public function checkDelivery($messageId) {
        // Implement delivery status check for Type 3 API
        
        return [
            'status' => 'unknown',
            'message_id' => $messageId
        ];
    }
}
?>
