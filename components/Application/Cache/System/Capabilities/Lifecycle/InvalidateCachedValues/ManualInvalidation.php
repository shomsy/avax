<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Override;

final readonly class ManualInvalidation implements InvalidationStrategy
{
    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        return $reason === 'explicit';
    }

    #[Override]
    public function strategyName() : string
    {
        return 'manual';
    }
}
