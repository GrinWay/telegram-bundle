Configuration
------
To look at `default` configuration execute:

```console
php bin/console config:dump-reference grinway_telegram
```

To look at `actual` configuration execute:

```console
php bin/console debug:config grinway_telegram
```

To configure this bundle follow to the
`%kernel.project_dir%/config/packages/grinway_telegram.yaml`
and change this file.

If the file above is not created automatically by the composer recipe
just copy the file
`@GrinWayTelegram/.install/symfony/config/packages/grinway_telegram.yaml`
and paste it by the above path.

> `Since 1.3.0` You can use the `grinway_telegram.dsn` parameter
> to reference already existing Telegram DSN

Example:

```yaml
# %kernel.project_dir%/config/packages/notifier.yaml
framework:
    notifier:
        chatter_transports:
            telegram: '%grinway_telegram.dsn%'
```
