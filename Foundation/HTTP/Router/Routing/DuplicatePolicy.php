<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing;

/**
 * Policy enumeration for handling duplicate route registration.
 *
 * Defines the available strategies for dealing with route conflicts
 * when multiple routes attempt to register with the same key.
 */
enum DuplicatePolicy
{
    /**
     * Throw an exception when duplicate routes are detected.
     *
     * This is the strictest policy, ensuring no accidental route shadowing.
     * Recommended for production environments.
     */
    case THROW;

    /**
     * Replace the existing route with the new one silently.
     *
     * Allows route overriding without warnings. Useful for testing
     * or dynamic route modification scenarios.
     */
    case REPLACE;

    /**
     * Ignore duplicate routes silently.
     *
     * Keeps the first registered route and discards subsequent duplicates.
     * Useful for scenarios where route precedence is established by registration order.
     */
    case IGNORE;

    /**
     * Get human-readable description of this policy.
     */
    public function describe() : string
    {
        return match ($this) {
            self::THROW => 'Throw exception on duplicate routes',
            self::REPLACE => 'Replace existing route with new one',
            self::IGNORE => 'Keep first route, ignore duplicates',
        };
    }
}