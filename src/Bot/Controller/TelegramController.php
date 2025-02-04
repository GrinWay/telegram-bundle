<?php

namespace GrinWay\Telegram\Bot\Controller;

use GrinWay\Telegram\Bot\Contract\Update\UpdateHandlerInterface;
use GrinWay\Telegram\GrinWayTelegramBundle;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * https://core.telegram.org/bots/api
 */
class TelegramController
{
    public const WEBHOOK_PATH = '/grinway/telegram/bot/webhook';
    public const TAG = GrinWayTelegramBundle::BUNDLE_PREFIX . 'bot.controller';

    public function __construct(
        private readonly ServiceLocator $serviceLocator,
        private readonly ServiceLocator $updateHandlersServiceLocator,
    )
    {
    }

    public function __invoke(): Response
    {
        $request = $this->serviceLocator->get('requestStack')->getCurrentRequest();
        if (null === $request) {
            $payload = [];
        } else {
            $payload = $request->getPayload()->all();
        }

        foreach ($payload as $updateField => $value) {
            if ($this->updateHandlersServiceLocator->has($updateField)) {
                /** @var UpdateHandlerInterface $updateHandler */
                $updateHandler = $this->updateHandlersServiceLocator->get($updateField);

                if ($updateHandler->supports($value)) {
                    $updateHandler->handle($value);
                }
            }
        }

        return new JsonResponse(null, 200);
    }
}
