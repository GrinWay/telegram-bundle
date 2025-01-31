<?php

namespace GrinWay\Telegram\Bot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * https://core.telegram.org/bots/api#setwebhook
 */
abstract class AbstractTelegramWebhookCommand extends Command
{
    public function __construct(
        protected readonly ServiceLocator $serviceLocator,
        ?string                           $name = null,
    )
    {
        parent::__construct(name: $name);
    }

    abstract protected function assignDopQuery(InputInterface $input, OutputInterface $output, array &$dopQuery): void;

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        $telegram = $this->serviceLocator->get('telegram');
        $pa = $this->serviceLocator->get('pa');

        $dopQuery = [];
        $this->assignDopQuery($input, $output, $dopQuery);

        $dumpActionInfo = '';
        if ($this instanceof TelegramRemoveWebhookCommand) {
            $responseContent = $telegram->removeWebhook($dopQuery);
            $dumpActionInfo = 'REMOVED';
        } elseif ($this instanceof TelegramSetWebhookCommand) {
            $responseContent = $telegram->setWebhook($dopQuery);
            $dumpActionInfo = 'SET';
        } else {
            throw new \LogicException('There is no appropriate action');
        }

        $ok = $pa->getValue($responseContent, '[ok]');
        $webhookUri = $telegram->getWebhookUri();

        if (true === $ok) {
            $description = $pa->getValue($responseContent, '[description]');

            $message = \sprintf(
                'Telegram webhook: "%s"|Status: [%s]|Description: [%s]',
                $webhookUri,
                'ok',
                $description,
            );
        } else {
            $errorCode = $pa->getValue($responseContent, '[error_code]');
            $parameters = $pa->getValue($responseContent, '[parameters]');

            if ($this->serviceLocator->has($key = 'logger')) {
                $this->serviceLocator->get($key)->error('Error', ['error_code' => $errorCode, 'parameters' => $parameters]);
            }

            $message = \sprintf(
                'Telegram webhook: "%s"|Status: [%s]|See error info in the telegram logs',
                $webhookUri,
                'err',
            );
        }

        $output->writeln(
            \explode('|', \sprintf('%s||%s', $dumpActionInfo, $message)),
        );

        return Command::SUCCESS;
    }
}
