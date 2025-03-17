<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\DownloadStickers\DownloadStickersHttpClient;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use GrinWay\Telegram\Tests\Unit\TelegramService\DownloadStickers\AbstractTelegramServiceDownloadStickersTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(Telegram::class, 'downloadStickers')]
class TelegramServiceDownloadStickersHttpClientTest extends AbstractTelegramServiceDownloadStickersTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function processGetContentResponseMock(
        MockObject $responseMock,
    ): InvocationMocker
    {
        return $responseMock
            ->expects($this->exactly(2))
            ->method('getContent')
            ->willReturn('{"ok":true,"result":{"file_path":"fake","stickers":[{"file_id":"fake", "emoji":"🧪"},{"file_id":"fake", "emoji":"🧪"},{"file_id":"fake", "emoji":"🧪"},{"file_id":"fake", "emoji":"🧪"}]}}')//
            ;
    }

    protected function processRequestGrinWayTelegramHttpClientWillReturnPayloadMock(
        MockObject $grinwayTelegramClientMock,
        string     $telegramMethod,
    ): InvocationMocker
    {
        return $grinwayTelegramClientMock
            ->expects(self::exactly(2))
            ->method('request')
            ->with(
                self::identicalTo($this->getMethodMethodGrinwayTelegramClient()),
                self::logicalXor(
                    self::identicalTo($telegramMethod),
                    self::identicalTo('getFile'),
                ),
                self::logicalXor(
                    self::equalTo($this->getRequestJsonGrinWayHttpClientTestAware()),
                    self::equalTo([
                        'json' => [
                            'file_id' => 'fake',
                        ],
                    ]),
                ),
            );
    }

    protected function assertSuccessfulPayload(mixed $payload): void
    {
        $this->assertTrue(\is_array($payload));
    }

    protected function assertFailedPayload(mixed $payload): void
    {
        $this->assertSame([], $payload);
    }

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'getStickerSet';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'name' => 'TEST',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: static::$existingAbsDir,
            overwrite: true,
            limit: 1,
            throw: $throw,
        );
    }
}
