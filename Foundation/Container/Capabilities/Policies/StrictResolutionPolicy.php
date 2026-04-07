<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Policies;

use Avax\Container\Capabilities\Policies\ContainerPolicy;

/**
 * Default implementation of ResolutionPolicy that honors ContainerPolicy settings.
 *
 */
readonly class StrictResolutionPolicy implements ResolutionPolicy
{
    /**
     * @param ContainerPolicy $policy Guard policy settings used to enforce strictness
     *
     */
    public function __construct(
        private ContainerPolicy $policy
    ) {}

    /**
     * @param string $abstract The requested abstract identifier (often a class name)
     *
     * @return bool True when allowed; otherwise false
     *
     */
    public function isAllowed(string $abstract) : bool
    {
        if ($this->policy->strict && ! class_exists($abstract)) {
            return false;
        }

        return true;
    }
}
