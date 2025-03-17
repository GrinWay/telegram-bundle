[CHANGELOG](https://symfony.com/doc/current/contributing/code/conventions.html#writing-a-changelog-entry)
=========

5.0
---

* Fix `TelegramLabeledPrices` from now on
able to set 001, 099 with zero start number.
<br>
For several payment providers it's not allowed to set 099, if it is an exception
will be thrown like: `HTTP Bad request` but `TelegramLabeledPrices` allow clients to set 001, 099

3.0
---

* Fix `grinway_telegram` methods made http request return request payload instead of ok bool state (previously)
* Add `Telegram::isResponseOk` and `Telegram::isResponseNotOk` static methods to manually check is ok
