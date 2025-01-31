<?php

namespace GrinWay\Telegram\Bot\Test\Payment\PreCheckoutQuery;

use GrinWay\Telegram\Bot\Handler\Topic\Payment\PreCheckoutQuery\AbstractPreCheckoutQueryHandler;
use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TestPreCheckoutQueryHandler extends AbstractPreCheckoutQueryHandler
{
    public const SUBJECT = 'TEST PAYMENT PRE CHECKOUT QUERY';

    public function __construct(
        PropertyAccessorInterface                                                                   $pa,
        TranslatorInterface                                                                         $t,
        Packages                                                                                    $asset,
        #[Autowire('%kernel.project_dir%')] string                                                  $projectDir,
        #[Autowire('%grinway_telegram.bot.name%')] string                                           $telegramBotName,
        Telegram                                                                                    $telegram,
        SerializerInterface                                                                         $serializer,
        HttpClientInterface                                                                         $grinwayTelegramClient,
        #[Autowire('%grinway_telegram.bot.on_topic_supergroup_message_reply_directly_there%')] bool $replyOnTopicSuperGroupMessage,
        //
        #[Autowire('%grinway_telegram.test.bot.chat_id%')] private readonly string                  $testChatId,
        //
        ?ChatterInterface                                                                           $chatter = null,
    )
    {
        parent::__construct(
            pa: $pa,
            t: $t,
            asset: $asset,
            projectDir: $projectDir,
            telegramBotName: $telegramBotName,
            telegram: $telegram,
            replyOnTopicSuperGroupMessage: $replyOnTopicSuperGroupMessage,
            serializer: $serializer,
            grinwayTelegramClient: $grinwayTelegramClient,
            chatter: $chatter,
        );
    }

    protected function doPreCheckoutQueryHandle(string $preCheckoutQueryId, ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $chatMessage->subject(self::SUBJECT);

        /**
         * You won't have chat_id
         *
         * In your client code use service:
         * "grinway_telegram" and call "answerPreCheckoutQuery" method
         * Needless set subject and return true, because you will answer right here without ChatMessage
         */
        $telegramOptions->chatId($this->testChatId);

        return true;
    }
}
