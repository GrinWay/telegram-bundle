<?php

namespace GrinWay\Telegram\Tests\Functional\Telegram;

use GrinWay\Service\Service\Currency;
use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Functional\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Telegram::class)]
class TelegramServiceRequestInvoiceHttpClientRetryableTest extends AbstractTelegramServiceTestCase
{
    protected static Currency $currencyService;

    protected function setUp(): void
    {
        parent::setUp();

        static::getContainer()->set(
            \sprintf('%s $grinwayTelegramClient', HttpClientInterface::class),
            new MockHttpClient([
                new MockResponse(\json_encode([]), ['http_code' => 500]),
                new MockResponse(\json_encode([]), ['http_code' => 500]),
                new MockResponse(\json_encode([]), ['http_code' => 500]),
                new MockResponse(\json_encode([]), ['http_code' => 200]),
            ]),
        );
    }

    protected static function isStubGrinWayTelegramClient(): bool
    {
        return false;
    }

    public function testCreateInvoiceLink()
    {
        $prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice('label 1', '100'),
        );

        $invoiceLink = static::$telegram->createInvoiceLink(
            title: 'title',
            description: 'description',
            prices: $prices,
            providerToken: static::$telegramTestPaymentProviderToken,
            currency: 'USD',
            forceMakeHttpRequestToCurrencyApi: true,
            allowDopPriceIfLessThanLowestPossible: false,
            allowNonRemovableCache: false,
            retryOnRequestException: true,
            throw: true, // don't reveal secrets of http client
        );

//        $this->assertNotNull($invoiceLink, 'Invoice link successfully created');
//        $response = $this->httpClient->request('GET', $invoiceLink);
//        $this->assertMatchesRegularExpression('~^2\d{2}$~', $response->getStatusCode());
        $n = Telegram::INVOICE_DOP_START_NUMBER_RETRY_ATTEMPTS;
        $this->assertSame($n . '100', $prices->getSumFigures());
    }
}