<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use RuntimeException;

/**
 * PersistentDataFailure - reports unsafe or unsupported persistent data access.
 */
final class PersistentDataFailure extends RuntimeException
{
    public static function missingNamedParameters() : self
    {
        return new self('Raw data queries must use at least one named parameter so dynamic values do not get embedded in SQL text.');
    }

    public static function positionalParametersAreForbidden() : self
    {
        return new self('Raw data queries must use named parameters. Positional ? parameters are not accepted at this boundary.');
    }

    public static function missingParameter(string $name) : self
    {
        return new self("Raw data query is missing a bound value for named parameter :{$name}.");
    }

    public static function unsupportedRuntime() : self
    {
        return new self('The configured database runtime does not expose executeRawDataQuery() or transactions()->run(); add an adapter before using this DataLayer boundary.');
    }
}
