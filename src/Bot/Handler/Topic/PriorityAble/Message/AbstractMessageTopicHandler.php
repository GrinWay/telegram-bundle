<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message;

use GrinWay\Telegram\Bot\Handler\Topic\AbstractTopicHandler;

/**
 * @internal
 *
 * For extending other priority able topic handlers of THIS bundle
 * Not for extending from client side
 */
abstract class AbstractMessageTopicHandler extends AbstractTopicHandler
{
    public function supports(mixed $fieldValue): bool
    {
        return null !== $this->chatId;
    }
}
