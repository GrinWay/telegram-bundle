<?php

namespace GrinWay\Telegram\Bot\Test\PriorityAble\Message\Group;

use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Group\AbstractGroupMessageHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestGroupMessageHandler extends AbstractGroupMessageHandler
{
    public const SUBJECT = 'TEST GROUP MESSAGE';

    protected function doGroupMessageHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);
        return true;
    }
}
