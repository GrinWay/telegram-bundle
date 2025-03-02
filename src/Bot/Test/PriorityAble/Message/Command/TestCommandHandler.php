<?php

namespace GrinWay\Telegram\Bot\Test\PriorityAble\Message\Command;

use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Command\AbstractCommandHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestCommandHandler extends AbstractCommandHandler
{
    public const COMMAND_NAME = 'test';
    public const SUBJECT = 'TEST (private chat short +|private chat full +|chat full +|chat short -|) COMMAND HANDLER';

    protected function doCommandHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);
        return true;
    }

    protected static function getCommandName(): string
    {
        return self::COMMAND_NAME;
    }

    protected function getCommandDescription(): string
    {
        return 'test';
    }
}
