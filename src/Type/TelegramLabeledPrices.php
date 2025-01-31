<?php

namespace GrinWay\Telegram\Type;

use GrinWay\Service\Service\FiguresRepresentation;
use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Validator\TelegramAmountWithEndFigures;
use Symfony\Component\Validator\Validation;

/**
 * https://core.telegram.org/bots/api#labeledprice
 * Price representation 000 === 0.00
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
            $startAmountPart = $this->getStartNumber($labeledPrice->getAmountWithEndFigures());
            if (Telegram::MIN_START_AMOUNT_PART > $startAmountPart) {
                continue;
            }
            $this->labeledPrices[] = $labeledPrice;
            $this->addSum($labeledPrice);
        }
    }

    public function getSumFigures(): string
    {
        return $this->sumFigures;
    }

    public function getStartSumNumber(): int
    {
        return (int)$this->getStartFigures($this->sumFigures);
    }

    public function getEndSumNumber(): int
    {
        return (int)$this->getEndFigures($this->sumFigures);
    }

    public function getStartEndSumNumbers(): array
    {
        return [$this->getStartSumNumber(), $this->getEndSumNumber()];
    }

    public function getStartEndNumbers(TelegramLabeledPrice|string $labeledPrice): array
    {
        return [$this->getStartNumber($labeledPrice), $this->getEndNumber($labeledPrice)];
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
        if (null === $offset) {
            $this->labeledPrices[] = $value;
        } else {
            $this->labeledPrices[$offset] = $value;
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
        $this->sumFigures = FiguresRepresentation::concatNumbersWithCorrectCountOfEndFigures($startSum, $endSum, Telegram::LENGTH_AMOUNT_END_FIGURES);
        return $this;
    }

    private function getStartFigures(TelegramLabeledPrice|string $labeledPrice): string
    {
        $stringPrice = $this->getConvertedToString($labeledPrice);
        $length = \strlen($stringPrice);
        return \substr($stringPrice, 0, $length - Telegram::LENGTH_AMOUNT_END_FIGURES);
    }

    private function getEndFigures(TelegramLabeledPrice|string $labeledPrice): string
    {
        $stringPrice = $this->getConvertedToString($labeledPrice);
        return \substr($stringPrice, -1 * (int)\abs(Telegram::LENGTH_AMOUNT_END_FIGURES));
    }

    private function getStartNumber(TelegramLabeledPrice|string $labeledPrice): int
    {
        return (int)$this->getStartFigures($labeledPrice);
    }

    private function getEndNumber(TelegramLabeledPrice|string $labeledPrice): int
    {
        return (int)$this->getEndFigures($labeledPrice);
    }

    private function getConvertedToString(TelegramLabeledPrice|string $labeledPrice): string
    {
        if ($labeledPrice instanceof TelegramLabeledPrice) {
            $labeledPrice = $labeledPrice->getAmountWithEndFigures();
        }
        if (!\is_string($labeledPrice)) {
            throw new \RuntimeException('Price must be string');
        }
        $this->validateStringNumber($labeledPrice);
        return $labeledPrice;
    }

    private function validateStringNumber(string $stringNumber): string
    {
        if (!Validation::createIsValidCallable(new TelegramAmountWithEndFigures())($stringNumber)) {
            throw new \RangeException(\sprintf('String "%s" not a number', $stringNumber));
        }
        return $stringNumber;
    }

    private function getAddedStartEndSumNumbers(TelegramLabeledPrice $price): array
    {
        $startSumNumber = $this->getStartNumber($this->sumFigures);
        $endSumNumber = $this->getEndNumber($this->sumFigures);

        $startPriceNumber = $this->getStartNumber($price);
        $endPriceNumber = $this->getEndNumber($price);

        $startSum = $startSumNumber + $startPriceNumber;
        $endSum = $endSumNumber + $endPriceNumber;

        $part = 10 ** Telegram::LENGTH_AMOUNT_END_FIGURES;

        // 199 -> 1 + startSum
        $frontNumbersEndSum = (int)($endSum / $part);
        $startSum += $frontNumbersEndSum;

        // 199 -> 99
        $endSum = (int)($endSum % $part);

        return [$startSum, $endSum];
    }
}
