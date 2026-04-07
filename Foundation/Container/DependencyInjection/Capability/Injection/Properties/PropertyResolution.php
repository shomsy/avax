<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Capability\Injection\Properties;

/**
 * Property resolution outcome for injection.
 *
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
     *
     */
    public static function resolved(mixed $value) : self
    {
        return new self(resolved: true, value: $value);
    }

    /**
     * Creates an unresolved resolution result.
     *
     */
    public static function unresolved() : self
    {
        return new self(resolved: false, value: null);
    }
}
