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

### Price auto-compensation to 1$ `since v1.1.0`

Looking at the
[Telegram Bot Api Currency](https://core.telegram.org/bots/payments#supported-currencies)
section we can find the information that the minimum summary price of the invoice can't be less than `1$`

Because of it this, auto-compensation feature was added to the `grinway_telegram` service
when you create an invoice link or send invoices.

With this feature if your sum of invoice less than `1$` it will be automatically increased to reach `$1`

> Without minimum allowed summary price of the invoice there is no guarantee that an invoice link will be created or invoice will be sent

> **IMPORTANT**: There is also the maximum invoice price restriction but this bundle doesn't process this situation
> <br>
> simply if your invoice sum exceeds the maximum possible amount invoice won't be sent or invoice link won't be created
> because internally Telegram API will return `4xx` status code
