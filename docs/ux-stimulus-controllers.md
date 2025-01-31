UX stimulus controllers
------

You have to have included
head [telegram's script html tag](https://core.telegram.org/bots/webapps#initializing-mini-apps)

| Name                                                                                                                    | Optional values | Optional targets |                                                                 Template example                                                                 |
|-------------------------------------------------------------------------------------------------------------------------|:---------------:|:----------------:|:------------------------------------------------------------------------------------------------------------------------------------------------:|
| [grinway-telegram--web-app](https://github.com/GrinWay/telegram-bundle/blob/main/assets/dist/bot/web-app_controller.js) | `userAgent`<br> |    `form`<br>    | [@GrinWayTelegram/telegram/bot/web-app.html.twig](https://github.com/GrinWay/telegram-bundle/blob/main/templates/telegram/bot/web-app.html.twig) |

#### `grinway-telegram--web-app` stimulus controller usage example:

```html

<div {{
     stimulus_controller('grinway-telegram--web-app', controllerValues={ userAgent: app.request.headers.get('User-Agent') })
}}
>
{# ... #}
</div>
```

| [grinway-telegram--web-app](https://github.com/GrinWay/telegram-bundle/blob/main/assets/dist/bot/web-app_controller.js) `properties` |                                                                Description                                                                 |
|--------------------------------------------------------------------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------------------------------------------------:|
| tg                                                                                                                                   |                                         Telegram instance provided by Telegram Mini App script tag                                         |
| userId                                                                                                                               |                                                        User id supplied by telegram                                                        |
| queryId                                                                                                                              |                           [query_id](https://core.telegram.org/bots/webapps#webappinitdata) supplied by telegram                           |
| headerColor                                                                                                                          |                                 During `connect` stimulus controller method value sets via `tg` parameter                                  |
| backgroundColor                                                                                                                      |                                 During `connect` stimulus controller method value sets via `tg` parameter                                  |
| bottomBarColor                                                                                                                       |                                 During `connect` stimulus controller method value sets via `tg` parameter                                  |
| colorTheme                                                                                                                           |                                 During `connect` stimulus controller method value sets via `tg` parameter                                  |
| userAgentAppVersion                                                                                                                  | If user entered via phone (not PC) during `connect` stimulus controller method value sets via passed stimulus controller `userAgent` value |
| userAgentManufacturer                                                                                                                | If user entered via phone (not PC) during `connect` stimulus controller method value sets via passed stimulus controller `userAgent` value |
| userAgentModel                                                                                                                       | If user entered via phone (not PC) during `connect` stimulus controller method value sets via passed stimulus controller `userAgent` value |
| userAgentAndroidVersion                                                                                                              | If user entered via phone (not PC) during `connect` stimulus controller method value sets via passed stimulus controller `userAgent` value |
| userAgentSdkVersion                                                                                                                  | If user entered via phone (not PC) during `connect` stimulus controller method value sets via passed stimulus controller `userAgent` value |
| userAgentPerformanceClass                                                                                                            | If user entered via phone (not PC) during `connect` stimulus controller method value sets via passed stimulus controller `userAgent` value |

| [grinway-telegram--web-app](https://github.com/GrinWay/telegram-bundle/blob/main/assets/dist/bot/web-app_controller.js)<br>`getters` |                                                                                                        Description                                                                                                         |
|--------------------------------------------------------------------------------------------------------------------------------------|:--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| form                                                                                                                                 | Allow you to get the `form` named [stimulus controller target](https://stimulus.hotwired.dev/reference/targets) if you have such a target inside an html element that uses `grinway-telegram--web-app` stimulus controller |

| [grinway-telegram--web-app](https://github.com/GrinWay/telegram-bundle/blob/main/assets/dist/bot/web-app_controller.js)<br>`connect` methods |                                                                                                                                                                                                       Description                                                                                                                                                                                                        |
|----------------------------------------------------------------------------------------------------------------------------------------------|:------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| tgInit                                                                                                                                       |                                                                                                                                                                                                 Writes this.tg property                                                                                                                                                                                                  |
| parseTelegramUserAgent                                                                                                                       |                                                                                                                                               Parses the user agent header modified by the telegram script tag when a user used a phone to enter into the telegram web app                                                                                                                                               |
| registerEvents                                                                                                                               |                                Registers some [telegram mini app events](https://core.telegram.org/bots/webapps#events-available-for-mini-apps) but not every existing event.<br><br>You can extend this stimulus controller class and use yours instead...<br>register new events...<br>but don't forget to unregister them in the `unregisterEvents` method of this stimulus controller                                |
| tgSettings                                                                                                                                   |                                                                                                                                      Sets the default behaviour defined by this stimulus controller, if you don't like the default behaviour, extend class and overwrite the method                                                                                                                                      |
| setColorTheme                                                                                                                                |                                                                                                                                       During `connect` of [stimulus controller lifecycle](https://stimulus.hotwired.dev/reference/lifecycle-callbacks) sets some color properties                                                                                                                                        |
| updateFormData                                                                                                                               | **This method is highly useful**<br><br>It sets `userId` and `queryId` properties<br><br>And also if the form target exists this stimulus controller automatically sets `web_app_user_id` and `web_app_query_id` form `hidden` fields.<br>Change this login overwriting `setFormHiddenFields` method<br><br>This information can't be reached from the backend side (symfony controller) because of this we add it later |
| tgWebAppReady                                                                                                                                |                                                                                                                                                                   By default this stimulus controller expands the web app and clears its loading state                                                                                                                                                                   |

| [grinway-telegram--web-app](https://github.com/GrinWay/telegram-bundle/blob/main/assets/dist/bot/web-app_controller.js)<br>`disconnect` methods |                                                                  Description                                                                  |
|-------------------------------------------------------------------------------------------------------------------------------------------------|:---------------------------------------------------------------------------------------------------------------------------------------------:|
| unregisterEvents                                                                                                                                | Unregisters set by this stimulus controller [telegram mini app events](https://core.telegram.org/bots/webapps#events-available-for-mini-apps) |

#### Out of the box "grinway-telegram--web-app" stimulus controller has several implementations of [Telegram Web\(Mini\) App Methods](https://core.telegram.org/bots/webapps#initializing-mini-apps)

| API methods       |
|-------------------|
| switchInlineQuery |
| openLink          |
| downloadFile      |
| showPopup         |
| showAlert         |
| showConfirm       |
| showScanQrPopup   |
| openInvoice       |
