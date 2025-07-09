<?php

namespace Functional\Telegram;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Functional\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Telegram::class)]
abstract class TelegramServiceTestCase extends AbstractTelegramServiceTestCase
{
    public function testNotNullInvoiceLinkCreatedWithMinItemAmountIs1AndMinSumAmountNotLessThanOneDollar()
    {
        $prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice('label 1', '1'), // min available item amount
        );

        $invoiceLink = static::$telegram->createInvoiceLink(
            title: 'title',
            description: 'description',
            prices: $prices,
            providerToken: static::$telegramTestPaymentProviderToken,
            currency: 'USD',
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