<?php

namespace App\Service\Handler;

use App\Service\Message\AlertMessage;
use App\Service\SmsService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler()]
class AlertMessageHandler
{
    public function __construct(private SmsService $smsService) {}
    public function __invoke(AlertMessage $message)
    {
        $this->smsService->sendMessage($message);
    }
}
