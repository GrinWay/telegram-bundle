### Services

### `grinway_telegram`

| methods                                                                                                      |
|--------------------------------------------------------------------------------------------------------------|
| [setWebhook](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L40)              |
| [removeWebhook](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L50)           |
| [downloadFile](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L62)            |
| [downloadStickers](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L124)       |
| [deleteMessage](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L185)          |
| [createInvoiceLink](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L210)      |
| [sendInvoice](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L272)            |
| [answerInlineQuery](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L336)      |
| [answerShippingQuery](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L368)    |
| [answerPreCheckoutQuery](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L412) |

> NOTE: You can click to the method link and see a useful reference to the Telegram Bot Api above the method to
> understand what this method for

If you found a new useful [Telegram Bot Api](https://core.telegram.org/bots/api#available-methods) method that lacks
here you can extend [Telegram](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php)
and add your new methods.

> If you made up your mind to extend the
> [Telegram](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php)
> class pay a special attention to the reusable shortcut methods:

| helper methods                                                                                            |                                                                 Description                                                                  |
|-----------------------------------------------------------------------------------------------------------|:--------------------------------------------------------------------------------------------------------------------------------------------:|
| [request](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L465)             | Makes a request with `Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramClient` service and decodes json payload to the array |
| [isResponsePayloadOk](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php#L478) |                                               Gets the 'ok' main key from the response payload                                               |

#### `Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramClient`

| Service description                                                                                                                                                                                                                                                                                                                       |
|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| It's the httpClient that already contains [https://...bot...](https://core.telegram.org/bots/api#making-requests) base uri with configured bot token<br><br>Usage:<br>$grinwayTelegramClient->request('POST', 'getMe', []);<br><br>`getMe` is one of the [Telegram Bot Api methods](https://core.telegram.org/bots/api#available-methods) |

Example:

```php
<?php

//... inside a controller

$this->grinwayTelegramClient->request('POST', 'sendMessage', [
    'json' => [
        'chat_id' => $this->testChatId,
        'text' => 'my choice',

        'reply_markup' => [
            'one_time_keyboard' => true,
            'resize_keyboard' => true,
            'input_field_placeholder' => 'my input field placeholder',
            'keyboard' => [
                [
                    [
                        'text' => 'Contact',
                        'request_contact' => true,
                    ],
                    [
                        'text' => 'WebApp',
                        'web_app' => [
                            'url' => $url,
                        ],
                    ],
                ],
            ]
        ],

    ],
]);
```

#### `Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramFileClient`

| Service description                                                                                                                                                                                                                                                     |
|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| It's the httpClient that already contains [https://.../file/bot...](https://core.telegram.org/bots/api#file) base uri with configured bot token<br><br>Usage:<br>$grinwayTelegramFileClient->request('GET', '[file_path](https://core.telegram.org/bots/api#getfile)'); |

> TIP: If you want to download a file from the telegram by its `file_id`<br>instead of using this service<br>just use
> the `downloadFile` method of the `grinway_telegram` one
