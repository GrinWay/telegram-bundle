Understand usage deeper
------

When `webhook` is registered with
[grinway_telegram:bot:set_webhook](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Command/TelegramSetWebhookCommand.php)
command <br>
[Telegram Bot Api](https://core.telegram.org/bots/api#available-methods) will be calling it each time client sends a
message to the bot or group/channel where your bot has admin rights.

#### Handler `supports`

If you have your created [handler](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Test)
which extends
[AbstractTopicHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/AbstractTopicHandler.php)
it has `supports` method which says:
> I can handle webhook `payload`, **stop looking for any other topic handlers** even if I won't send a response

#### Handler `sending a response`

[AbstractTopicHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/AbstractTopicHandler.php)
implements [TopicHandlerInterface](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Contract/Topic/TopicHandlerInterface.php)
and has the second important method called `handle`
but you will only deal with `do*Handle` methods because you will extend
[AbstractTopicHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/AbstractTopicHandler.php)
and this method says:
> I'm responsible to call `$chatMessage->subject('NOT EMPTY RESPONSE DATA');` and return `bool` true to send a message
> (both are required)
> <br><br>
> Keep in mind that in several handlers
> (such as
> [InlineQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Test/InlineQuery/TestInlineQueryHandler.php#L51), [ShippingQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Test/Payment/ShippingQuery/TestShippingQueryHandler.php#L51), [PreCheckoutQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Test/Payment/PreCheckoutQuery/TestPreCheckoutQueryHandler.php#L51),
> later [Telegram Bot Api](https://core.telegram.org/bots/api#update)
> may create more such updates which doesn't require to be handled in a usual way) you won't call
`$chatMessage->subject('...');`
> and to be perfectly sure that your message response won't be sent in a usual way you can return `bool` false. We did
> an absolutely opposite thing in these handlers because you'll handle these messages straight away in the
`do*Handle` method with
> [grinway_telegram](https://github.com/GrinWay/telegram-bundle/blob/main/docs/services.md#grinway_telegram)
> service, it'll be explained later

Client can send different messages, and it means that `webhook` will receive `POST` request with appropriate `payload`

Depending on what client sent, the `webhook` will receive an appropriate `payload` which will contain information
about (one of):

* `/telegramcommand` (client sent a command)
* Group/Supergroup message (client sent a message from a group or supergroup)
* Channel message (client sent a message from a channel)
* Private chat message (client sent a message directly to the bot)
* Payment message (for instance after a successful payment)

> For the future, I say straight away `group/supergroup` and `channel` messages easy to differ by their main optional
> key (`message`, `channel_post` respectively) but unfortunately it's always the `message` for any `/command`

To differ these messages this bundle suggest the following combination (they work together):

1. Create your handler and specify `Handler::supports()` method where you have access to the `payload` (can I process
   this `payload`?)
1. Set a higher `priority` attribute of
   [grinway_telegram.bot.
   `UPDATE_FIELD`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/docs/reference.md#handler-tags)

#### You can ask me why we need to set a `priority` if we have `Handler::supports()` method

By the way where we have full control under the webhook `payload`

> ANSWER: Imagine your bot have several commands: (`/start`, `/terms` and so on) and also imagine client sent a message
`/nonexistentcommand`. So I suppose you don't want to handle an incorrect command name. If it is you need a handler that
> have a lower priority than handlers for all valid commands.

#### Because of this you have the following abstract classes out of the box:

* [AbstractCommandHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Command/AbstractCommandHandler.php)
  has `40` priority of the `grinway_telegram.bot.message_handler` tag
* [AbstractNullResponseToIncorrectCommandHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/Command/LowPriority/AbstractNullResponseToIncorrectCommandHandler.php)
  has `30` priority of the `grinway_telegram.bot.message_handler` tag
