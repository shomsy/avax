<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\TTL\Adapters;

use Avax\HTTP\Session\Features\TTL\TTLManagerInterface;
use Avax\HTTP\Session\Storage\SessionStore;

/**
 * InMemoryTTLManager
 *
 * In-memory TTL manager using session storage.
 *
 * @package Avax\HTTP\Session\Features\TTL\Adapters
 */
final class InMemoryTTLManager implements TTLManagerInterface
{
    private const META_PREFIX = '_ttl.';

    /**
     * InMemoryTTLManager Constructor.
     *
     * @param SessionStore $store The session store.
     */
    public function __construct(
        private readonly SessionStore $store
    ) {}

    /**
     * {@inheritdoc}
     */
    public function touch(string $key, int $seconds): void
    {
        $metaKey = self::META_PREFIX . $key;
        $this->store->put($metaKey, time() + $seconds);
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpired(string $key): bool
    {
        $expiration = $this->getExpiration($key);

        if ($expiration === null) {
            return false;
        }

        return time() >= $expiration;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiration(string $key): int|null
    {
        $metaKey = self::META_PREFIX . $key;
        return $this->store->get($metaKey);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(): int
    {
        $cleaned = 0;
        $all = $this->store->all();

        foreach ($all as $key => $value) {
            if (str_starts_with($key, self::META_PREFIX)) {
                continue;
            }

            if ($this->hasExpired($key)) {
                $this->store->delete($key);
                $this->store->delete(self::META_PREFIX . $key);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
