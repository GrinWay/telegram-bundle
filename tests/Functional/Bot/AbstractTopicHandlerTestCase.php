<?php

namespace GrinWay\Telegram\Tests\Functional\Bot;

use GrinWay\Telegram\Tests\AbstractTelegramTestCase;
use Symfony\Component\Notifier\Message\ChatMessage;
use Zenstruck\Browser\KernelBrowser;

abstract class AbstractTopicHandlerTestCase extends AbstractTelegramTestCase
{
    protected function assertTelegramBotHandledPayload(array $payload, int $botRepliedCount, ?string $subject = null): void
    {
        $this->invokeWebhook($payload);
        $this->transport('sync')->queue()->assertCount($botRepliedCount);
        $this->transport('sync')->rejected()->assertEmpty();

        if (0 < $botRepliedCount) {
            /** @var ChatMessage $chatMessage */
            $chatMessage = $this->transport('sync')->queue()->first(ChatMessage::class)->getMessage();
            $this->assertNotNull($chatMessage);
            $this->assertNotNull($subject);
            self::assertSame(
                $chatMessage->getSubject(),
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
