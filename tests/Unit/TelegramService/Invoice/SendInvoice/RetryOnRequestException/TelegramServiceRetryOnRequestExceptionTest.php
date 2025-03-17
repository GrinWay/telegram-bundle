<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\Invoice\SendInvoice\RetryOnRequestException;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(Telegram::class, 'sendInvoice')]
class TelegramServiceRetryOnRequestExceptionTest extends AbstractTelegramServiceTestCase
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

    protected function processRequestGrinWayTelegramHttpClientWillThrowMock(
        MockObject $grinwayTelegramClientMock,
        string     $telegramMethod,
    ): InvocationMocker
    {
        return $grinwayTelegramClientMock
            ->expects(self::exactly(1 + Telegram::INVOICE_DOP_START_NUMBER_RETRY_ATTEMPTS))
            ->method('request')
            ->with(
                self::identicalTo($this->getMethodMethodGrinwayTelegramClient()),
                self::identicalTo($telegramMethod),
                self::equalTo($this->getRequestJsonGrinWayHttpClientTestAware()),
            );
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        $prices = static::$prices;
        $prices[] = new TelegramLabeledPrice(
            label: 'default_label',
            amountWithEndFigures: (
                Telegram::INVOICE_DOP_START_NUMBER_RETRY_INCREMENT * Telegram::INVOICE_DOP_START_NUMBER_RETRY_ATTEMPTS
            ) . '00',
        );
        return [
            'json' => [
                'chat_id' => 'TEST',
                'title' => 'title',
                'description' => 'description',
                'payload' => 'payload',
                'currency' => 'USD',
                'photo_url' => 'photo_url',
                'prices' => $prices->toArray(),
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
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: 'default_label',
            forceMakeHttpRequestToCurrencyApi: true,
            allowDopPriceIfLessThanLowestPossible: false,
            allowNonRemovableCache: false,

            // THIS TEST TESTS THIS ARGUMENT
            retryOnRequestException: true,

            throw: $throw,
        );

        self::assertInstanceOf(TelegramLabeledPrices::class, static::$prices);

        return $response;
    }
}
