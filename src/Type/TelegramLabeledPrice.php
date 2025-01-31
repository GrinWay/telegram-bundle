<?php

namespace GrinWay\Telegram\Type;

/**
 * https://core.telegram.org/bots/api#labeledprice
 */
class TelegramLabeledPrice
{
    public function __construct(
        private string $label,
        private string $amountWithEndFigures,
    )
    {
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getAmountWithEndFigures(): string
    {
        return $this->amountWithEndFigures;
    }

    public function setAmountWithEndFigures(string $amountWithEndFigures): void
    {
        $this->amountWithEndFigures = $amountWithEndFigures;
    }

    public function toArray(): array
    {
        return [
            'label' => $this->getLabel(),
            'amount' => $this->getAmountWithEndFigures(),
        ];
    }
}
