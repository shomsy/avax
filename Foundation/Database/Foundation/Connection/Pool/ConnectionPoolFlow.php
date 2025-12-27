<?php

declare(strict_types=1);

namespace Avax\Database\Foundation\Connection\Pool;

/**
 * Fluent builder for constructing database connection pools.
 *
 * @see docs/Concepts/Connections.md
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
     *
     * @return self
     */
    public static function begin(): self
    {
        return new self();
    }

    /**
     * Provide configuration settings for the pool.
     *
     * @param array $config
     * @return self
     */
    public function using(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Finish the wizard and return the constructed pool.
     *
     * @return ConnectionPool
     */
    public function pool(): ConnectionPool
    {
        return new ConnectionPool(config: $this->config);
    }
}
