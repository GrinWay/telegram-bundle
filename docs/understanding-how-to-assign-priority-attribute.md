Understanding how to assign `priority` attribute
------
> NOTE: Changing the `priority` attribute of a
> [certain tag](https://github.com/GrinWay/telegram-bundle/blob/main/docs/reference.md#handler-tags)
> is only reasonable if your handlers have the
> same [tag](https://github.com/GrinWay/telegram-bundle/blob/main/docs/reference.md#handler-tags).
> For instance handlers belong to the `grinway_telegram.bot.message_handler` tag

When you have described `Handler::supports()` you may want to give a higher of the lower `priority` attribute of a
certain tag

For instance if you have a bunch of telegram commands:

* /start
* /terms
* ...

And you probably don't want process nonexistent command names:

* /wrongcommand
* /nonexistentcommand
* ...

You can
extend [TestNullResponseToIncorrectCommandHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Test/PriorityAble/Message/Command/LowPriority/TestNullResponseToIncorrectCommandHandler.php)
at any place in your code (I would do it in the `src/TelegramBot/Command/NullResponseForNonexistentCommand.php`)

So all commands with `40` priority will be processed faster, only then if none of them support the payload, incorrect
handler with `30` priority will be processed and because it supports text staring with `/` it will always support any
`/command`

> NOTE: commands belong to the `grinway_telegram.bot.message_handler` tag.

> **PRO TIP:** We understood that commands belong to the `grinway_telegram.bot.message_handler` tag because the main
> optional webhook payload [update key](https://core.telegram.org/bots/api#update) was the`message`

So when you assign a `priority` attribute to the service that has a certain
[grinway_telegram.bot.
`UPDATE_FIELD`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/docs/reference.md#handler-tags)
tag this directory structure takes place:

* `TelegramBotHandler/`
*
    * `CallbackQuery/`
*
    * `Message/`
*
    * `...`
*
    * `PriorityAble/`
*
    *
        * `Hight/`
            * `Message/`
                * `Group/`
                * `PrivateChat/`
*
    *
        * `Low/`
            * `Message/`
                * `Group/`
                * `PrivateChat/`
