<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Exceptions;

use Avax\Database\Exceptions\DatabaseException;

/**
 * Triggered when the query builder receives malformed or dangerous criteria.
 *
 * -- intent: prevent SQL injection and logical errors at the DSL level.
 */
final class InvalidCriteriaException extends DatabaseException
{
    /**
     * Constructor capturing the problematic method and reason.
     *
     * -- intent: provide specific feedback on which builder method was misused.
     *
     * @param  string  $method  Technical name of the builder method
     * @param  string  $reason  Human-readable explanation of why the input is invalid
     */
    public function __construct(
        private readonly string $method,
        string $reason
    ) {
        parent::__construct(message: "Invalid criteria in [{$method}]: {$reason}");
    }

    /**
     * Retrieve the name of the method where the error originated.
     *
     * -- intent: pinpoint the logical source of the usage error.
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
