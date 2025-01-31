<?php

namespace GrinWay\Telegram\Tests;

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

    protected string $telegramBotTestChatId;
    protected \Closure $getenv;
    protected Telegram $telegram;
    protected string $telegramBotName;
    protected string $fullCommandName;
    protected string $telegramTestPaymentProviderToken;
    protected array $callbackQueryPayload = [];
    protected array $inlineQueryPayload = [];
    protected array $groupPayload = [];
    protected array $privateChatPayload = [];
    protected array $existentNotFullNamedCommandFromChatPayload = [];
    protected array $existentFullCommandFromChatPayload = [];
    protected array $existentNotFullNamedCommandFromPrivateChatPayload = [];
    protected array $existentFullCommandFromPrivateChatPayload = [];
    protected array $nonExistentCommandFromChatPayload = [];
    protected array $nonExistentCommandFromPrivateChatPayload = [];
    protected array $replyToMessageFromPrivateChatPayload = [];
    protected array $replyToMessageFromChatPayload = [];
    protected array $paymentShippingQueryPayload = [];
    protected array $paymentPreCheckoutQueryPayload = [];

    protected function setUp(): void
    {
        parent::setUp();

        $grinwayTelegramClientResponseGenerator = static function (): \Generator {
            while (true) {
                yield new MockResponse('{ "ok": true, "result": {"name": "TEST", "stickers": [{"file_id": "TEST", "emoji": "😏"},{"file_id": "TEST", "emoji": "👍"},{"file_id": "TEST", "emoji": "💀"},{"file_id": "TEST", "emoji": "💕"}], "file_path": "TEST"} }');
            }
        };
        $grinwayTelegramFileClientResponseGenerator = static function (): \Generator {
            while (true) {
                yield new MockResponse('TEST');
            }
        };

        $this->getenv = self::getContainer()->get('container.getenv');

        self::getContainer()->set(\sprintf('%s $grinwayTelegramClient', HttpClientInterface::class), new MockHttpClient(
            $grinwayTelegramClientResponseGenerator(),
        ));

        self::getContainer()->set(\sprintf('%s $grinwayTelegramFileClient', HttpClientInterface::class), new MockHttpClient(
            $grinwayTelegramFileClientResponseGenerator(),
        ));

        $this->telegramBotName = ($this->getenv)(\sprintf(
            'default:%s:',
            GrinWayTelegramBundle::bundlePrefixed('bot.name')
        ));

        $this->fullCommandName = \sprintf("/test@%s", $this->telegramBotName);

        $this->telegramBotTestChatId = ($this->getenv)(\sprintf(
            'default:%s:',
            GrinWayTelegramBundle::bundlePrefixed('test.bot.chat_id'),
        ));

        $this->telegramTestPaymentProviderToken = ($this->getenv)(\sprintf(
            'default:%s:',
            GrinWayTelegramBundle::bundlePrefixed('test.bot.payment_provider_token')
        ));

        $this->telegram = self::getContainer()->get('grinway_telegram');

        $this->callbackQueryPayload = [
            "update_id" => 0,
            "callback_query" => [
                "id" => "0",
                "from" => [
                    "id" => $this->telegramBotTestChatId,
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
                        "id" => $this->telegramBotTestChatId,
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
        $this->inlineQueryPayload = [
            "update_id" => 0,
            "inline_query" => [
                "id" => "0",
                "from" => [
                    "id" => $this->telegramBotTestChatId,
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
        $this->groupPayload = [
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
                    "id" => $this->telegramBotTestChatId,
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
        $this->privateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => $this->telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => $this->telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "text" => "TEST",
            ],
        ];
        $this->existentNotFullNamedCommandFromChatPayload = [
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
                    "id" => $this->telegramBotTestChatId,
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
        $this->existentFullCommandFromChatPayload = [
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
                    "id" => $this->telegramBotTestChatId,
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
                "text" => $this->fullCommandName,
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
        $this->existentNotFullNamedCommandFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => $this->telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => $this->telegramBotTestChatId,
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
        $this->existentFullCommandFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => $this->telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => $this->telegramBotTestChatId,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "type" => "private",
                ],
                "date" => 0,
                "text" => $this->fullCommandName,
                "entities" => [
                    0 => [
                        "offset" => 0,
                        "length" => 3,
                        "type" => "bot_command",
                    ],
                ],
            ],
        ];
        $this->nonExistentCommandFromChatPayload = [
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
                    "id" => $this->telegramBotTestChatId,
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
        $this->nonExistentCommandFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => $this->telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => $this->telegramBotTestChatId,
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
        $this->replyToMessageFromPrivateChatPayload = [
            "update_id" => 0,
            "message" => [
                "message_id" => 0,
                "from" => [
                    "id" => $this->telegramBotTestChatId,
                    "is_bot" => false,
                    "first_name" => "TEST",
                    "username" => "TEST",
                    "language_code" => "en",
                ],
                "chat" => [
                    "id" => $this->telegramBotTestChatId,
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
                        "id" => $this->telegramBotTestChatId,
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
        $this->replyToMessageFromChatPayload = [
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
                    "id" => $this->telegramBotTestChatId,
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
        $this->paymentShippingQueryPayload = [
            "update_id" => 0,
            "shipping_query" => [
                "id" => "0",
                "from" => [
                    "id" => $this->telegramBotTestChatId,
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
        $this->paymentPreCheckoutQueryPayload = [
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
