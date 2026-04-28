<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

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