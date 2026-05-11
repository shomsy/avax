<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\InvalidateCompiledPolicy;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;

/**
 * InvalidateCompiledPolicy — Invalidates stale compiled policies from the cache.
 */
final readonly class InvalidateCompiledPolicy
{
    public function for(string $targetClass, string $targetMethod): void
    {
        $key = $targetClass . '::' . $targetMethod;
        CompiledPolicyCache::invalidate($key);
    }

    public function all(): void
    {
        CompiledPolicyCache::clear();
    }
}
