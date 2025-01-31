Payment process
------
> TIP: `grinway_telegram` service already contains most common payment api methods such as:

* `createInvoiceLink`
* `sendInvoice`
* `answerShippingQuery`<br>(call it in your code in the
  [AbstractShippingQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/Payment/ShippingQuery/AbstractShippingQueryHandler.php)
  extended handler)
* `answerPreCheckoutQuery`<br>(call it in your code in the
  [AbstractPreCheckoutQueryHandler](https://github.com/GrinWay/telegram-bundle/blob/main/src/Bot/Handler/Topic/Payment/PreCheckoutQuery/AbstractPreCheckoutQueryHandler.php)
  extended handler)

[Telegram Bot Api Payment process](https://core.telegram.org/bots/payments-stars#step-by-step-process)
