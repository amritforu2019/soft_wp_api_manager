<?php
require_once 'Type1Api.php';
require_once 'Type2Api.php';
require_once 'Type3Api.php';

class ApiFactory {
    public static function getApiHandler($type) {
        switch ($type) {
            case 'type1':
                return new Type1Api();
            case 'type2':
                return new Type2Api();
            case 'type3':
                return new Type3Api();
            default:
                throw new Exception("Invalid API type: $type");
        }
    }
}
?>
