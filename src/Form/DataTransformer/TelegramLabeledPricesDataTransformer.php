<?php

namespace GrinWay\Telegram\Form\DataTransformer;

use GrinWay\Telegram\Type\TelegramLabeledPrices;
use Symfony\Component\Form\DataTransformerInterface;

class TelegramLabeledPricesDataTransformer implements DataTransformerInterface
{
    /**
     * to form
     */
    public function transform(mixed $value): mixed
    {
        try {
            if (!$value instanceof TelegramLabeledPrices) {
                return $value;
            }

            return $value->toArray();
        } catch (\Exception) {
            return $value;
        }
    }

    /**
     * to model
     */
    public function reverseTransform(mixed $value): mixed
    {
        try {
            if (!\is_array($value)) {
                return $value;
            }

            return TelegramLabeledPrices::fromArray($value);
        } catch (\Exception) {
            return $value;
        }
    }
}
