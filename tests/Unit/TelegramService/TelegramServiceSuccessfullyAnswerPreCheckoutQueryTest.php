<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'answerPreCheckoutQuery')]
class TelegramServiceSuccessfullyAnswerPreCheckoutQueryTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'answerPreCheckoutQuery';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'pre_checkout_query_id' => 'TEST',
                'ok' => true,
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        /*
         * Depending on payload you decide this payment successful or not
         *
         * I decided payload is valid
         */
        return $telegram->$method(
            preCheckoutQueryId: 'TEST', // in the webhook handler you will get a real pre_checkout_query_id in the payload
            preCheckoutQueryIsValid: true,
            throw: $throw,
        );
    }
}
