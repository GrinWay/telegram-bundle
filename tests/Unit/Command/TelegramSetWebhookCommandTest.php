<?php

namespace GrinWay\Telegram\Tests\Unit\Command;

use GrinWay\Telegram\Bot\Command\TelegramSetWebhookCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(TelegramSetWebhookCommand::class)]
class TelegramSetWebhookCommandTest extends AbstractTelegramWebhookCommandTestCase
{
    protected function getCommandName(): string
    {
        return TelegramSetWebhookCommand::NAME;
    }

    protected function getTelegramServiceMethod(): string
    {
        return 'setWebhook';
    }

    protected function getExpectedDisplayText(): string
    {
        return 'SET';
    }

    protected function processTelegramMock(MockObject $telegramMock): void
    {
        $telegramMock
            ->expects(self::never())
            ->method('removeWebhook')//
        ;
    }

    protected function getFlags(): array
    {
        return [
            '--drop-pending-updates' => null,
        ];
    }
}
