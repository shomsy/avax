<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PdoPooledConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnection;
use PDO;

/**
 * PDO-backed pooled connection wrapper.
 *
 * Tracks creation time, last-used time, and execution count for pool governance.
 */
final class PdoPooledConnection implements PooledConnection
{
    private float $createdAt;
    private float $lastUsedAt;
    private int $executeCount = 0;

    public function __construct(
        private readonly PDO $pdo,
    ) {
        $this->createdAt = microtime(true);
        $this->lastUsedAt = $this->createdAt;
    }

    public function getResource() : PDO
    {
        $this->lastUsedAt = microtime(true);

        return $this->pdo;
    }

    public function isValid() : bool
    {
        try {
            return $this->pdo->query('SELECT 1') !== false;
        } catch (\Throwable) {
            return false;
        }
    }

    public function getCreatedAt() : float
    {
        return $this->createdAt;
    }

    public function getLastUsedAt() : float
    {
        return $this->lastUsedAt;
    }

    public function executeCount() : int
    {
        return $this->executeCount;
    }

    public function recordExecute() : void
    {
        $this->executeCount++;
    }
}
