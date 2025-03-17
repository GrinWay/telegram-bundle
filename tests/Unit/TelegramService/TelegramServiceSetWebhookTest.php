<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
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
                'drop_pending_updates' => true,
                'url' => \sprintf('https://%s%s', 'example.com:80', static::WEBHOOK),
            ],
            'timeout' => 30,
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->$method(
            prependRequestOptions: [
                'query' => [
                    'drop_pending_updates' => true,
                ],
            ],
            throw: $throw,
        );
    }
}
