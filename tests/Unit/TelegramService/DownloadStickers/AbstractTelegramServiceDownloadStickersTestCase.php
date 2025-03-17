<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\DownloadStickers;

use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;

abstract class AbstractTelegramServiceDownloadStickersTestCase extends AbstractTelegramServiceTestCase
{
    protected static string $existingAbsDir;

    protected function setUp(): void
    {
        parent::setUp();

        static::$existingAbsDir = \sprintf('%s/stickers', $this->cacheDir);
    }
}
