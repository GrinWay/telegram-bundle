<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Command;

use GrinWay\Telegram\Bot\Contract\Topic\CommandMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\AbstractMessageTopicHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * When a "/command" was sent
 * https://core.telegram.org/bots/features#commands
 */
abstract class AbstractCommandHandler extends AbstractMessageTopicHandler implements CommandMessageHandlerInterface
{
    public const COMMAND_NAME = '!CHANGE_ME!';

    abstract protected function doCommandHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    protected static function getCommandName(): string
    {
        return static::COMMAND_NAME;
    }

    public function supports(mixed $fieldValue): bool
    {
        return parent::supports($fieldValue) && $this->alwaysAcceptFullVersionButForPrivateChatAcceptShortOne($fieldValue);
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        return $this->doCommandHandle($chatMessage, $telegramOptions, $fieldValue);
    }

    protected function alwaysAcceptFullVersionButForPrivateChatAcceptShortOne(mixed $fieldValue): bool
    {
        return $this->acceptFullCommandVersion($fieldValue) || $this->acceptShortAndFullVersionOnlyIfPrivateChat($fieldValue);
    }

    protected function acceptFullCommandVersion(mixed $fieldValue): bool
    {
        return $this->text === \sprintf('/%s@%s', static::getCommandName(), $this->telegramBotName);
    }

    protected function acceptShortAndFullVersionOnlyIfPrivateChat(mixed $fieldValue): bool
    {
        return (
                $this->text === \sprintf('/%s', static::getCommandName())
                || $this->acceptFullCommandVersion($fieldValue)
            )
            && $this->pa->getValue($fieldValue, '[chat][id]') === $this->pa->getValue($fieldValue, '[from][id]');
    }
}
