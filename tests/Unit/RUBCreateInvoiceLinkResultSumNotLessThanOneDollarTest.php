<?php

namespace GrinWay\Telegram\Tests\Unit;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Telegram::class)]
class RUBCreateInvoiceLinkResultSumNotLessThanOneDollarTest extends AbstractGrinWayServiceInvoiceMethodResultSumNotLessThanOneDollarTestCase
{
    public const CURRENCY = 'RUB';

    protected static string $mockedGrinwayTelegramClientPlainResponse;

    protected function setUp(): void
    {
        self::$mockedGrinwayTelegramClientPlainResponse = '{"ok": "true", "result": "https://fakeurl"}';
        parent::setUp();
    }

    protected function createAndMutatePricesWithGrinWayServiceMethod(array $priceAmounts, string $currency): TelegramLabeledPrices
    {
        $prices = $this->getTestPricesByPriceAmounts($priceAmounts);

        static::$telegram->createInvoiceLink(
            title: 'TEST',
            description: 'TEST',
            prices: $prices,
            providerToken: 'TEST',
            currency: $currency,
            forceMakeHttpRequestToCurrencyApi: true, // force request the mocked fixer API
        );

        return $prices;
    }
}
