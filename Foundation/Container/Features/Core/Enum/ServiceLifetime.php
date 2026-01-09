<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Enum;

/**
 * @package Avax\Container\Core\Enum
 *
 * Defines the lifecycle duration and persistence policy of a service instance.
 *
 * ServiceLifetime determines how the container manages object instances after they
 * are created. It governs whether an instance is reused, cached for a specific
 * duration, or discarded immediately.
 *
 * WHY IT EXISTS:
 * - To provide a type-safe way to configure service persistence.
 * - To distinguish between shared state (Singletons) and ephemeral logic (Transients).
 * - To support unit-of-work patterns through Scoped lifetimes.
 *
 * WHEN TO USE:
 * - Use Singleton for stateless services or global configurations.
 * - Use Scoped for services that maintain state relative to a single operation/request.
 * - Use Transient for lightweight, stateless objects or when fresh state is required.
 */
enum ServiceLifetime : string
{
    /** One instance shared across the entire application lifecycle within the container. */
    case Singleton = 'singleton';

    /** One instance per defined scope; reused within the scope but destroyed when the scope ends. */
    case Scoped = 'scoped';

    /** A new instance created every time the service is requested; no persistence. */
    case Transient = 'transient';
}