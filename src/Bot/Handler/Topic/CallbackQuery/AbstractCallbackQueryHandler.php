<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\CallbackQuery;

use GrinWay\Telegram\Bot\Contract\Topic\CallbackQueryHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\AbstractTopicHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * When an inline callback button was pushed
 * https://core.telegram.org/bots/2-0-intro#callback-buttons
 */
abstract class AbstractCallbackQueryHandler extends AbstractTopicHandler implements CallbackQueryHandlerInterface
{
    protected string|int|null $callbackQueryId = null;

    abstract protected function doCallbackQueryHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    public function supports(mixed $fieldValue): bool
    {
        return true;
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $this->callbackQueryId = $this->pa->getValue($fieldValue, '[id]');

        if (null === $this->chatId || null === $this->callbackQueryId) {
            return false;
        }

        return $this->doCallbackQueryHandle($chatMessage, $telegramOptions, $fieldValue);
    }
}
