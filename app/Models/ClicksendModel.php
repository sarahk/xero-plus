<?php

namespace App\Models;
require_once('vendor/autoload.php');

class ClicksendModel
{
    private string $username = 'sarah@cabinking.nz';
    private string $api_key = 'A786C74D-2859-9392-5C40-E9FA32CD7B95';


    function sendSMS($to, $message): void
    {
        var_dump($to);
        var_dump($message);
        $config = ClickSend\Configuration::getDefaultConfiguration()
            ->setUsername($this->username)
            ->setPassword($this->api_key);

        $apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(), $config);

        $msg = new \ClickSend\Model\SmsMessage();
        $msg->setSource("CKM");
        $msg->setBody($message);
        $msg->setTo($to);

        //$msg->set

        $sms_messages = new \ClickSend\Model\SmsMessageCollection();
        $sms_messages->setMessages([$msg]);

        try {
            $result = $apiInstance->smsSendPost($sms_messages);
            print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling SMSApi->smsSendPost: ', $e->getMessage(), PHP_EOL;
        }
    }
}
