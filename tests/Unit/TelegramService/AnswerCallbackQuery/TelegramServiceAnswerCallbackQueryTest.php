<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\AnswerCallbackQuery;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Test\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'answerCallbackQuery')]
class TelegramServiceAnswerCallbackQueryTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'answerCallbackQuery';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'callback_query_id' => 'TEST',
                'text' => 'TEST TEXT',
                'show_alert' => false,
                'url' => 'TEST URL',
                'cache_time' => 2,
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->$method(
            callbackQueryId: 'TEST',
            text: 'TEST TEXT',
            showAlert: false,
            prependJsonRequest: [
                'url' => 'TEST URL',
                'cache_time' => 42,
            ],
            appendJsonRequest: [
                'cache_time' => 2,
            ],
            throw: $throw,
        );
    }
}
