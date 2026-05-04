<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\ConnectionFailed;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\ConnectionOpened;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Throwable;

/**
 * Opens one direct database connection with optional telemetry dispatch.
 */
final readonly class OpenConnection
{
    public function __construct(
        private BuildPhysicalConnection $buildPhysicalConnection,
        private ?EventBus $eventBus = null,
        private ?ExecutionScope $executionScope = null,
    ) {}

    /**
     * @param array<string, mixed> $config
     *
     * @throws Throwable
     */
    public function using(array $config): DatabaseConnection
    {
        $label = $config['name'] ?? 'default';
        $scope = $this->executionScope ?? ExecutionScope::fresh();

        try {
            $connection = $this->buildPhysicalConnection->from(config: $config);

            $this->eventBus?->dispatch(event: new ConnectionOpened(
                connectionName: $label,
                correlationId : $scope->correlationId,
            ));

            return $connection;
        } catch (Throwable $throwable) {
            $this->eventBus?->dispatch(event: new ConnectionFailed(
                connectionName: $label,
                exception     : $throwable,
                correlationId : $scope->correlationId,
            ));

            throw $throwable;
        }
    }

    public function withEvents(EventBus $eventBus): self
    {
        return new self(
            buildPhysicalConnection: $this->buildPhysicalConnection,
            eventBus               : $eventBus,
            scope                  : $this->executionScope,
        );
    }

    public function withScope(ExecutionScope $executionScope): self
    {
        return new self(
            buildPhysicalConnection: $this->buildPhysicalConnection,
            eventBus               : $this->eventBus,
            scope                  : $executionScope,
        );
    }
}
