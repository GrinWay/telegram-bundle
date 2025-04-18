Dynamic command
------

If you have database with all commands, their descriptions and reply subjects,
take the following snippet into consideration:

```php
<?php

namespace App\TelegramBot\PriorityAble\Message\Command;

use App\Repository\TgBotCommandReplyRepository;
use GrinWay\Telegram\Bot\Contract\Topic\CommandMessageHandlerInterface;
use GrinWay\Telegram\Bot\Handler\Topic\PriorityAble\Message\PrivateChat\AbstractPrivateChatHandler;
use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DynamicCommand extends AbstractPrivateChatHandler
{
    public function __construct(PropertyAccessorInterface $pa, TranslatorInterface $t, Packages $asset, #[Autowire('%kernel.project_dir%')] string $projectDir, #[Autowire('%grinway_telegram.bot.name%')] string $telegramBotName, Telegram $telegram, #[Autowire('%grinway_telegram.bot.on_topic_supergroup_message_reply_directly_there%')] bool $replyOnTopicSuperGroupMessage, private readonly TgBotCommandReplyRepository $tgBotCommandReplyRepository, ?ChatterInterface $chatter = null)
    {
        parent::__construct($pa, $t, $asset, $projectDir, $telegramBotName, $telegram, $replyOnTopicSuperGroupMessage, $chatter);
    }

    public function supports(mixed $fieldValue): bool
    {
        return parent::supports($fieldValue)
            && \str_starts_with($this->text, '/');
    }

    protected function doHandle(ChatMessage $chatMessage, TelegramOptions $telegramOptions, mixed $fieldValue): bool
    {
        $commandName = \substr($this->text, 1);
        $tgBotCommandReplyEntity = $this->tgBotCommandReplyRepository->findOneBy([
            'name' => $commandName,
        ]);
        if (null === $tgBotCommandReplyEntity) {
            return false;
        }
        $chatMessage->subject($tgBotCommandReplyEntity->getSubject());
        return true;
    }
}
```

```yaml
# %kernel.project_dir%/config/services.yaml
services:
    _defaults:
        autowire: true
        
        # IMPORTANT to switch off default priority assignments
        autoconfigure: false

    # Actually extends the private message handler but has command priority 
    App\TelegramBot\PriorityAble\Message\Command\DynamicCommand:
        class: 'App\TelegramBot\PriorityAble\Message\Command\DynamicCommand'
        tags:
        -   name: !php/const GrinWay\Telegram\Bot\Contract\Topic\CommandMessageHandlerInterface::TAG
            priority: !php/const GrinWay\Telegram\Bot\Contract\Topic\CommandMessageHandlerInterface::PRIORITY

    App\TelegramBot\PriorityAble\Message\PrivateChat\KeywordPrivateChatHandler:
        class: 'App\TelegramBot\PriorityAble\Message\PrivateChat\KeywordPrivateChatHandler'
        tags:
        -   name: !php/const GrinWay\Telegram\Bot\Contract\Topic\PrivateChatMessageHandlerInterface::TAG
            priority: 21

    App\TelegramBot\PriorityAble\Message\PrivateChat\DefMessPrivateChatHandler:
        class: 'App\TelegramBot\PriorityAble\Message\PrivateChat\DefMessPrivateChatHandler'
        tags:
        -   name: !php/const GrinWay\Telegram\Bot\Contract\Topic\PrivateChatMessageHandlerInterface::TAG
            priority: 20
```
