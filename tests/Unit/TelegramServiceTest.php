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
        $response = static::$telegram->setWebhook();
        $this->assertSame(true, $response['ok'] ?? null);
    }

    public function testRemoveWebhook()
    {
        $response = static::$telegram->removeWebhook();
        $this->assertSame(true, $response['ok'] ?? null);
    }

    public function testSuccessfulDownloadFileByFileIdWithAbsoluteFilename()
    {
        $absFilepath = \sprintf('%s/tg_test_file.txt', $this->cacheDir);
        \unlink($absFilepath);

        $wasDownloaded = static::$telegram->downloadFile(
            fileId: 'TEST_FILE_ID',
            absFilepathTo: $absFilepath,
            overwrite: true,
            throw: false,
        );
        $this->assertTrue($wasDownloaded);
        $this->assertFileExists($absFilepath);
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

    public function testSuccessfulDownloadingStickersWithAbsoluteDir()
    {
        $absDir = \sprintf('%s/stickers', $this->cacheDir);

        $madeAbsPaths = static::$telegram->downloadStickers(
            stickersName: 'TEST',
            absDirTo: $absDir,
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

    public function testDeleteMessage()
    {
        $payload = static::$telegram->deleteMessage(
            chatId: 'TEST',
            messageId: 'TEST',
            throw: false,
        );
        $this->assertTrue(Telegram::isResponseOk($payload));
    }

    public function testPreCheckoutQuerySuccessfulOnValidPayload()
    {
        /*
         * Depending on payload you decide this payment successful or not
         *
         * I decided payload is valid
         */
        $isValid = static fn() => true;

        $payload = static::$telegram->answerPreCheckoutQuery(
            preCheckoutQueryId: 'TEST', // in the webhook handler you will get a real pre_checkout_query_id in the payload
            preCheckoutQueryIsValid: $isValid(),
            throw: false,
        );

        $this->assertTrue(Telegram::isResponseOk($payload));
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

        $payload = static::$telegram->answerPreCheckoutQuery(
            preCheckoutQueryId: 'TEST', // in the webhook handler you will get a real pre_checkout_query_id in the payload
            preCheckoutQueryIsValid: $isValid(),
            throw: false,
        );

        $this->assertFalse(Telegram::isResponseOk($payload));
    }

    public function testAnswerInlineQuery()
    {
        /**
         * For you when webhook will be called you will get "inlineQueryId" in the payload
         */
        $payload = static::$telegram->answerInlineQuery(
            inlineQueryId: 'TEST',
            type: 'gif',
            results: [
                'gif_url' => 'https://test.test/gif.gif',
            ],
            throw: false,
        );

        $this->assertTrue(Telegram::isResponseOk($payload));
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
        $payload = static::$telegram->answerShippingQuery(
            shippingQueryId: 'TEST',
            shippingOptions: [],
            shippingQueryIsValid: true,
            throw: false,
        );

        $this->assertTrue(Telegram::isResponseOk($payload));
    }

    public function testAnswerShippingQueryNotSuccessfulOnInvalidPayload()
    {
        /*
         * You will get "shipping_query_id" in your real webhook when it's called by the Telegram Bot Api
         *
         * I decided payload is not valid
         */
        $payload = static::$telegram->answerShippingQuery(
            shippingQueryId: 'TEST',
            shippingOptions: [],
            shippingQueryIsValid: 'ERROR',
            throw: false,
        );

        $this->assertFalse(Telegram::isResponseOk($payload));
    }

    public function testAnswerCallbackQuery()
    {
        $payload = static::$telegram->answerCallbackQuery(
            callbackQueryId: 'TEST',
            throw: false,
        );

        $this->assertTrue(Telegram::isResponseOk($payload));
    }

    public function testSendInvoice()
    {
        $prices = new TelegramLabeledPrices(
            new TelegramLabeledPrice('label 1', '100'),
        );
        $payload = static::$telegram->sendInvoice(
            chatId: $this->telegramBotTestChatId,
            title: 'title',
            description: 'description',
            prices: $prices,
            providerToken: $this->telegramTestPaymentProviderToken,
            currency: 'RUB',
            needName: true,
            needPhoneNumber: true,
            needEmail: true,
            needShippingAddress: true,
            sendPhoneNumberToProvider: true,
            sendEmailToProvider: true,
            isFlexible: true,
            forceMakeHttpRequestToCurrencyApi: true,
            throw: false,
        );

        $this->assertSame(true, Telegram::isResponseOk($payload));
    }
}
