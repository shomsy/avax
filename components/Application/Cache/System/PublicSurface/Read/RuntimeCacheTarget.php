<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Read;

use Override;

readonly class RuntimeCacheTarget implements CacheReadTarget
{
    public function __construct(
        public string $key,
        public mixed $default = null,
        public ?string $store = null,
    ) {
    }

    public static function key(string $key, mixed $default = null, ?string $store = null): self
    {
        return new self(key: $key, default: $default, store: $store);
    }

    #[Override]
    public function kind(): CacheReadKind
    {
        return CacheReadKind::RUNTIME;
    }
}
