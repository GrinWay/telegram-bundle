<?php

namespace GrinWay\Telegram\Tests\Unit\Command;

use GrinWay\Telegram\Bot\Command\TelegramRemoveWebhookCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(TelegramRemoveWebhookCommand::class)]
class TelegramRemoveWebhookCommandTest extends AbstractTelegramWebhookCommandTestCase
{
    protected function getCommandName(): string
    {
        return TelegramRemoveWebhookCommand::NAME;
    }

    protected function getTelegramServiceMethod(): string
    {
        return 'removeWebhook';
    }

    protected function getExpectedDisplayText(): string
    {
        return 'REMOVED';
    }

    protected function processTelegramMock(MockObject $telegramMock): void
    {
        $telegramMock
            ->expects(self::never())
            ->method('setWebhook')//
        ;
    }

    protected function getFlags(): array
    {
        return [];
    }
}
