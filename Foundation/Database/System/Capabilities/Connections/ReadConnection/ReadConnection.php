<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\ReadConnection;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Connections\Exceptions\ConnectionException;
use Avax\Database\System\Capabilities\Connections\OpenConnection\BuildPhysicalConnection;
use Avax\Database\System\Capabilities\Connections\OpenConnection\OpenConnection;
use Avax\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use Avax\Database\System\Capabilities\Connections\Pools\PooledConnectionAuthority;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Random\RandomException;
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

    private ExecutionScope|null               $scope;
    private readonly ResolveDefaultConnection $resolveDefaultConnection;
    private readonly RememberConnection       $rememberConnection;

    /**
     * @param array<string, mixed> $config
     *
     * @throws RandomException
     */
    public function __construct(
        private readonly array         $config,
        private readonly EventBus|null $eventBus = null,
        ExecutionScope|null            $scope = null,
        ResolveDefaultConnection|null  $resolveDefaultConnection = null,
        RememberConnection|null        $rememberConnection = null
    )
    {
        $this->scope                    = $scope ?? ExecutionScope::fresh();
        $this->resolveDefaultConnection = $resolveDefaultConnection ?? new ResolveDefaultConnection(config: $this->config);
        $this->rememberConnection       = $rememberConnection ?? new RememberConnection();
    }

    /**
     * @throws Throwable
     */
    public function connection(string|null $name = null) : DatabaseConnection
    {
        $resolvedName = $this->resolveDefaultConnection->resolve(connectionName: $name);
        $cached       = $this->rememberConnection->read(connections: $this->connections, name: $resolvedName);

        if ($cached !== null) {
            return $cached;
        }

        $config = $this->config['connections'][$resolvedName] ?? null;

        if (! is_array(value: $config)) {
            throw new ConnectionException(name: $resolvedName, message: 'Database connection configuration not found.');
        }

        $config['name'] = $resolvedName;

        if (isset($config['pool'])) {
            return new PooledConnectionAuthority(pool: $this->pool(name: $resolvedName, config: $config));
        }

        return $this->rememberConnection->remember(
            connections: $this->connections,
            name       : $resolvedName,
            connection : $this->open(config: $config)
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function pool(string $name, array $config) : ConnectionPool
    {
        $pool = $this->rememberConnection->readPool(pools: $this->pools, name: $name)
            ?? $this->rememberConnection->rememberPool(
                pools: $this->pools,
                name : $name,
                pool : new ConnectionPool(config: $config, eventBus: $this->eventBus)
            );

        if ($this->scope !== null) {
            $pool->withScope(scope: $this->scope);
        }

        return $pool;
    }

    public function withScope(ExecutionScope $scope) : self
    {
        return clone(object: $this, withProperties: [
            'scope' => $scope,
        ]);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws Throwable
     */
    private function open(array $config) : DatabaseConnection
    {
        return (new OpenConnection(
            buildPhysicalConnection: new BuildPhysicalConnection(),
            eventBus               : $this->eventBus,
            scope                  : $this->scope
        ))->using(config: $config);
    }
}
