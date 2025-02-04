<?php

namespace GrinWay\Telegram\Bot\Command;

use GrinWay\Telegram\GrinWayTelegramBundle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * https://core.telegram.org/bots/api#setwebhook
 */
class TelegramSetWebhookCommand extends AbstractTelegramWebhookCommand
{
    public const NAME = GrinWayTelegramBundle::COMMAND_PREFIX . 'bot:set_webhook';
    public const HELP = 'Sets the telegram bot webhook';
    public const DESCRIPTION = self::HELP;

    protected function assignDopQuery(InputInterface $input, OutputInterface $output, array &$dopQuery): void
    {
        if (null !== $dropPendingUpdates = $input->getOption('drop-pending-updates')) {
            $dopQuery['drop_pending_updates'] = (bool)$dropPendingUpdates;
        }
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->addOption(
                'drop-pending-updates',
                'd',
                InputOption::VALUE_NONE,
                'Drop all pending updates',
            );
    }
}
