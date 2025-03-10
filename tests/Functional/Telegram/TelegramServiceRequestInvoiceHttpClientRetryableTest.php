<?php

namespace GrinWay\Telegram\Tests\Functional\Telegram;

use GrinWay\Service\Service\Currency;
use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Functional\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Telegram::class)]
class TelegramServiceRequestInvoiceHttpClientRetryableTest extends AbstractTelegramServiceTestCase
{
    protected static Currency $currencyService;

    public function testCreateInvoiceLink()
    {
        $prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice('label 1', '100'),
        );

        $invoiceLink = static::$telegram->createInvoiceLink(
            title: 'title',
            description: 'description',
            prices: $prices,
            providerToken: $this->telegramTestPaymentProviderToken,
            currency: 'RUB',
            forceMakeHttpRequestToCurrencyApi: true,
            allowDopPriceIfLessThanLowestPossible: false,
            allowNonRemovableCache: false,
            allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough: true,
            throw: false, // don't reveal secrets of http client
        );

//        $this->assertNotNull($invoiceLink, 'Invoice link successfully created');
//        $response = $this->httpClient->request('GET', $invoiceLink);
//        $this->assertMatchesRegularExpression('~^2\d{2}$~', $response->getStatusCode());
        $n = Telegram::INVOICE_DOP_START_NUMBER_MAX_ATTEMPTS;
        $this->assertSame($n . '100', $prices->getSumFigures());
    }
}
