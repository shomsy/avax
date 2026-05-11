<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PdoConnectionPool;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\ConnectionPool;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PdoPooledConnection\PdoPooledConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnection;
use PDO;

/**
 * Concrete PDO connection pool.
 *
 * Creates and manages a pool of PDO connections with min/max bounds,
 * connection timeout, and idle timeout.
 */
final class PdoConnectionPool extends ConnectionPool
{
    /** @var array<string, mixed> */
    private array $dsn;
    private string|null $username;
    private string|null $password;

    /**
     * @param array<string, mixed> $dsn  PDO DSN config (driver, host, port, database, etc.)
     */
    public function __construct(
        array $dsn, string|null $username = null, string|null $password = null,
        int $minConnections = 2,
        int $maxConnections = 10,
        int $connectionTimeoutMs = 5000,
        int $idleTimeoutMs = 300000,
    ) {
        parent::__construct(
            minConnections: $minConnections,
            maxConnections: $maxConnections,
            connectionTimeoutMs: $connectionTimeoutMs,
            idleTimeoutMs: $idleTimeoutMs,
        );

        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
    }

    protected function validateConnection(PooledConnection $pooledConnection) : bool
    {
        return $pooledConnection->isValid();
    }

    protected function createConnection() : PooledConnection
    {
        $pdo = new PDO(
            dsn: $this->buildDsn(),
            username: $this->username,
            password: $this->password,
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return new PdoPooledConnection($pdo);
    }

    private function buildDsn() : string
    {
        $driver = $this->dsn['driver'] ?? 'mysql';

        // SQLite uses a simple 'sqlite:/path' DSN format, not host/port/dbname.
        if ($driver === 'sqlite') {
            $path = $this->dsn['path'] ?? $this->dsn['database'] ?? ':memory:';

            return "sqlite:{$path}";
        }

        $params = [];
        $allowed = ['host', 'port', 'dbname', 'unix_socket', 'charset'];

        foreach ($allowed as $key) {
            if (isset($this->dsn[$key])) {
                $params[] = "{$key}={$this->dsn[$key]}";
            }
        }

        return "{$driver}:" . implode(';', $params);
    }
}
