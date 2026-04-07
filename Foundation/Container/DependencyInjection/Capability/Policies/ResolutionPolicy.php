<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Policies;

/**
 * Contract for resolution security policies.
 *
 * A resolution policy decides whether the container is allowed to resolve a given abstract/service identifier.
 * Keep implementations small and focused so policies remain composable and easy to test.
 *
 */
interface ResolutionPolicy
{
    /**
     * Determines whether resolution is allowed for the given abstract.
     *
     * @param string $abstract The service identifier or contract name being resolved
     *
     * @return bool True when resolution is allowed; otherwise false
     *
     */
    public function isAllowed(string $abstract) : bool;
}
