<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\GetPriceWithDopIfAmountLessThanPossibleLowestPrice;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'getPriceWithDopIfAmountLessThanPossibleLowestPrice')]
class TelegramServiceGetPriceWithDopIfAmountLessThanPossibleLowestPriceByDefaultTest extends AbstractTelegramServiceTestCase
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
            forceMakeHttpRequestToCurrencyApi: true,
        );

        $this->assertCount(2, $result);
        $this->assertSame([
            [
                'label' => 'test',
                'amount' => '100',
            ],
            [
                'label' => 'Minimum price compensation',
                'amount' => '9756',
            ],
        ], $result->toArray());
    }
}
