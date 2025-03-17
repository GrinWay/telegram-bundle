<?php

namespace GrinWay\Telegram\Tests\Trait\TelegramService;

use GrinWay\Telegram\Service\Telegram;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

trait TelegramGrinWayHttpClientRequestTestAware
{
    abstract protected function getTelegramApiMethodGrinWayHttpClientTestAware(): string;

    abstract protected function makeMethodCall(Telegram $telegram, string $method, bool $throw): mixed;

    abstract protected function getRequestJsonGrinWayHttpClientTestAware(): array;

    protected static function isStubGrinwayTelegramClient(): bool
    {
        return false;
    }

    protected function getMethodMethodGrinwayTelegramClient(): string
    {
        return 'POST';
    }

    protected function assertSuccessfulPayload(mixed $payload): void
    {
        static::assertTrue(Telegram::isResponseOk($payload));
    }

    protected function assertFailedPayload(mixed $payload): void
    {
        $this->assertSame(Telegram::FAILURE_RESPONSE, $payload);
    }

    protected function processGetContentResponseMock(
        MockObject $responseMock,
    ): InvocationMocker
    {
        return $responseMock
            ->expects($this->once())
            ->method('getContent')
            ->willReturn('{"ok":true}')//
        ;
    }

    protected function processRequestGrinWayTelegramHttpClientWillReturnPayloadMock(
        MockObject $grinwayTelegramClientMock,
        string     $telegramMethod,
    ): InvocationMocker
    {
        return $grinwayTelegramClientMock
            ->expects(self::once())
            ->method('request')
            ->with(
                self::identicalTo($this->getMethodMethodGrinwayTelegramClient()),
                self::identicalTo($telegramMethod),
                self::equalTo($this->getRequestJsonGrinWayHttpClientTestAware()),
            );
    }

    protected function processRequestGrinWayTelegramHttpClientWillThrowMock(
        MockObject $grinwayTelegramClientMock,
        string     $telegramMethod,
    ): InvocationMocker
    {
        return $grinwayTelegramClientMock
            ->expects(self::once())
            ->method('request')
            ->with(
                self::identicalTo($this->getMethodMethodGrinwayTelegramClient()),
                self::identicalTo($telegramMethod),
                self::equalTo($this->getRequestJsonGrinWayHttpClientTestAware()),
            );
    }

    public function testHttpRequestWasMadeExactlyWithPassedArgs()
    {
        $telegramMethod = $this->getTelegramApiMethodGrinWayHttpClientTestAware();

        $responseMock = $this->createMock(ResponseInterface::class);
        static::processGetContentResponseMock($responseMock);

        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $this->processRequestGrinWayTelegramHttpClientWillReturnPayloadMock(
            grinwayTelegramClientMock: $httpClientMock,
            telegramMethod: $telegramMethod,
        )
            ->willReturn($responseMock)//
        ;
        static::getContainer()->set(
            \sprintf('%s $grinwayTelegramClient', HttpClientInterface::class),
            $httpClientMock,
        );

        $payload = $this->makeMethodCall(
            telegram: static::$telegram,
            method: $telegramMethod,
            throw: false,
        );

        $this->assertSuccessfulPayload(payload: $payload);
    }

    public function testFailureResponseReturnedOnBadRequestWhenNotThrow()
    {
        $telegramMethod = $this->getTelegramApiMethodGrinWayHttpClientTestAware();

        $grinwayTelegramClientMock = $this->createMock(HttpClientInterface::class);
        $this->processRequestGrinWayTelegramHttpClientWillThrowMock(
            grinwayTelegramClientMock: $grinwayTelegramClientMock,
            telegramMethod: $telegramMethod,
        )
            ->willThrowException(new HttpException(404, 'Bad request'))//
        ;
        static::getContainer()->set(
            \sprintf('%s $grinwayTelegramClient', HttpClientInterface::class),
            $grinwayTelegramClientMock,
        );

        $payload = $this->makeMethodCall(
            telegram: static::$telegram,
            method: $telegramMethod,
            throw: false,
        );

        $this->assertFailedPayload(payload: $payload);
    }

    public function testExceptionOnBadRequestWhenThrow()
    {
        $telegramMethod = $this->getTelegramApiMethodGrinWayHttpClientTestAware();

        $grinwayTelegramClientMock = $this->createMock(HttpClientInterface::class);
        $this->processRequestGrinWayTelegramHttpClientWillThrowMock(
            grinwayTelegramClientMock: $grinwayTelegramClientMock,
            telegramMethod: $telegramMethod,
        )
            ->willThrowException(new HttpException(404, 'Bad request'))//
        ;
        static::getContainer()->set(
            \sprintf('%s $grinwayTelegramClient', HttpClientInterface::class),
            $grinwayTelegramClientMock,
        );

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Bad request');
        $this->makeMethodCall(
            telegram: static::$telegram,
            method: $telegramMethod,
            throw: true,
        );
    }
}
