<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Override;
use Throwable;

/**
 * Specialized exception for failures occurring during SQL compilation or execution.
 *
 * -- intent: provide diagnostic context including the failing SQL and its parameter bindings.
 */
class QueryException extends DatabaseException
{
    /**
     * Constructor promoting diagnostic properties via PHP 8.3 features.
     *
     * -- intent: capture the full state of the failure for debugging and logging.
     *
     * @param string         $message  Technical failure description
     * @param string         $sql      The dialect-specific SQL string that failed
     * @param array          $bindings Secure parameter values used in the query
     * @param Throwable|null $previous The underlying driver exception
     */
    #[Override]
    public function __construct(
        string                  $message,
        private readonly string $sql,
        private readonly array  $bindings = [],
        Throwable|null          $previous = null
    )
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }

    /**
     * Retrieve the failing SQL statement.
     *
     * -- intent: expose the problematic query for technical analysis.
     *
     * @return string
     */
    public function getSql() : string
    {
        return $this->sql;
    }

    /**
     * Retrieve the parameter bindings used with the failing statement.
     *
     * -- intent: expose the provided data values for debugging.
     *
     * @return array
     */
    public function getBindings() : array
    {
        return $this->bindings;
    }
}


