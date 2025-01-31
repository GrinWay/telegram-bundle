<?php

namespace GrinWay\Telegram\Bot\Handler\Topic\Payment\ShippingQuery;

use GrinWay\Telegram\Bot\Contract\Topic\ShippingQueryHandlerInterface;
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
 * When you started payment created with requirement of an address and is_flexible option
 * https://core.telegram.org/bots/api#answershippingquery
 */
abstract class AbstractShippingQueryHandler extends AbstractTopicHandler implements ShippingQueryHandlerInterface
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

    /**
     * We're here when Telegram::sendInvoice(..., needShippingAddress: true, isFlexible: true)
     */
    abstract protected function doShippingQueryHandle(string $shippingQueryId, ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool;

    public function supports(mixed $fieldValue): bool
    {
        return true;
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $shippingQueryId = $this->pa->getValue($fieldValue, '[id]');
        if (null === $shippingQueryId) {
            return false;
        }
        $shippingQueryId = (string)$shippingQueryId;

        return $this->doShippingQueryHandle($shippingQueryId, $chatMessage, $telegramOptions, $fieldValue);
    }
}
