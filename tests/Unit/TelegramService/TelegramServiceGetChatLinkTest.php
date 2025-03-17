<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(Telegram::class, 'getChatLink')]
class TelegramServiceGetChatLinkTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function processGetContentResponseMock(
        MockObject $responseMock,
    ): InvocationMocker
    {
        return $responseMock
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"ok":true,"result":{"username":"test"}}')//
            ;
    }

    protected function assertSuccessfulPayload(mixed $payload): void
    {
        static::assertTrue(\is_string($payload));
    }

    protected function assertFailedPayload(mixed $payload): void
    {
        $this->assertFalse($payload);
    }

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'getChat';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'chat_id' => 'TEST',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->getChatLink(
            chatId: 'TEST',
            throw: $throw,
        );
    }
}
