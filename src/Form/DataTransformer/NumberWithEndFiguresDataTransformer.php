<?php

namespace GrinWay\Telegram\Form\DataTransformer;

use GrinWay\Service\Service\FiguresRepresentation;
use Symfony\Component\Form\DataTransformerInterface;

class NumberWithEndFiguresDataTransformer implements DataTransformerInterface
{
    /**
     * to form (100002 -> 1 000.02)
     */
    public function transform(mixed $value): mixed
    {
        try {
            if (!\is_string($value)) {
                return $value;
            }

            $startEndNumber = FiguresRepresentation::getStartEndNumbersWithEndFigures(
                $value,
                2,
            );

            $startEndNumber[0] = \number_format(
                $startEndNumber[0],
                0,
                '',
                thousands_separator: ' ',
            );

            return \implode('.', $startEndNumber);
        } catch (\Exception) {
            return $value;
        }
    }

    /**
     * to model (1 000.02 -> 100002)
     */
    public function reverseTransform(mixed $value): mixed
    {
        try {
            if (!\is_string($value)) {
                return $value;
            }

            $value = \str_replace(' ', '', $value);

            return FiguresRepresentation::getStringWithEndFigures($value, 2);
        } catch (\Exception) {
            return $value;
        }
    }
}
