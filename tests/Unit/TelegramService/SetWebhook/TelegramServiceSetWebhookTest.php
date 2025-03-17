<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\SetWebhook;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'setWebhook')]
class TelegramServiceSetWebhookTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getMethodMethodGrinwayTelegramClient(): string
    {
        return 'GET';
    }

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'setWebhook';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'query' => [
                'url' => \sprintf('https://%s%s', 'example.com:80', static::WEBHOOK),
            ],
            'timeout' => 30,
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->$method(
            throw: $throw,
        );
    }
}
