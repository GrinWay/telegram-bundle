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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
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

    public const INVOICE_DOP_START_NUMBER_RETRY_ATTEMPTS = 3;
    public const INVOICE_DOP_START_NUMBER_RETRY_INCREMENT = 10;

    public const FAILURE_RESPONSE = ['ok' => false];

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
            'grinwayTelegramClient' => new Autowire('@grinway_telegram.client'),
            'grinwayTelegramFileClient' => new Autowire('@grinway_telegram.file.client'),
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
    public function setWebhook(
        array $prependRequestOptions = [],
        array $appendRequestOptions = [],
        bool  $throw = false,
    ): mixed
    {
        return $this->webhook(
            set: true,
            remove: false,
            prependRequestOptions: $prependRequestOptions,
            appendRequestOptions: $appendRequestOptions,
            throw: $throw,
        );
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#setwebhook
     */
    public function removeWebhook(
        array $prependRequestOptions = [],
        array $appendRequestOptions = [],
        bool  $throw = false,
    ): mixed
    {
        return $this->webhook(
            set: false,
            remove: true,
            prependRequestOptions: $prependRequestOptions,
            appendRequestOptions: $appendRequestOptions,
            throw: $throw,
        );
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#deletemessage
     */
    public function deleteMessage(
        ?string $chatId,
        ?string $messageId,
        bool    $throw = false,
    ): array
    {
        if (null === $chatId || null === $messageId) {
            return static::FAILURE_RESPONSE;
        }

        try {
            $responsePayload = $this->request('POST', __FUNCTION__, [
                'json' => [
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ],
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }

        return $responsePayload;
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
    ): array
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
            $responsePayload = $this->request('POST', __FUNCTION__, [
                'json' => $jsonRequest,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }

        return $responsePayload;
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
        ?bool                       $retryOnRequestException = null,
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
            retryOnRequestException: $retryOnRequestException,
            throw: $throw,
        );

        if (static::isResponseOk($responsePayload)) {
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
        ?bool                       $retryOnRequestException = null,
        ?bool                       $throw = null,
    ): array
    {
        return $this->getRetryableInvoiceResponsePayload(
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
            retryOnRequestException: $retryOnRequestException,
            throw: $throw,
        );
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#answerinlinequery
     * @param string $type https://core.telegram.org/bots/api#inlinequeryresult
     * @param ?string $id https://core.telegram.org/bots/api#inlinequeryresult
     * @param array $results https://core.telegram.org/bots/api#inlinequeryresult
     */
    public function answerInlineQuery(
        string  $inlineQueryId,
        string  $type,
        array   $results,
        ?string $id = null,
        bool    $throw = false,
    ): array
    {
        // https://core.telegram.org/bots/api#inlinequeryresultgif
        $id ??= (string)\substr(\uniqid('', true), 0, 64);

        try {
            $responsePayload = $this->request('POST', __FUNCTION__, [
                'json' => [
                    'inline_query_id' => $inlineQueryId,
                    'results' => [
                        \array_merge($results, [
                            'type' => $type,
                            'id' => $id,
                        ]),
                    ],
                ],
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }
        return $responsePayload;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#answercallbackquery
     */
    public function answerCallbackQuery(
        string  $callbackQueryId,
        ?string $text = null,
        ?bool   $showAlert = null,
        ?array  $prependJsonRequest = null,
        ?array  $appendJsonRequest = null,
        bool    $throw = false,
    ): array
    {
        $showAlert ??= true; // allow a client to close an alert by (her/him)self
        $prependJsonRequest ??= [];
        $appendJsonRequest ??= [];

        try {
            $json = [
                'callback_query_id' => $callbackQueryId,
                'show_alert' => $showAlert,
            ];
            if (!empty($text)) {
                $json['text'] = $text;
            }
            $json = \array_merge(
                $prependJsonRequest,
                $json,
                $appendJsonRequest,
            );
            $responsePayload = $this->request('POST', __FUNCTION__, [
                'json' => $json,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }
        return $responsePayload;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * https://core.telegram.org/bots/api#answershippingquery
     *
     * @param array $shippingOptions Array of https://core.telegram.org/bots/api#shippingoption
     */
    public function answerShippingQuery(
        string      $shippingQueryId,
        array       $shippingOptions,
        true|string $shippingQueryIsValid,
        bool        $throw = false,
    ): array
    {
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
            $responsePayload = $this->request('POST', __FUNCTION__, [
                'json' => $requestJson,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }

        return $responsePayload;
    }

    /**
     * TELEGRAM BOT API METHOD
     *
     * @param true|string $preCheckoutQueryIsValid Depending on payload you decide this payment successful or not
     *
     * https://core.telegram.org/bots/api#answerprecheckoutquery
     * https://core.telegram.org/bots/payments
     */
    public function answerPreCheckoutQuery(
        string      $preCheckoutQueryId,
        true|string $preCheckoutQueryIsValid,
        bool        $throw = false,
    ): array
    {
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
            $responseJson = $this->request('POST', __FUNCTION__, [
                'json' => $requestJson,
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }

        return $responseJson;
    }

    /**
     * API
     *
     * Automatically makes a directory if it doesn't exist
     *
     * @return bool True if made, false if not
     */
    public function downloadFile(
        string $fileId,
        string $absFilepathTo,
        bool   $overwrite = false,
        bool   $throw = false,
    ): bool
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
                'json' => [
                    'file_id' => $fileId,
                ],
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        /** @var PropertyAccessorInterface $pa */
        $pa = $this->serviceLocator->get('pa');

        $ok = $pa->getValue($content, '[ok]');
        if (true !== $ok) {
            return false;
        }

        $filepath = $pa->getValue($content, '[result][file_path]');
        if (empty($filepath)) {
            return false;
        }

        /** @var HttpClientInterface $grinwayHttpFileClient */
        $grinwayHttpFileClient = $this->serviceLocator->get('grinwayTelegramFileClient');

        $url = \ltrim($filepath, '/\\');
        try {
            $response = $this->request(
                'GET',
                $url,
                httpClient: $grinwayHttpFileClient,
            );
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
        foreach ($grinwayHttpFileClient->stream($response) as $chunk) {
            \fwrite($handler, $chunk->getContent());
        }
        if (\is_resource($handler)) {
            \fclose($handler);
        }
        return true;
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/api#getstickerset
     *
     * @return array A collection of absolute file paths to the downloaded stickers
     */
    public function downloadStickers(
        string  $stickersName,
        string  $absDirTo,
        bool    $overwrite = false,
        string  $prefixFilename = '',
        ?int    $limit = null,
        ?string $stickerFileExtension = null,
        bool    $throw = false,
    ): array
    {
        if (null !== $limit && 0 > $limit) {
            $limit = 0;
        }

        $stickerFileExtension ??= 'webp';

        $made = [];
        $transliterator = EmojiTransliterator::create('emoji-text');

        try {
            $payload = $this->request('POST', 'getStickerSet', [
                'json' => [
                    'name' => $stickersName,
                ],
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return $made;
        }

        /** @var PropertyAccessorInterface $pa */
        $pa = $this->serviceLocator->get('pa');

        /** @var SluggerInterface $slugger */
        $slugger = $this->serviceLocator->get('slugger');

        $stickerSetName = \sprintf(
            '%s%s',
            $prefixFilename,
            $pa->getValue($payload, '[result][name]'),
        );

        $fileIdsObject = $pa->getValue($payload, '[result][stickers]');
        if (\is_array($fileIdsObject)) {
            $i = 0;
            $limitCounter = 0;
            foreach ($fileIdsObject as $fileIdObject) {
                if (null !== $limit && $limitCounter >= $limit) {
                    break;
                }
                $fileId = $pa->getValue($fileIdObject, '[file_id]');
                if ($fileId) {
                    if (empty($prefixFilename)) {
                        $prefix = '%s';
                    } else {
                        $prefix = '%s_';
                    }
                    $emoji = $pa->getValue($fileIdObject, '[emoji]') ?: $i++;
                    $emojiTextRepresentation = $transliterator->transliterate($emoji);
                    $filename = (string)$slugger->slug(
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
                        ++$limitCounter;
                    }
                }
            }
        }
        return $made;
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/api#getchat
     */
    public function getChatLink(
        int|string|null $chatId,
        bool            $throw = false,
    ): false|string
    {
        if (null === $chatId) {
            return false;
        }

        $username = null;

        try {
            $responseJson = $this->request('POST', 'getChat', [
                'json' => [
                    'chat_id' => $chatId,
                ],
            ]);
            if (\is_array($responseJson)) {
                /** @var PropertyAccessorInterface $pa */
                $pa = $this->serviceLocator->get('pa');

                $username = $pa->getValue($responseJson, '[result][username]');
            }
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return false;
        }

        if (null === $username || static::isResponseNotOk($responseJson)) {
            return false;
        }

        return \sprintf('https://t.me/%s', $username);
    }

    /**
     * API
     *
     * https://core.telegram.org/bots/api#getchat
     */
    public function getChat(
        int|string|null $chatId,
        bool            $throw = false,
    ): array
    {
        if (null === $chatId) {
            return static::FAILURE_RESPONSE;
        }

        try {
            $responseJson = $this->request('POST', 'getChat', [
                'json' => [
                    'chat_id' => $chatId,
                ],
            ]);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }

        if (!\is_array($responseJson)) {
            $responseJson = [$responseJson];
        }

        return $responseJson;
    }

    /**
     * API
     *
     * Response is ok checker
     */
    public static function isResponseOk(array $responsePayload): bool
    {
        $ok = $responsePayload['ok'] ?? null;
        return true === $ok;
    }

    /**
     * API
     *
     * Response is ok checker
     */
    public static function isResponseNotOk(array $responsePayload): bool
    {
        return !static::isResponseOk($responsePayload);
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
        ?bool                       $allowInvoiceDopIncrementStartNumber = null,
    ): TelegramLabeledPrices
    {
        $forceMakeHttpRequestToCurrencyApi ??= false;
        $allowNonRemovableCache ??= true;
        $allowDopPriceIfLessThanLowestPossible ??= true;
        $allowInvoiceDopIncrementStartNumber ??= false;

        $dopStartAmountNumber = 0;
        $dopEndAmountNumber = 0;

        if (\is_array($prices)) {
            $prices = TelegramLabeledPrices::fromArray($prices);
        }

        // in order not to override the below, type it here
        if (true === $allowInvoiceDopIncrementStartNumber) {
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
                static::LENGTH_AMOUNT_END_FIGURES,
            );

            if (null === $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi) {
                $labelDopPrice = (string)u($this->serviceLocator->get('t')->trans(
                    'label_dop_price_to_achieve_min_one',
                ))->title();
            } else {
                $labelDopPrice = $this->serviceLocator->get('t')->trans(
                    $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi,
                );
            }

            $dopTelegramLabeledPrice = new TelegramLabeledPrice(
                $labelDopPrice,
                $dopAmountWithEndFigures,
            );

            if (0 !== $this->invoiceDopIncrementStartNumber && true === $allowInvoiceDopIncrementStartNumber) {
                $pricesArray = $prices;
                if ($pricesArray instanceof TelegramLabeledPrices) {
                    $pricesArray = $prices->toArray();
                }
                $reversedPrices = \array_reverse($pricesArray, preserve_keys: true);

                foreach ($reversedPrices as $k => $price) {
                    if ($labelDopPrice === $price['label']) {
                        unset($prices[$k]);
                        break;
                    }
                }
            }
            $prices[] = $dopTelegramLabeledPrice;
        }

        return $prices;
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
        ?bool                       $retryOnRequestException = null,
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
        $retryOnRequestException ??= true;
        $throw ??= false;

        $prices = $pricesRef;

        // At least these settings must exist by default
        $payload ??= '{}';
        $currency ??= static::TELEGRAM_STARS_CURRENCY;
        $providerToken ??= '';
        $startParameter ??= 'service';

        // currency, provider token VALIDATION
        if (static::TELEGRAM_STARS_CURRENCY === $currency) {
            $needName = false;
            $needPhoneNumber = false;
            $needEmail = false;
            $needShippingAddress = false;
            $sendEmailToProvider = false;
            $sendPhoneNumberToProvider = false;
        }
        if (static::TELEGRAM_STARS_CURRENCY === $currency && '' !== $providerToken) {
            if (true === $throw) {
                throw new \LogicException(\sprintf('If currency is "%s", provider token MUST be \'\' (empty string) but you passed "%s"', $currency, $providerToken));
            } else {
                $providerToken = '';
            }
        }
        if (static::TELEGRAM_STARS_CURRENCY !== $currency && '' === $providerToken) {
            throw new \LogicException(\sprintf('Currency is not "%s" you MUST point out the provider token', static::TELEGRAM_STARS_CURRENCY));
        }
        if (static::TELEGRAM_STARS_CURRENCY === $currency && !Validation::createIsValidCallable(
                new Assert\Count(exactly: 1)
            )($prices)
        ) {
            throw new \LogicException(\sprintf(
                'When your currency is "%s" you have to have exactly 1 $prices item',
                static::TELEGRAM_STARS_CURRENCY,
            ));
        }

        // prices VALIDATION
        if (!Validation::createIsValidCallable(
            new Assert\Count(min: 1),
            new Assert\AtLeastOneOf([
                // at least on of the following constraints must be true
                new Assert\All([new Assert\Type(TelegramLabeledPrice::class)]),
                new Assert\All([new Assert\Type('array')]),
            ]))($prices)
        ) {
            throw new \InvalidArgumentException(\sprintf(
                    'All the elements of the prices argument must be either "%s" or "array", empty is not allowed.',
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
            allowInvoiceDopIncrementStartNumber: $retryOnRequestException,
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

    /**
     * Helper
     *
     * Makes a request with 'Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramClient' and decodes json payload to the array
     *
     * May throw exceptions (because of bad request and invalid json data decoding)
     *
     * @param ?callable $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse MUST ALWAYS return an array
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
        string               $method,
        string               $url,
        array                $options = [],
        ?HttpClientInterface $httpClient = null,
        bool                 $httpClientThrow = true,
        ?callable            $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse = null,
        ?callable            $httpClientNoExceptionCallback = null,
    ): mixed
    {
        $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse ??= static fn(\Exception $exception): array => [];
        $httpClientNoExceptionCallback ??= static fn() => null;

        try {
            $httpClient ??= $this->serviceLocator->get('grinwayTelegramClient');

            $response = $this->getResponse(
                httpClient: $httpClient,
                method: $method,
                url: $url,
                options: $options,
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
        array    $options,
        callable $httpClientExceptionCallbackReturn,
        callable $httpClientNoExceptionCallback,
    ): array
    {
        $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse = static function (\Exception $exception) use ($httpClientExceptionCallbackReturn): mixed {
            return $httpClientExceptionCallbackReturn($exception);
        };
        return $this->request(
            method: $method,
            url: $url,
            options: $options,
            httpClientThrow: false,
            httpClientExceptionCallbackReturnWhenHttpClientThrowFalse: $httpClientExceptionCallbackReturnWhenHttpClientThrowFalse,
            httpClientNoExceptionCallback: $httpClientNoExceptionCallback,
        );
    }

    private function requestInvoiceHttpClientRetryable(
        string   $method,
        string   $url,
        array    $options,
        callable $httpClientExceptionRecursionMethod,
    ): array
    {
        $clearStateCallback = function (): void {
            $this->invoiceDopIncrementStartNumber = 0;
            $this->invoiceDopStartNumberAttemptsCount = 0;
        };
        $httpClientExceptionCallbackReturn = function (\Exception $exception) use ($clearStateCallback, $httpClientExceptionRecursionMethod): mixed {
            if (static::INVOICE_DOP_START_NUMBER_RETRY_ATTEMPTS > $this->invoiceDopStartNumberAttemptsCount) {
                $this->invoiceDopIncrementStartNumber += Telegram::INVOICE_DOP_START_NUMBER_RETRY_INCREMENT;
                ++$this->invoiceDopStartNumberAttemptsCount;
                $response = $httpClientExceptionRecursionMethod();
                return $response;
            }
            $clearStateCallback();
            throw $exception;
        };
        return $this->requestHttpClientRetryable(
            method: $method,
            url: $url,
            options: $options,
            httpClientExceptionCallbackReturn: $httpClientExceptionCallbackReturn,
            httpClientNoExceptionCallback: $clearStateCallback,
        );
    }

    private function getResponse(HttpClientInterface $httpClient, string $method, string $url, array $options): ResponseInterface
    {
        return $httpClient->request(
            method: $method,
            url: $url,
            options: $options,
        );
    }

    /**
     * @internal
     */
    protected function getRetryableInvoiceResponsePayload(
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
        ?bool                       $retryOnRequestException = null,
        ?bool                       $throw = null,
    ): array
    {
        $retryOnRequestException ??= true;

        $httpClientExceptionRecursionMethod = function () use (&$prices, $functionPhpConstOrUrl, $chatId, $retryOnRequestException, $allowDopPriceIfLessThanLowestPossible, $allowNonRemovableCache, $payload, $throw, $forceMakeHttpRequestToCurrencyApi, $labelDopPriceToAchieveMinOneBecauseOfTelegramBotApi, $appendJsonRequest, $prependJsonRequest, $providerData, $startParameter, $isFlexible, $sendEmailToProvider, $sendPhoneNumberToProvider, $needShippingAddress, $needEmail, $needPhoneNumber, $needName, $photoUri, $currency, $providerToken, $description, $title) {
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
                retryOnRequestException: $retryOnRequestException,
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
                retryOnRequestException: $retryOnRequestException,
                throw: $throw,
            );
            if (true === $retryOnRequestException) {
                return $this->requestInvoiceHttpClientRetryable(
                    'POST',
                    $functionPhpConstOrUrl,
                    [
                        'json' => $invoicePayload,
                    ],
                    httpClientExceptionRecursionMethod: $httpClientExceptionRecursionMethod,
                );
            } else {
                return $this->request(
                    'POST',
                    $functionPhpConstOrUrl,
                    [
                        'json' => $invoicePayload,
                    ],
                );
            }
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }
    }

    /**
     * @internal
     *
     * https://core.telegram.org/bots/api#setwebhook
     */
    protected function webhook(
        bool  $set,
        bool  $remove,
        array $prependRequestOptions = [],
        array $appendRequestOptions = [],
        bool  $throw = false,
    ): mixed
    {
        $url = '';

        if (true === $set) {
            $url = $this->getWebhookUri();
        } elseif (true === $remove) {
            $url = '';
        }

        $options = \array_merge_recursive(
            $prependRequestOptions,
            [
                'query' => [
                    'url' => $url,
                ],
                'timeout' => 30,
            ],
            $appendRequestOptions,
        );

        try {
            $responsePayload = $this->request('GET', 'setWebhook', $options);
        } catch (\Exception $exception) {
            if (true === $throw) {
                throw $exception;
            }
            return static::FAILURE_RESPONSE;
        }

        return $responsePayload;
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
            static::LENGTH_AMOUNT_END_FIGURES,
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
                if (static::MIN_START_AMOUNT_PART < $dopStartAmountNumber) {
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
