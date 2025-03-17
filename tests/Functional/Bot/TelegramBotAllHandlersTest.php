<?php

namespace GrinWay\Telegram\Tests\Functional\Bot;

use GrinWay\Telegram\Bot\Handler\Topic\CallbackQuery\AbstractCallbackQueryHandler;
use GrinWay\Telegram\Bot\Handler\Topic\InlineQuery\AbstractInlineQueryHandler;
use GrinWay\Telegram\Bot\Handler\Topic\Payment\PreCheckoutQuery\AbstractPreCheckoutQueryHandler;
use GrinWay\Telegram\Bot\Handler\Topic\Payment\ShippingQuery\AbstractShippingQueryHandler;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Command\AbstractCommandHandler;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Command\LowPriority\AbstractNullResponseToIncorrectCommandHandler;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Group\AbstractGroupMessageHandler;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\Payment\AbstractSuccessfulPaymentMessageHandler;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\PrivateChat\AbstractPrivateChatHandler;
use GrinWay\Telegram\Bot\Handler\Topic\ReplyToMessage\AbstractReplyToMessageHandler;
use GrinWay\Telegram\Bot\Test\CallbackQuery\TestCallbackQueryHandler;
use GrinWay\Telegram\Bot\Test\InlineQuery\TestInlineQueryHandler;
use GrinWay\Telegram\Bot\Test\Payment\PreCheckoutQuery\TestPreCheckoutQueryHandler;
use GrinWay\Telegram\Bot\Test\Payment\ShippingQuery\TestShippingQueryHandler;
use GrinWay\Telegram\Bot\Test\PriorityAble\Message\Command\TestCommandHandler;
use GrinWay\Telegram\Bot\Test\PriorityAble\Message\Group\TestGroupMessageHandler;
use GrinWay\Telegram\Bot\Test\PriorityAble\Message\PrivateChat\TestPrivateChatHandler;
use GrinWay\Telegram\Bot\Test\ReplyToMessage\TestReplyToMessageHandler;
use GrinWay\Telegram\GrinWayTelegramBundle;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractCallbackQueryHandler::class)]
#[CoversClass(AbstractInlineQueryHandler::class)]
#[CoversClass(AbstractPreCheckoutQueryHandler::class)]
#[CoversClass(AbstractShippingQueryHandler::class)]
#[CoversClass(AbstractCommandHandler::class)]
#[CoversClass(AbstractNullResponseToIncorrectCommandHandler::class)]
#[CoversClass(AbstractGroupMessageHandler::class)]
#[CoversClass(AbstractSuccessfulPaymentMessageHandler::class)]
#[CoversClass(AbstractPrivateChatHandler::class)]
#[CoversClass(AbstractReplyToMessageHandler::class)]
class TelegramBotAllHandlersTest extends AbstractTopicHandlerTestCase
{
    public function testCallbackQueryHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$callbackQueryPayload,
            1,
            TestCallbackQueryHandler::SUBJECT,
        );
    }

    public function testInlineQueryHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$inlineQueryPayload,
            1,
            TestInlineQueryHandler::SUBJECT,
        );
    }

    public function testPaymentShippingQueryMeansAddressRequiredHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$paymentShippingQueryPayload,
            1,
            TestShippingQueryHandler::SUBJECT,
        );
    }

    public function testPaymentPayButtonPushedMeansPreCheckoutQueryHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$paymentPreCheckoutQueryPayload,
            1,
            TestPreCheckoutQueryHandler::SUBJECT,
        );
    }

    public function testExistentNotFullNamedCommandMessageFromPrivateChatHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$existentNotFullNamedCommandFromPrivateChatPayload,
            1,
            TestCommandHandler::SUBJECT,
        );
    }

    public function testExistentNotFullNamedCommandMessageFromChatNotHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$existentNotFullNamedCommandFromChatPayload,
            0,
        );
    }

    public function testExistentFullCommandMessageFromPrivateChatHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$existentFullCommandFromPrivateChatPayload,
            1,
            TestCommandHandler::SUBJECT,
        );
    }

    public function testExistentFullCommandMessageFromChatHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$existentFullCommandFromChatPayload,
            1,
            TestCommandHandler::SUBJECT,
        );
    }

    public function testNonExistentCommandMessageFromChatNotHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$nonExistentCommandFromChatPayload,
            0,
        );
    }

    public function testNonExistentCommandMessageFromPrivateChatNotHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$nonExistentCommandFromPrivateChatPayload,
            0,
        );
    }

    public function testGroupMessageHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$groupPayload,
            1,
            TestGroupMessageHandler::SUBJECT,
        );
    }

    public function testPrivateChatMessageHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$privateChatPayload,
            1,
            TestPrivateChatHandler::SUBJECT,
        );
    }

    public function testReplyToMessageFromPrivateChatHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$replyToMessageFromPrivateChatPayload,
            1,
            TestReplyToMessageHandler::SUBJECT,
        );
    }

    public function testReplyToMessageFromChatHandled()
    {
        $this->assertTelegramBotHandledPayload(
            static::$replyToMessageFromChatPayload,
            1,
            TestReplyToMessageHandler::SUBJECT,
        );
    }
}
