Understanding how to write `Handler::supports()`
------
When a webhook was called by the Telegram Bot Api (when a message was sent to the bot)
<br>
go to the Symfony
[profiler](https://github.com/GrinWay/telegram-bundle/blob/main/docs/media/image/profiler_webhook_profiler.png)
and look at the made request
[webhook payload](https://github.com/GrinWay/telegram-bundle/blob/main/docs/media/image/profiler_webhook_payload.png)
<br>
Looking at the payload you have to distinguish new unique data and rely on it in the `Handler::supports()`

To prove or refute your supposal go to the [Telegram Bot Api](https://core.telegram.org/bots/api#message) official docs
to understand what optional keys of Message payload you will get and when you get them

> For instance payload key `message_thread_id` you will get when a message was sent from a certain supergroup
> topic.<br><br>
> Or look at the
> [AbstractPrivateChatHandler::supports\(\)](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/PriorityAble/Message/PrivateChat/AbstractPrivateChatHandler.php#L13)
> where you have equal `fieldValue[chat][id]` and `fieldValue[from][id]`.
> It means that a message was sent from the private chat (directory to the bot)

> **PRO TIP**: `fieldValue` is a related to a certain handler webhook `payload`
