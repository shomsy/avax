<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition;

/**
 * Policy enumeration for handling duplicate route registration.
 */
enum DuplicatePolicy
{
    case THROW;
    case REPLACE;
    case IGNORE;

    public function describe() : string
    {
        return match ($this) {
            self::THROW   => 'Throw exception on duplicate routes',
            self::REPLACE => 'Replace existing route with new one',
            self::IGNORE  => 'Keep first route, ignore duplicates',
        };
    }
}
