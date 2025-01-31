Bot understanding
------
When a telegram user sent a message to the `bot` or to the `group/channel` where the bot is admin
<br>
if the webhook is registered with the help of `grinway_telegram:bot:set_webhook` command
<br>
`webhook` is called by the Telegram Bot Api with main payload [keys](https://core.telegram.org/bots/api#update):

* `REQUIRED` key (update_id)
* at most one of the optional `UPDATE_FILED`
  [key](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Handler/Update)

Then your bot just decides to "answer" or not (internally "answer" it's a "POST" request).
