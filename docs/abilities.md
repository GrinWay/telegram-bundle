Abilities
------
This bundle helps you to:

* write your [telegram bots](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Test)
* use stimulus
  [grinway-telegram--web-app](https://github.com/GrinWay/telegram-bundle/blob/main/assets/dist/bot/web-app_controller.js)
  controller
* use
  [grinway_telegram](https://github.com/GrinWay/telegram-bundle/blob/main/src/Service/Telegram.php) service
* use
  [Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramClient](https://github.com/GrinWay/telegram-bundle/blob/main/config/packages/framework_http_client.yaml)
  service
* use
  [Symfony\Contracts\HttpClient\HttpClientInterface $grinwayTelegramFileClient](https://github.com/GrinWay/telegram-bundle/blob/main/config/packages/framework_http_client.yaml)
  service
* use
  [grinway_telegram:bot:set_webhook](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Command/TelegramSetWebhookCommand.php)
  symfony command
* use
  [grinway_telegram:bot:remove_webhook](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Command/TelegramRemoveWebhookCommand.php)
  symfony command
* Ready to use form types:
  * [NumberWithEndFiguresFormType](https://github.com/GrinWay/telegram-bundle/blob/main/src/Form/NumberWithEndFiguresFormType.php)
  * [TelegramLabeledPriceFormType](https://github.com/GrinWay/telegram-bundle/blob/main/src/Form/TelegramLabeledPriceFormType.php)
  * [TelegramLabeledPricesFormType](https://github.com/GrinWay/telegram-bundle/blob/main/src/Form/TelegramLabeledPricesFormType.php)
* Related form type data transformers :
  * [NumberWithEndFiguresDataTransformer](https://github.com/GrinWay/telegram-bundle/blob/main/src/Form/DataTransformer/NumberWithEndFiguresDataTransformer.php)
  * [TelegramLabeledPriceDataTransformer](https://github.com/GrinWay/telegram-bundle/blob/main/src/Form/DataTransformer/TelegramLabeledPriceDataTransformer.php)
  * [TelegramLabeledPricesDataTransformer](https://github.com/GrinWay/telegram-bundle/blob/main/src/Form/DataTransformer/TelegramLabeledPricesDataTransformer.php)

This bundle also adds
a [grinway_telegram_bot_webhook](https://github.com/GrinWay/telegram-bundle/blob/main/.install/symfony/config/routes/grinway_telegram_routes.yaml)
route to accept webhook calls
