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
 * Fixer API for currency information is mocked and always returns static data, to get a fixed data
 * here we just test how this method adds item to reach the 1$ if it's needed
 *
 * Test naming pattern:
 * test[start amount part]And[end amount part]In[currency][Changes|DoesNotChange]Prices[To count if changes]ResultSum[how much]
 *
 * https://core.telegram.org/bots/payments#supported-currencies
 */
#[CoversClass(Telegram::class)]
abstract class AbstractInvoiceRelatedMethodAlwaysKeepsResultSumPricesNotLessThanOneDollar extends AbstractTelegramTestCase
{
    // rub
    protected int $rubOneDollarWithEndFigures;
    protected float $floatOneDollar;
    protected int $rubStartOneDollarInt;
    protected int $rubEndOneDollarInt;
    protected int $halfEndDollarWithEndFigures;
    protected int $endDollarWithEndFigures;
    protected int $rubOneDollarStartWithHalfEndDollar;
    protected int $halfDollarWithEndFigures;

    /**
     * Pay your attention at getTestPricesByPriceAmounts of this abstract class
     */
    abstract protected function createAndMutatePricesWithGrinWayServiceMethod(array $priceAmounts, string $currency): TelegramLabeledPrices;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rubSetters();
    }

    public function test1And00InRubChangesPricesTo2ResultSumOneDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                '100',
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function test1And11InRubChangesPricesTo2ResultSumOneDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                '111',
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function test1And99InRubChangesPricesTo2ResultSumOneDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                '199',
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function test1AndHalfEndDollarInRubChangesPricesTo2ResultSumOneDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                \sprintf('1%s', $this->halfEndDollarWithEndFigures),
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function test1AndEndDollarInRubChangesPricesTo2ResultSumOneDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                \sprintf('1%s', $this->endDollarWithEndFigures),
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function testHalfDollarInRubChangesPricesTo2ResultSumOneDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                $this->halfDollarWithEndFigures,
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function testExactly1DollarInRubDoesNotChangePricesResultSum1Dollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                $this->rubOneDollarWithEndFigures,
            ],
            'RUB',
        );

        $this->assertCount(1, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            $this->rubEndOneDollarInt,
        );
    }

    public function testStartOneDollarAnd99InRubDoesNotChangePricesResultSumStartOneDollarAnd99()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                $this->rubStartOneDollarInt . '99',
            ],
            'RUB',
        );

        $this->assertCount(1, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            99,
        );
    }

    public function testStartOneDollarAnd00InRubChangesPricesResultTo2SumStartOneDollarPlus1And00()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                $this->rubStartOneDollarInt . '00',
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt + 1,
            0,
        );
    }

    public function testStartOneDollarAndHalfEndDollarInRubChangesPricesResultTo2SumStartOneDollarPlus1AndHalfEndDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                $this->rubOneDollarStartWithHalfEndDollar,
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt + 1,
            (int)($this->rubEndOneDollarInt / 2),
        );
    }

    public function testStartOneDollarSub1And99InRubChangesPricesResultTo2SumStartOneDollarAnd99()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                ($this->rubStartOneDollarInt - 1) . '99',
            ],
            'RUB',
        );

        $this->assertCount(2, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt,
            99,
        );
    }

    public function testStartOneDollarPlus1And00InRubDoesNotChangePricesResultSumStartOneDollarPlus1And00()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                ($this->rubStartOneDollarInt + 1) . '00',
            ],
            'RUB',
        );

        $this->assertCount(1, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt + 1,
            0,
        );
    }

    public function testStartOneDollarPlus1AndHalfEndDollarInRubDoesNotChangePricesResultSumStartOneDollarPlus1AndHalfEndDollar()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                ($this->rubStartOneDollarInt + 1) . $this->halfEndDollarWithEndFigures,
            ],
            'RUB',
        );

        $this->assertCount(1, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt + 1,
            $this->halfEndDollarWithEndFigures,
        );
    }

    public function testStartOneDollarPlus1And99InRubDoesNotChangePricesResultSumStartOneDollarPlus1And99()
    {
        $prices = $this->createAndMutatePricesWithGrinWayServiceMethod(
            [
                ($this->rubStartOneDollarInt + 1) . '99',
            ],
            'RUB',
        );

        $this->assertCount(1, $prices);
        $this->assertStartEndPricesSum(
            $prices,
            $this->rubStartOneDollarInt + 1,
            '99',
        );
    }


    /**
     * Helper
     *
     * @internal
     */
    protected function assertStartEndPricesSum(TelegramLabeledPrices $prices, int $startSum, int $endSum): void
    {
        [$startSumNumber, $endSumNumber] = FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $prices->getSumFigures(),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $this->assertSame($startSumNumber, $startSum);
        $this->assertSame($endSumNumber, $endSum);
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

    /**
     * @internal
     */
    private function rubSetters(): void
    {
        $this->rubOneDollarWithEndFigures = $this->oneDollarWithEndFiguresIn('RUB');

        $this->floatOneDollar = FiguresRepresentation::numberWithEndFiguresAsFloat(
            $this->rubOneDollarWithEndFigures,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        [$this->rubStartOneDollarInt, $this->rubEndOneDollarInt] = FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $this->rubOneDollarWithEndFigures,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $this->halfEndDollarWithEndFigures = FiguresRepresentation::getEndNumberWithEndFigures(
            (int)($this->rubEndOneDollarInt / 2),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $this->rubOneDollarStartWithHalfEndDollar = FiguresRepresentation::concatNumbersWithCorrectCountOfEndFigures(
            $this->rubStartOneDollarInt,
            (int)($this->rubEndOneDollarInt / 2),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $this->endDollarWithEndFigures = FiguresRepresentation::getEndNumberWithEndFigures(
            $this->rubEndOneDollarInt,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $this->halfDollarWithEndFigures = FiguresRepresentation::getStringWithEndFigures(
            $this->floatOneDollar / 2,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
    }

    /**
     * Helper API method
     * for a certain tests extending this abstract class
     */
    protected function getTestPricesByPriceAmounts(array $priceAmounts): TelegramLabeledPrices
    {
        $prices = new TelegramLabeledPrices();

        $priceAmounts = \array_values($priceAmounts);
        foreach ($priceAmounts as $i => $itemTelegramAmount) {
            $prices[] = new TelegramLabeledPrice(
                \sprintf('TEST %s', $i),
                $itemTelegramAmount,
            );
        }

        return $prices;
    }
}
