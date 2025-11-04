<?php
require_once 'SmsApiInterface.php';

class Type1Api implements SmsApiInterface {
    private $apiUrl = 'https://wasenderapi.com/api/send-message';
    private $apiKey = '2b13cf3a28774eaedce2063ccffcbac1f123c868bf68a140d33fa6ec1f12b8a7';
    
    public function sendMessage($mobile, $message, $customerApiKey = null, $useCustomerApi = false) {
        // Use customer's own API key if provided, otherwise use system default
        $finalApiKey = ($useCustomerApi && !empty($customerApiKey)) ? $customerApiKey : $this->apiKey;
        
        // Prepare API request payload (JSON format for WaSender API)
        $payload = [
            'to' => '+91' . $mobile,
            'text' => $message . ' --- '
        ];
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $finalApiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 30
        ]);
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Check if request was successful
        $isSuccess = ($response && !$error) ? true : false;
        
        // Return response
        return [
            'success' => $isSuccess,
            'response' => $response,
            'status_code' => $httpCode,
            'error' => $error,
            'request_url' => $this->apiUrl,
            'request_data' => json_encode($payload),
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
