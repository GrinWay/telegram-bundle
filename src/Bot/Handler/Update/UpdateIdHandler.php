<?php

namespace GrinWay\Telegram\Bot\Handler\Update;

/**
 * https://core.telegram.org/bots/api#update
 */
class UpdateIdHandler extends AbstractUpdateHandler
{
    public const UPDATE_FIELD = 'update_id';

    public function isOptional(): bool
    {
        return false;
    }
}
