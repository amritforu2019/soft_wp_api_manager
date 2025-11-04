<?php
require_once 'SmsApiInterface.php';

class Type1Api implements SmsApiInterface {
    private $apiUrl = 'https://api-type1.example.com/send';
    private $apiKey = 'YOUR_TYPE1_API_KEY';
    
    public function sendMessage($mobile, $message, $customerApiKey = null, $useCustomerApi = false) {
        // Use customer's own API key if provided, otherwise use system default
        $finalApiKey = ($useCustomerApi && !empty($customerApiKey)) ? $customerApiKey : $this->apiKey;
        
        // Prepare API request
        $postData = [
            'api_key' => $finalApiKey,
            'mobile' => $mobile,
            'message' => $message
        ];
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Return response
        return [
            'success' => ($httpCode == 200),
            'response' => $response,
            'status_code' => $httpCode,
            'error' => $error,
            'request_url' => $this->apiUrl,
            'request_data' => json_encode($postData),
            'api_used' => $useCustomerApi ? 'customer_api' : 'system_default'
        ];
    }
    
    public function checkDelivery($messageId) {
        // Implement delivery status check
        // This would call the API's delivery report endpoint
        
        return [
            'status' => 'delivered',
            'message_id' => $messageId
        ];
    }
}
?>
