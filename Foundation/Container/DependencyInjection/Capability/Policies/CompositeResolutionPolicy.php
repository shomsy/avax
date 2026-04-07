<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Policies;

/**
 * Composite resolution policy that combines multiple policies.
 *
 * This is an "all-of" composite: resolution is allowed only when every configured sub-policy allows it.
 *
 */
final readonly class CompositeResolutionPolicy implements ResolutionPolicy
{
    /** @var ResolutionPolicy[] */
    private array $policies;

    /**
     * @param array $policies A list of policies; non-ResolutionPolicy values are ignored
     *
     */
    public function __construct(array $policies)
    {
        $this->policies = array_values(array_filter($policies, static fn($p) => $p instanceof ResolutionPolicy));
    }

    /**
     * Convenience factory for composing policies.
     *
     * @param ResolutionPolicy ...$policies Policies to combine
     *
     */
    public static function with(ResolutionPolicy ...$policies) : self
    {
        return new self(policies: $policies);
    }

    /**
     * Checks whether resolution is allowed by all sub-policies.
     *
     * @param string $abstract The abstract/service identifier being resolved
     *
     * @return bool True when all policies allow the abstract; otherwise false
     *
     */
    public function isAllowed(string $abstract) : bool
    {
        foreach ($this->policies as $policy) {
            if (! $policy->isAllowed(abstract: $abstract)) {
                return false;
            }
        }

        return true;
    }
}
