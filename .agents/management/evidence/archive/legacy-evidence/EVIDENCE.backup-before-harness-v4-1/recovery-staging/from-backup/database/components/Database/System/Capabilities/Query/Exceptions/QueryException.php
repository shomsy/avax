<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\Exceptions;

use components\Database\System\Foundation\Exceptions\DatabaseException;
use SensitiveParameter;
use Throwable;

/**
 * Specialized exception for failures occurring during SQL compilation or execution.
 *
 * -- intent: provide diagnostic context including the failing SQL and its parameter bindings.
 */
class QueryException extends DatabaseException
{
    /**
     * @var array Redacted bindings safe for diagnostics
     */
    private readonly array $redactedBindings;

    private readonly array $rawBindings;

    private readonly string $sql;

    /**
     * Constructor promoting diagnostic properties via PHP 8.3 features.
     *
     * -- intent: capture the full state of the failure for debugging and logging.
     *
     * @param string         $message  Technical failure description
     * @param string         $sql      The dialect-specific SQL string that failed
     * @param Throwable|null $previous The underlying driver exception
     */
    public function __construct(
        string                      $message,
        string                      $sql,
        #[SensitiveParameter] array $rawBindings = [],
        ?Throwable                  $previous = null
    )
    {
        $this->sql              = $sql;
        $this->rawBindings      = $rawBindings;
        $this->redactedBindings = $this->redactBindings(bindings: $this->rawBindings);
        parent::__construct(message: $message, code: 0, previous: $previous);
    }

    /**
     * Redact sensitive values from binding payloads.
     */
    private function redactBindings(array $bindings) : array
    {
        return array_map(callback: static fn ($value) => '[REDACTED]', array: $bindings);
    }

    /**
     * Retrieve the failing SQL statement.
     *
     * -- intent: expose the problematic query for technical analysis.
     */
    public function getSql() : string
    {
        return $this->sql;
    }

    /**
     * Retrieve the parameter bindings used with the failing statement.
     *
     * -- intent: expose the provided data values for debugging.
     */
    public function getBindings(bool $redacted = true) : array
    {
        return $redacted ? $this->redactedBindings : $this->rawBindings;
    }
}
