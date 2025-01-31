<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\InlineQuery;

use GrinWay\Telegram\Bot\Contract\Topic\InlineQueryHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\AbstractTopicHandler;
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

/**
 * When user types: "@TELEGRAM_BOT_NAME "
 * https://telegram.org/blog/inline-bots
 */
abstract class AbstractInlineQueryHandler extends AbstractTopicHandler implements InlineQueryHandlerInterface
{
    public function __construct(
        PropertyAccessorInterface                                                                   $pa,
        TranslatorInterface                                                                         $t,
        Packages                                                                                    $asset,
        #[Autowire('%kernel.project_dir%')] string                                                  $projectDir,
        #[Autowire('%grinway_telegram.bot.name%')] string                                           $telegramBotName,
        Telegram                                                                                    $telegram,
        #[Autowire('%grinway_telegram.bot.on_topic_supergroup_message_reply_directly_there%')] bool $replyOnTopicSuperGroupMessage,
        //
        protected readonly SerializerInterface                                                      $serializer,
        protected readonly HttpClientInterface                                                      $grinwayTelegramClient,
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
            chatter: $chatter,
        );
    }

    abstract protected function doInlineQueryHandle(string $inlineQueryId, ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    public function supports(mixed $fieldValue): bool
    {
        return true;
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $inlineQueryId = $this->pa->getValue($fieldValue, '[id]');
        if (null === $inlineQueryId) {
            return false;
        }
        $inlineQueryId = (string)$inlineQueryId;

        return $this->doInlineQueryHandle($inlineQueryId, $chatMessage, $telegramOptions, $fieldValue);
    }
}
