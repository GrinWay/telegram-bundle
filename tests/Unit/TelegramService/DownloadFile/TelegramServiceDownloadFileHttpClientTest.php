<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\DownloadFile;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Test\Trait\TelegramService\TelegramGrinWayHttpClientRequestTestAware;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversMethod(Telegram::class, 'downloadFile')]
class TelegramServiceDownloadFileHttpClientTest extends AbstractTelegramServiceDownloadFileTestCase
{
    use TelegramGrinWayHttpClientRequestTestAware;

    protected function processGetContentResponseMock(
        MockObject $responseMock,
    ): InvocationMocker
    {
        return $responseMock
            ->expects($this->exactly(1))
            ->method('getContent')
            ->willReturn('{"ok":true,"result":{"file_path":"fake"}}')//
            ;
    }

    protected function processRequestGrinWayTelegramHttpClientWillReturnPayloadMock(
        MockObject $grinwayTelegramClientMock,
        string     $telegramMethod,
    ): InvocationMocker
    {
        return $grinwayTelegramClientMock
            ->expects(self::exactly(1))
            ->method('request')
            ->with(
                self::identicalTo($this->getMethodMethodGrinwayTelegramClient()),
                self::identicalTo($telegramMethod),
                self::equalTo($this->getRequestJsonGrinWayHttpClientTestAware()),
            );
    }

    protected function assertSuccessfulPayload(mixed $payload): void
    {
        $this->assertTrue($payload);
    }

    protected function assertFailedPayload(mixed $payload): void
    {
        $this->assertFalse($payload);
    }

    protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string
    {
        return 'getFile';
    }

    protected function getRequestJsonGrinWayHttpClientTestAware(): array
    {
        return [
            'json' => [
                'file_id' => 'TEST',
            ],
        ];
    }

    protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed
    {
        return $telegram->downloadFile(
            fileId: 'TEST',
            absFilepathTo: static::$absFilepath,
            overwrite: true,
            throw: $throw,
        );
    }
}
