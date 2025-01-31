<?php

namespace GrinWay\Telegram\Bot\Command;

use GrinWay\Telegram\GrinWayTelegramBundle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * https://core.telegram.org/bots/api#setwebhook
 */
class TelegramRemoveWebhookCommand extends AbstractTelegramWebhookCommand
{
    public const NAME = GrinWayTelegramBundle::COMMAND_PREFIX . 'bot:remove_webhook';
    public const HELP = 'Removes telegram bot webhook';
    public const DESCRIPTION = self::HELP;

    protected function assignDopQuery(InputInterface $input, OutputInterface $output, array &$dopQuery): void
    {
    }
}
