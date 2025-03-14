<?php

namespace GrinWay\Telegram\Tests\Unit\Type;

use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TelegramLabeledPrices::class)]
class TelegramLabeledPricesSumFiguresIsValidTest extends AbstractTypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSumAndCountViaArrayAccess()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices();
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '101');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '208');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '001');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '099');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '10000');

        $this->assertCount(3, $telegramLabeledPrices);
        $this->assertSame('10309', $telegramLabeledPrices->getSumFigures());
        [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();
        $this->assertSame(103, $startNumber);
        $this->assertSame('09', $endNumber);

        unset($telegramLabeledPrices[0]);
        unset($telegramLabeledPrices[0]);
        $this->assertSame('10000', $telegramLabeledPrices->getSumFigures());

        unset($telegramLabeledPrices[0]);
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());
    }

    public function testSumAndCountViaConstructor()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices(
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '101'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '208'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '001'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '099'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '10000'),
        );

        $this->assertCount(3, $telegramLabeledPrices);
        $this->assertSame('10309', $telegramLabeledPrices->getSumFigures());
        [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();
        $this->assertSame(103, $startNumber);
        $this->assertSame('09', $endNumber);

        unset($telegramLabeledPrices[0]);
        unset($telegramLabeledPrices[0]);
        $this->assertSame('10000', $telegramLabeledPrices->getSumFigures());

        unset($telegramLabeledPrices[0]);
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());
    }

    public function testUnsetExcessive()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices();

        unset($telegramLabeledPrices[0]);
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());

        unset($telegramLabeledPrices[0]);
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());
    }

    public function testGetStartEndNumbers()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices(
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '101'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '208'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '001'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '099'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '10000'),
        );

        $this->assertCount(3, $telegramLabeledPrices);
        [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();
        $this->assertSame(103, $startNumber);
        $this->assertSame('09', $endNumber);

        unset($telegramLabeledPrices[0]);
        $this->assertCount(2, $telegramLabeledPrices);
        [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();
        $this->assertSame(102, $startNumber);
        $this->assertSame('08', $endNumber);

        unset($telegramLabeledPrices[0]);
        $this->assertCount(1, $telegramLabeledPrices);
        [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();
        $this->assertSame(100, $startNumber);
        $this->assertSame('00', $endNumber);

        unset($telegramLabeledPrices[0]);
        $this->assertCount(0, $telegramLabeledPrices);
        [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();
        $this->assertSame(0, $startNumber);
        $this->assertSame('00', $endNumber);
    }

    public function testToArray()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices(
            new TelegramLabeledPrice(label: 't1', amountWithEndFigures: '101'),
            new TelegramLabeledPrice(label: 't2', amountWithEndFigures: '208'),
            new TelegramLabeledPrice(label: 'tn1', amountWithEndFigures: '001'),
            new TelegramLabeledPrice(label: 'tn2', amountWithEndFigures: '099'),
            new TelegramLabeledPrice(label: 't3', amountWithEndFigures: '10000'),
        );

        $this->assertCount(3, $telegramLabeledPrices);
        $array = $telegramLabeledPrices->toArray();
        $this->assertSame([
            [
                'label' => 't1',
                'amount' => '101',
            ],
            [
                'label' => 't2',
                'amount' => '208',
            ],
            [
                'label' => 't3',
                'amount' => '10000',
            ],
        ], $array);
    }

    public function testFromArray()
    {
        $fromArray = [
            [
                'label' => 't1',
                'amount' => '101',
            ],
            [
                'label' => 't2',
                'amount' => '208',
            ],
            [
                'label' => 't3',
                'amount' => '10000',
            ],
        ];

        $telegramLabeledPrices = TelegramLabeledPrices::fromArray($fromArray);

        $this->assertCount(3, $telegramLabeledPrices);
        $array = $telegramLabeledPrices->toArray();
        $this->assertSame($fromArray, $array);
    }
}
