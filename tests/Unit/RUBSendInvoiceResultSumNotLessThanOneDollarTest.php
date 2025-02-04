<?php

namespace GrinWay\Telegram\Tests\Unit;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Telegram::class)]
class RUBSendInvoiceResultSumNotLessThanOneDollarTest extends AbstractGrinWayServiceInvoiceMethodResultSumNotLessThanOneDollarTestCase
{
    public const CURRENCY = 'RUB';

    protected function createAndMutatePricesWithGrinWayServiceMethod(array $priceAmounts, string $currency): TelegramLabeledPrices
    {
        $prices = $this->getTestPricesByPriceAmounts($priceAmounts);

        static::$telegram->sendInvoice(
            chatId: 'TEST',
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
