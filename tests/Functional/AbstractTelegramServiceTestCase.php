<?php

namespace GrinWay\Telegram\Tests\Functional;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\AbstractTelegramTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(Telegram::class)]
abstract class AbstractTelegramServiceTestCase extends AbstractTelegramTestCase
{
    protected HttpClientInterface $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        static::ensureKernelShutdown();
        // in order to use fixer API fake data always (fill in the cache with fake data)
        static::setUpGrinWayServiceMockedDependencies();
        static::$telegram = static::getContainer()->get('grinway_telegram');

        $this->httpClient = static::getContainer()->get(HttpClientInterface::class);
    }
}
