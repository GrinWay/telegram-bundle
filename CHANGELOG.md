[CHANGELOG](https://symfony.com/doc/current/contributing/code/conventions.html#writing-a-changelog-entry)
=========

3.0
---

 * Fix `grinway_telegram` methods made http request return request payload instead of ok bool state (previously)
 * Add `Telegram::isResponseOk` and `Telegram::isResponseNotOk` static methods to manually check is ok