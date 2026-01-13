<?php

declare(strict_types=1);

namespace Avax\Database\Avax\Connection\Pool;

/**
 * Fluent builder for constructing database connection pools.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/Concepts/Connections.md
 */
final class ConnectionPoolFlow
{
    /** @var array<string, mixed> The settings we are carrying through the assembly line. */
    private array $config = [];

    /**
     * Private constructor — use `ConnectionPoolFlow::begin()` to start the wizard.
     */
    private function __construct() {}

    /**
     * Start the setup wizard for a new connection pool.
     */
    public static function begin() : self
    {
        return new self;
    }

    /**
     * Provide configuration settings for the pool.
     */
    public function using(array $config) : self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Finish the wizard and return the constructed pool.
     */
    public function pool() : ConnectionPool
    {
        return new ConnectionPool(config: $this->config);
    }
}
