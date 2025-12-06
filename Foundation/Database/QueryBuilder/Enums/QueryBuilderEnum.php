<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Enums;

/**
 * Enumeration of query builder types and supported database drivers.
 *
 * Technical Description:
 * - Defines constants for different types of query operations.
 * - Includes supported database drivers.
 * - Provides utility methods for validation and retrieval of all available types.
 *
 * Business Description:
 * - Ensures consistency in query type definitions across the application.
 * - Prevents errors by validating query types and database drivers.
 */
enum QueryBuilderEnum: string
{
    /** Query Types */
    case QUERY_TYPE_SELECT         = 'SELECT';

    case QUERY_TYPE_INSERT         = 'INSERT';

    case QUERY_TYPE_UPDATE         = 'UPDATE';

    case QUERY_TYPE_DELETE         = 'DELETE';

    case QUERY_TYPE_UPSERT         = 'UPSERT';

    case QUERY_TYPE_SOFT_DELETE    = 'SOFT DELETE';

    case QUERY_TYPE_RESTORE        = 'RESTORE';

    case QUERY_TYPE_TRUNCATE       = 'TRUNCATE';

    case QUERY_TYPE_CASCADE_DELETE = 'CASCADE DELETE';

    case QUERY_TYPE_DELETE_JOIN    = 'DELETE JOIN';

    /** Database Drivers */
    case DRIVER_MYSQL  = 'mysql';

    case DRIVER_PGSQL  = 'pgsql';

    case DRIVER_SQLITE = 'sqlite';

    case DRIVER_MSSQL  = 'sqlsrv';

    case DRIVER_ORACLE = 'oci';

    /**
     * Checks if the provided string is a valid query type.
     *
     * @param string $queryType The query type to validate.
     *
     * @return bool Returns true if the provided query type is valid; otherwise, false.
     */
    public static function isValidQueryType(string $queryType) : bool
    {
        return in_array(strtoupper($queryType), self::queryTypes(), true);
    }

    /**
     * Returns all possible query types as an array of strings.
     *
     * @return array An array containing all query types as strings.
     */
    public static function queryTypes() : array
    {
        return [
            self::QUERY_TYPE_SELECT->value,
            self::QUERY_TYPE_INSERT->value,
            self::QUERY_TYPE_UPDATE->value,
            self::QUERY_TYPE_DELETE->value,
            self::QUERY_TYPE_UPSERT->value,
            self::QUERY_TYPE_SOFT_DELETE->value,
            self::QUERY_TYPE_RESTORE->value,
            self::QUERY_TYPE_TRUNCATE->value,
            self::QUERY_TYPE_CASCADE_DELETE->value,
            self::QUERY_TYPE_DELETE_JOIN->value,
        ];
    }

    /**
     * Checks if the provided string is a valid database driver.
     *
     * @param string $driver The database driver to validate.
     *
     * @return bool Returns true if the provided driver is valid; otherwise, false.
     */
    public static function isValidDriver(string $driver) : bool
    {
        return in_array(strtolower($driver), self::drivers(), true);
    }

    /**
     * Returns all possible database drivers as an array of strings.
     *
     * @return array An array containing all supported database drivers.
     */
    public static function drivers() : array
    {
        return [
            self::DRIVER_MYSQL->value,
            self::DRIVER_PGSQL->value,
            self::DRIVER_SQLITE->value,
            self::DRIVER_MSSQL->value,
            self::DRIVER_ORACLE->value,
        ];
    }
}
