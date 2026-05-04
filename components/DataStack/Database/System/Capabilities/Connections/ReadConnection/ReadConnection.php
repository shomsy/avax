<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\ConnectionException;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\BuildPhysicalConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection\OpenConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnectionAuthority;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
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

    private readonly ?ExecutionScope $executionScope;

    private readonly ResolveDefaultConnection $resolveDefaultConnection;

    private readonly RememberConnection $rememberConnection;

    /**
     * @param array<string, mixed> $config
     *
     * @throws RandomException
     */
    public function __construct(
        private readonly array $config,
        private readonly ?EventBus $eventBus = null,
        ?ExecutionScope           $executionScope = null,
        ?ResolveDefaultConnection $resolveDefaultConnection = null,
        ?RememberConnection       $rememberConnection = null,
    ) {
        $this->executionScope = $executionScope ?? ExecutionScope::fresh();
        $this->resolveDefaultConnection = $resolveDefaultConnection ?? new ResolveDefaultConnection(config: $this->config);
        $this->rememberConnection = $rememberConnection ?? new RememberConnection();
    }

    /**
     * @throws Throwable
     */
    public function connection(?string $name = null) : DatabaseConnection
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
            return new PooledConnectionAuthority(pool: $this->pool(name: $resolvedName, config: $config));
        }

        return $this->rememberConnection->remember(
            connections: $this->connections,
            name       : $resolvedName,
            connection : $this->open(config: $config),
        );
    }

    /**
     * @param array<string, mixed> $config
     */
    private function pool(string $name, array $config): ConnectionPool
    {
        $pool = $this->rememberConnection->readPool(pools: $this->pools, name: $name)
            ?? $this->rememberConnection->rememberPool(
                pools: $this->pools,
                name : $name,
                pool : new ConnectionPool(config: $config, eventBus: $this->eventBus),
            );

        $pool->withScope(scope: $this->executionScope);

        return $pool;
    }

    public function withScope(ExecutionScope $executionScope) : self
    {
        return clone (object: $this, withProperties: [
            'scope' => $executionScope,
        ]);
    }

    /**
     * @param array<string, mixed> $config
     *
     * @throws Throwable
     */
    private function open(array $config): DatabaseConnection
    {
        return new OpenConnection(
            buildPhysicalConnection: new BuildPhysicalConnection(),
            eventBus               : $this->eventBus,
            scope                  : $this->executionScope,
        )->using(config: $config);
    }
}
