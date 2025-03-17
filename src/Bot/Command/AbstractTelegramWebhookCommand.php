<?php

namespace GrinWay\Telegram\Bot\Command;

use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * https://core.telegram.org/bots/api#setwebhook
 */
abstract class AbstractTelegramWebhookCommand extends Command
{
    public const NAME = '!CHANGE ME!';
    public const HELP = '!CHANGE ME!';
    public const DESCRIPTION = '!CHANGE ME!';

    public function __construct(
        protected readonly ServiceLocator $serviceLocator,
        ?string                           $name = null,
    )
    {
        parent::__construct(name: $name);
    }

    abstract protected function assignPrependRequestOptions(InputInterface $input, OutputInterface $output, array &$prependRequestOptions): void;

    protected function configure()
    {
        parent::configure();

        $this
            ->setDescription(static::DESCRIPTION)
            ->setHelp(static::HELP)//
        ;
    }

    protected function execute(
        InputInterface  $input,
        OutputInterface $output,
    ): int
    {
        /** @var Telegram $telegram */
        $telegram = $this->serviceLocator->get('telegram');
        $pa = $this->serviceLocator->get('pa');

        $prependRequestOptions = [];
        $this->assignPrependRequestOptions($input, $output, $prependRequestOptions);

        if ($this instanceof TelegramRemoveWebhookCommand) {
            $responseContent = $telegram->removeWebhook(prependRequestOptions: $prependRequestOptions);
            $dumpActionInfo = 'REMOVED';
        } elseif ($this instanceof TelegramSetWebhookCommand) {
            $responseContent = $telegram->setWebhook(prependRequestOptions: $prependRequestOptions);
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
