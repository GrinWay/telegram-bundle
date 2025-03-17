<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\DownloadStickers\DownloadStickers;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\Unit\TelegramService\DownloadStickers\AbstractTelegramServiceDownloadStickersTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'downloadStickers')]
class TelegramServiceDownloadStickersTest extends AbstractTelegramServiceDownloadStickersTestCase
{
    public function testSuccessfulDownloadingStickersWithAbsoluteDir()
    {
        $madeAbsPaths = static::$telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: static::$existingAbsDir,
            overwrite: true,
            prefixFilename: 'TEST_',
            limit: 3,
            stickerFileExtension: 'txt', // use default value instead
            throw: false,
        );

        $this->assertCount(3, $madeAbsPaths);

        foreach ($madeAbsPaths as $key => $madeAbsPath) {
            $this->assertSame($key, $madeAbsPath);
            $this->assertFileExists($madeAbsPath);
        }
    }

    public function testDoNotDownloadStickersWithNotAbsoluteDir()
    {
        $absDir = 'stickers';

        $madeAbsPaths = static::$telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: $absDir,
            overwrite: true,
            prefixFilename: 'TEST_',
            limit: 3,
            stickerFileExtension: 'txt', // use default value instead
            throw: false,
        );

        $this->assertCount(0, $madeAbsPaths);
    }
}
