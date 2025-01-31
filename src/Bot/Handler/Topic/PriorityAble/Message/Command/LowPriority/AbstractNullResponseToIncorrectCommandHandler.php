<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Command\LowPriority;

use GrinWay\Telegram\Bot\Contract\Topic\LowPriorityCommandMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\AbstractMessageTopicHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * Supports but not handles (null answer to incorrect command names)
 *
 * Null reaction on not supported command
 */
abstract class AbstractNullResponseToIncorrectCommandHandler extends AbstractMessageTopicHandler implements LowPriorityCommandMessageHandlerInterface
{
    public function supports(mixed $fieldValue): bool
    {
        return \is_string($this->text) && \str_starts_with($this->text, '/');
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        return false;
    }
}
