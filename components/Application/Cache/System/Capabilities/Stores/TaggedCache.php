<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Stores;

use Avax\Components\Application\Cache\System\Foundation\CacheStoreInterface;

final class TaggedCache
{
    private string $tagKey;

    /** @var array<string, true> */
    private array $trackedKeys = [];

    /**
     * @param array<string> $tags
     */
    public function __construct(
        private CacheStoreInterface $store,
        array                       $tags,
    )
    {
        sort($tags);
        $this->tagKey = 'tag:' . implode(':', $tags);
    }

    public function get(string $key) : mixed
    {
        return $this->store->get($this->scopedKey(key: $key));
    }

    public function set(string $key, mixed $value, int $ttl = 0) : bool
    {
        $this->trackedKeys[$key] = true;

        if ($this->store instanceof RedisCacheStore) {
            $this->store->getRedis()->sAdd($this->tagKey, $key);
        }

        return $this->store->set($this->scopedKey(key: $key), $value, $ttl);
    }

    private function scopedKey(string $key) : string
    {
        return $this->tagKey . ':' . $key;
    }

    public function flush() : bool
    {
        $keys = array_keys($this->trackedKeys);

        if ($this->store instanceof RedisCacheStore) {
            $redisKeys = $this->store->getRedis()->sMembers($this->tagKey);

            if (is_array($redisKeys)) {
                $keys = array_values(array_unique(array_merge($keys, array_map('strval', $redisKeys))));
            }
        }

        foreach ($keys as $key) {
            $this->store->forget($this->scopedKey(key: $key));
        }

        $this->trackedKeys = [];

        if ($this->store instanceof RedisCacheStore) {
            $deleted = $this->store->getRedis()->del($this->tagKey);

            return is_int($deleted) && $deleted > 0;
        }

        return true;
    }
}
