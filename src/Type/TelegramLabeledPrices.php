<?php

namespace GrinWay\Telegram\Type;

use ArrayIterator;
use GrinWay\Service\Service\FiguresRepresentation;
use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Validator\StringNumberWithEndFigures;
use Symfony\Component\Validator\Validation;
use Traversable;

/**
 * https://core.telegram.org/bots/api#labeledprice
 *
 * Telegram payments have a restriction that one item in a whole invoice can't be 0.xx
 * it must be 1.xx
 * if you pass 0.99 or 0.01 as an item amount of the invoice telegram will return 4xx http status code
 *
 * Imagined amount 1.00 writes as 100
 */
class TelegramLabeledPrices implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $labeledPrices;
    private string $sumFigures;

    public function __construct(TelegramLabeledPrice...$labeledPrices)
    {
        $this->labeledPrices = [];
        $this->sumFigures = '000';

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

    /**
     * Factory
     */
    public static function fromArray(array $telegramLabeledPrices): static
    {
        $transformedPrices = [];
        foreach ($telegramLabeledPrices as $price) {
            $label = $price['label'];
            $amount = $price['amount'];
            $transformedPrices[] = new TelegramLabeledPrice($label, $amount);
        }
        return new static(...$transformedPrices);
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

        if ($this->telegramLabeledPriceIsNotValid($labeledPrice)) {
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
        if (!$this->offsetExists($offset)) {
            return;
        }

        $this->subSum($this->labeledPrices[$offset]);

        unset($this->labeledPrices[$offset]);

        $this->labeledPrices = \array_values($this->labeledPrices);
    }

    protected function addSum(TelegramLabeledPrice $labeledPrice): static
    {
        if ($this->telegramLabeledPriceIsNotValid($labeledPrice)) {
            return $this;
        }

        [$startSum, $endSum] = $this->getAddSumStartEndSumNumbers($labeledPrice);

        $this->sumFigures = FiguresRepresentation::concatStartEndPartsWithEndFigures(
            $startSum,
            $endSum,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        return $this;
    }

    protected function subSum(TelegramLabeledPrice $labeledPrice): static
    {
        if ($this->telegramLabeledPriceIsNotValid($labeledPrice)) {
            return $this;
        }

        [$startSum, $endSum] = $this->getSubSumStartEndSumNumbers($labeledPrice);

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

    private function getAddSumStartEndSumNumbers(TelegramLabeledPrice $price): array
    {
        return $this->getProcessedStartEndSumNumbers(
            price: $price,
            startNumberCallback: static fn($startSumNumber, $startPriceNumber) => $startSumNumber + $startPriceNumber,
            endNumberCallback: static fn($endSumNumber, $endPriceNumber) => $endSumNumber + $endPriceNumber,
        );
    }

    private function getSubSumStartEndSumNumbers(TelegramLabeledPrice $price): array
    {
        return $this->getProcessedStartEndSumNumbers(
            price: $price,
            startNumberCallback: static fn($startSumNumber, $startPriceNumber) => $startSumNumber - $startPriceNumber,
            endNumberCallback: static fn($endSumNumber, $endPriceNumber) => $endSumNumber - $endPriceNumber,
        );
    }

    /**
     * @return array [
     *     (int) startNumber
     *     (string) endNumber - as string not to lose lead zeros
     * ]
     */
    private function getProcessedStartEndSumNumbers(
        TelegramLabeledPrice $price,
        callable             $startNumberCallback,
        callable             $endNumberCallback,
    ): array
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

        $startSum = $startNumberCallback($startSumNumber, $startPriceNumber);
        $endSum = $endNumberCallback($endSumNumber, $endPriceNumber);

        $part = 10 ** Telegram::LENGTH_AMOUNT_END_FIGURES;

        // 199 -> 1 + startSum
        $frontNumbersEndSum = (int)($endSum / $part);
        $startSum += $frontNumbersEndSum;

        // 199 -> 99
        $endSum %= $part;
        $endLen = \strlen((string)$endSum);
        $endLenDiff = Telegram::LENGTH_AMOUNT_END_FIGURES - $endLen;
        if (0 < $endLenDiff) {
            $endSum = \sprintf('%s%s', \str_repeat('0', $endLenDiff), $endSum);
        }

        return [$startSum, (string)$endSum];
    }

//    public function current(): mixed
//    {
//        return $this->labeledPrices[$this->labeledPricesIdx];
//    }
//
//    public function next(): void
//    {
//        $this->labeledPricesIdx++;
//    }
//
//    public function key(): mixed
//    {
//        return $this->labeledPricesIdx;
//    }
//
//    public function valid(): bool
//    {
//        return isset($this->labeledPrices[$this->labeledPricesIdx]);
//    }
//
//    public function rewind(): void
//    {
//        $this->labeledPricesIdx = 0;
//    }

    public function count(): int
    {
        return \count($this->labeledPrices);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->labeledPrices);
    }

    private function telegramLabeledPriceIsValid(TelegramLabeledPrice $telegramLabeledPrice): bool
    {
        $startAmountPart = FiguresRepresentation::getStartNumberWithEndFigures(
            $telegramLabeledPrice->getAmountWithEndFigures(),
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );

        if (Telegram::MIN_START_AMOUNT_PART > $startAmountPart) {
            return false;
        }

        return true;
    }

    private function telegramLabeledPriceIsNotValid(TelegramLabeledPrice $telegramLabeledPrice): bool
    {
        return !$this->telegramLabeledPriceIsValid($telegramLabeledPrice);
    }
}
