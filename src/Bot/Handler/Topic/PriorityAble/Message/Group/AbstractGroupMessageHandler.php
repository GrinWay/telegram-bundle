<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Group;

use GrinWay\Telegram\Bot\Contract\Topic\GroupMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\AbstractMessageTopicHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * When a message was sent to the group
 *
 * HAS A HIGHER PRIORITY than the AbstractMessageHandler
 */
abstract class AbstractGroupMessageHandler extends AbstractMessageTopicHandler implements GroupMessageHandlerInterface
{
    public function supports(mixed $fieldValue): bool
    {
        return parent::supports($fieldValue)
            && null !== $this->text;
    }

    abstract protected function doGroupMessageHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        return $this->doGroupMessageHandle($chatMessage, $telegramOptions, $fieldValue);
    }
}
