<?php

    namespace GrinWay\Telegram\Validator;

use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use function Symfony\Component\String\u;

class TelegramAmountWithEndFiguresValidator extends ConstraintValidator
{
    public function validate(mixed $amount, Constraint $constraint): void
    {
        /* @var TelegramAmountWithEndFigures $constraint */

        if (null === $amount || '' === $amount) {
            return;
        }

        $validTelegramAmountRegex = \sprintf(
            '~^(?<valid_telegram_amount>[0-9]{%s,})$~',
            Telegram::LENGTH_AMOUNT_END_FIGURES + 1,
        );
        if (null === u($amount)->match($validTelegramAmountRegex)['valid_telegram_amount'] ?? null) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ amount }}', $amount)
                ->addViolation();
        }
    }
}
