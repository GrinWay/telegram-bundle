<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\Message;

use GrinWay\Telegram\Bot\Contract\Topic\MessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\AbstractTopicHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * When a telegram user sent a message
 *
 * !CAUTION! It's a default handler originally with the LOWEST priority for message update field
 * because of this if you have defined private chat message handler it will support sent message faster
 * it means no chances to be handled here if private chat message was sent
 */
abstract class AbstractMessageHandler extends AbstractTopicHandler implements MessageHandlerInterface
{
    abstract protected function doDefaultHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    public function supports(mixed $fieldValue): bool
    {
        return null !== $this->chatId;
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        return $this->doDefaultHandle($chatMessage, $telegramOptions, $fieldValue);
    }
}
