<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Override;

final readonly class WriteThroughInvalidation implements InvalidationStrategy
{
    /**
     * @param array<string, mixed> $context
     */
    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if ($reason === 'source_updated') {
            return true;
        }

        return ($context['operation'] ?? null) === 'write';
    }

    #[Override]
    public function strategyName() : string
    {
        return 'write_through';
    }
}
