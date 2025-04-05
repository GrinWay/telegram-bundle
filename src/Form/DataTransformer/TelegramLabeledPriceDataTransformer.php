<?php

namespace GrinWay\Telegram\Form\DataTransformer;

use GrinWay\Telegram\Type\TelegramLabeledPrice;
use Symfony\Component\Form\DataTransformerInterface;

class TelegramLabeledPriceDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly bool $modelUsesTelegramLabeledPriceType = false,
    )
    {
    }

    /**
     * to form
     */
    public function transform(mixed $value): mixed
    {
        try {
            if (!$value instanceof TelegramLabeledPrice) {
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

            if ($this->modelUsesTelegramLabeledPriceType) {
                return TelegramLabeledPrice::fromArray($value);
            } else {
                return $value;
            }
        } catch (\Exception) {
            return $value;
        }
    }
}
