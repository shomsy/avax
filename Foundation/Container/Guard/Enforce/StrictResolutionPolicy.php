<?php

declare(strict_types=1);
namespace Avax\Container\Guard\Enforce;

use Avax\Container\Guard\Rules\ContainerPolicy;

/**
 * Default implementation of ResolutionPolicy that honors ContainerPolicy settings.
 *
 * @see docs_md/Guard/Enforce/StrictResolutionPolicy.md#quick-summary
 */
readonly class StrictResolutionPolicy implements ResolutionPolicy
{
    /**
     * @param ContainerPolicy $policy Guard policy settings used to enforce strictness
     * @see docs_md/Guard/Enforce/StrictResolutionPolicy.md#method-__construct
     */
    public function __construct(
        private ContainerPolicy $policy
    ) {}

    /**
     * @param string $abstract The requested abstract identifier (often a class name)
     *
     * @return bool True when allowed; otherwise false
     * @see docs_md/Guard/Enforce/StrictResolutionPolicy.md#method-isallowed
     */
    public function isAllowed(string $abstract) : bool
    {
        if ($this->policy->strict && ! class_exists($abstract)) {
            return false;
        }

        return true;
    }
}
