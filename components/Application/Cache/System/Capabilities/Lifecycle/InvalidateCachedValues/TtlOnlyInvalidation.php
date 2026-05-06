<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Override;

final readonly class TtlOnlyInvalidation implements InvalidationStrategy
{
    /**
     * @param  array<string, mixed>  $context
     */
    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []): bool
    {
        return $reason === 'ttl_expired';
    }

    #[Override]
    public function strategyName(): string
    {
        return 'ttl_only';
    }
}
