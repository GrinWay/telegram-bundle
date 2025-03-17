<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\DownloadFile;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Unit\TelegramService\AbstractTelegramServiceTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

abstract class AbstractTelegramServiceDownloadFileTestCase extends AbstractTelegramServiceTestCase
{
    protected static string $absFilepath;

    protected function setUp(): void
    {
        parent::setUp();

        static::$absFilepath = \sprintf('%s/tg_test_file.txt', $this->cacheDir);
    }
}
