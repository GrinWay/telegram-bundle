Installation
------

1. Execute (for `vendor` dependencies)

```console
composer require grinway/telegram-bundle
```

> NOTE: With the help of composer recipe you will get<br>`config/packages/grinway_telegram.yaml` and
> `config/routes/grinway_telegram_routes.yaml`<br>**Check they're not empty!**

2. Add this to your `bundles.php`

```php
<?php

// %kernel.project_dir%/config/bundles.php
return [
    GrinWay\Service\GrinWayServiceBundle::class => ['all' => true],
    GrinWay\Telegram\GrinWayTelegramBundle::class => ['all' => true],
];
```

If you didn't get these configuration files just copy them from `@GrinWayTelegram/.install/symfony/config`

3. Execute (for `node_modules` dependencies)

```console
yarn install --force
```

4. Set all ENV variables of this bundle (required by the `config/packages/grinway_telegram.yaml` file):

```env
###> grinway/telegram-bundle ###
APP_HOST=

#
# to be more secure
# set this to the symfony secrets https://symfony.com/doc/current/configuration/secrets.html
#
# APP_TELEGRAM_BOT_NAME=
# APP_TELEGRAM_BOT_API_TOKEN=
#

###< grinway/telegram-bundle ###

###> grinway/telegram-bundle (test) ###
#
# to be more secure
# set this to the symfony secrets https://symfony.com/doc/current/configuration/secrets.html
#
# APP_TEST_TELEGRAM_BOT_NAME=
# APP_TEST_TELEGRAM_BOT_API_TOKEN=
# APP_TEST_TELEGRAM_BOT_CHAT_ID=
# APP_TEST_TELEGRAM_BOT_PAYMENT_PROVIDER_TOKEN=
#
###< grinway/telegram-bundle (test) ###
```
