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
        self::ensureKernelShutdown();

        $this->telegram = self::getContainer()->get('grinway_telegram');

        $this->httpClient = self::getContainer()->get(HttpClientInterface::class);
    }

    public function testCreateAndSuccessfullyVisitInvoiceLink()
    {
        $invoiceLink = $this->telegram->createInvoiceLink(
            title: 'title',
            description: 'description',
            prices: new TelegramLabeledPrices(
                new TelegramLabeledPrice('label 1', '100'),
                new TelegramLabeledPrice('label 2', '1000000'),
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

        $this->assertNotNull($invoiceLink);
        $response = $this->httpClient->request('GET', $invoiceLink);
        $this->assertMatchesRegularExpression('~^2\d{2}$~', $response->getStatusCode());
    }
}
