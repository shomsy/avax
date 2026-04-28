<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface\Read;

final readonly class RuntimeCacheTarget implements CacheReadTarget
{
    public function __construct(
        public string      $key,
        public mixed       $default = null,
        public string|null $store = null,
    ) {}

    public static function key(string $key, mixed $default = null, string|null $store = null) : self
    {
        return new self(key: $key, default: $default, store: $store);
    }

    public function kind() : CacheReadKind
    {
        return CacheReadKind::RUNTIME;
    }
}