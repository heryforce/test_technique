<?php

namespace App\Service\Handler;

use App\Service\Message\SmsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class SmsServiceHandler
{
    public function __construct(private LoggerInterface $customLogger) {}
    public function __invoke(SmsService $message)
    {
        $this->customLogger->notice("Sending message to " . $message->getNumber() . " with content : " . $message->getContent());
    }
}
