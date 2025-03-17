<?php

namespace GrinWay\Telegram\Tests\Unit\Type;

use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TelegramLabeledPrices::class)]
class TelegramLabeledPricesTest extends AbstractTypeTestCase
{
    public function testSumAndCountViaArrayAccess()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices();
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '101');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '207');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '099');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '002');
        $telegramLabeledPrices[] = new TelegramLabeledPrice(label: 't', amountWithEndFigures: '10000');

        $this->assertTelegramLabeledPricesSumCountStartEndNumbers($telegramLabeledPrices);
    }

    public function testSumCountStartEndNumbers()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices(
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '101'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '207'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '099'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '002'),
            new TelegramLabeledPrice(label: 't', amountWithEndFigures: '10000'),
        );

        $this->assertTelegramLabeledPricesSumCountStartEndNumbers($telegramLabeledPrices);
    }

    public function testUnsetExcessive()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices();

        unset($telegramLabeledPrices[0]);
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());

        unset($telegramLabeledPrices[0]);
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());
    }

    public function testToArray()
    {
        $telegramLabeledPrices = new TelegramLabeledPrices(
            new TelegramLabeledPrice(label: 't1', amountWithEndFigures: '101'),
            new TelegramLabeledPrice(label: 't2', amountWithEndFigures: '208'),
            new TelegramLabeledPrice(label: 't3', amountWithEndFigures: '001'),
            new TelegramLabeledPrice(label: 't4', amountWithEndFigures: '099'),
            new TelegramLabeledPrice(label: 't5', amountWithEndFigures: '10000'),
        );

        $this->assertCount(5, $telegramLabeledPrices);
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
                'amount' => '001',
            ],
            [
                'label' => 't4',
                'amount' => '099',
            ],
            [
                'label' => 't5',
                'amount' => '10000',
            ],
        ], $array);
    }

    public function testFromArray()
    {
        $fromArray = [
            [
                'label' => 'test',
                'amount' => '101',
            ],
            [
                'label' => 'test',
                'amount' => '208',
            ],
            [
                'label' => 'test',
                'amount' => '001',
            ],
            [
                'label' => 'test',
                'amount' => '099',
            ],
            [
                'label' => 'test',
                'amount' => '10000',
            ],
        ];

        $telegramLabeledPrices = TelegramLabeledPrices::fromArray($fromArray);

        $this->assertCount(5, $telegramLabeledPrices);
        $array = $telegramLabeledPrices->toArray();
        $this->assertSame($fromArray, $array);
    }

    public function testSumFiguresIs000ForEmptyTelegramLabeledPrices()
    {
        $telegramLabeledPrices = TelegramLabeledPrices::fromArray(
            [],
        );
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());

        $telegramLabeledPrices = new TelegramLabeledPrices();
        $this->assertSame('000', $telegramLabeledPrices->getSumFigures());
    }

    private function assertTelegramLabeledPricesSumCountStartEndNumbers(
        TelegramLabeledPrices $telegramLabeledPrices,
    ): void
    {
        foreach ([
                     [
                         'expectedCount' => 5,
                         'expectedSum' => '10409',
                         'expectedStart' => 104,
                         'expectedEnd' => '09',
                     ],
                     [
                         'expectedCount' => 4,
                         'expectedSum' => '10308',
                         'expectedStart' => 103,
                         'expectedEnd' => '08',
                     ],
                     [
                         'expectedCount' => 3,
                         'expectedSum' => '10101',
                         'expectedStart' => 101,
                         'expectedEnd' => '01',
                     ],
                     [
                         'expectedCount' => 2,
                         'expectedSum' => '10002',
                         'expectedStart' => 100,
                         'expectedEnd' => '02',
                     ],
                     [
                         'expectedCount' => 1,
                         'expectedSum' => '10000',
                         'expectedStart' => 100,
                         'expectedEnd' => '00',
                     ],
                     [
                         'expectedCount' => 0,
                         'expectedSum' => '000',
                         'expectedStart' => 0,
                         'expectedEnd' => '00',
                     ],
                 ] as ['expectedCount' => $expectedCount, 'expectedSum' => $expectedSum, 'expectedStart' => $expectedStart, 'expectedEnd' => $expectedEnd]) {
            [$startNumber, $endNumber] = $telegramLabeledPrices->getStartEndSumNumbers();

            $this->assertCount($expectedCount, $telegramLabeledPrices);
            $this->assertSame($expectedSum, $telegramLabeledPrices->getSumFigures());
            $this->assertSame($expectedStart, $startNumber);
            $this->assertSame($expectedEnd, $endNumber);
            unset($telegramLabeledPrices[0]);
        }
    }
}
