<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\SendMessage;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'sendMessage')]
class TelegramServiceSendMessageTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'sendMessage';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'chat_id' => 'TEST',
                'text' => 'text',
                'prependJsonRequest' => 'prependJsonRequestValue',
                'appendJsonRequest' => 'appendJsonRequestValue',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->$method(
            chatId: 'TEST',
            text: 'text',
            prependJsonRequest: [
                'prependJsonRequest' => 'prependJsonRequestValue',
            ],
            appendJsonRequest: [
                'appendJsonRequest' => 'appendJsonRequestValue',
            ],
            throw: $throw,
        );
    }
}
