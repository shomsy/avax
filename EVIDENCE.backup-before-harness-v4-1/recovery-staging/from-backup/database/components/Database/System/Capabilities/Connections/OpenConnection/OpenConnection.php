<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Connections\OpenConnection;

use components\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use components\Database\System\Capabilities\Telemetry\Events\ConnectionFailed;
use components\Database\System\Capabilities\Telemetry\Events\ConnectionOpened;
use components\Database\System\Capabilities\Telemetry\Events\EventBus;
use components\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Throwable;

/**
 * Opens one direct database connection with optional telemetry dispatch.
 */
final readonly class OpenConnection
{
    public function __construct(
        private BuildPhysicalConnection $buildPhysicalConnection,
        private ?EventBus               $eventBus = null,
        private ?ExecutionScope         $scope = null
    ) {}

    /**
     * @param array<string, mixed> $config
     *
     * @throws Throwable
     */
    public function using(array $config) : DatabaseConnection
    {
        $label = $config['name'] ?? 'default';
        $scope = $this->scope ?? ExecutionScope::fresh();

        try {
            $connection = $this->buildPhysicalConnection->from(config: $config);

            $this->eventBus?->dispatch(event: new ConnectionOpened(
                                                  connectionName: $label,
                                                  correlationId : $scope->correlationId
                                              ));

            return $connection;
        } catch (Throwable $throwable) {
            $this->eventBus?->dispatch(event: new ConnectionFailed(
                                                  connectionName: $label,
                                                  exception     : $throwable,
                                                  correlationId : $scope->correlationId
                                              ));

            throw $throwable;
        }
    }

    public function withEvents(EventBus $eventBus) : self
    {
        return new self(
            buildPhysicalConnection: $this->buildPhysicalConnection,
            eventBus               : $eventBus,
            scope                  : $this->scope
        );
    }

    public function withScope(ExecutionScope $scope) : self
    {
        return new self(
            buildPhysicalConnection: $this->buildPhysicalConnection,
            eventBus               : $this->eventBus,
            scope                  : $scope
        );
    }
}
