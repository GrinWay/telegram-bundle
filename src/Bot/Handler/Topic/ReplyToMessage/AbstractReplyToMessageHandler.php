<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\ReplyToMessage;

use GrinWay\Telegram\Bot\Contract\Topic\ReplyToMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\AbstractTopicHandler;

/**
 * When user answered with "reply" ability
 */
abstract class AbstractReplyToMessageHandler extends AbstractTopicHandler implements ReplyToMessageHandlerInterface
{
    protected ?string $replyText = null;
    protected ?string $replyToMessageText = null;

    public function supports(mixed $fieldValue): bool
    {
        return null !== $this->chatId && null !== ($this->replyText = $this->pa->getValue($fieldValue, '[text]')) && null !== ($this->replyToMessageText = $this->pa->getValue($fieldValue, '[reply_to_message][text]'));
    }
}
