R (REALIZE)

R: setMessageReaction
    random good emoji
    random bad emoji

how to extend ux controller
```js
// file ends with "_controller.js"
// for instance "web-app_controller.js"
import TelegramWebApp from "@grinway/telegram-bundle";

class default_1 extends TelegramWebApp {
    connect() {
        alert(1)
        super.connect();
    }
}

export { default_1 as default }
```


`docs`
```js
    setColorTheme() {
        const theme = this.tg?.colorScheme
        this.setTheme({ theme })
    }

    themeChanged() {
        const theme = this.tg?.colorScheme
        this.setTheme({ theme })
    }
```


test
> If you pass array, array will be assigned
> <br>
> If you pass `TelegramLabeledPrices`, `TelegramLabeledPrices` will be assigned


`getChatLink`

`SINCE 3.0.0`
handlers should use #[Required] instead of constructor dependencies

`docs about changelog file`
