<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Events\ConnectionFailed;
use Avax\Database\Events\ConnectionOpened;
use Avax\Database\Events\EventBus;
use Throwable;

/**
 * DSL for establishing direct (non-pooled) database connections.
 */
final class DirectConnectionFlow
{
    /**
     * Database connection configuration parameters.
     */
    private array $config = [];

    /**
     * Event dispatcher for connection lifecycle events.
     */
    private EventBus|null $eventBus = null;

    /**
     * Private constructor for static factory pattern.
     */
    private function __construct() {}

    /**
     * Create a new connection flow builder instance.
     *
     * @return self Fluent interface for method chaining
     */
    public static function begin() : self
    {
        return new self();
    }

    /**
     * Set the database connection configuration.
     *
     * @param array $config Connection parameters (host, port, database, etc.)
     *
     * @return self Fluent interface for method chaining
     */
    public function using(array $config) : self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Attach event dispatcher for connection lifecycle notifications.
     *
     * @param EventBus $eventBus Event dispatcher instance
     *
     * @return self Fluent interface for method chaining
     */
    public function withEvents(EventBus $eventBus) : self
    {
        $this->eventBus = $eventBus;

        return $this;
    }

    /**
     * Establish the database connection with event notifications.
     *
     * -- intent: creates a new connection instance, dispatches lifecycle events,
     * and propagates any connection errors to the event bus.
     *
     * @return DatabaseConnection Active database connection instance
     *
     * @throws Throwable Any exception from connection factory or event dispatch
     */
    public function connect() : DatabaseConnection
    {
        $label = $this->config['name'] ?? 'default';
        try {
            $connection = ConnectionFactory::from(config: $this->config);
            $this->eventBus?->dispatch(event: new ConnectionOpened(connectionName: $label));

            return $connection;
        } catch (Throwable $e) {
            $this->eventBus?->dispatch(event: new ConnectionFailed(connectionName: $label, exception: $e));
            throw $e;
        }
    }
}
