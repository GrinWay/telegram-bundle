<?php

namespace GrinWay\Telegram;

use GrinWay\Service\Pass\TagServiceLocatorsPass;
use GrinWay\Telegram\Bot\Contract\Topic\CallbackQueryHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\CommandMessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\GroupMessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\InlineQueryHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\LowPriorityCommandMessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\MessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\PreCheckoutQueryHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\PrivateChatMessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\ReplyToMessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\ShippingQueryHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\SuccessfulPaymentMessageHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Topic\TopicHandlerInterface;
use GrinWay\Telegram\Bot\Contract\Update\UpdateHandlerInterface;
use GrinWay\Telegram\Bot\Controller\TelegramController;
use GrinWay\Telegram\Bot\Trait\TelegramAwareTrait;
use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @final
 */
class GrinWayTelegramBundle extends AbstractBundle
{
    use TelegramAwareTrait;

    public const EXTENSION_ALIAS = 'grinway_telegram';
    public const BUNDLE_PREFIX = self::EXTENSION_ALIAS . '.';
    public const COMMAND_PREFIX = self::EXTENSION_ALIAS . ':';

    public const EXTENSION_ALIAS_KEBAB = 'grinway-telegram';
    public const TWIG_PREFIX_KEBAB = self::EXTENSION_ALIAS_KEBAB . ':';

    public const GENERIC_CACHE_TAG = self::EXTENSION_ALIAS;

    protected string $extensionAlias = self::EXTENSION_ALIAS;

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()//

            ->stringNode('app_host')
            ->info('Optional: used when executing command: php bin/console grinway_telegram:bot:set_webhook -d')
            ->defaultNull()
            ->end()//

            ->arrayNode('bot')
            ->children()//
            //###> bot array node ###

            ->scalarNode('name')
            ->cannotBeEmpty()
            ->isRequired()
            ->end()//

            ->scalarNode('api_token')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()//

            ->booleanNode('on_topic_supergroup_message_reply_directly_there')
            ->info('Optional: When telegram supergroup topic message was sent you will get "is_topic_message" in the payload we heed to set "message_thread_id" equals to the "topic_id" from webhook payload, this option does this for you')
            ->defaultTrue()
            ->end()//

            ->scalarNode('chat_id')
            ->info('REQUIRED ONLY WHEN TEST (for some webhooks handlers don\'t have chat_id key in the payload, and it\'s ok because we don\'t need to reply as a message to the chat but this bundle should know if a message was handled and sent and for this chat_id used)')
            ->defaultNull()
            ->end()//

            ->scalarNode('payment_provider_token')
            ->info('REQUIRED ONLY WHEN TEST (to send an invoice)')
            ->defaultNull()
            ->end()//

            //###< bot array node ###
            ->end()
            ->end()

            ->end()//
        ;
    }

    /**
     * Helper
     */
    private function setServiceContainerParameters(array $config, ContainerConfigurator $container): void
    {
        $env = $container->env();
        $parameters = $container->parameters();

        $parameters
            ->set(self::bundlePrefixed('app_host'), $config['app_host'])//

            ->set(self::bundlePrefixed('bot.webhook_path'), TelegramController::WEBHOOK_PATH)
            ->set(self::bundlePrefixed('bot.api_token'), $config['bot']['api_token'])
            ->set(self::bundlePrefixed('bot.on_topic_supergroup_message_reply_directly_there'), $config['bot']['on_topic_supergroup_message_reply_directly_there'])//

            ->set(self::bundlePrefixed('bot.name'), $config['bot']['name'])//
        ;

        if ('test' === $env) {
            foreach ([
                         [
                             'key' => self::bundlePrefixed('test.bot.chat_id'),
                             'value' => $config['bot']['chat_id'],
                             'isRequired' => true,
                         ],
                         [
                             'key' => self::bundlePrefixed('test.bot.payment_provider_token'),
                             'value' => $config['bot']['payment_provider_token'],
                             'isRequired' => true,
                         ],
                     ] as ['key' => $key, 'value' => $value, 'isRequired' => $isRequired]) {
                if (true === (bool)$isRequired && null === $value) {
                    $message = \sprintf('You must configure "%s" option', $key);
                    throw new \InvalidArgumentException($message);
                }
                $parameters->set($key, $value);
            }

            // if app_host is empty won't' pass the tests
            $parameters->set(self::bundlePrefixed('app_host'), 'example.com:80');
        }
    }

    /**
     * use loadExtension method instead
     */
//    public function getContainerExtension(): ?ExtensionInterface
//    {
//        return new GrinWayTelegramExtension();
//    }

    /**
     * Before service container compiled
     */
    public function build(ContainerBuilder $builder): void
    {
        parent::build($builder);
        $this->registerCompilerPasses($builder);
    }

    /**
     * Before service container compiled (late for registering compiler pass here)
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $this->importOtherBundleConfigurations($container, $builder);
        $this->registerForAutoconfiguration($container, $builder);
    }

    /**
     * After service container compiled
     *
     * Too late for registering compiler pass here
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $this->setServiceContainerParameters($config, $container);
        $this->setServiceContainerServices($config, $container);
    }

    /**
     * Root directory of this bundle
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * Helper
     */
    public static function bundlePrefixed(string $name): string
    {
        return \sprintf(
            '%s%s',
            self::BUNDLE_PREFIX,
            \ltrim($name, '._:'),
        );
    }

    /**
     * Helper
     */
    public function absPath(string $path): string
    {
        return \sprintf(
            '%s/%s',
            \rtrim($this->getPath(), '/\\'),
            \ltrim($path, '/\\'),
        );
    }

    /**
     * Helper
     */
    private function telegramUpdateHandlerPass(ContainerBuilder $builder): void
    {
        $builder->addCompilerPass(new TagServiceLocatorsPass(
            Telegram::class,
            'setUpdateHandlerIterator',
            UpdateHandlerInterface::TAG,
            'updateField',
            $this->getUpdateHandlerKey('%s'),
        ));
    }

    /**
     * Helper
     */
    private function registerCompilerPasses(ContainerBuilder $builder): void
    {
        $this->telegramUpdateHandlerPass($builder);
        $this->hideServices($builder);
    }

    /**
     * Helper
     */
    private function setServiceContainerServices(array $config, ContainerConfigurator $container): void
    {
        $container->import($this->absPath('config/services.yaml'));
    }

    /**
     * Helper
     */
    private function isAssetMapperAvailable(ContainerBuilder $builder): bool
    {
        if (!\interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        // check that FrameworkBundle 6.3 or higher is installed
        $bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
        if (!isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }

        return \is_file($bundlesMetadata['FrameworkBundle']['path'] . '/Resources/config/asset_mapper.php');
    }

    /**
     * Helper
     */
    private function assetMapperEnable(ContainerBuilder $builder): void
    {
        if (!$this->isAssetMapperAvailable($builder)) {
            return;
        }

        $builder->prependExtensionConfig('framework', [
            'asset_mapper' => [
                'paths' => [
                    __DIR__ . '/../assets/dist' => '@grinway/telegram-bundle',
                ],
            ],
        ]);
    }

    /**
     * Helper
     *
     *  Use %env(default:<parameter_name>:)%
     *  to access bundle parameters, that haven't been set yet
     */
    private function importOtherBundleConfigurations(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $this->assetMapperEnable($builder);
        $container->import($this->absPath('config/packages/framework_assets.yaml'));
        $container->import($this->absPath('config/packages/framework_http_client.yaml'));
        $container->import($this->absPath('config/packages/framework_messenger.yaml'));
        $container->import($this->absPath('config/packages/framework_notifier.yaml'));
        $container->import($this->absPath('config/packages/framework_property_access.yaml'));
        $container->import($this->absPath('config/packages/framework_request.yaml'));
        $container->import($this->absPath('config/packages/framework_serializer.yaml'));
        $container->import($this->absPath('config/packages/framework_translator.yaml'));
        $container->import($this->absPath('config/packages/framework_validation.yaml'));
        $container->import($this->absPath('config/packages/framework_test.yaml'));
        $container->import($this->absPath('config/packages/framework_cache.yaml'));
    }

    /**
     * Helper
     */
    private function hideServices(ContainerBuilder $builder): void
    {
//        $builder->addCompilerPass(new HideServiceByTagPass(
//            '',
//            TestBotInterface::TAG,
//        ));
    }

    /**
     * Helper
     *
     * !WHEN CHANGE TopicHandler SECTION!
     * Assign tags to the appropriate topic handlers in the:
     * config/service_hierarchy/services/TestBots.yaml
     */
    private function registerForAutoconfiguration(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ([
                     //###> TopicHandler ###
                     [
                         'interface' => TopicHandlerInterface::class,
                         'tag_name' => TopicHandlerInterface::TOPIC_HANDLER_TAG,
                         'tag_attributes' => []
                     ],
                     [
                         'interface' => ReplyToMessageHandlerInterface::class,
                         'tag_name' => ReplyToMessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => ReplyToMessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => SuccessfulPaymentMessageHandlerInterface::class,
                         'tag_name' => SuccessfulPaymentMessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => SuccessfulPaymentMessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => CommandMessageHandlerInterface::class,
                         'tag_name' => CommandMessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => CommandMessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => LowPriorityCommandMessageHandlerInterface::class,
                         'tag_name' => LowPriorityCommandMessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => LowPriorityCommandMessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => PrivateChatMessageHandlerInterface::class,
                         'tag_name' => PrivateChatMessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => PrivateChatMessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => GroupMessageHandlerInterface::class,
                         'tag_name' => GroupMessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => GroupMessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => MessageHandlerInterface::class,
                         'tag_name' => MessageHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => MessageHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => CallbackQueryHandlerInterface::class,
                         'tag_name' => CallbackQueryHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => CallbackQueryHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => InlineQueryHandlerInterface::class,
                         'tag_name' => InlineQueryHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => InlineQueryHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => PreCheckoutQueryHandlerInterface::class,
                         'tag_name' => PreCheckoutQueryHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => PreCheckoutQueryHandlerInterface::PRIORITY,
                         ]
                     ],
                     [
                         'interface' => ShippingQueryHandlerInterface::class,
                         'tag_name' => ShippingQueryHandlerInterface::TAG,
                         'tag_attributes' => [
                             'priority' => ShippingQueryHandlerInterface::PRIORITY,
                         ]
                     ],
                     //###< TopicHandler ###
                     [
                         'interface' => UpdateHandlerInterface::class,
                         'tag_name' => UpdateHandlerInterface::TAG,
                         'tag_attributes' => []
                     ],
                 ] as ['interface' => $interface, 'tag_name' => $tagName, 'tag_attributes' => $tagAttributes]) {
            $builder->registerForAutoconfiguration($interface)
                ->addTag($tagName, $tagAttributes)//
            ;
        }
    }
}
