<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ResolveFailurePolicy;

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;

/**
 * ResolveFailurePolicy — Resolves the compiled failure policy for a given context.
 */
final readonly class ResolveFailurePolicy
{
    public function for(FailureContext $context): FailurePolicy
    {
        $targetKey = $context->targetKey();

        if ($targetKey === '') {
            return new FailurePolicy();
        }

        $compiled = CompiledPolicyCache::get($targetKey);

        if ($compiled !== null) {
            return $compiled->policy;
        }

        return new FailurePolicy();
    }
}
