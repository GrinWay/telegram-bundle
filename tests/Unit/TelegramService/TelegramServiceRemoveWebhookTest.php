<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'removeWebhook')]
class TelegramServiceRemoveWebhookTest extends AbstractTelegramServiceTestCase
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
                'non_existent_parameter' => false,
                'url' => '',
            ],
            'timeout' => 30,
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->removeWebhook(
            prependRequestOptions: [
                'query' => [
                    'non_existent_parameter' => false,
                ],
            ],
            throw: $throw,
        );
    }
}
