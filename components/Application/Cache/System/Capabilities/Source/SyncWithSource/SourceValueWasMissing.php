<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

final readonly class SourceValueWasMissing
{
    public function __construct(
        public CacheSourceKey $cacheSourceKey,
    ) {
    }
}
