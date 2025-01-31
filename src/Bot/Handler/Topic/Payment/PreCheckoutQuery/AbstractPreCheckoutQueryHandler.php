<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\Payment\PreCheckoutQuery;

use GrinWay\Telegram\Bot\Contract\Topic\PreCheckoutQueryHandlerInterface;
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
 * When a pay button was pushed
 * https://core.telegram.org/bots/payments-stars#3-pre-checkout
 */
abstract class AbstractPreCheckoutQueryHandler extends AbstractTopicHandler implements PreCheckoutQueryHandlerInterface
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

    abstract protected function doPreCheckoutQueryHandle(string $preCheckoutQueryId, ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    public function supports(mixed $fieldValue): bool
    {
        return true;
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $preCheckoutQueryId = $this->pa->getValue($fieldValue, '[id]');
        if (null === $preCheckoutQueryId) {
            return false;
        }
        $preCheckoutQueryId = (string)$preCheckoutQueryId;

        return $this->doPreCheckoutQueryHandle($preCheckoutQueryId, $chatMessage, $telegramOptions, $fieldValue);
    }
}
