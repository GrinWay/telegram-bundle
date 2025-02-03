<?php

namespace GrinWay\Telegram\Type;

use GrinWay\Service\Service\FiguresRepresentation;
use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Validator\StringNumberWithEndFigures;
use Symfony\Component\Validator\Validation;

/**
 * https://core.telegram.org/bots/api#labeledprice
 *
 * Telegram payments have a restriction that one item in a whole invoice can't be 0.xx
 * it must be 1.xx
 * if you pass 0.99 or 0.01 as an item amount of the invoice telegram will return 4xx http status code
 *
 * Imagined amount 1.00 writes as 100
 */
class TelegramLabeledPrices implements \ArrayAccess, \Countable, \Iterator
{
    private array $labeledPrices;
    private int $labeledPricesIdx;
    private string $sumFigures;

    public function __construct(TelegramLabeledPrice...$labeledPrices)
    {
        $this->labeledPrices = [];
        $this->sumFigures = '000';
        $this->labeledPricesIdx = 0;

        foreach ($labeledPrices as $labeledPrice) {
            $this[] = $labeledPrice;
        }
    }

    public function getSumFigures(): string
    {
        return $this->sumFigures;
    }

    public function getStartEndSumNumbers(): array
    {
        return FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $this->sumFigures,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
    }

    public function getStartEndNumbers(TelegramLabeledPrice $labeledPrice): array
    {
        $stringPrice = $this->getConvertedToStringWithEndFigures($labeledPrice);

        return FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $stringPrice,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
    }

    public function toArray(): array
    {
        $prices = [];
        foreach ($this->labeledPrices as $price) {
            \assert($price instanceof TelegramLabeledPrice);

            $prices[] = $price->toArray();
        }
        return $prices;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->labeledPrices[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->labeledPrices[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof TelegramLabeledPrice) {
            $message = \sprintf(
                'Invalid value, got value type of "%s", allowed only "%s"',
                \get_debug_type($value),
                TelegramLabeledPrice::class,
            );
            throw new \InvalidArgumentException($message);
        }
        $labeledPrice = $value;

        $startAmountPart = FiguresRepresentation::getStartNumberWithEndFigures(
            $labeledPrice->getAmountWithEndFigures(),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        if (Telegram::MIN_START_AMOUNT_PART > $startAmountPart) {
            return;
        }

        $this->addSum($labeledPrice);
        if (null === $offset) {
            $this->labeledPrices[] = $labeledPrice;
        } else {
            $this->labeledPrices[$offset] = $labeledPrice;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->labeledPrices[$offset]);
    }

    public function count(): int
    {
        return \count($this->labeledPrices);
    }

    public function current(): mixed
    {
        return $this->labeledPrices[$this->labeledPricesIdx];
    }

    public function next(): void
    {
        $this->labeledPricesIdx++;
    }

    public function key(): mixed
    {
        return $this->labeledPricesIdx;
    }

    public function valid(): bool
    {
        return isset($this->labeledPrices[$this->labeledPricesIdx]);
    }

    public function rewind(): void
    {
        $this->labeledPricesIdx = 0;
    }

    private function addSum(TelegramLabeledPrice $labeledPrice): static
    {
        [$startSum, $endSum] = $this->getAddedStartEndSumNumbers($labeledPrice);

        $this->sumFigures = FiguresRepresentation::concatStartEndPartsWithEndFigures(
            $startSum,
            $endSum,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        return $this;
    }

    private function getConvertedToStringWithEndFigures(TelegramLabeledPrice $labeledPrice): string
    {
        $stringPriceWithEndFigures = $labeledPrice->getAmountWithEndFigures();
        Validation::createCallable(new StringNumberWithEndFigures())($stringPriceWithEndFigures);
        return $stringPriceWithEndFigures;
    }

    private function getAddedStartEndSumNumbers(TelegramLabeledPrice $price): array
    {
        $stringPriceWithEndFigures = $this->getConvertedToStringWithEndFigures($price);

        [$startSumNumber, $endSumNumber] = FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $this->sumFigures,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
        [$startPriceNumber, $endPriceNumber] = FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $stringPriceWithEndFigures,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        $startSum = $startSumNumber + $startPriceNumber;
        $endSum = $endSumNumber + $endPriceNumber;

        $part = 10 ** Telegram::LENGTH_AMOUNT_END_FIGURES;

        // 199 -> 1 + startSum
        $frontNumbersEndSum = (int)($endSum / $part);
        $startSum += $frontNumbersEndSum;

        // 199 -> 99
        $endSum %= $part;

        return [$startSum, $endSum];
    }
}
