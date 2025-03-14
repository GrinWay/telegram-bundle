<?php

namespace GrinWay\Telegram\Tests\Unit\Type;

use GrinWay\Telegram\Type\TelegramLabeledPrice;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TelegramLabeledPrice::class)]
class TelegramLabeledPriceTest extends AbstractTypeTestCase
{
    public function testToArray()
    {
        $telegramLabeledPrice = new TelegramLabeledPrice(
            label: 'test label',
            amountWithEndFigures: '001',
        );

        $this->assertSame([
            'label' => 'test label',
            'amount' => '001',
        ], $telegramLabeledPrice->toArray());
    }
}
