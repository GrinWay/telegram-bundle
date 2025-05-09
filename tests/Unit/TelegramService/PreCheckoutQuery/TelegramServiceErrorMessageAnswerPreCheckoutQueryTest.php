<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\PreCheckoutQuery;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Test\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'answerPreCheckoutQuery')]
class TelegramServiceErrorMessageAnswerPreCheckoutQueryTest extends AbstractTelegramServiceTestCase
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
                'ok' => false,
                'error_message' => 'TEST ERROR',
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
            preCheckoutQueryIsValid: 'TEST ERROR',
            throw: $throw,
        );
    }
}
