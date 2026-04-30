<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\CreateBuilder;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\PDOExecutor;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\QueryOrchestrator;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\GrammarInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use ReflectionException;
use Throwable;

/**
 * Creates connection-bound QueryBuilder instances for the Query capability.
 */
final readonly class CreateBuilder
{
    public function __construct(
        private Connections         $connections,
        private GrammarInterface    $grammar,
        private EventBus|null       $eventBus = null,
        private ExecutionScope|null $scope = null,
    ) {}

    /**
     * @throws ReflectionException
     * @throws Throwable
     */
    public function for(string|null $connectionName = null) : QueryBuilder
    {
        $connection   = $this->connections->connection(name: $connectionName);
        $orchestrator = new QueryOrchestrator(
            executor: new PDOExecutor(
                          connection    : $connection,
                          eventBus      : $this->eventBus,
                          connectionName: $connection->getName(),
                      ),
            scope   : $this->scope,
        );

        return new QueryBuilder(
            grammar     : $this->grammar,
            orchestrator: $orchestrator,
        );
    }
}
