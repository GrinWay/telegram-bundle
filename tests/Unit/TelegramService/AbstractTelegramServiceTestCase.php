<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService;

use GrinWay\Telegram\Tests\AbstractTelegramTestCase;

abstract class AbstractTelegramServiceTestCase extends AbstractTelegramTestCase
{
    protected string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = self::getContainer()->getParameter('kernel.cache_dir');
    }
}
