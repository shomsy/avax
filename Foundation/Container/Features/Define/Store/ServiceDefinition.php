<?php

declare(strict_types=1);

namespace Avax\Container\Features\Define\Store;

use Avax\Container\Features\Core\Enum\ServiceLifetime;

/**
 * Service Definition Data Transfer Object (DTO).
 *
 * Immutable blueprint that stores how a specific service should be resolved,
 * its lifetime (singleton, transient, scoped), and any associated metadata including
 * tags and constructor argument overrides.
 *
 * @see     docs/Features/Define/Store/ServiceDefinition.md
 */
class ServiceDefinition
{
    /** @var mixed The concrete implementation, factory closure, or specific instance. */
    public mixed $concrete = null;

    /** @var ServiceLifetime The persistence policy (Singleton, Scoped, Transient). */
    public ServiceLifetime $lifetime = ServiceLifetime::Transient;

    /** @var array<int, string> List of categories/tags associated with this service. */
    public array $tags = [];

    /** @var array<string, mixed> Associative array of constructor arguments to override. */
    public array $arguments = [];

    /**
     * Initializes a new definition for the given abstract.
     *
     * @param string $abstract The unique identifier (class or alias) for the service.
     *
     * @see docs/Features/Define/Store/ServiceDefinition.md#method-__construct
     */
    public function __construct(
        public readonly string $abstract
    ) {}

    /**
     * Supports PHP's var_export for native evaluation during cache loading.
     *
     * @param array $array Raw data array.
     *
     * @return self Hydrated instance.
     *
     * @see docs/Features/Define/Store/ServiceDefinition.md#method-__set_state
     */
    public static function __set_state(array $array) : self
    {
        $definition            = new self(abstract: $array['abstract']);
        $definition->concrete  = $array['concrete'] ?? null;
        $definition->lifetime  = $array['lifetime'] ?? ServiceLifetime::Transient;
        $definition->tags      = $array['tags'] ?? [];
        $definition->arguments = $array['arguments'] ?? [];

        return $definition;
    }
}
