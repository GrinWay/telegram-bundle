<?php

namespace GrinWay\Telegram\Tests\Unit\TelegramService\DownloadFile;

use GrinWay\Telegram\Service\Telegram;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(Telegram::class, 'downloadFile')]
class TelegramServiceDownloadFileTest extends AbstractTelegramServiceDownloadFileTestCase
{
    public function testSuccessfulDownloadFileByFileIdWithAbsoluteFilename()
    {
        \unlink(static::$absFilepath);

        $wasDownloaded = static::$telegram->downloadFile(
            fileId: 'TEST_FILE_ID',
            absFilepathTo: static::$absFilepath,
            overwrite: true,
            throw: false,
        );
        $this->assertTrue($wasDownloaded);
        $this->assertFileExists(static::$absFilepath);
    }

    public function testDoNotDownloadFileByFileIdWithNotAbsoluteFilename()
    {
        $absFilepath = 'tg_test_file.txt';

        $wasDownloaded = static::$telegram->downloadFile(
            fileId: 'TEST_FILE_ID',
            absFilepathTo: $absFilepath,
            overwrite: true,
            throw: false,
        );
        $this->assertFalse($wasDownloaded);
        $this->assertFileDoesNotExist($absFilepath);
    }
}
