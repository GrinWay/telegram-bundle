<?php

namespace GrinWay\Telegram\Bot\Contract\Topic;

interface LowPriorityCommandMessageHandlerInterface
{
    public const TAG = 'grinway_telegram.bot.message_handler';
    public const PRIORITY = 30;
}
