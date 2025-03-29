<?php

namespace GrinWay\Telegram\Test\Trait;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait GrinWayTelegramStubsAware
{
    abstract protected static function isStubGrinWayTelegramClient(): bool;

    abstract protected static function isStubGrinwayTelegramFileClient(): bool;

    protected static function getStubGrinwayTelegramClientResponseBody(): string {
        return '{}';
    }

    protected static function getStubGrinwayTelegramFileClientResponseBody(): string {
        return '{}';
    }

    protected static function setUpGrinWayTelegramMockedDependencies(): void
    {
        if (true === static::isStubGrinwayTelegramClient()) {
            $grinwayTelegramClientResponseGenerator = static function (): \Generator {
                while (true) {
                    yield new MockResponse(static::getStubGrinwayTelegramClientResponseBody());
                }
            };
            static::getContainer()->set(
                \sprintf('%s $grinwayTelegramClient', HttpClientInterface::class),
                new MockHttpClient(
                    $grinwayTelegramClientResponseGenerator(),
                ),
            );
        }

        if (true === static::isStubGrinwayTelegramFileClient()) {
            $grinwayTelegramFileClientResponseGenerator = static function (): \Generator {
                while (true) {
                    yield new MockResponse(static::getStubGrinwayTelegramFileClientResponseBody());
                }
            };
            static::getContainer()->set(
                \sprintf('%s $grinwayTelegramFileClient', HttpClientInterface::class),
                new MockHttpClient(
                    $grinwayTelegramFileClientResponseGenerator(),
                ),
            );
        }
    }
}
