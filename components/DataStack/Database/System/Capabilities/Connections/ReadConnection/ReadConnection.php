<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\ConnectionException;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\BuildPhysicalConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\OpenConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnectionAuthority;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\QueryEvents\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Throwable;

/**
 * Reads one named database connection from cache, pool, or a fresh open path.
 */
final class ReadConnection
{
    /** @var array<string, DatabaseConnection> */
    private array $connections = [];

    /** @var array<string, ConnectionPool> */
    private array $pools = [];

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array                    $config,
        private readonly ResolveDefaultConnection $resolveDefaultConnection,
        private readonly RememberConnection       $rememberConnection,
        private readonly ?EventBus                $eventBus = null,
        private readonly ExecutionScope|null      $executionScope = null,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function connection(string|null $name = null) : DatabaseConnection
    {
        $resolvedName = $this->resolveDefaultConnection->resolve(connectionName: $name);
        $cached = $this->rememberConnection->read(connections: $this->connections, name: $resolvedName);

        if ($cached instanceof DatabaseConnection) {
            return $cached;
        }

        $config = $this->config['connections'][$resolvedName] ?? null;

        if (! is_array(value: $config)) {
            throw new ConnectionException(name: $resolvedName, message: 'Database connection configuration not found.');
        }

        $config['name'] = $resolvedName;

        if (isset($config['pool'])) {
            return new PooledConnectionAuthority(connectionPool: $this->pool(name: $resolvedName, config: $config));
        }

        return $this->rememberConnection->remember(
            connections: $this->connections,
            name       : $resolvedName,
            databaseConnection: $this->open(config: $config),
        );
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function pool(string $name, array $config): ConnectionPool
    {
        $pool = $this->rememberConnection->readPool(pools: $this->pools, name: $name)
            ?? $this->rememberConnection->rememberPool(
                pools: $this->pools,
                name : $name,
                connectionPool: new ConnectionPool(config: $config, eventBus: $this->eventBus),
            );

        $pool->withScope(executionScope: $this->executionScope);

        return $pool;
    }

    public function withScope(ExecutionScope $executionScope): self
    {
        return clone (object: $this, withProperties: [
            'executionScope' => $executionScope,
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     *
     * @throws Throwable
     */
    private function open(array $config): DatabaseConnection
    {
        return new OpenConnection(
            buildPhysicalConnection: new BuildPhysicalConnection(),
            eventBus               : $this->eventBus,
            executionScope         : $this->executionScope,
        )->using(config: $config);
    }
}
