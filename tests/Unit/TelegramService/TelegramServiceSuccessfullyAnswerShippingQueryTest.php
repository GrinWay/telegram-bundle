<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'answerShippingQuery')]
class TelegramServiceSuccessfullyAnswerShippingQueryTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'answerShippingQuery';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'shipping_query_id' => 'TEST',
                'shipping_options' => [
                    'id' => 'id',
                    'title' => 'title',
                    'prices' => [
                        [
                            'label' => 'l',
                            'amount' => '000',
                        ],
                    ],
                ],
                'ok' => true,
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        /*
         * You will get "shipping_query_id" in your real webhook
         * when an invoice was created by these rules https://core.telegram.org/bots/api#sendinvoice
         *
         * webhook will be called by the Telegram Bot Api
         *
         * I decided payload is valid
         */
        return $telegram->$method(
            shippingQueryId: 'TEST',
            shippingOptions: [
                'id' => 'id',
                'title' => 'title',
                'prices' => [
                    [
                        'label' => 'l',
                        'amount' => '000',
                    ],
                ],
            ],
            shippingQueryIsValid: true,
            throw: $throw,
        );
    }
}
