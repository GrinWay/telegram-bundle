<?php

namespace GrinWay\Telegram\Tests\Unit;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Telegram::class)]
class CreateInvoiceLinkCorrectlyMutatePricesTest extends AbstractInvoiceRelatedMethodAlwaysKeepsResultSumPricesNotLessThanOneDollar
{
    protected function setUp(): void
    {
        $this->mockedGrinwayTelegramClientPlainResponse = '{"ok": "true", "result": "https://fakeurl"}';
        parent::setUp();
    }

    protected function createAndMutatePricesWithGrinWayServiceMethod(array $priceAmounts, string $currency): TelegramLabeledPrices
    {
        $prices = $this->getTestPricesByPriceAmounts($priceAmounts);

        $this->telegram->createInvoiceLink(
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
