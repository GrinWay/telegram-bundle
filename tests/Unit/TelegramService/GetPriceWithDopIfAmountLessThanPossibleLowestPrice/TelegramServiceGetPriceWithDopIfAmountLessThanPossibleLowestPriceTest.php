<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\GetPriceWithDopIfAmountLessThanPossibleLowestPrice;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'getPriceWithDopIfAmountLessThanPossibleLowestPrice')]
class TelegramServiceGetPriceWithDopIfAmountLessThanPossibleLowestPriceTest extends AbstractTelegramServiceTestCase
{
    public function test()
    {
        $prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice(
                label: 'test',
                amountWithEndFigures: '100',
            ),
        );

        $result = static::$telegram->getPriceWithDopIfAmountLessThanPossibleLowestPrice(
            prices: $prices,
            currency: 'RUB',
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: 'test',
            forceMakeHttpRequestToCurrencyApi: true,
            allowNonRemovableCache: true,
            allowDopPriceIfLessThanLowestPossible: true,
            allowInvoiceDopIncrementStartNumber: false,
        );

        $this->assertCount(2, $result);
        $this->assertSame([
            [
                'label' => 'test',
                'amount' => '100',
            ],
            [
                'label' => 'test',
                'amount' => '9756',
            ],
        ], $result->toArray());
    }
}
