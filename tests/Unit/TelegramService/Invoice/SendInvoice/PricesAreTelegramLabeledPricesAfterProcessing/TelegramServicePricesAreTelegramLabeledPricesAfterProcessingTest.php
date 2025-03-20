<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\Invoice\SendInvoice\PricesAreTelegramLabeledPricesAfterProcessing;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Test\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'sendInvoice')]
class TelegramServicePricesAreTelegramLabeledPricesAfterProcessingTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected static $prices;

    protected function setUp(): void
    {
        parent::setUp();

        static::$prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice('label', '100'),
        );
    }

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'sendInvoice';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'chat_id' => 'TEST',
                'title' => 'title',
                'description' => 'description',
                'payload' => 'payload',
                'currency' => 'USD',
                'photo_url' => 'photo_url',
                'prices' => static::$prices->toArray(),
                'provider_token' => 'provider_token',
                'need_name' => true,
                'need_phone_number' => true,
                'need_email' => true,
                'need_shipping_address' => true,
                'send_phone_number_to_provider' => true,
                'send_email_to_provider' => true,
                'is_flexible' => true,
                'start_parameter' => 'start_parameter',
                'provider_data' => [
                    'testProviderData' => 'providerData',
                ],
                'testPrependJsonRequest' => 'prependJsonRequest',
                'testAppendJsonRequest' => 'appendJsonRequest',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        $response = $telegram->$method(
            chatId: 'TEST',
            title: 'title',
            description: 'description',
            prices: static::$prices,
            providerToken: 'provider_token',
            currency: 'USD',
            photoUri: 'photo_url',
            needName: true,
            needPhoneNumber: true,
            needEmail: true,
            needShippingAddress: true,
            sendPhoneNumberToProvider: true,
            sendEmailToProvider: true,
            isFlexible: true,
            payload: 'payload',
            startParameter: 'start_parameter',
            providerData: [
                'testProviderData' => 'providerData',
            ],
            prependJsonRequest: [
                'testPrependJsonRequest' => 'prependJsonRequest',
            ],
            appendJsonRequest: [
                'testAppendJsonRequest' => 'appendJsonRequest',
            ],
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: 'labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi',
            forceMakeHttpRequestToCurrencyApi: true,
            allowDopPriceIfLessThanLowestPossible: false,
            allowNonRemovableCache: false,
            retryOnRequestException: false,
            throw: $throw,
        );

        self::assertInstanceOf(TelegramLabeledPrices::class, static::$prices);

        return $response;
    }
}
