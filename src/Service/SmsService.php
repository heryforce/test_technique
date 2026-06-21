<?php

namespace App\Service;

use App\Service\Message\AlertMessage;
use Psr\Log\LoggerInterface;

class SmsService
{
    public function __construct(private LoggerInterface $customLogger) {}
    public function sendMessage(AlertMessage $message)
    {
        $this->customLogger->notice(new \DateTime()->format('d/m/Y H:i:s') . " : sending message to " . $message->getNumber() . " with content : " . $message->getContent());
    }
}
