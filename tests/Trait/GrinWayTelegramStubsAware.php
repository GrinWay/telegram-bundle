<?php

namespace GrinWay\Telegram\Tests\Trait;

use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

trait GrinWayTelegramStubsAware
{
    protected static Telegram $telegram;

    abstract protected static function isStubGrinwayTelegramClient(): bool;

    abstract protected static function getStubGrinwayTelegramClientResponseBody(): string;

    abstract protected static function isStubGrinwayTelegramFileClient(): bool;

    abstract protected static function getStubGrinwayTelegramFileClientResponseBody(): string;

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

        static::$telegram = static::getContainer()->get('grinway_telegram');
    }
}
