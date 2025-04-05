<?php

namespace GrinWay\Telegram\Type;

/**
 * https://core.telegram.org/bots/api#labeledprice
 */
class TelegramLabeledPrice implements \ArrayAccess
{
    public function __construct(
        private ?string $label = null,
        private ?string $amountWithEndFigures = null,
    )
    {
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getAmountWithEndFigures(): ?string
    {
        return $this->amountWithEndFigures;
    }

    public function setAmountWithEndFigures(?string $amountWithEndFigures): void
    {
        $this->amountWithEndFigures = $amountWithEndFigures;
    }

    public function toArray(): array
    {
        return [
            'label' => (string)$this->getLabel(),
            'amount' => (string)$this->getAmountWithEndFigures(),
        ];
    }

    public static function fromArray(array $price): static
    {
        return new static(label: $price['label'], amountWithEndFigures: $price['amount']);
    }

    public function offsetExists(mixed $offset): bool
    {
        return 'label' == $offset || 'amount' == $offset;
    }

    public function offsetGet(mixed $offset): mixed
    {
        if ('label' == $offset) {
            return $this->getLabel();
        }

        if ('amount' == $offset) {
            return $this->getAmountWithEndFigures();
        }

        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ('label' == $offset) {
            $this->setLabel($value);
        }

        if ('amount' == $offset) {
            $this->setAmountWithEndFigures($value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if ('label' == $offset) {
            $this->setLabel(null);
        }

        if ('amount' == $offset) {
            $this->setAmountWithEndFigures(null);
        }
    }
}
