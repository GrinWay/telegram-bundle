<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'deleteMessage')]
class TelegramServiceDeleteMessageTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'deleteMessage';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'chat_id' => 'TEST',
                'message_id' => 'TEST MESSAGE ID',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->$method(
            chatId: 'TEST',
            messageId: 'TEST MESSAGE ID',
            throw: $throw,
        );
    }
}
