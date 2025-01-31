<?php

namespace GrinWay\Telegram\Bot\Contract\Test;

use GrinWay\Telegram\GrinWayTelegramBundle;

interface TestBotInterface
{
    public const TAG = GrinWayTelegramBundle::BUNDLE_PREFIX . 'bot.test';
}
