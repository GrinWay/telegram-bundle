<?php

namespace GrinWay\Telegram\Tests;

use GrinWay\Service\Service\Currency;
use GrinWay\Telegram\GrinWayTelegramBundle;
use GrinWay\Telegram\Service\Telegram;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Zenstruck\Browser\Test\HasBrowser;

abstract class AbstractTelegramTestCase extends WebTestCase
{
    use HasBrowser;

    public const WEBHOOK = '/grinway/telegram/bot/webhook';

    /**
     * Overwrite with yours if below is not appropriate
     *
     * use before parent::setUP()
     */
    protected static string $mockedGrinwayTelegramClientPlainResponse = '{ "ok": true, "result": {"name": "TEST", "stickers": [{"file_id": "TEST", "emoji": "😏"},{"file_id": "TEST", "emoji": "👍"},{"file_id": "TEST", "emoji": "💀"},{"file_id": "TEST", "emoji": "💕"}], "file_path": "TEST"} }';
    protected static string $mockedGrinWayTelegramFileClientPlainResponse = '{}';
    protected static string $mockedGrinWayServiceFixerLatestClientPlainResponse = '{"grinway_key_fake_fixer": true, "success":true,"timestamp":1738432743,"base":"EUR","date":"2025-02-01","rates":{"AED":3.805967,"AFN":78.238268,"ALL":99.501027,"AMD":411.740564,"ANG":1.866857,"AOA":472.514554,"ARS":1088.994123,"AUD":1.6614,"AWG":1.867778,"AZN":1.76568,"BAM":1.952512,"BBD":2.091448,"BDT":126.313056,"BGN":1.95472,"BHD":0.39064,"BIF":3066.137446,"BMD":1.036215,"BND":1.405686,"BOB":7.157844,"BRL":6.053612,"BSD":1.035841,"BTC":1.0056838e-5,"BTN":89.677843,"BWP":14.427499,"BYN":3.389778,"BYR":20309.819708,"BZD":2.080667,"CAD":1.506709,"CDF":2956.322601,"CHF":0.943799,"CLF":0.036927,"CLP":1018.93163,"CNY":7.447076,"CNH":7.585656,"COP":4357.2853,"CRC":522.512665,"CUC":1.036215,"CUP":27.459705,"CVE":110.077004,"CZK":25.201071,"DJF":184.156589,"DKK":7.462864,"DOP":63.992254,"DZD":140.189974,"EGP":52.046257,"ERN":15.543229,"ETB":132.686171,"EUR":1,"FJD":2.407077,"FKP":0.853413,"GBP":0.83559,"GEL":2.96398,"GGP":0.853413,"GHS":15.848087,"GIP":0.853413,"GMD":75.129599,"GNF":8953.622076,"GTQ":8.012509,"GYD":216.711978,"HKD":8.073206,"HNL":26.38757,"HRK":7.6468,"HTG":135.491868,"HUF":407.802929,"IDR":16947.560142,"ILS":3.70332,"IMP":0.853413,"INR":89.83712,"IQD":1356.915318,"IRR":43624.664125,"ISK":146.687036,"JEP":0.853413,"JMD":163.359429,"JOD":0.734888,"JPY":160.825835,"KES":133.882955,"KGS":90.617425,"KHR":4167.922003,"KMF":489.974798,"KPW":932.593877,"KRW":1510.574324,"KWD":0.319652,"KYD":0.863234,"KZT":536.738148,"LAK":22535.729651,"LBP":92758.476841,"LKR":308.690248,"LRD":206.129949,"LSL":19.334745,"LTL":3.059675,"LVL":0.626797,"LYD":5.085266,"MAD":10.397593,"MDL":19.339158,"MGA":4816.820039,"MKD":61.522939,"MMK":3365.586846,"MNT":3521.059671,"MOP":8.31478,"MRU":41.496132,"MUR":48.339835,"MVR":15.96847,"MWK":1796.149765,"MXN":21.432081,"MYR":4.616379,"MZN":66.22491,"NAD":19.334745,"NGN":1557.431939,"NIO":38.115823,"NOK":11.72965,"NPR":143.483566,"NZD":1.838239,"OMR":0.399053,"PAB":1.035841,"PEN":3.853412,"PGK":4.217756,"PHP":60.536773,"PKR":288.922632,"PLN":4.213993,"PYG":8170.2435,"QAR":3.775842,"RON":4.975288,"RSD":117.12449,"RUB":102.138579,"RWF":1470.30312,"SAR":3.886514,"SBD":8.759842,"SCR":15.558581,"SDG":622.765742,"SEK":11.498678,"SGD":1.406459,"SHP":0.853413,"SLE":23.703464,"SLL":21728.916467,"SOS":591.99467,"SRD":36.370643,"STD":21447.564418,"SVC":9.063433,"SYP":13472.871201,"SZL":19.322466,"THB":35.014097,"TJS":11.326765,"TMT":3.637116,"TND":3.308429,"TOP":2.426924,"TRY":36.977382,"TTD":7.026068,"TWD":34.138152,"TZS":2642.34934,"UAH":43.198623,"UGX":3813.578955,"USD":1.036215,"UYU":44.824528,"UZS":13440.37002,"VES":60.484509,"VND":25988.279504,"VUV":123.02156,"WST":2.90226,"XAF":654.844937,"XAG":0.0331,"XAU":0.00037,"XCD":2.800424,"XDR":0.79184,"XOF":654.83232,"XPF":119.331742,"YER":257.888119,"ZAR":19.347108,"ZMK":9327.184796,"ZMW":28.97779,"ZWL":333.660901}}';

    protected static bool $stubGrinwayTelegramClient = true;
    protected static bool $stubGrinwayTelegramFileClient = true;
    protected static bool $stubCurrencyFixerLatest = true;

    protected static Telegram $telegram;
    protected static Currency $currencyService;

    protected static string $telegramBotTestChatId;
    protected static \Closure $getenv;
    protected static string $telegramBotName;
    protected static string $fullCommandName;
    protected static string $telegramTestPaymentProviderToken;
    protected static array $callbackQueryPayload = [];
    protected static array $inlineQueryPayload = [];
    protected static array $groupPayload = [];
    protected static array $privateChatPayload = [];
    protected static array $existentNotFullNamedCommandFromChatPayload = [];
    protected static array $existentFullCommandFromChatPayload = [];
    protected static array $existentNotFullNamedCommandFromPrivateChatPayload = [];
    protected static array $existentFullCommandFromPrivateChatPayload = [];
    protected static array $nonExistentCommandFromChatPayload = [];
    protected static array $nonExistentCommandFromPrivateChatPayload = [];
    protected static array $replyToMessageFromPrivateChatPayload = [];
    protected static array $replyToMessageFromChatPayload = [];
    protected static array $paymentShippingQueryPayload = [];
    protected static array $paymentPreCheckoutQueryPayload = [];

    protected function setUp(): void
    {
        parent::setUp();
        static::setUpTelegramAndItsMockedDependencies();
        static::setUpCurrencyAndItsMockedDependencies();
        static::setUpState();
    }

    protected static function isStubGrinwayTelegramClient(): bool
    {
        return static::$stubGrinwayTelegramClient;
    }

    protected static function isStubGrinwayTelegramFileClient(): bool
    {
        return static::$stubGrinwayTelegramFileClient;
    }

    protected static function isStubCurrencyFixerLatest(): bool
    {
        return static::$stubCurrencyFixerLatest;
    }

    /**
     * @internal
     */
    protected static function setUpTelegramAndItsMockedDependencies(): void
    {
        if (true === static::isStubGrinwayTelegramClient()) {
            $grinwayTelegramClientResponseGenerator = static function (): \Generator {
                while (true) {
                    yield new MockResponse(static::$mockedGrinwayTelegramClientPlainResponse);
                }
            };
            static::getContainer()->set(\sprintf('%s $grinwayTelegramClient', HttpClientInterface::class), new MockHttpClient(
                $grinwayTelegramClientResponseGenerator(),
            ));
        }

        if (true === static::isStubGrinwayTelegramFileClient()) {
            $grinwayTelegramFileClientResponseGenerator = static function (): \Generator {
                while (true) {
                    yield new MockResponse(static::$mockedGrinWayTelegramFileClientPlainResponse);
                }
            };
            static::getContainer()->set(\sprintf('%s $grinwayTelegramFileClient', HttpClientInterface::class), new MockHttpClient(
                $grinwayTelegramFileClientResponseGenerator(),
            ));
        }

        static::$telegram = static::getContainer()->get('grinway_telegram');
    }

    /**
     * @internal
     */
    protected static function setUpCurrencyAndItsMockedDependencies(): void
    {
        if (true === static::isStubCurrencyFixerLatest()) {
            $grinwayTelegramFileClientResponseGenerator = static function (): \Generator {
                while (true) {
                    yield new MockResponse(static::$mockedGrinWayServiceFixerLatestClientPlainResponse);
                }
            };
            static::getContainer()->set('grinway_service.currency.fixer_latest', new MockHttpClient(
                $grinwayTelegramFileClientResponseGenerator(),
            ));
        }

        $currencyFixerPayload = static::getContainer()
            ->get('grinway_service.currency.fixer_latest')
            ->request('GET', '')
            ->getContent()//
        ;
        $fixerArrayPayload = static::getContainer()
            ->get('serializer')
            ->decode($currencyFixerPayload, 'json')//
        ;
        if (null === ($fixerArrayPayload['grinway_key_fake_fixer'] ?? null)) {
            $message = '!!! Accidentally used a real fixer API service, MOCK IT !!!';
            echo $message . \PHP_EOL . \PHP_EOL;
            throw new \RuntimeException($message);
        }
        static::$currencyService = static::getContainer()->get('grinway_service.currency');
    }

    /**
     * @internal
     */
    protected static function setUpState()
    {
        static::$getenv = static::getContainer()->get('container.getenv');
        static::$telegramBotName = (static::$getenv)(\sprintf(
            'default:%s:',
            GrinWayTelegramBundle::bundlePrefixed('bot.name')
        ));
        static::$fullCommandName = \sprintf("/test@%s", static::$telegramBotName);
        static::$telegramBotTestChatId = (static::$getenv)(\sprintf(
            'default:%s:',
            GrinWayTelegramBundle::bundlePrefixed('test.bot.chat_id'),
        ));
        static::$telegramTestPaymentProviderToken = (static::$getenv)(\sprintf(
            'default:%s:',
            GrinWayTelegramBundle::bundlePrefixed('test.bot.payment_provider_token')
        ));
        static::$callbackQueryPayload = [
            "update_id" => 0,
            "callback_query" => [
                "id" => "0",
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => static::$telegramBotTestChatId,
                        "first_name" => "TEST",
                        "username" => "TEST",
                        "type" => "private",
                    ],
                    "date" => 0,
                    "text" => "TEST",
                    "reply_markup" => [
                        "inline_keyboard" => [
                            0 => [
                                0 => [
                                    "text" => "TEST",
                                    "callback_data" => "TEST",
                                ],
                            ],
                        ],
                    ],
                ],
                "chat_instance" => "0",
                "data" => "0",
            ],
        ];
        static::$inlineQueryPayload = [
            "update_id" => 0,
            "inline_query" => [
                "id" => "0",
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat_type" => "sender",
                "query" => "",
                "offset" => "",
            ],
        ];
        static::$groupPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => 0,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "title" => "TEST",
                    "username" => "TEST",
                    "is_forum" => true,
                    "type" => "supergroup",
                ],
                "date" => 0,
                "message_thread_id" => 0,
                "reply_to_message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => 0,
                        "title" => "TEST",
                        "username" => "TEST",
                        "is_forum" => true,
                        "type" => "supergroup",
                    ],
                    "date" => 0,
                    "message_thread_id" => 0,
                    "forum_topic_created" => [
                        "name" => "TEST",
                        "icon_color" => 0,
                        "icon_custom_emoji_id" => "0",
                    ],
                    "is_topic_message" => true,
                ],
                "text" => "TEST",
                "is_topic_message" => true,
            ],
        ];
        static::$privateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "text" => "TEST",
            ],
        ];
        static::$existentNotFullNamedCommandFromChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => 0,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "title" => "TEST",
                    "username" => "TEST",
                    "is_forum" => true,
                    "type" => "supergroup",
                ],
                "date" => 0,
                "message_thread_id" => 0,
                "reply_to_message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => 0,
                        "title" => "TEST",
                        "username" => "TEST",
                        "is_forum" => true,
                        "type" => "supergroup",
                    ],
                    "date" => 0,
                    "message_thread_id" => 0,
                    "forum_topic_created" => [
                        "name" => "TEST",
                        "icon_color" => 0,
                        "icon_custom_emoji_id" => "0",
                    ],
                    "is_topic_message" => true,
                ],
                "text" => '/test',
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 8,
                        "type" => "bot_command",
                    ],
                ],
                "is_topic_message" => true,
            ],
        ];
        static::$existentFullCommandFromChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => 0,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "title" => "TEST",
                    "username" => "TEST",
                    "is_forum" => true,
                    "type" => "supergroup",
                ],
                "date" => 0,
                "message_thread_id" => 0,
                "reply_to_message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => 0,
                        "title" => "TEST",
                        "username" => "TEST",
                        "is_forum" => true,
                        "type" => "supergroup",
                    ],
                    "date" => 0,
                    "message_thread_id" => 0,
                    "forum_topic_created" => [
                        "name" => "TEST",
                        "icon_color" => 0,
                        "icon_custom_emoji_id" => "0",
                    ],
                    "is_topic_message" => true,
                ],
                "text" => static::$fullCommandName,
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 8,
                        "type" => "bot_command",
                    ],
                ],
                "is_topic_message" => true,
            ],
        ];
        static::$existentNotFullNamedCommandFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "text" => "/test",
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 3,
                        "type" => "bot_command",
                    ],
                ],
            ],
        ];
        static::$existentFullCommandFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "text" => static::$fullCommandName,
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 3,
                        "type" => "bot_command",
                    ],
                ],
            ],
        ];
        static::$nonExistentCommandFromChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => 0,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "title" => "TEST",
                    "username" => "TEST",
                    "is_forum" => true,
                    "type" => "supergroup",
                ],
                "date" => 0,
                "message_thread_id" => 0,
                "reply_to_message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => 0,
                        "title" => "TEST",
                        "username" => "TEST",
                        "is_forum" => true,
                        "type" => "supergroup",
                    ],
                    "date" => 0,
                    "message_thread_id" => 0,
                    "forum_topic_created" => [
                        "name" => "TEST",
                        "icon_color" => 0,
                        "icon_custom_emoji_id" => "0",
                    ],
                    "is_topic_message" => true,
                ],
                "text" => "/nonexistent",
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 8,
                        "type" => "bot_command",
                    ],
                ],
                "is_topic_message" => true,
            ],
        ];
        static::$nonExistentCommandFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "text" => "/nonexistent",
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 3,
                        "type" => "bot_command",
                    ],
                ],
            ],
        ];
        static::$replyToMessageFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "reply_to_message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => static::$telegramBotTestChatId,
                        "first_name" => "TEST",
                        "username" => "TEST",
                        "type" => "private",
                    ],
                    "date" => 0,
                    "text" => "TEST",
                ],
                "text" => "TEST",
            ],
        ];
        static::$replyToMessageFromChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => 0,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => static::$telegramBotTestChatId,
                    "title" => "TEST",
                    "username" => "TEST",
                    "is_forum" => true,
                    "type" => "supergroup",
                ],
                "date" => 0,
                "message_thread_id" => 0,
                "reply_to_message" => [
                    "message_id" => 0,
                    "from" => [
                        "id" => 0,
                        "is_bot" => true,
                        "first_name" => "TEST",
                        "username" => "TEST",
                    ],
                    "chat" => [
                        "id" => 0,
                        "title" => "TEST",
                        "username" => "TEST",
                        "is_forum" => true,
                        "type" => "supergroup",
                    ],
                    "date" => 0,
                    "message_thread_id" => 0,
                    "text" => "TEST",
                    "is_topic_message" => true,
                ],
                "text" => "TEST",
                "is_topic_message" => true,
            ],
        ];
        static::$paymentShippingQueryPayload = [
            "update_id" => 0,
            "shipping_query" => [
                "id" => "0",
                "from" => [
                    "id" => static::$telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "invoice_payload" => "{}",
                "shipping_address" => [
                    "country_code" => "GB",
                    "state" => "hk",
                    "city" => "hk",
                    "street_line1" => "TEST",
                    "street_line2" => "TEST",
                    "post_code" => "0",
                ],
            ],
        ];
        static::$paymentPreCheckoutQueryPayload = [
            "update_id" => 0,
            "pre_checkout_query" => [
                "id" => "0",
                "from" => [
                    "id" => 0,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "currency" => "RUB",
                "total_amount" => 100,
                "invoice_payload" => "{}",
            ],
        ];
    }
}
