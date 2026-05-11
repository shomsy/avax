<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Flows\ResolveFailurePolicy;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CompileFailurePolicies\CompileFailurePolicies;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\ResolveFailurePolicy\ResolveFailurePolicy as ResolveCompiledPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;

/**
 * ResolveFailurePolicy — Resolves failure policy for a target, compiling if not cached.
 *
 * This flow combines cache lookup with on-demand compilation.
 */
final readonly class ResolveFailurePolicy
{
    public function __construct(
        private ResolveCompiledPolicy $resolveCompiled,
        private CompileFailurePolicies $compiler,
    ) {
    }

    public function for(FailureContext $context): FailurePolicy
    {
        $targetKey = $context->targetKey();

        if ($targetKey === '') {
            return new FailurePolicy();
        }

        // Try cache first (fast path — no reflection)
        $cached = CompiledPolicyCache::get($targetKey);
        if ($cached !== null) {
            return $cached->policy;
        }

        // Compile on demand (reflection path — only once per method)
        $compiled = $this->compiler->compile($context->targetClass, $context->targetMethod);

        if ($compiled !== null) {
            CompiledPolicyCache::put($targetKey, $compiled);
            return $compiled->policy;
        }

        return new FailurePolicy();
    }
}
