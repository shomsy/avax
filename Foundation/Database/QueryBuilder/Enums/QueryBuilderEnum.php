<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Enums;

/**
 * Technical base class for all enumerations used within the QueryBuilder domain.
 *
 * -- intent: centralize common enum-specific utility methods.
 */
abstract class QueryBuilderEnum
{
    /**
     * Retrieve all defined values for the enumeration.
     *
     * -- intent: provide a programmatic way to list all valid enum cases.
     */
    abstract public static function values(): array;
}
