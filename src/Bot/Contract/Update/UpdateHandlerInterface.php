<?php

namespace GrinWay\Telegram\Bot\Contract\Update;

use GrinWay\Telegram\GrinWayTelegramBundle;

/**
 * https://core.telegram.org/bots/api#update
 */
interface UpdateHandlerInterface
{
    public const TAG = GrinWayTelegramBundle::BUNDLE_PREFIX . 'bot.update_handler';

    public function isOptional(): bool;

    /**
     * If update filed existence depends on other keys existence use it
     *
     * @param mixed $fieldValue
     * @return bool
     */
    public function supports(mixed $fieldValue): bool;

    public static function updateField(): string;

    public function handle(mixed $fieldValue): void;
}
