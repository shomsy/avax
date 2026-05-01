<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\Pools;

use Exception;

final readonly class RetryablePool
{
    public function __construct(
        private BaseConnectionPool $baseConnectionPool,
        private int $maxRetries = 3,
    ) {}

    /**
     * @throws Exception
     */
    public function execute(callable $operation): mixed
    {
        $attempts = 0;

        while ($attempts < $this->maxRetries) {
            try {
                return $operation($this->baseConnectionPool);
            } catch (Exception $exception) {
                $attempts++;
                if ($attempts >= $this->maxRetries) {
                    throw $exception;
                }

                usleep(microseconds: 100000 * $attempts);
            }
        }

        return null;
    }
}
