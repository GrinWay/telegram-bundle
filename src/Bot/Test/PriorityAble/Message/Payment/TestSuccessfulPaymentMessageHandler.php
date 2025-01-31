<?php

namespace GrinWay\Telegram\Bot\Test\PriorityAble\Message\Payment;

use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Payment\AbstractSuccessfulPaymentMessageHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestSuccessfulPaymentMessageHandler extends AbstractSuccessfulPaymentMessageHandler
{
    protected function doSuccessfulPaymentHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        return false;
    }
}
