Reference
------

### Handler tags

Out of the box "grinway_telegram.bot.`UPDATE_FIELD`_handler" tags:

| [TagServiceLocatorsPass](https://github.com/GrinWay/service-bundle/blob/main/src/Pass/TagServiceLocatorsPass.php) [symfony compiler pass](https://symfony.com/doc/current/service_container/compiler_passes.html)<br>looks for services that have these tags<br>because of update handler existence |
|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [grinway_telegram.bot.`business_connection`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/BusinessConnectionHandler.php#L7)                                                                                                                                  |
| [grinway_telegram.bot.`business_message`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/BusinessMessageHandler.php#L7)                                                                                                                                        |
| [grinway_telegram.bot.`callback_query`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/CallbackQueryHandler.php#L7)                                                                                                                                            |
| [grinway_telegram.bot.`channel_post`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/ChannelPostHandler.php#L7)                                                                                                                                                |
| [grinway_telegram.bot.`chat_boost`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/ChatBoostHandler.php#L7)                                                                                                                                                    |
| [grinway_telegram.bot.`chat_join_request`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/ChatJoinRequestHandler.php#L7)                                                                                                                                       |
| [grinway_telegram.bot.`chat_member`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/ChatMemberHandler.php#L7)                                                                                                                                                  |
| [grinway_telegram.bot.`chosen_inline_result`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/ChosenInlineResultHandler.php#L7)                                                                                                                                 |
| [grinway_telegram.bot.`deleted_business_messages`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/DeletedBusinessMessagesHandler.php#L7)                                                                                                                       |
| [grinway_telegram.bot.`edited_business_message`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/EditedBusinessMessageHandler.php#L7)                                                                                                                           |
| [grinway_telegram.bot.`edited_channel_post`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/EditedChannelPostHandler.php#L7)                                                                                                                                   |
| [grinway_telegram.bot.`edited_message`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/EditedMessageHandler.php#L7)                                                                                                                                            |
| [grinway_telegram.bot.`inline_query`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/InlineQueryHandler.php#L7)                                                                                                                                                |
| [grinway_telegram.bot.`message`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/MessageHandler.php#L7)                                                                                                                                                         |
| [grinway_telegram.bot.`message_reaction_count`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/MessageReactionCountHandler.php#L7)                                                                                                                             |
| [grinway_telegram.bot.`message_reaction`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/MessageReactionHandler.php#L7)                                                                                                                                        |
| [grinway_telegram.bot.`my_chat_member`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/MyChatMemberHandler.php#L7)                                                                                                                                             |
| [grinway_telegram.bot.`poll_answer`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/PollAnswerHandler.php#L7)                                                                                                                                                  |
| [grinway_telegram.bot.`poll`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/PollHandler.php#L7)                                                                                                                                                               |
| [grinway_telegram.bot.`pre_checkout_query`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/PreCheckoutQueryHandler.php#L7)                                                                                                                                     |
| [grinway_telegram.bot.`purchased_paid_media`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/PurchasedPaidMediaHandler.php#L7)                                                                                                                                 |
| [grinway_telegram.bot.`removed_chat_boost`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/RemovedChatBoostHandler.php#L7)                                                                                                                                     |
| [grinway_telegram.bot.`shipping_query`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/ShippingQueryHandler.php#L7)                                                                                                                                            |
| [grinway_telegram.bot.`update_id`_handler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/UpdateIdHandler.php#L10)                                                                                                                                                     |

> NOTE: All these tags are looking for automatically in the bundle with the help of
`GrinWay\Service\Pass\TagServiceLocatorsPass`

You can use the following command to find out how many `message` handlers exist in your app

```console
php bin/console debug:container --tag grinway_telegram.bot.message_handler --env dev
```

You can easily create a new update handler extending from
[AbstractUpdateHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Update/AbstractUpdateHandler.php)
, and it will look like all out of the box update handlers
depending on `UPDATE_FIELD` property

The `GrinWay\Service\Pass\TagServiceLocatorsPass` will look for all services have
the appropriate tag (including a new teg as well).
