<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Connections\Pools;

final class CockroachDBPool extends PostgreSQLPool
{
    public function withRetry(int $maxRetries = 3): RetryablePool
    {
        return new RetryablePool(pool: $this, maxRetries: $maxRetries);
    }
}
