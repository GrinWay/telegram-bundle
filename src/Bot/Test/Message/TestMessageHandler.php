<?php

namespace GrinWay\Telegram\Bot\Test\Message;

use GrinWay\Telegram\Bot\Handler\Topic\Message\AbstractMessageHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestMessageHandler extends AbstractMessageHandler
{
    public const SUBJECT = 'TEST MESSAGE HANDLER';

    protected function doDefaultHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);
        return true;
    }
}
