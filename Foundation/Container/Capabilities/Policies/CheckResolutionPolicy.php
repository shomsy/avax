<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Policies;

use Avax\Container\Capabilities\Policies\Decisions\ResolutionAllowed;
use Avax\Container\Capabilities\Policies\Decisions\ResolutionBlocked;

/**
 * Small policy checker used by the resolution pipeline.
 */
final readonly class CheckResolutionPolicy
{
    public function __construct(
        private ResolutionPolicy $policy
    ) {}

    public function check(string $abstract) : ResolutionAllowed|ResolutionBlocked
    {
        if (! $this->policy->isAllowed(abstract: $abstract)) {
            return new ResolutionBlocked(
                message: "Autowiring blocked for [{$abstract}] by strict security policy.",
                code   : 'policy.blocked'
            );
        }

        return new ResolutionAllowed(message: 'Resolution allowed.');
    }
}
