<?php

namespace GrinWay\Telegram\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class StringNumberWithEndFigures extends Constraint
{
    public string $message = 'The value "{{ value }}" is not valid telegram amount representation, valid is "100" for "1.00".';
}
