<?php

namespace GrinWay\Telegram\Extension\Test;

use PHPUnit\Event\TestRunner\Finished as TestRunnerFinishedEvent;
use PHPUnit\Event\TestRunner\FinishedSubscriber as TestRunnerFinishedSubscriber;
use PHPUnit\Event\TestRunner\Started as TestRunnerStartedEvent;
use PHPUnit\Event\TestRunner\StartedSubscriber as TestRunnerStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * https://docs.phpunit.de/en/11.3/extending-phpunit.html#extending-the-test-runner
 */
class GrinWayTelegramForceRemoveTestWebhook implements Extension
{
    public function bootstrap(
        Configuration       $configuration,
        Facade              $facade,
        ParameterCollection $parameters
    ): void
    {
        $facade->registerSubscriber(new class() implements TestRunnerStartedSubscriber {
            public function __construct()
            {
            }

            public function notify(TestRunnerStartedEvent $event): void
            {
            }
        });

        $facade->registerSubscriber(new class() implements TestRunnerFinishedSubscriber {
            public function __construct()
            {
            }

            public function notify(TestRunnerFinishedEvent $event): void
            {
                \exec('php bin/console grinway_telegram:bot:remove_webhook --env test');
            }
        });
    }
}
