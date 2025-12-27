<?php

declare(strict_types=1);

namespace Avax\Database\Connection;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\Events\ConnectionFailed;
use Avax\Database\Events\ConnectionOpened;
use Avax\Database\Events\EventBus;
use Avax\Database\Support\ExecutionScope;
use Throwable;

/**
 * Fluent builder for establishing direct database connections.
 *
 * @see docs/Concepts/Connections.md
 */
final class DirectConnectionFlow
{
    /** @var array<string, mixed> The list of server addresses, usernames, and passwords. */
    private array $config = [];

    /** @var EventBus|null The system we use to send connection events. */
    private EventBus|null $eventBus = null;

    /** @var ExecutionScope|null The "Passenger Ticket" used to tag this connection for logs. */
    private ExecutionScope|null $scope = null;

    /**
     * Private constructor — use `DirectConnectionFlow::begin()` to start.
     */
    private function __construct() {}

    /**
     * Start the setup wizard for a new database connection.
     *
     * @return self
     */
    public static function begin(): self
    {
        return new self();
    }

    /**
     * Give the helper the settings it needs (Host, Driver, etc.).
     *
     * @param array<string, mixed> $config The settings dictionary.
     * @return self The helper itself, so you can keep adding instructions.
     */
    public function using(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Tell the helper where to send "Connected" or "Failed" notifications.
     */
    public function withEvents(EventBus $eventBus): self
    {
        $this->eventBus = $eventBus;

        return $this;
    }

    /**
     * Give the connection a "Luggage Tag" (Scope) so we can trace its work.
     */
    public function withScope(ExecutionScope $scope): self
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Establish the physical connection with event dispatching.
     *
     * @throws Throwable
     * @return DatabaseConnection
     */
    public function connect(): DatabaseConnection
    {
        $label = $this->config['name'] ?? 'default';
        $scope = $this->scope ?? ExecutionScope::fresh();

        try {
            $connection = ConnectionFactory::from(config: $this->config);
            // Signal to the system that the line is open.
            $this->eventBus?->dispatch(event: new ConnectionOpened(
                connectionName: $label,
                correlationId: $scope->correlationId
            ));

            return $connection;
        } catch (Throwable $e) {
            // Signal to the system that we had an error.
            $this->eventBus?->dispatch(event: new ConnectionFailed(
                connectionName: $label,
                exception: $e,
                correlationId: $scope->correlationId
            ));

            throw $e;
        }
    }
}
