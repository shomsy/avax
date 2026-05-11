<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Avax\Components\Operations\Resilience\System\PublicSurface\Resilience;

final readonly class RetryablePool
{
    public function __construct(
        private BaseConnectionPool $baseConnectionPool,
        private int $maxRetries = 3,
    ) {
    }

    public function execute(callable $operation): mixed
    {
        $result = Resilience::retry(fn () => $operation($this->baseConnectionPool))
            ->times($this->maxRetries)
            ->backoff(100)
            ->run();

        if (! $result->success) {
            return null;
        }

        return $result->result;
    }
}
