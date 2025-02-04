<?php

namespace GrinWay\Telegram\Bot\Contract\Topic;

/**
 * https://core.telegram.org/bots/api#update
 */
interface TopicHandlerInterface
{
    public const TOPIC_HANDLER_TAG = 'grinway_telegram.bot.handler';

    public function beforeSupports(mixed $fieldValue): static;

    /**
     * @param mixed $fieldValue
     * @return bool If true it stops looking for the other topic handlers
     *     If you return false it means you want to go on looking for the other possible handlers
     */
    public function supports(mixed $fieldValue): bool;

    public function handle(mixed $fieldValue): void;
}
