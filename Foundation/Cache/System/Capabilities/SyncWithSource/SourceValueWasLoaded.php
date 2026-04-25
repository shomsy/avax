<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

final readonly class SourceValueWasLoaded
{
    public function __construct(
        public CacheSourceKey $key,
        public mixed          $value
    ) {}

    public function hasValue() : bool
    {
        return $this->value !== null;
    }
}