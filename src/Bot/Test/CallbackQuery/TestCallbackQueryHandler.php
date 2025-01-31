<?php

namespace GrinWay\Telegram\Bot\Test\CallbackQuery;

use GrinWay\Telegram\Bot\Handler\Topic\CallbackQuery\AbstractCallbackQueryHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

class TestCallbackQueryHandler extends AbstractCallbackQueryHandler
{
    public const SUBJECT = 'TEST CALLBACK QUERY HANDLER';

    protected function doCallbackQueryHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);
        return true;
    }
}
