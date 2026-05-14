<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PdoPooledConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools\PooledConnection;
use PDO;
use RuntimeException;
use Throwable;

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
    private bool $closed = false;

    public function __construct(
        private ?PDO $pdo,
    ) {
        $this->createdAt = microtime(true);
        $this->lastUsedAt = $this->createdAt;
    }

    /**
 * @throws RuntimeException
 */
public function getResource() : PDO
    {
        if ($this->closed || $this->pdo === null) {
            throw new RuntimeException('Cannot get resource from closed pooled connection');
        }

        $this->lastUsedAt = microtime(true);

        return $this->pdo;
    }

    public function isValid() : bool
    {
        if ($this->closed || $this->pdo === null) {
            return false;
        }

        try {
            return $this->pdo->query('SELECT 1') !== false;
        } catch (Throwable) {
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

    public function reset() : void
    {
        if ($this->closed || $this->pdo === null) {
            return;
        }

        // Roll back any active transaction to prevent lock carryover
        try {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        } catch (Throwable) {
            // Transaction state may be broken, just continue
        }

        // Reset execution count for the new lease
        $this->executeCount = 0;
    }

    public function close() : void
    {
        $this->pdo    = null;
        $this->closed = true;
    }
}
