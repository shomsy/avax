<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Foundation;

use Avax\Components\Application\Cache\System\Capabilities\Stores\TaggedCache;

interface CacheStoreInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl = 0): bool;

    public function forget(string $key): bool;

    public function flush(): bool;

    public function remember(string $key, int $ttl, callable $callback): mixed;

    public function has(string $key): bool;

    /**
     * @param array<string> $tags
     */
    public function tags(array $tags): TaggedCache;
}
