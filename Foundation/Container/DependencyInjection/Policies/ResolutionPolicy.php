<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Policies;

/**
 * Resolution policy for the container runtime.
 */
final readonly class ResolutionPolicy
{
    public function __construct(
        public bool $strict = false,
        public bool $debug = false
    ) {}

    public function isAllowed(string $abstract) : bool
    {
        if (! $this->strict) {
            return true;
        }

        return class_exists($abstract) || interface_exists($abstract);
    }
}
