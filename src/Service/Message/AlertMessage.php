<?php

namespace App\Service\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class AlertMessage
{
    public function __construct(
        private string $content,
        private string $number
    ) {}

    public function getContent(): string
    {
        return $this->content;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
