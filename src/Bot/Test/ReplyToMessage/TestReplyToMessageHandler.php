<?php

namespace GrinWay\Telegram\Bot\Test\ReplyToMessage;

use GrinWay\Telegram\Bot\Handler\Topic\ReplyToMessage\AbstractReplyToMessageHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestReplyToMessageHandler extends AbstractReplyToMessageHandler
{
    public const SUBJECT = 'TEST REPLY TO MESSAGE';

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);
        return true;
    }
}
