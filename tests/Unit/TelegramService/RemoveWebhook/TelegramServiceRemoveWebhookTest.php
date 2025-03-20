<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\RemoveWebhook;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Test\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
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
