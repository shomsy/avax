<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

interface PooledConnection
{
    public function getResource(): object;

    public function isValid(): bool;

    public function getCreatedAt(): float;

    public function getLastUsedAt(): float;

    public function executeCount(): int;

    /**
     * Reset the connection state for safe reuse from the pool.
     * Should clear any leftover transaction state, temporary tables,
     * session variables, and prepared statement handles where applicable.
     */
    public function reset() : void;

    /**
     * Permanently close the underlying connection resource.
     * After calling close(), the connection must not be used again.
     */
    public function close() : void;
}
