<?php

namespace GrinWay\Telegram\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * https://core.telegram.org/bots/api#chatfullinfo
 */
class TgAccountUserNameIdDataTransformer implements DataTransformerInterface
{
    /**
     * to form (id -> username) POINTLESS BECAUSE OF THE BELOW
     */
    public function transform(mixed $value): mixed
    {
        return $value;
    }

    /**
     * to model (username -> id) AT THIS MOMENT IMPOSSIBLE
     */
    public function reverseTransform(mixed $value): mixed
    {
        return $value;
    }
}
