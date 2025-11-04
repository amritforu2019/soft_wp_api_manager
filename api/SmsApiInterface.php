<?php
// API Interface for all SMS/WhatsApp providers
interface SmsApiInterface {
    public function sendMessage($mobile, $message, $customerApiKey = null, $useCustomerApi = false);
    public function checkDelivery($messageId);
}
?>
