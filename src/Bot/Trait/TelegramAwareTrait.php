<?php

namespace GrinWay\Telegram\Bot\Trait;

use GrinWay\Telegram\GrinWayTelegramBundle;

trait TelegramAwareTrait
{
    protected function getUpdateHandlerKey(string $topic): string
    {
        $name = \sprintf(
            '%s%s',
            GrinWayTelegramBundle::BUNDLE_PREFIX,
            'bot.%s_handler',
        );
        return \sprintf($name, $topic);
    }
}
