<?php

namespace GrinWay\Telegram\Bot\Handler\Topic;

use GrinWay\Telegram\Bot\Contract\Topic\TopicHandlerInterface;
use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\VarExporter\Hydrator;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractTopicHandler implements TopicHandlerInterface
{
    // Not required for inline query
    protected ?string $chatId = null;
    protected ?string $text = null;

    public function __construct(
        protected PropertyAccessorInterface                                                                            $pa,
        protected TranslatorInterface                                                                                  $t,
        protected Packages                                                                                             $asset,
        #[Autowire('%kernel.project_dir%')] protected string                                                           $projectDir,
        #[Autowire('%grinway_telegram.bot.name%')] protected string                                                    $telegramBotName,
        protected Telegram                                                                                             $telegram,
        #[Autowire('%grinway_telegram.bot.on_topic_supergroup_message_reply_directly_there%')] protected readonly bool $replyOnTopicSuperGroupMessage,
        protected ?ChatterInterface                                                                                    $chatter = null,
    )
    {
    }

    /**
     * You must explicitly $chatMessage->subject('NOT EMPTY CONTENT')
     *
     * @param ChatMessage $chatMessage
     * @param TelegramOptions $telegramOptions
     * @param mixed $fieldValue
     * @return bool If false there will be NO response (but you supported that NO response)
     */
    abstract protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    /**
     * Client can change this logic
     */
    protected function processTelegramOptionsBeforeSendMessage(TelegramOptions $telegramOptions, array $fieldValue): void
    {
        /*
         * A topic message was sent I guess the bot should reply directly to the topic
         * if not change the bundle configuration
         */
        if (true === $this->replyOnTopicSuperGroupMessage) {
            if (true === $this->pa->getValue($fieldValue, '[is_topic_message]')
                || true === $this->pa->getValue($fieldValue, '[message][is_topic_message]')
            ) {
                $topicId = $this->pa->getValue($fieldValue, '[message_thread_id]') ?? $this->pa->getValue($fieldValue, '[message][message_thread_id]');
                if ($topicId) {

                    // cuz of there is no such a method I have to do this masochistic actions
//                $telegramOptions->messageThreadId($topicId);

                    Hydrator::hydrate($telegramOptions, [
                        'options' => [
                            ...$telegramOptions->toArray(),
                            'message_thread_id' => $topicId,
                        ],
                    ]);
                }
            }
        }
    }

    public function handleSentMessage(?SentMessage $sentMessage): void
    {
    }

    /**
     * Sets some commonly used values when webhook was called
     */
    public function beforeSupports(mixed $fieldValue): static
    {
        return $this
            ->setChatId($fieldValue)
            ->setText($fieldValue)//
            ;
    }

    public function handle(mixed $fieldValue): void
    {
        $chatMessage = new ChatMessage('');
        $telegramOptions = (new TelegramOptions())
            ->parseMode(TelegramOptions::PARSE_MODE_HTML)//
        ;
        $this->configureTelegramOptions($telegramOptions, $fieldValue);
        $isHandleable = $this->doHandle($chatMessage, $telegramOptions, $fieldValue);

        if ('' === $chatMessage->getSubject() || false === $isHandleable) {
            return;
        }

        $this->processTelegramOptionsBeforeSendMessage($telegramOptions, $fieldValue);

        $chatMessage->options($telegramOptions);
        $sentMessage = $this->chatter?->send($chatMessage);

        $this->handleSentMessage($sentMessage);
    }

    /**
     * @internal
     */
    protected function configureTelegramOptions(TelegramOptions $telegramOptions, mixed $fieldValue): void
    {
        if (null !== $this->chatId) {
            $telegramOptions->chatId($this->chatId);
        }
    }

    /**
     * @internal
     */
    protected function setChatId(mixed $fieldValue): static
    {
        if (!\is_array($fieldValue)) {
            return $this;
        }
        $chatId = $this->pa->getValue($fieldValue, '[chat][id]');
        if (null === $chatId) {
            $chatId = $this->pa->getValue($fieldValue, '[message][chat][id]');
        }
        $this->chatId = $chatId;
        return $this;
    }

    /**
     * @internal
     */
    protected function setText(mixed $fieldValue): static
    {
        $this->text = $this->pa->getValue($fieldValue, '[text]');
        return $this;
    }
}
