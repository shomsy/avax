<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Stores;

use Avax\Components\Application\Cache\System\Foundation\CacheStoreInterface;
use Memcached;
use Redis;
use RuntimeException;



final class TaggedCache
{
    private string $tagKey;

    public function __construct(
        private RedisCacheStore $store,
        private array           $tags,
    )
    {
        $this->tagKey = 'tag:' . implode(':', $tags);
    }

    public function get(string $key) : mixed
    {
        return $this->store->get($this->tagKey . ':' . $key);
    }

    public function set(string $key, mixed $value, int $ttl = 0) : bool
    {
        $this->store->getRedis()->sAdd($this->tagKey, $key);

        return $this->store->set($this->tagKey . ':' . $key, $value, $ttl);
    }

    public function flush() : bool
    {
        $keys = $this->store->getRedis()->sMembers($this->tagKey);

        foreach ($keys as $key) {
            $this->store->forget($key);
        }

        return $this->store->getRedis()->del($this->tagKey) > 0;
    }
}