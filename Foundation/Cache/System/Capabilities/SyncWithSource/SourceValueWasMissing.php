<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

final readonly class SourceValueWasMissing
{
    public function __construct(
        public CacheSourceKey $key
    ) {}
}