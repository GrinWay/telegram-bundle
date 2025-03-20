<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\Invoice\CreateInvoiceLink\PricesIsArrayAfterProcessing;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Test\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(Telegram::class, 'createInvoiceLink')]
class TelegramServicePricesIsArrayAfterProcessingByDefaultTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected static $prices;

    protected function setUp(): void
    {
        parent::setUp();

        static::$prices = (new TelegramLabeledPrices(
            new TelegramLabeledPrice('label', '100'),
        ))->toArray();
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

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'createInvoiceLink';
    }

    protected function assertSuccessfulPayload(mixed $payload): void
    {
        static::assertTrue(\is_string($payload));
    }

    protected function assertFailedPayload(mixed $payload): void
    {
        $this->assertNull($payload);
    }

    protected function processGetContentResponseMock(MockObject $responseMock): void
    {
        $responseMock
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"ok":true,"result":"invoice link"}')//
        ;
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'title' => 'title',
                'description' => 'description',
                'currency' => 'USD',
                'prices' => static::$prices,
                'provider_token' => 'provider_token',
                'need_name' => false,
                'need_phone_number' => false,
                'need_email' => false,
                'need_shipping_address' => false,
                'send_phone_number_to_provider' => false,
                'send_email_to_provider' => false,
                'is_flexible' => false,
                'payload' => '{}',
                'start_parameter' => 'service',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        $response = $telegram->$method(
            title: 'title',
            description: 'description',
            prices: static::$prices,
            providerToken: 'provider_token',
            currency: 'USD',
            forceMakeHttpRequestToCurrencyApi: true,
            throw: $throw,
        );

        self::assertTrue(\is_array(static::$prices));

        return $response;
    }
}
