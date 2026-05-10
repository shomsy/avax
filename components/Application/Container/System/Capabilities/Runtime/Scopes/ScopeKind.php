<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes;

/**
 * Canonical scope kind names used by runtime lifetimes.
 *
 * Converted from class constants to a PHP 8.1 backed enum
 * for type safety and better IDE support.
 */
enum ScopeKind: string
{
    case Any = 'scoped';
    case Operation = 'operation';
    case Request = 'request';
    case Job = 'job';
    case Tenant = 'tenant';

    public static function rank(self $kind): int
    {
        return match ($kind) {
            self::Operation => 1,
            self::Request => 2,
            self::Job => 3,
            self::Tenant => 4,
            default => 0,
        };
    }

    public static function normalize(string $kind): self
    {
        return match (trim(string: $kind)) {
            self::Any->value,
            self::Operation->value,
            self::Request->value,
            self::Job->value,
            self::Tenant->value => self::from(trim(string: $kind)),
            default => self::Operation,
        };
    }
}
