After installation (prerequisite)
------

### If you want to use telegram web(mini) app

For pages with telegram web(mini) app you will need to add the telegram's `script` tag to the `head` html element

```html

<head>
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
```

> NOTE: If you use `webpack encore` I advise you to use a separate entry
`Encore.addEntry('telegram-web-app', './assets/telegram/web-app/app.js')`

### If you'll use this bundle tests

Add the following PHPUnit extension to guarantee the `test bot` won't have set webhook after all tests
(because after all tests webhook will be explicitly removed for the test environment)

```xml
<!-- phpunit.xml.dist -->
<bootstrap class="GrinWay\Telegram\Extension\Test\GrinWayTelegramForceRemoveTestWebhook"/>
```

> **VERY IMPORTANT WARNING**: If you use this bundle tests and didn't add the above extension there is a possibility
> that the `test bot` will have set a webhook (maybe you accidentally set it in the test environment) so if a message
> will be sent to the `test bot`
> Telegram Bot Api will call this webhook.<br><br>Even worse if the `test bot` have the same `webhook` as a `real bot`
> when `test bot` gets a message Telegram Bot Api will call `webhook` for the `test bot` and `real bot` as well!
