<?php

namespace GrinWay\Telegram\Tests\Unit\Command;

use GrinWay\Telegram\Bot\Command\AbstractTelegramWebhookCommand;
use GrinWay\Telegram\Bot\Command\TelegramSetWebhookCommand;
use GrinWay\Telegram\Service\Telegram;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(AbstractTelegramWebhookCommand::class)]
abstract class AbstractTelegramWebhookCommandTestCase extends KernelTestCase
{
    public const SUCCESSFUL_PAYLOAD = ['ok' => true, 'description' => 'description'];
    public const FAILURE_PAYLOAD = ['ok' => false, 'error_code' => 'TEST', 'parameters' => 'TEST'];

    private MockObject $telegramMock;

    abstract protected function getCommandName(): string;

    abstract protected function getTelegramServiceMethod(): string;

    abstract protected function getFlags(): array;

    abstract protected function getExpectedDisplayText(): string;

    abstract protected function processTelegramMock(MockObject $telegramMock): void;

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $this->telegramMock = $this->createMock(Telegram::class);
        $this->processTelegramMock($this->telegramMock);
        self::getContainer()->set('grinway_telegram', $this->telegramMock);
    }

    public function testTelegramWebhookSuccessWithText(): void
    {
        $this->telegramMock
            ->expects(self::once())
            ->method($this->getTelegramServiceMethod())
            ->willReturn(self::SUCCESSFUL_PAYLOAD)//
        ;
        $this->processTelegramWebhook();
    }

    public function testTelegramWebhookFailureWithText(): void
    {
        $this->telegramMock
            ->expects(self::once())
            ->method($this->getTelegramServiceMethod())
            ->willReturn(self::FAILURE_PAYLOAD)//
        ;
        $this->processTelegramWebhook();
    }

    /**
     * Helper
     */
    private function processTelegramWebhook(): void
    {
        $application = new Application(self::$kernel);

        $command = $application->find($this->getCommandName());
        $commandTester = new CommandTester($command);
        $commandTester->execute($this->getFlags());

        $commandTester->assertCommandIsSuccessful();
        $this->assertStringContainsString(
            $this->getExpectedDisplayText(),
            $commandTester->getDisplay(),
        );
    }
}
