<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationStrategies;

final readonly class WriteThroughInvalidation implements InvalidationStrategy
{
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if ($reason === 'source_updated') {
            return true;
        }

        $isWriteOperation = $context['operation'] ?? null === 'write';

        return $isWriteOperation;
    }

    public function strategyName() : string
    {
        return 'write_through';
    }
}