<?php

namespace GrinWay\Telegram\Tests\Functional;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\AbstractTelegramTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Telegram::class)]
class TelegramServiceTest extends AbstractTelegramTestCase
{
    private HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        static::ensureKernelShutdown();
        // in order to use fixer API fake data always (fill in the cache with fake data)
        static::setUpCurrencyAndItsMockedDependencies();
        static::$telegram = static::getContainer()->get('grinway_telegram');

        $this->httpClient = static::getContainer()->get(HttpClientInterface::class);
    }

    public function testNotNullInvoiceLinkCreatedWithMinItemAmountIs1AndMinSumAmountNotLessThanOneDollar()
    {
        $invoiceLink = static::$telegram->createInvoiceLink(
            title: 'title',
            description: 'description',
            prices: new TelegramLabeledPrices(
                new TelegramLabeledPrice('label 1', '100'), // min available item amount
            ),
            providerToken: $this->telegramTestPaymentProviderToken,
            currency: 'RUB',
            needName: true,
            needPhoneNumber: true,
            needEmail: true,
            needShippingAddress: true,
            sendPhoneNumberToProvider: true,
            sendEmailToProvider: true,
            isFlexible: true,
            throw: false, // don't reveal secrets of http client
        );

        $this->assertNotNull($invoiceLink, 'Invoice link successfully created');
        $response = $this->httpClient->request('GET', $invoiceLink);
        $this->assertMatchesRegularExpression('~^2\d{2}$~', $response->getStatusCode());
    }
}
