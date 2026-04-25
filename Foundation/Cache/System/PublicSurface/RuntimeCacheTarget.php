<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

final readonly class RuntimeCacheTarget implements CacheReadTarget
{
    public function __construct(
        public string  $key,
        public mixed   $default = null,
        public ?string $store = null,
    ) {}

    public static function key(string $key, mixed $default = null, ?string $store = null) : self
    {
        return new self($key, $default, $store);
    }

    public function kind() : CacheReadKind
    {
        return CacheReadKind::RUNTIME;
    }
}