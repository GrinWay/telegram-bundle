<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Payment;

use GrinWay\Telegram\Bot\Contract\Topic\SuccessfulPaymentMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\AbstractMessageTopicHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\Message\ChatMessage;

/**
 * When a payment was successfully finished (successfully because of current supports realization)
 *
 * https://core.telegram.org/bots/payments-stars#4-checkout
 */
abstract class AbstractSuccessfulPaymentMessageHandler extends AbstractMessageTopicHandler implements SuccessfulPaymentMessageHandlerInterface
{
    protected ?array $successfulPaymentPayload = null;

    public function supports(mixed $fieldValue): bool
    {
        return parent::supports($fieldValue)
            && $this->guaranteePaymentIsSuccessful($fieldValue);
    }

    abstract protected function doSuccessfulPaymentHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        \assert(null !== $this->successfulPaymentPayload);

        return $this->doSuccessfulPaymentHandle($chatMessage, $telegramOptions, $fieldValue);
    }

    /**
     * According to the https://core.telegram.org/bots/payments-stars#4-checkout
     */
    protected function guaranteePaymentIsSuccessful(mixed $fieldValue)
    {
        $this->successfulPaymentPayload = $this->pa->getValue($fieldValue, '[successful_payment]');
        return null !== $this->successfulPaymentPayload;
    }
}
