<?php

declare(strict_types=1);
namespace Avax\Container\Guard\Enforce;

/**
 * Contract for resolution security policies.
 *
 * A resolution policy decides whether the container is allowed to resolve a given abstract/service identifier.
 * Keep implementations small and focused so policies remain composable and easy to test.
 *
 * @see docs_md/Guard/Enforce/ResolutionPolicy.md#quick-summary
 */
interface ResolutionPolicy
{
    /**
     * Determines whether resolution is allowed for the given abstract.
     *
     * @param string $abstract The service identifier or contract name being resolved
     *
     * @return bool True when resolution is allowed; otherwise false
     * @see docs_md/Guard/Enforce/ResolutionPolicy.md#method-isallowed
     */
    public function isAllowed(string $abstract) : bool;
}
