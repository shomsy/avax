<?php

declare(strict_types=1);

namespace Avax\Container\Features\Actions\Inject\Resolvers;

/**
 * Property resolution outcome for injection.
 *
 * @see docs_md/Features/Actions/Inject/Resolvers/PropertyResolution.md#quick-summary
 */
final readonly class PropertyResolution
{
    private function __construct(
        public bool  $resolved,
        public mixed $value,
    ) {}

    /**
     * Creates a successful resolution result.
     *
     * @param mixed $value Value to inject
     * @return self
     * @see docs_md/Features/Actions/Inject/Resolvers/PropertyResolution.md#method-resolved
     */
    public static function resolved(mixed $value) : self
    {
        return new self(resolved: true, value: $value);
    }

    /**
     * Creates an unresolved resolution result.
     *
     * @return self
     * @see docs_md/Features/Actions/Inject/Resolvers/PropertyResolution.md#method-unresolved
     */
    public static function unresolved() : self
    {
        return new self(resolved: false, value: null);
    }
}
