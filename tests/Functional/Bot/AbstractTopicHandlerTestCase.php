<?php

namespace GrinWay\Telegram\Tests\Functional\Bot;

use GrinWay\Telegram\Tests\AbstractTelegramTestCase;
use Zenstruck\Browser\KernelBrowser;

abstract class AbstractTopicHandlerTestCase extends AbstractTelegramTestCase
{
    protected function assertTelegramBotHandledPayload(array $payload, int $botRepliedCount, ?string $subject = null): void
    {
        $this->invokeWebhook($payload);
        self::assertNotificationCount($botRepliedCount);

        if (0 < $botRepliedCount) {
            $notification = self::getNotifierEvent(0)?->getMessage();
            $this->assertNotNull($notification);
            $this->assertNotNull($subject);
            self::assertNotificationSubjectContains(
                $notification,
                $subject,
            );
        }
    }

    private function invokeWebhook(array $payload): KernelBrowser
    {
        return $this->browser()
            ->withProfiling()
            ->post(
                AbstractTelegramTestCase::WEBHOOK,
                [
                    'json' => $payload,
                ],
            )
            ->assertSuccessful();
    }
}
