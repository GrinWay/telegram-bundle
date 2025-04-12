<?php

namespace GrinWay\Telegram\Form\DataTransformer;

use GrinWay\Telegram\Service\Telegram;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use function Symfony\Component\String\u;

/**
 * https://core.telegram.org/bots/api#chatfullinfo
 */
class TgAccountUserNameIdDataTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly Telegram               $telegram,
        private readonly SluggerInterface       $slugger,
        private readonly TagAwareCacheInterface $grinwayTelegramFormDataTransformerCachePool,
    )
    {
    }

    /**
     * to form (id -> username)
     */
    public function transform(mixed $value): mixed
    {
        if (empty($value)) {
            return $value;
        }

        $username = null;
        if (\is_scalar($value)) {
            $valueString = (string)$value;
            $value = $this->getNormalizedUsername($valueString);

            if (!\str_contains($value, '@')) {
                $cacheKey = $this->slugger->slug($valueString);
                $username = $this->grinwayTelegramFormDataTransformerCachePool->get($cacheKey, function () use ($value) {
                    $chatInfo = $this->telegram->getChat(
                        chatId: $value,
                        throw: false,
                    );
                    return $chatInfo['result']['username'] ?? null;
                });
                if (empty($username)) {
                    $this->grinwayTelegramFormDataTransformerCachePool->delete($cacheKey);
                }
            }
        }

        return $username ?: $value;
    }

    /**
     * to model (username -> id)
     */
    public function reverseTransform(mixed $value): mixed
    {
        if (empty($value)) {
            return $value;
        }

        $id = null;
        if (\is_scalar($value)) {
            $valueString = (string)$value;
            $value = $this->getNormalizedUsername($valueString);

            if (\str_contains($value, '@')) {
                $cacheKey = $this->slugger->slug($valueString);
                $id = $this->grinwayTelegramFormDataTransformerCachePool->get($cacheKey, function () use ($value) {
                    $chatInfo = $this->telegram->getChat(
                        chatId: $value,
                        throw: false,
                    );
                    return $chatInfo['result']['id'] ?? null;
                });
                if (empty($id)) {
                    $this->grinwayTelegramFormDataTransformerCachePool->delete($cacheKey);
                }
            }
        }

        return $id ?: $value;
    }

    private function getNormalizedUsername(string $value): string
    {
        if (u($value)->match('~[a-z\p{Cyrillic}]~iu')[0] ?? false) {
            $value = (string)u($value)->ensureStart('@');
        }
        return $value;
    }
}
