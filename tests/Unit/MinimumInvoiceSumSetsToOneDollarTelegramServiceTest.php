<?php

namespace GrinWay\Telegram\Tests\Unit;

use GrinWay\Service\Service\Currency;
use GrinWay\Service\Service\FiguresRepresentation;
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
    protected Currency $currencyService;
    protected int $startOneDollarIntInRUB;
    protected int $endOneDollarIntInRUB;

    protected function setUp(): void
    {
        parent::setUp();

        $currencyFixerPayload = self::getContainer()
            ->get(\sprintf('%s $grinwayServiceCurrencyFixerLatest', HttpClientInterface::class))
            ->request('GET', '')
            ->getContent()//
        ;
        self::$fixerPayload = self::getContainer()
            ->get('serializer')
            ->decode($currencyFixerPayload, 'json')//
        ;

        //
        if (null === (self::$fixerPayload['grinway_key_fake_fixer'] ?? null)) {
            $message = '!!! Accidentally used a real fixer API service, MOCK IT !!!';
            echo $message . \PHP_EOL . \PHP_EOL;
            throw new \RuntimeException($message);
        }

        $this->currencyService = self::getContainer()->get('GrinWay\Service\Service\Currency');

        [$this->startOneDollarIntInRUB, $this->endOneDollarIntInRUB] = FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $this->oneDollarWithEndFiguresIn('RUB'),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
    }


    public function testRubAmountLessThanOneDollarButGetsOneDollar()
    {
        $currency = 'RUB';

        $_1_halfEndDollar = FiguresRepresentation::concatNumbersWithCorrectCountOfEndFigures(
            1,
            (int)($this->endOneDollarIntInRUB / 2),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
        $_1_endDollar = FiguresRepresentation::concatNumbersWithCorrectCountOfEndFigures(
            1,
            $this->endOneDollarIntInRUB,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $oneDollarWithEndFigures = $this->oneDollarWithEndFiguresIn($currency);
        $floatOneDollar = FiguresRepresentation::amountWithEndFiguresAsFloat(
            $oneDollarWithEndFigures,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
//        $halfDollar = FiguresRepresentation::getStringWithEndFigures(
//            $hd = $floatOneDollar / 2,
//            Telegram::LENGTH_AMOUNT_END_FIGURES,
//        );
//        \dump($floatOneDollar, $hd, $halfDollar);

        $this->assertCountIs2AndSumSameAsOneDollar('100', $currency); // 1.00 RUB
        $this->assertCountIs2AndSumSameAsOneDollar('111', $currency); // 1.11 RUB
        $this->assertCountIs2AndSumSameAsOneDollar($_1_halfEndDollar, $currency);
        $this->assertCountIs2AndSumSameAsOneDollar($_1_endDollar, $currency);
        $this->assertCountIs2AndSumSameAsOneDollar('199', $currency);
//        $this->assertCountIs2AndSumSameAsOneDollar($halfDollar, $currency);
    }

    /**
     * Helper
     *
     * @internal
     */
    protected function assertCountIs2AndSumSameAsOneDollar(string $amountWithEndFigures, string $currency): void
    {
        $prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice('TEST', $amountWithEndFigures),
        );

        $this->telegram->appendDopPriceIfAmountLessThanPossibleLowestPrice(
            prices: $prices,
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: '',
            currency: $currency,
            forceMakeHttpRequestToCurrencyApi: true, // force request the mocked fixer API
        );

        $this->assertCount(2, $prices);
        $this->assertSame(
            $this->oneDollarWithEndFiguresIn($currency),
            $prices->getSumFigures(),
        );
    }

    /**
     * Helper
     *
     * @internal
     */
    protected function oneDollarWithEndFiguresIn(string $currency): string
    {
        return $this->currencyService->transferAmountFromToWithEndFigures(
            '100',
            'USD',
            $currency,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
    }
}
