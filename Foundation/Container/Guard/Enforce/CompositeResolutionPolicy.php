<?php

declare(strict_types=1);

namespace Avax\Container\Guard\Enforce;

/**
 * Composite resolution policy that combines multiple policies.
 *
 * This is an "all-of" composite: resolution is allowed only when every configured sub-policy allows it.
 *
 * @see docs_md/Guard/Enforce/CompositeResolutionPolicy.md#quick-summary
 */
final readonly class CompositeResolutionPolicy implements ResolutionPolicy
{
    /** @var ResolutionPolicy[] */
    private array $policies;

    /**
     * @param array $policies A list of policies; non-ResolutionPolicy values are ignored
     * @see docs_md/Guard/Enforce/CompositeResolutionPolicy.md#method-__construct
     */
    public function __construct(array $policies)
    {
        $this->policies = array_values(array_filter($policies, fn($p) => $p instanceof ResolutionPolicy));
    }

    /**
     * Checks whether resolution is allowed by all sub-policies.
     *
     * @param string $abstract The abstract/service identifier being resolved
     *
     * @return bool True when all policies allow the abstract; otherwise false
     * @see docs_md/Guard/Enforce/CompositeResolutionPolicy.md#method-isallowed
     */
    public function isAllowed(string $abstract): bool
    {
        foreach ($this->policies as $policy) {
            if (!$policy->isAllowed(abstract: $abstract)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convenience factory for composing policies.
     *
     * @param ResolutionPolicy ...$policies Policies to combine
     *
     * @return self
     * @see docs_md/Guard/Enforce/CompositeResolutionPolicy.md#method-with
     */
    public static function with(ResolutionPolicy ...$policies): self
    {
        return new self($policies);
    }
}
