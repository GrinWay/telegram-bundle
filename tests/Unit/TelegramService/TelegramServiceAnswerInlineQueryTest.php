<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'answerInlineQuery')]
class TelegramServiceAnswerInlineQueryTest extends AbstractTelegramServiceTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'answerInlineQuery';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'inline_query_id' => 'TEST',
                'results' => [
                    [
                        'id' => 'TEST ID',
                        'type' => 'TEST TYPE',
                        'audio_file_id' => 'TEST AUDIO',
                    ],
                ],
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->$method(
            inlineQueryId: 'TEST',
            type: 'TEST TYPE',
            results: [
                'audio_file_id' => 'TEST AUDIO',
            ],
            id: 'TEST ID',
            throw: $throw,
        );
    }
}
