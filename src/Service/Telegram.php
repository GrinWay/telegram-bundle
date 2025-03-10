<?php

namespace GrinWay\Telegram\Service;

use GrinWay\Service\Service\Currency;
use GrinWay\Service\Service\FiguresRepresentation;
use GrinWay\Service\Validator\AbsolutePath;
use GrinWay\Telegram\Type\TelegramLabeledPrice;
use GrinWay\Telegram\Type\TelegramLabeledPrices;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Emoji\EmojiTransliterator;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\Attribute\Required;
use function Symfony\Component\String\u;

class Telegram
{
    public const LENGTH_AMOUNT_END_FIGURES = 2;
    /**
     * Telegram API won't accept amount like this in a payment:
     * 099(0.99) or 001(0.01)
     * instead it should be at least 100(1.00) but not 099(0.99)
     */
    public const MIN_START_AMOUNT_PART = 1;
    public const TELEGRAM_STARS_CURRENCY = 'XTR';

    public const INVOICE_DOP_START_NUMBER_MAX_ATTEMPTS = 3;

    /**
     * @var array Array of ServiceLocator
     */
    private array $updateHandlerIterators = [];

    private int $invoiceDopIncrementStartNumber = 0;
    private int $invoiceDopStartNumberAttemptsCount = 0;

    protected ServiceLocator $serviceLocator;
    protected string $telegramWebhookPath;
    protected string $appHost;

    #[Required]
    public function _setRequired_telegram(
        #[AutowireLocator([
            'grinwayTelegramClient' => new Autowire('@Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramClient'),
            'grinwayTelegramFileClient' => new Autowire('@Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramFileClient'),
            'serializer' => new Autowire('@Symfony\Component\Serializer\SerializerInterface'),
            'pa' => new Autowire('@Symfony\Component\PropertyAccess\PropertyAccessorInterface'),
            'filesystem' => new Autowire('@Symfony\Component\Filesystem\Filesystem'),
            'slugger' => new Autowire('@Symfony\Component\String\Slugger\SluggerInterface'),
            'currency' => new Autowire('@grinway_service.currency'),
            't' => new Autowire('@.grinway_telegram.translator'),
        ])]
        ServiceLocator $serviceLocator,

        #[Autowire('%env(string:default:grinway_telegram.bot.webhook_path:)%')]
        string         $telegramWebhookPath,

        #[Autowire('%env(string:default:grinway_telegram.app_host:)%')]
        string         $appHost,
    )
    {
        $this->serviceLocator = $serviceLocator;
        $this->telegramWebhookPath = $telegramWebhookPath;
        $this->appHost = $appHost;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#setwebhook
     */
    public function setWebhook(array $dopQuery = []): mixed
    {
        return $this->webhook(true, false, $dopQuery);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#setwebhook
     */
    public function removeWebhook(array $dopQuery = []): mixed
    {
        return $this->webhook(false, true, $dopQuery);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * Automatically makes a directory if it doesn't exist
     *
     * @return bool True if made, false if not
     */
    public function downloadFile(string $fileId, string $absFilepathTo, bool $overwrite = false, bool $throw = false): bool
    {
        if (!Validation::createIsValidCallable(new AbsolutePath())($absFilepathTo)) {
            if (true === $throw) {
                throw new \InvalidArgumentException(\sprintf('You passed not an absolute path: "%s"', $absFilepathTo));
            }
            return false;
        }

        if (\is_file($absFilepathTo) && false === $overwrite) {
            return false;
        }

        try {
            $content = $this->request('POST', 'getFile', [
                'file_id' => $fileId,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        $ok = $this->serviceLocator->get('pa')->getValue($content, '[ok]');
        if (true !== $ok) {
            return false;
        }

        $filepath = $this->serviceLocator->get('pa')->getValue($content, '[result][file_path]');
        if (empty($filepath)) {
            return false;
        }

        try {
            $response = $this->serviceLocator->get('grinwayTelegramFileClient')->request('GET', \ltrim($filepath, '/\\'));
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        $absPathTo = Path::getDirectory($absFilepathTo);
        if (!\is_dir($absPathTo)) {
            $this->serviceLocator->get('filesystem')->mkdir($absPathTo);
        }
        $handler = \fopen($absFilepathTo, 'wb');
        foreach ($this->serviceLocator->get('grinwayTelegramFileClient')->stream($response) as $chunk) {
            \fwrite($handler, $chunk->getContent());
        }
        \fclose($handler);
        return true;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#getstickerset
     *
     * @return array A collection of absolute file paths to the downloaded stickers
     */
    public function downloadStickers(string $stickersName, string $absDirTo, bool $overwrite = false, string $prefixFilename = '', ?int $limit = null, ?string $stickerFileExtension = null, bool $throw = false): array
    {
        $stickerFileExtension ??= 'webp';

        $made = [];
        $transliterator = EmojiTransliterator::create('emoji-text');

        try {
            $payload = $this->request('POST', 'getStickerSet', [
                'name' => $stickersName,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return $made;
        }

        $stickerSetName = \sprintf('%s%s', $prefixFilename, $this->serviceLocator->get('pa')->getValue($payload, '[result][name]'));

        $fileIdsObject = $this->serviceLocator->get('pa')->getValue($payload, '[result][stickers]');
        if (\is_array($fileIdsObject)) {
            $i = 0;
            $limitCounter = 0;
            foreach ($fileIdsObject as $fileIdObject) {
                if (null !== $limit && ++$limitCounter > $limit) {
                    break;
                }
                $fileId = $this->serviceLocator->get('pa')->getValue($fileIdObject, '[file_id]');
                if ($fileId) {
                    if (empty($prefixFilename)) {
                        $prefix = '%s';
                    } else {
                        $prefix = '%s_';
                    }
                    $emoji = $this->serviceLocator->get('pa')->getValue($fileIdObject, '[emoji]') ?: $i++;
                    $emojiTextRepresentation = $transliterator->transliterate($emoji);
                    $filename = (string)$this->serviceLocator->get('slugger')->slug(
                        \sprintf($prefix . '%s', $stickerSetName, $emojiTextRepresentation),
                    );
                    $absFilepathTo = \sprintf('%s/%s.%s', $absDirTo, $filename, $stickerFileExtension);
                    $wasMade = $this->downloadFile(
                        $fileId,
                        $absFilepathTo,
                        overwrite: $overwrite,
                        throw: $throw,
                    );
                    if (true === $wasMade) {
                        $made[$absFilepathTo] = $absFilepathTo;
                    }
                }
            }
        }
        return $made;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#deletemessage
     */
    public function deleteMessage(?string $chatId, ?string $messageId, bool $throw = false): bool
    {
        if (null === $chatId || null === $messageId) {
            return false;
        }

        try {
            $responsePayload = $this->request('POST', 'deleteMessage', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }
        return $this->isResponsePayloadOk($responsePayload);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#sendmessage
     */
    public function sendMessage(
        string $chatId,
        string $text,
        ?array $prependJsonRequest = null,
        ?array $appendJsonRequest = null,
        ?bool  $throw = null,
    ): bool
    {
        $throw ??= false;
        $prependJsonRequest ??= [];
        $appendJsonRequest ??= [];

        $jsonRequest = \array_merge(
            $prependJsonRequest,
            [
                'chat_id' => $chatId,
                'text' => $text,
            ],
            $appendJsonRequest,
        );

        try {
            $responsePayload = $this->request('POST', 'sendMessage', $jsonRequest);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        return $this->isResponsePayloadOk($responsePayload);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#createinvoicelink
     */
    public function createInvoiceLink(
        string                      $title,
        string                      $description,
        TelegramLabeledPrices|array &$prices,
        ?string                     $providerToken = null,
        ?string                     $currency = null,
        ?string                     $photoUri = null,
        ?bool                       $needName = null,
        ?bool                       $needPhoneNumber = null,
        ?bool                       $needEmail = null,
        ?bool                       $needShippingAddress = null,
        ?bool                       $sendPhoneNumberToProvider = null,
        ?bool                       $sendEmailToProvider = null,
        ?bool                       $isFlexible = null,
        ?string                     $payload = null,
        ?string                     $startParameter = null,
        ?array                      $providerData = null,
        ?array                      $prependJsonRequest = null,
        ?array                      $appendJsonRequest = null,
        ?string                     $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi = null,
        ?bool                       $forceMakeHttpRequestToCurrencyApi = null,
        ?bool                       $allowDopPriceIfLessThanLowestPossible = null,
        ?bool                       $allowNonRemovableCache = null,
        ?bool                       $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough = null,
        ?bool                       $throw = null,
    ): ?string
    {
        $responsePayload = $this->getRetryableInvoiceResponsePayload(
            functionPhpConstOrUrl: __FUNCTION__,
            title: $title,
            description: $description,
            prices: $prices,
            providerToken: $providerToken,
            currency: $currency,
            photoUri: $photoUri,
            needName: $needName,
            needPhoneNumber: $needPhoneNumber,
            needEmail: $needEmail,
            needShippingAddress: $needShippingAddress,
            sendPhoneNumberToProvider: $sendPhoneNumberToProvider,
            sendEmailToProvider: $sendEmailToProvider,
            isFlexible: $isFlexible,
            payload: $payload,
            startParameter: $startParameter,
            providerData: $providerData,
            prependJsonRequest: $prependJsonRequest,
            appendJsonRequest: $appendJsonRequest,
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
            forceMakeHttpRequestToCurrencyApi: $forceMakeHttpRequestToCurrencyApi,
            allowDopPriceIfLessThanLowestPossible: $allowDopPriceIfLessThanLowestPossible,
            allowNonRemovableCache: $allowNonRemovableCache,
            allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough: $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough,
            throw: $throw,
        );

        if ($this->isResponsePayloadOk($responsePayload)) {
            return $this->serviceLocator->get('pa')->getValue($responsePayload, '[result]');
        }

        return null;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#sendinvoice
     */
    public function sendInvoice(
        string                      $chatId,
        string                      $title,
        string                      $description,
        TelegramLabeledPrices|array &$prices,
        ?string                     $providerToken = null,
        ?string                     $currency = null,
        ?string                     $photoUri = null,
        ?bool                       $needName = null,
        ?bool                       $needPhoneNumber = null,
        ?bool                       $needEmail = null,
        ?bool                       $needShippingAddress = null,
        ?bool                       $sendPhoneNumberToProvider = null,
        ?bool                       $sendEmailToProvider = null,
        ?bool                       $isFlexible = null,
        ?string                     $payload = null,
        ?string                     $startParameter = null,
        ?array                      $providerData = null,
        ?array                      $prependJsonRequest = null,
        ?array                      $appendJsonRequest = null,
        ?string                     $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi = null,
        ?bool                       $forceMakeHttpRequestToCurrencyApi = null,
        ?bool                       $allowDopPriceIfLessThanLowestPossible = null,
        ?bool                       $allowNonRemovableCache = null,
        ?bool                       $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough = null,
        ?bool                       $throw = null,
    ): bool
    {
        $responsePayload = $this->getRetryableInvoiceResponsePayload(
            functionPhpConstOrUrl: __FUNCTION__,
            title: $title,
            description: $description,
            prices: $prices,
            chatId: $chatId,
            providerToken: $providerToken,
            currency: $currency,
            photoUri: $photoUri,
            needName: $needName,
            needPhoneNumber: $needPhoneNumber,
            needEmail: $needEmail,
            needShippingAddress: $needShippingAddress,
            sendPhoneNumberToProvider: $sendPhoneNumberToProvider,
            sendEmailToProvider: $sendEmailToProvider,
            isFlexible: $isFlexible,
            payload: $payload,
            startParameter: $startParameter,
            providerData: $providerData,
            prependJsonRequest: $prependJsonRequest,
            appendJsonRequest: $appendJsonRequest,
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
            forceMakeHttpRequestToCurrencyApi: $forceMakeHttpRequestToCurrencyApi,
            allowDopPriceIfLessThanLowestPossible: $allowDopPriceIfLessThanLowestPossible,
            allowNonRemovableCache: $allowNonRemovableCache,
            allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough: $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough,
            throw: $throw,
        );

        return $this->isResponsePayloadOk($responsePayload);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#answerinlinequery
     * @param string $type https://core.telegram.org/bots/api#inlinequeryresult
     * @param ?string $id https://core.telegram.org/bots/api#inlinequeryresult
     * @param array $results https://core.telegram.org/bots/api#inlinequeryresult
     */
    public function answerInlineQuery(string $inlineQueryId, string $type, array $results, ?string $id = null, bool $throw = false): bool
    {
        // https://core.telegram.org/bots/api#inlinequeryresultgif
        $id ??= (string)\substr(\uniqid('', true), 0, 64);

        try {
            $responsePayload = $this->request('POST', 'answerInlineQuery', [
                'inline_query_id' => $inlineQueryId,
                'results' => [
                    \array_merge($results, [
                        'type' => $type,
                        'id' => $id,
                    ]),
                ],
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }
        return $this->isResponsePayloadOk($responsePayload);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#answershippingquery
     *
     * @param array $shippingOptions Array of https://core.telegram.org/bots/api#shippingoption
     * @return bool
     */
    public function answerShippingQuery(string $shippingQueryId, array $shippingOptions, true|string $shippingQueryIsValid, bool $throw = false): bool
    {
        $ok = null;
        $okJson = [
            'shipping_query_id' => $shippingQueryId,
            'shipping_options' => $shippingOptions,
        ];
        $errorJson = [
            'shipping_query_id' => $shippingQueryId,
            'error_message' => $shippingQueryIsValid,
        ];

        if (true === $shippingQueryIsValid) {
            $ok = true;
            $requestJson = $okJson;
        } else {
            $ok = false;
            $requestJson = $errorJson;
        }
        \assert(\is_bool($ok));
        $requestJson['ok'] = $ok;

        try {
            $this->request('POST', 'answerShippingQuery', $requestJson);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        $responsePayload = ['ok' => $ok];

        return $this->isResponsePayloadOk($responsePayload);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * @param true|string $preCheckoutQueryIsValid Depending on payload you decide this payment successful or not
     *
     * https://core.telegram.org/bots/api#answerprecheckoutquery
     * https://core.telegram.org/bots/payments
     */
    public function answerPreCheckoutQuery(string $preCheckoutQueryId, true|string $preCheckoutQueryIsValid, bool $throw = false): bool
    {
        $ok = null;
        $okJson = [
            'pre_checkout_query_id' => $preCheckoutQueryId,
        ];
        $errorJson = [
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'error_message' => $preCheckoutQueryIsValid,
        ];

        if (true === $preCheckoutQueryIsValid) {
            $ok = true;
            $requestJson = $okJson;
        } else {
            $ok = false;
            $requestJson = $errorJson;
        }

        \assert(\is_bool($ok));
        $requestJson['ok'] = $ok;

        try {
            $this->request('POST', 'answerPreCheckoutQuery', $requestJson);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        $responseJson = ['ok' => $ok];

        return $this->isResponsePayloadOk($responseJson);
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#getchat
     */
    public function getChatLink(int|string|null $chatId, bool $throw = false): false|string
    {
        if (null === $chatId) {
            return false;
        }

        $username = null;

        try {
            $responseJson = $this->request('POST', 'getChat', [
                'chat_id' => $chatId,
            ]);
            if (\is_array($responseJson)) {
                $username = $responseJson['result']['username'] ?? null;
            }
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        if (null === $username || !$this->isResponsePayloadOk($responseJson)) {
            return false;
        }

        return \sprintf('https://t.me/%s', $username);
    }

    /**
     * Helper
     *
     * Makes a request with 'Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramClient' and decodes json payload to the array
     *
     * May throw exceptions (because of bad request and invalid json data decoding)
     *
     * @param string $method Usually almost always 'POST' but 'GET' for 'getMe' url for instance
     * @param string $url Any valid Telegram Bot Api method:
     *     https://core.telegram.org/bots/api#available-methods
     *     https://core.telegram.org/bots/api#updating-messages
     *     https://core.telegram.org/bots/api#stickers
     *     https://core.telegram.org/bots/api#inline-mode
     *     https://core.telegram.org/bots/api#payments
     *     ...
     * @return mixed response payload
     */
    protected function request(
        string    $method,
        string    $url,
        array     $json,
        bool      $httpClientThrow = true,
        ?callable $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse = null,
        ?callable $httpClientNoExceptionCallback = null,
    ): mixed
    {
        $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse ??= static fn(\Exception $exception): mixed => null;
        $httpClientNoExceptionCallback ??= static fn() => null;

        try {
            $response = $this->getResponse(
                method: $method,
                url: $url,
                json: $json,
            );
            $content = $response->getContent();
            $httpClientNoExceptionCallback();
        } catch (\Exception $exception) {
            $callbackReturn = $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse($exception);
            if (false === $httpClientThrow) {
                return $callbackReturn;
            } else {
                throw $exception;
            }
        }

        return $this->serviceLocator->get('serializer')->decode($content, 'json');
    }

    private function requestHttpClientRetryable(
        string   $method,
        string   $url,
        array    $json,
        callable $httpClientExceptionCallbackReturn,
        callable $httpClientNoExceptionCallback,
    ): mixed
    {
        $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse = static function (\Exception $exception) use ($httpClientExceptionCallbackReturn): mixed {
            return $httpClientExceptionCallbackReturn($exception);
        };
        return $this->request(
            method: $method,
            url: $url,
            json: $json,
            httpClientThrow: false,
            httpClientExceptionCallbackReturnWhenHttpClientThrowFalse: $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse,
            httpClientNoExceptionCallback: $httpClientNoExceptionCallback,
        );
    }

    private function requestInvoiceHttpClientRetryable(
        string   $method,
        string   $url,
        array    $json,
        callable $httpClientExceptionRecursionMethod,
    ): mixed
    {
        $clearStateCallback = function (): void {
            $this->invoiceDopIncrementStartNumber = 0;
            $this->invoiceDopStartNumberAttemptsCount = 0;
        };
        $httpClientExceptionCallbackReturn = function (\Exception $exception) use ($clearStateCallback, $httpClientExceptionRecursionMethod): mixed {
            if (static::INVOICE_DOP_START_NUMBER_MAX_ATTEMPTS > $this->invoiceDopStartNumberAttemptsCount) {
                $this->invoiceDopIncrementStartNumber += 10;
                ++$this->invoiceDopStartNumberAttemptsCount;
                return $httpClientExceptionRecursionMethod();
            }
            $clearStateCallback();
            return null;
        };
        return $this->requestHttpClientRetryable(
            method: $method,
            url: $url,
            json: $json,
            httpClientExceptionCallbackReturn: $httpClientExceptionCallbackReturn,
            httpClientNoExceptionCallback: $clearStateCallback,
        );
    }

    private function getResponse(string $method, string $url, array $json): ResponseInterface
    {
        /** @var HttpClientInterface $grinwayTelegramClient */
        $grinwayTelegramClient = $this->serviceLocator->get('grinwayTelegramClient');

        return $grinwayTelegramClient->request(
            method: $method,
            url: $url,
            options: [
                'json' => $json,
            ],
        );
    }

    /**
     * Helper
     *
     * Telegram Bot Api response checker
     */
    protected function isResponsePayloadOk(mixed $responsePayload): bool
    {
        if (!\is_array($responsePayload)) {
            return false;
        }
        return true === $this->serviceLocator->get('pa')->getValue($responsePayload, '[ok]');
    }

    /**
     * Invoice Helper
     *
     * The lowest amount (price) is 1$ (it's a Telegram Bot Api restriction)
     * Using this method by default 1$ amount in a chosen currency will be set if described price will be lower than 1$
     * Otherwise an Error would be
     *
     * https://core.telegram.org/bots/api#sendinvoice
     *
     * https://yookassa.ru/docs/support/payments/onboarding/integration/cms-module/telegram#telegram__03
     * Yoo Kassa test cards: https://yookassa.ru/developers/payment-acceptance/testing-and-going-live/testing#test-bank-card-success
     *
     * @param ?array $prependJsonRequest Other parameters have more priority
     * @param ?array $appendJsonRequest Overwrites parameters
     * @param ?string $startParameter Has a string by default in order to start private conversation with the bot by '/start <parameter>'
     * @param ?array $providerData If your data is already got just set it straight away
     * @param bool $isFlexible Is the final price depends on shipping method
     *     https://yookassa.ru/developers/api#create_payment
     *     https://yookassa.ru/developers/payment-acceptance/receipts/54fz/yoomoney/parameters-values#payment-subject
     * @throws \LogicException REGARDLESS $throw parameter: If combination of $currency and $providerToken is incorrect
     * @throws \LogicException REGARDLESS $throw parameter: When your currency is telegram stars you have to have exactly 1 $prices item
     * @throws \LogicException REGARDLESS $throw parameter: At least ONE or more elements of a certain type must exist in the $prices array
     */
    protected function getInvoicePayload(
        string                      $title,
        string                      $description,
        TelegramLabeledPrices|array &$pricesRef,
        ?string                     $chatId = null,
        ?string                     $providerToken = null,
        ?string                     $currency = null,
        ?string                     $photoUri = null,
        ?bool                       $needName = null,
        ?bool                       $needPhoneNumber = null,
        ?bool                       $needEmail = null,
        ?bool                       $needShippingAddress = null,
        ?bool                       $sendPhoneNumberToProvider = null,
        ?bool                       $sendEmailToProvider = null,
        ?bool                       $isFlexible = null,
        ?string                     $payload = null,
        ?string                     $startParameter = null,
        ?array                      $providerData = null,
        ?array                      $prependJsonRequest = null,
        ?array                      $appendJsonRequest = null,
        ?string                     $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi = null,
        ?bool                       $forceMakeHttpRequestToCurrencyApi = null,
        ?bool                       $allowDopPriceIfLessThanLowestPossible = null,
        ?bool                       $allowNonRemovableCache = null,
        ?bool                       $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough = null,
        ?bool                       $throw = null,
    ): array
    {
        //defaults
        $needName ??= false;
        $needPhoneNumber ??= false;
        $needEmail ??= false;
        $needShippingAddress ??= false;
        $sendPhoneNumberToProvider ??= false;
        $sendEmailToProvider ??= false;
        $isFlexible ??= false;
        $prependJsonRequest ??= [];
        $appendJsonRequest ??= [];
        $forceMakeHttpRequestToCurrencyApi ??= false;
        $allowDopPriceIfLessThanLowestPossible ??= true;
        $allowNonRemovableCache ??= true;
        $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough ??= true;
        $throw ??= false;

        $prices = $pricesRef;

        // At least these settings must exist by default
        $payload ??= '{}';
        $currency ??= self::TELEGRAM_STARS_CURRENCY;
        $providerToken ??= '';
        $startParameter ??= 'service';

        // currency, provider token VALIDATION
        if (self::TELEGRAM_STARS_CURRENCY === $currency) {
            $needName = false;
            $needPhoneNumber = false;
            $needEmail = false;
            $needShippingAddress = false;
            $sendEmailToProvider = false;
            $sendPhoneNumberToProvider = false;
        }
        if (self::TELEGRAM_STARS_CURRENCY === $currency && '' !== $providerToken) {
            if (true === $throw) {
                throw new \LogicException(\sprintf('If currency is "%s", provider token MUST be \'\' (empty string) but you passed "%s"', $currency, $providerToken));
            } else {
                $providerToken = '';
            }
        }
        if (self::TELEGRAM_STARS_CURRENCY !== $currency && '' === $providerToken) {
            throw new \LogicException(\sprintf('Currency is not "%s" you MUST point out the provider token', self::TELEGRAM_STARS_CURRENCY));
        }
        if (self::TELEGRAM_STARS_CURRENCY === $currency && !Validation::createIsValidCallable(
                new Assert\Count(exactly: 1)
            )($prices)
        ) {
            throw new \LogicException(\sprintf(
                'When your currency is "%s" you have to have exactly 1 $prices item',
                self::TELEGRAM_STARS_CURRENCY,
            ));
        }

        // prices VALIDATION
        if (!Validation::createIsValidCallable(
            new Assert\Count(min: 1),
            new Assert\AtLeastOneOf([
                new Assert\Type(TelegramLabeledPrices::class),
                new Assert\Type('array'),
            ]))($prices)
        ) {
            throw new \InvalidArgumentException(\sprintf(
                    'At least ONE or more elements of type: "%s" must exist in the $prices',
                    TelegramLabeledPrice::class,
                )
            );
        }
        if (\is_array($prices)) {
            $prices = TelegramLabeledPrices::fromArray($prices);
        }
        $prices = $this->getPriceWithDopIfAmountLessThanPossibleLowestPrice(
            prices: $prices,
            currency: $currency,
            labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
            forceMakeHttpRequestToCurrencyApi: $forceMakeHttpRequestToCurrencyApi,
            allowNonRemovableCache: $allowNonRemovableCache,
            allowDopPriceIfLessThanLowestPossible: $allowDopPriceIfLessThanLowestPossible,
            allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough: $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough,
        );
        if ($prices instanceof TelegramLabeledPrices) {
            if ($pricesRef instanceof TelegramLabeledPrices) {
                $pricesRef = $prices;
            }

            $prices = $prices->toArray();

            if (\is_array($pricesRef)) {
                $pricesRef = $prices;
            }
        }

        $invoicePayload = \array_merge(
            $prependJsonRequest,
            [
                'title' => $title,
                'description' => $description,
                'payload' => $payload,
                'currency' => $currency,
                'prices' => $prices,
                'provider_token' => $providerToken,
                'need_name' => $needName,
                'need_phone_number' => $needPhoneNumber,
                'need_email' => $needEmail,
                'need_shipping_address' => $needShippingAddress,
                'send_phone_number_to_provider' => $sendPhoneNumberToProvider,
                'send_email_to_provider' => $sendEmailToProvider,
                'is_flexible' => $isFlexible,
                'start_parameter' => $startParameter,
            ],
            $appendJsonRequest,
        );

        if (null !== $chatId) {
            $invoicePayload['chat_id'] = $chatId;
        }
        if (null !== $photoUri) {
            $invoicePayload['photo_url'] = $photoUri;
        }
        if (null !== $providerData) {
            $invoicePayload['provider_data'] = $providerData;
        }

        return $invoicePayload;
    }

    public function getRetryableInvoiceResponsePayload(
        string                      $functionPhpConstOrUrl,
        string                      $title,
        string                      $description,
        TelegramLabeledPrices|array &$prices,
        ?string                     $chatId = null,
        ?string                     $providerToken = null,
        ?string                     $currency = null,
        ?string                     $photoUri = null,
        ?bool                       $needName = null,
        ?bool                       $needPhoneNumber = null,
        ?bool                       $needEmail = null,
        ?bool                       $needShippingAddress = null,
        ?bool                       $sendPhoneNumberToProvider = null,
        ?bool                       $sendEmailToProvider = null,
        ?bool                       $isFlexible = null,
        ?string                     $payload = null,
        ?string                     $startParameter = null,
        ?array                      $providerData = null,
        ?array                      $prependJsonRequest = null,
        ?array                      $appendJsonRequest = null,
        ?string                     $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi = null,
        ?bool                       $forceMakeHttpRequestToCurrencyApi = null,
        ?bool                       $allowDopPriceIfLessThanLowestPossible = null,
        ?bool                       $allowNonRemovableCache = null,
        ?bool                       $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough = null,
        ?bool                       $throw = null,
    ): mixed
    {
        $httpClientExceptionRecursionMethod = function () use (&$prices, $functionPhpConstOrUrl, $chatId, $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough, $allowDopPriceIfLessThanLowestPossible, $allowNonRemovableCache, $payload, $throw, $forceMakeHttpRequestToCurrencyApi, $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi, $appendJsonRequest, $prependJsonRequest, $providerData, $startParameter, $isFlexible, $sendEmailToProvider, $sendPhoneNumberToProvider, $needShippingAddress, $needEmail, $needPhoneNumber, $needName, $photoUri, $currency, $providerToken, $description, $title) {
            return $this->getRetryableInvoiceResponsePayload(
                functionPhpConstOrUrl: $functionPhpConstOrUrl,
                title: $title,
                description: $description,
                prices: $prices,
                chatId: $chatId,
                providerToken: $providerToken,
                currency: $currency,
                photoUri: $photoUri,
                needName: $needName,
                needPhoneNumber: $needPhoneNumber,
                needEmail: $needEmail,
                needShippingAddress: $needShippingAddress,
                sendPhoneNumberToProvider: $sendPhoneNumberToProvider,
                sendEmailToProvider: $sendEmailToProvider,
                isFlexible: $isFlexible,
                payload: $payload,
                startParameter: $startParameter,
                providerData: $providerData,
                prependJsonRequest: $prependJsonRequest,
                appendJsonRequest: $appendJsonRequest,
                labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
                forceMakeHttpRequestToCurrencyApi: $forceMakeHttpRequestToCurrencyApi,
                allowDopPriceIfLessThanLowestPossible: $allowDopPriceIfLessThanLowestPossible,
                allowNonRemovableCache: $allowNonRemovableCache,
                allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough: $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough,
                throw: $throw,
            );
        };
        try {
            $invoicePayload = $this->getInvoicePayload(
                title: $title,
                description: $description,
                pricesRef: $prices,
                chatId: $chatId,
                providerToken: $providerToken,
                currency: $currency,
                photoUri: $photoUri,
                needName: $needName,
                needPhoneNumber: $needPhoneNumber,
                needEmail: $needEmail,
                needShippingAddress: $needShippingAddress,
                sendPhoneNumberToProvider: $sendPhoneNumberToProvider,
                sendEmailToProvider: $sendEmailToProvider,
                isFlexible: $isFlexible,
                payload: $payload,
                startParameter: $startParameter,
                providerData: $providerData,
                prependJsonRequest: $prependJsonRequest,
                appendJsonRequest: $appendJsonRequest,
                labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi: $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
                forceMakeHttpRequestToCurrencyApi: $forceMakeHttpRequestToCurrencyApi,
                allowDopPriceIfLessThanLowestPossible: $allowDopPriceIfLessThanLowestPossible,
                allowNonRemovableCache: $allowNonRemovableCache,
                allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough: $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough,
                throw: $throw,
            );
            return $this->requestInvoiceHttpClientRetryable(
                'POST',
                $functionPhpConstOrUrl,
                $invoicePayload,
                httpClientExceptionRecursionMethod: $httpClientExceptionRecursionMethod,
            );
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return null;
        }
    }

    /**
     * @internal
     */
    public function getWebhookUri(): string
    {
        if (empty($this->appHost)) {
            $message = \sprintf(
                'You may forgot to set the ENV originally named as "%s" var, it should be like this: "%s"',
                'APP_HOST',
                'your-domain.com[:PORT]'
            );
            throw new \RangeException($message);
        }

        return \sprintf(
            'https://%s/%s',
            \trim($this->appHost, '/\\'),
            \ltrim($this->telegramWebhookPath, '/\\'),
        );
    }

    /**
     * Telegram Bot Api restricts the possible minimum price for the invoice as 1$
     *
     * https://core.telegram.org/bots/payments#supported-currencies
     */
    public function getPriceWithDopIfAmountLessThanPossibleLowestPrice(
        TelegramLabeledPrices|array $prices,
        string                      $currency,
        ?string                     $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi = null,
        ?bool                       $forceMakeHttpRequestToCurrencyApi = null,
        ?bool                       $allowNonRemovableCache = null,
        ?bool                       $allowDopPriceIfLessThanLowestPossible = null,
        ?bool                       $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough = null,
    ): TelegramLabeledPrices
    {
        $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi ??= 'label_dop_price_to_achieve_min_one';
        $forceMakeHttpRequestToCurrencyApi ??= false;
        $allowNonRemovableCache ??= true;
        $allowDopPriceIfLessThanLowestPossible ??= true;
        $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough ??= true;

        $dopStartAmountNumber = 0;
        $dopEndAmountNumber = 0;

        if (\is_array($prices)) {
            $prices = TelegramLabeledPrices::fromArray($prices);
        }

        // in order not to override the below I type it here
        if (true === $allowFallbackIncrementStartNumberIfLowestPriceIsNotEnough) {
            $dopStartAmountNumber = $this->invoiceDopIncrementStartNumber;
        }

        // DON'T OVERRIDE IT (IT'S A MAIN ALGO TO GET CORRECT 1$ SUM PRICE)
        if (true === $allowDopPriceIfLessThanLowestPossible) {
            [$dopStartAmountNumber, $dopEndAmountNumber] = $this->getCalculatedDopStartEndNumbersToReachValidLowestInvoicePrice(
                $prices,
                $currency,
                $forceMakeHttpRequestToCurrencyApi,
                allowNonRemovableCache: $allowNonRemovableCache,
            );
        }

        if (0 !== $dopStartAmountNumber || 0 !== $dopEndAmountNumber) {

            $dopAmountWithEndFigures = FiguresRepresentation::concatStartEndPartsWithEndFigures(
                $dopStartAmountNumber,
                $dopEndAmountNumber,
                self::LENGTH_AMOUNT_END_FIGURES,
            );

            $labelDopPrice = $this->serviceLocator->get('t')->trans(
                $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
            );
            $labelDopPrice = (string)u($labelDopPrice)->title();

            $dopTelegramLabeledPrice = new TelegramLabeledPrice(
                $labelDopPrice,
                $dopAmountWithEndFigures,
            );

            foreach ($prices as $k => $price) {
                \assert($price instanceof TelegramLabeledPrice);

                if ($labelDopPrice === $price->getLabel()) {
                    unset($prices[$k]);
                    break;
                }
            }
            $prices[] = $dopTelegramLabeledPrice;
        }

        return $prices;
    }

    /**
     * @internal
     *
     * https://core.telegram.org/bots/api#setwebhook
     */
    protected function webhook(bool $set, bool $remove, array $dopQuery = []): mixed
    {
        $url = '';

        if (true === $set) {
            $url = $this->getWebhookUri();
        } elseif (true === $remove) {
            $url = '';
        }

        $response = $this->serviceLocator->get('grinwayTelegramClient')->request('GET', 'setWebhook', [
            'query' => [
                ...$dopQuery,
                'url' => $url,
            ],
            'timeout' => 30,
        ]);

        $responseContent = $response->getContent();
        return $this->serviceLocator->get('serializer')->decode($responseContent, 'json');
    }

    /**
     * Helper
     */
    private function getStartEndOneDollarInCurrentCurrency(TelegramLabeledPrices $prices, string $currency, bool $forceMakeHttpRequestToCurrencyApi, bool $allowNonRemovableCache): array
    {
        /** @var Currency $currencyService */
        $currencyService = $this->serviceLocator->get('currency');

        $oneDollarToPassedCurrency = $currencyService->convertFromCurrencyToAnotherWithEndFigures(
            '100',
            'USD',
            $currency,
            self::LENGTH_AMOUNT_END_FIGURES,
            forceMakeHttpRequestToFixer: $forceMakeHttpRequestToCurrencyApi,
            allowNonRemovableCache: $allowNonRemovableCache,
        );

        return FiguresRepresentation::getStartEndNumbersWithEndFigures(
            $oneDollarToPassedCurrency,
            Telegram::LENGTH_AMOUNT_END_FIGURES,
        );
    }

    /**
     * Helper
     *
     * @internal
     */
    protected function getCalculatedDopStartEndNumbersToReachValidLowestInvoicePrice(
        TelegramLabeledPrices $prices,
        string                $currency,
        bool                  $forceMakeHttpRequestToCurrencyApi,
        bool                  $allowNonRemovableCache,
    ): array
    {
        $dopStartAmountNumber = 0;
        $dopEndAmountNumber = 0;

        [$startPassedAmount, $endPassedAmount] = $prices->getStartEndSumNumbers();

        [$startOneDollarAmountInCurrentCurrency, $endOneDollarAmountInCurrentCurrency]
            = $this->getStartEndOneDollarInCurrentCurrency(
            $prices,
            $currency,
            $forceMakeHttpRequestToCurrencyApi,
            allowNonRemovableCache: $allowNonRemovableCache,
        );

        if ($startPassedAmount < $startOneDollarAmountInCurrentCurrency) {
            $dopStartAmountNumber = $startOneDollarAmountInCurrentCurrency - $startPassedAmount;

            // after start exactly (<=) because there is else
            if ($endPassedAmount <= $endOneDollarAmountInCurrentCurrency) {
                $dopEndAmountNumber = $endOneDollarAmountInCurrentCurrency - $endPassedAmount;
            } else {
                // Do nothing when can't apply reduction to the start part, because it won't be correct
                // can't operate with 0.xx only with 1.xx
                if (self::MIN_START_AMOUNT_PART < $dopStartAmountNumber) {
                    // reduction
                    --$dopStartAmountNumber;
                    $dopEndAmountNumber = (10 ** static::LENGTH_AMOUNT_END_FIGURES) - ($endPassedAmount - $endOneDollarAmountInCurrentCurrency);
                }
            }
            // can't operate with 0.xx only with 1.xx
        } elseif ($startPassedAmount === $startOneDollarAmountInCurrentCurrency) {
            // exactly (<) because if start equals and end equals to dollar do nothing
            if ($endPassedAmount < $endOneDollarAmountInCurrentCurrency) {
                ++$dopStartAmountNumber;
            }
        }

        return [$dopStartAmountNumber, $dopEndAmountNumber];
    }

    /**
     * Used by the \GrinWay\Telegram\Bot\Handler\Update\AbstractUpdateHandler
     *
     * @internal
     */
    public function getUpdateHandlerIterator(string|int $key): iterable
    {
        return $this->updateHandlerIterators[$key] ?? [];
    }

    /**
     * It's filled by the \GrinWay\Service\Pass\TagServiceLocatorsPass
     *
     * @internal
     */
    public function setUpdateHandlerIterator(string|int $key, iterable $iterable): static
    {
        $this->updateHandlerIterators[$key] = $iterable;
        return $this;
    }
}
