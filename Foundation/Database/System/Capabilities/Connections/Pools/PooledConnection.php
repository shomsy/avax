<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\Pools;

interface PooledConnection
{
    public function getResource() : object;

    public function isValid() : bool;

    public function getCreatedAt() : float;

    public function getLastUsedAt() : float;

    public function executeCount() : int;
}
