<?php

// Enforce strict type safety for the entire Database module
declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Foundation\Connection\ConnectionPool;
use InvalidArgumentException;

/**
 * Class ConnectionPoolFlow
 *
 * -- intent: Fluent DSL builder for creating and configuring database connection pools.
 * -- context: Used by infrastructure bootstrapping to declaratively assemble connection pools.
 * -- guarantees: Always returns a valid ConnectionPool instance with provided configuration.
 *
 * Example:
 * ```php
 * $pool = ConnectionPoolFlow::begin()
 *     ->using(['driver' => 'mysql', 'host' => 'localhost'])
 *     ->pool();
 * ```
 *
 * @package Avax\Database\Connection
 * @since   1.0.0
 */
final class ConnectionPoolFlow
{
    /**
     * -- intent: Hold configuration for the pool being constructed.
     * -- role: Acts as a temporary state container between fluent builder steps.
     *
     * @var array<string,mixed>
     */
    private array $config = [];

    /**
     * -- intent: Prevent direct instantiation to enforce the fluent DSL entry point (begin()).
     */
    private function __construct() {}

    /**
     * -- intent: Entry point for starting a new connection pool DSL flow.
     * -- rationale: Mirrors natural language — “begin → using → pool”.
     *
     * Example:
     * ```php
     * ConnectionPoolFlow::begin();
     * ```
     *
     * @return self
     */
    public static function begin() : self
    {
        // -- intent: Start new configuration flow
        return new self();
    }

    /**
     * -- intent: Attach configuration array for connection pool creation.
     * -- rationale: Allows chaining for declarative style configuration.
     *
     * Example:
     * ```php
     * ->using(['driver' => 'mysql', 'host' => '127.0.0.1'])
     * ```
     *
     * @param array<string,mixed> $config Connection pool configuration.
     *
     * @return self Fluent interface continuation.
     */
    public function using(array $config) : self
    {
        // -- SECURITY: Defensive copy (never mutate external config reference)
        $this->config = $config;

        return $this;
    }

    /**
     * -- intent: Finalize DSL flow and build the ConnectionPool instance.
     * -- guarantees: Returns a ready-to-use pool with all connections managed.
     *
     * Example:
     * ```php
     * $pool = ConnectionPoolFlow::begin()->using($config)->pool();
     * ```
     *
     * @return ConnectionPool Ready connection pool instance.
     * @throws InvalidArgumentException If configuration is invalid.
     */
    public function pool() : ConnectionPool
    {
        // -- intent: Construct new ConnectionPool using provided configuration
        return new ConnectionPool(config: $this->config);
    }
}
