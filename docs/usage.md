Usage
------

1. Set the webhook

```console
php bin/console grinway_telegram:bot:set_webhook -d
```

2. Create a handler that will extend one of the following:

| Abstract class                                                                                                                                                                                                         |                                                                                                                                                    When it happens                                                                                                                                                    |
|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| [AbstractCallbackQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/CallbackQuery/AbstractCallbackQueryHandler.php)                                                              |                                                                                                        When an [inline callback button](https://core.telegram.org/bots/2-0-intro#callback-buttons) was pushed                                                                                                         |
| [AbstractInlineQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/InlineQuery/AbstractInlineQueryHandler.php)                                                                    |                                                                                                                                   When user types: "@TELEGRAM_BOT_NAME " (@gif wow)                                                                                                                                   |
| [AbstractMessageHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/Message/AbstractMessageHandler.php)                                                                                |                                                                                                                                          When a telegram user sent a message                                                                                                                                          |
| [AbstractCommandHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Command/AbstractCommandHandler.php)                                                           |                                                                                                                 When a "/command" was sent<br>`HAS A HIGHER PRIORITY` than the AbstractMessageHandler                                                                                                                 |
| [AbstractNullResponseToIncorrectCommandHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Command/LowPriority/AbstractNullResponseToIncorrectCommandHandler.php) |                                                                                      When a "/command" was sent with `LOWER PRIORITY` than AbstractCommandHandler but<br>`HAS A HIGHER PRIORITY` than the AbstractMessageHandler                                                                                      |
| [AbstractGroupMessageHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Group/AbstractGroupMessageHandler.php)                                                   |                                                                                                            When a message was sent to the group<br>`HAS A HIGHER PRIORITY` than the AbstractMessageHandler                                                                                                            |
| [AbstractSuccessfulPaymentMessageHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Payment/AbstractSuccessfulPaymentMessageHandler.php)                         | When a payment was successfully finished (successfully because of current [supports realization](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Payment/AbstractSuccessfulPaymentMessageHandler.php#L22))<br>`HAS A HIGHER PRIORITY` than the AbstractMessageHandler |
| [AbstractPrivateChatHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/PrivateChat/AbstractPrivateChatHandler.php)                                               |                                                                                                       When a message sent exactly from private chat<br>`HAS A HIGHER PRIORITY` than the AbstractMessageHandler                                                                                                        |
| [AbstractShippingQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/Payment/ShippingQuery/AbstractShippingQueryHandler.php)                                                      |                                                                                       When you started payment created with requirement of an address and [is_flexible](https://core.telegram.org/bots/api#sendinvoice) option                                                                                        |
| [AbstractPreCheckoutQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/Payment/PreCheckoutQuery/AbstractPreCheckoutQueryHandler.php)                                             |                                                                                                                                             When a pay button was pushed                                                                                                                                              |
| [AbstractReplyToMessageHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/ReplyToMessage/AbstractReplyToMessageHandler.php)                                                           |                                                                                                                                        When user answered with "reply" ability                                                                                                                                        |

> NOTE: Additionally you can look at
> real [test handler](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Test)
> examples of this bundle, it's used for testing this bundle

That's it! Done 🔥

> ADVANCED: You can change any
> [telegram options](https://github.com/symfony/telegram-notifier/tree/7.2?tab=readme-ov-file#adding-interactions-to-a-message)
> before the bot will
> send a notification somehow if some setters are not supported which already available in the Telegram Bot Api
> use the following approach to write additional options
> [AbstractTopicHandler::processTelegramOptionsBeforeSendMessage\(\)](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/AbstractTopicHandler.php#L49)

Now when you send a message to the bot, Telegram Bot Api will call the registered webhook and this bundle will process
the request payload in the `grinway_telegram_bot_webhook` controller.

`grinway_telegram_bot_webhook` will scan all existent
[update handlers](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Handler/Update)
, at the same time each update handler has its
own collection (`Symfony\Component\DependencyInjection\ServiceLocator`) of so-called
[topic handlers](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Handler/Topic)
<br>
including one of the topic handler you just created at the step `2.` when you extended a certain abstract class (
actually your service got a special tag
[see the Reference section](https://github.com/GrinWay/telegram-bundle/blob/main/docs/reference.md#handler-tags))
