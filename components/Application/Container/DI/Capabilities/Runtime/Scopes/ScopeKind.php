<?php

declare(strict_types=1);

namespace Avax\Components\Container\DI\Capabilities\Runtime\Scopes;

/**
 * Canonical scope kind names used by runtime lifetimes.
 */
final class ScopeKind
{
    public const string ANY = 'scoped';

    public const string OPERATION = 'operation';

    public const string REQUEST = 'request';

    public const string JOB = 'job';

    public const string TENANT = 'tenant';

    public static function rank(string $kind) : int
    {
        return match (self::normalize(kind: $kind)) {
            self::OPERATION => 1,
            self::REQUEST   => 2,
            self::JOB       => 3,
            self::TENANT    => 4,
            default         => 0,
        };
    }

    public static function normalize(string $kind) : string
    {
        return match (trim(string: $kind)) {
            self::ANY,
            self::OPERATION,
            self::REQUEST,
            self::JOB,
            self::TENANT => trim(string: $kind),
            default      => self::OPERATION,
        };
    }
}
