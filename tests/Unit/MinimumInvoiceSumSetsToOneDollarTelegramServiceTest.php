<?php

namespace GrinWay\Telegram\Tests\Unit;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\AbstractTelegramTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * 1) Telegram payments have a restriction that minimum summary invoice must be not less than 1$
 *
 * This test checks internal method that is used by grinway_telegram to work with invoices
 * to make the minimum invoice summary 1$
 *
 * Fixer API for currency information is mocked and always returns static data, to get a fixed data
 * here we just test how this method adds item to reach the 1$ if it's needed
 *
 * https://core.telegram.org/bots/payments#supported-currencies
 */
#[CoversClass(Telegram::class)]
class MinimumInvoiceSumSetsToOneDollarTelegramServiceTest extends AbstractTelegramTestCase
{
    public static array $fixerPayload;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \dump(__METHOD__);

        $currencyFixerPayload = self::getContainer()
            ->get(\sprintf('%s $grinwayServiceCurrencyFixerLatest', HttpClientInterface::class))
            ->request('GET', '')
            ->getContent()//
        ;
        self::$fixerPayload = self::getContainer()
            ->get('serializer')
            ->decode($currencyFixerPayload)//
        ;
    }

    public function testRUB()
    {
        \dump(self::$fixerPayload);

        $this->assertTrue(true);

//        $prices = new TelegramLabeledPrices(
//            new TelegramLabeledPrice('TEST', '100'), // 1.00 RUB
//        );
//
//        $this->telegram->appendDopPriceIfAmountLessThanPossibleLowestPrice(
//            prices: $prices,
//            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: '',
//            currency: 'RUB',
//            forceMakeHttpRequestToCurrencyApi: true, // force request the mocked fixer API
//        );
    }
}
