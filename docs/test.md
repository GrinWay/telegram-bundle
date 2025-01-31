Test
------

| Functional tests                                                                                                                       |                                                                                                                                                                                                                                           Description                                                                                                                                                                                                                                           |
|----------------------------------------------------------------------------------------------------------------------------------------|:-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------:|
| [TelegramBotAllHandlersTest](https://github.com/GrinWay/telegram-bundle/blob/main/tests/Functional/Bot/TelegramBotAllHandlersTest.php) | Test all test handlers with client 'POST' `webhook` requests<br><br>To tell the truth [TestMessageHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Test/Message/TestMessageHandler.php) is not tested because it's impossible to reach this handler because of [priority able versions](https://github.com/GrinWay/telegram-bundle/tree/main/src/Bot/Test/PriorityAble/Message) of the `message` [optional key webhook payload](https://core.telegram.org/bots/api#update) |
| [TelegramServiceTest](https://github.com/GrinWay/telegram-bundle/blob/main/tests/Functional/TelegramServiceTest.php)                   |                                                                                                                                                                                                                     `grinway_telegram` service tests with real http client                                                                                                                                                                                                                      |

> NOTE: `webhook` is called by ourselves (not by Telegram Bot Api).
> <br>Actually we don't need to register a webhook for the test bot (mean in the test environment)!
> <br>Because that's not safe

In the `TelegramBotAllHandlersTest` tests we have no real sent messages in the telegram by a client.

So `TelegramBotAllHandlersTest` just make a "POST" request to the `webhook`
(so we imitate `webhook` was called by Telegram Bot Api) and
wait to be explicitly handled by test handlers (registered by this bundle as services only when test environment).

| Unit tests                                                                                                     |             Description              |
|----------------------------------------------------------------------------------------------------------------|:------------------------------------:|
| [TelegramServiceTest](https://github.com/GrinWay/telegram-bundle/blob/main/tests/Unit/TelegramServiceTest.php) | Stub tests without a real httpClient |

> TIP: All test handlers of this bundle won't conflict your real handlers because test handlers only added to the
> service container when `test` environment

> NOTE: If you decided to execute this bundle tests in your project by extending test classes, keep in mind that your
> real handlers can conflict with test handlers and some tests can fail.
> To avoid this situation you need to test this bundle right after installation of the bundle when you
> **haven't created your handlers yet**
