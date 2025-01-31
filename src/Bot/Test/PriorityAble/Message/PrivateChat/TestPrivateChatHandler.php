<?php

namespace GrinWay\Telegram\Bot\Test\PriorityAble\Message\PrivateChat;

use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\PrivateChat\AbstractPrivateChatHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestPrivateChatHandler extends AbstractPrivateChatHandler
{
    public const SUBJECT = 'TEST PRIVATE CHAT HANDLER';

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);
        return true;
    }
}
