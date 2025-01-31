<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\PrivateChat;

use GrinWay\Telegram\Bot\Contract\Topic\PrivateChatMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\AbstractMessageTopicHandler;

/**
 * When a message sent exactly from private chat HAS A HIGHER PRIORITY than the AbstractMessageHandler
 */
abstract class AbstractPrivateChatHandler extends AbstractMessageTopicHandler implements PrivateChatMessageHandlerInterface
{
    public function supports(mixed $fieldValue): bool
    {
        return parent::supports($fieldValue)
            && null !== $this->text
            && $this->pa->getValue($fieldValue, '[chat][id]') === $this->pa->getValue($fieldValue, '[from][id]');
    }
}
