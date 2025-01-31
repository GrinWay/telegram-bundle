<?php

namespace GrinWay\Telegram\Tests\Unit;

use GrinWay\Telegram\Service\Telegram;
use GrinWay\Telegram\Tests\AbstractTelegramTestCase;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Telegram::class)]
class TelegramServiceTest extends AbstractTelegramTestCase
{
    protected string $cacheDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheDir = self::getContainer()->getParameter('kernel.cache_dir');
    }

    public function testSetWebhookWithNotEmptyAppHost()
    {
        $response = $this->telegram->setWebhook();
        $this->assertSame(true, $response['ok'] ?? null);
    }

    public function testRemoveWebhook()
    {
        $response = $this->telegram->removeWebhook();
        $this->assertSame(true, $response['ok'] ?? null);
    }

    public function testSuccessfulDownloadFileByFileIdWithAbsoluteFilename()
    {
        $absFilepath = \sprintf('%s/tg_test_file.txt', $this->cacheDir);
        \unlink($absFilepath);

        $wasDownloaded = $this->telegram->downloadFile(
            fileId: 'TEST_FILE_ID',
            absFilepathTo: $absFilepath,
            overwrite: true,
            throw: true,
        );
        $this->assertTrue($wasDownloaded);
        $this->assertFileExists($absFilepath);
    }

    public function testDoNotDownloadFileByFileIdWithNotAbsoluteFilename()
    {
        $absFilepath = 'tg_test_file.txt';

        $wasDownloaded = $this->telegram->downloadFile(
            fileId: 'TEST_FILE_ID',
            absFilepathTo: $absFilepath,
            overwrite: true,
            throw: false,
        );
        $this->assertFalse($wasDownloaded);
        $this->assertFileDoesNotExist($absFilepath);

        $this->expectException(\InvalidArgumentException::class);
        $this->telegram->downloadFile(
            fileId: 'TEST_FILE_ID',
            absFilepathTo: $absFilepath,
            overwrite: true,
            throw: true,
        );
    }

    public function testSuccessfulDownloadingStickersWithAbsoluteDir()
    {
        $absDir = \sprintf('%s/stickers', $this->cacheDir);

        $madeAbsPaths = $this->telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: $absDir,
            overwrite: true,
            prefixFilename: 'TEST_',
            limit: 3,
            stickerFileExtension: 'txt', // use default value instead
            throw: true,
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

        $madeAbsPaths = $this->telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: $absDir,
            overwrite: true,
            prefixFilename: 'TEST_',
            limit: 3,
            stickerFileExtension: 'txt', // use default value instead
            throw: false,
        );

        $this->assertCount(0, $madeAbsPaths);

        $this->expectException(\InvalidArgumentException::class);
        $this->telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: $absDir,
            overwrite: true,
            prefixFilename: 'TEST_',
            limit: 3,
            stickerFileExtension: 'txt', // use default value instead
            throw: true,
        );
    }

    public function testDeleteMessage()
    {
        $ok = $this->telegram->deleteMessage(
            chatId: 'TEST',
            messageId: 'TEST',
            throw: true,
        );
        $this->assertTrue($ok);
    }

    public function testPreCheckoutQuerySuccessfulOnValidPayload()
    {
        /*
         * Depending on payload you decide this payment successful or not
         *
         * I decided payload is valid
         */
        $isValid = static fn() => true;

        $ok = $this->telegram->answerPreCheckoutQuery(
            preCheckoutQueryId: 'TEST', // in the webhook handler you will get a real pre_checkout_query_id in the payload
            preCheckoutQueryIsValid: $isValid(),
            throw: true,
        );

        $this->assertTrue($ok);
    }

    public function testPreCheckoutQueryNotSuccessfulOnInvalidPayload()
    {
        /*
         * Depending on payload you decide this payment successful or not
         *
         * I decided payload is not valid
         *
         * And I write an error message but not bool false!
         */
        $isValid = static fn() => 'An error happened';

        $ok = $this->telegram->answerPreCheckoutQuery(
            preCheckoutQueryId: 'TEST', // in the webhook handler you will get a real pre_checkout_query_id in the payload
            preCheckoutQueryIsValid: $isValid(),
            throw: true,
        );

        $this->assertFalse($ok);
    }

    public function testAnswerInlineQuery()
    {
        /**
         * For you when webhook will be called you will get "inlineQueryId" in the payload
         */
        $ok = $this->telegram->answerInlineQuery(
            inlineQueryId: 'TEST',
            type: 'gif',
            results: [
                'gif_url' => 'https://test.test/gif.gif',
            ],
            throw: true,
        );

        $this->assertTrue($ok);
    }

    public function testAnswerShippingQuerySuccessfulOnValidPayload()
    {
        /*
         * You will get "shipping_query_id" in your real webhook
         * when an invoice was created by these rules https://core.telegram.org/bots/api#sendinvoice
         *
         * webhook will be called by the Telegram Bot Api
         *
         * I decided payload is valid
         */
        $ok = $this->telegram->answerShippingQuery(
            shippingQueryId: 'TEST',
            shippingOptions: [],
            shippingQueryIsValid: true,
            throw: true,
        );

        $this->assertTrue($ok);
    }

    public function testAnswerShippingQueryNotSuccessfulOnInvalidPayload()
    {
        /*
         * You will get "shipping_query_id" in your real webhook when it's called by the Telegram Bot Api
         *
         * I decided payload is not valid
         */
        $ok = $this->telegram->answerShippingQuery(
            shippingQueryId: 'TEST',
            shippingOptions: [],
            shippingQueryIsValid: 'ERROR',
            throw: true,
        );

        $this->assertFalse($ok);
    }

    public function testSendInvoice()
    {
        $ok = $this->telegram->sendInvoice(
            chatId: $this->telegramBotTestChatId,
            title: 'title',
            description: 'description',
            prices: new TelegramLabeledPrices(
                new TelegramLabeledPrice('label 1', '100'),
            ),
            providerToken: $this->telegramTestPaymentProviderToken,
            currency: 'RUB',
            needName: true,
            needPhoneNumber: true,
            needEmail: true,
            needShippingAddress: true,
            sendPhoneNumberToProvider: true,
            sendEmailToProvider: true,
            isFlexible: true,
            throw: true,
        );

        $this->assertSame(true, $ok);
    }
}
