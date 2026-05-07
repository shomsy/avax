<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Timeout;

use Closure;

final readonly class Timeout
{
    public function __construct(
        public int $timeoutMs = 5000,
    ) {}

    /**
     * @template TResult
     * @param Closure(): TResult $operation
     *
     * @return TResult
     */
    public function run(Closure $operation) : mixed
    {
        $startTime = hrtime(true);

        $result = $operation();

        $elapsedMs = (hrtime(true) - $startTime) / 1_000_000;

        if ($elapsedMs > $this->timeoutMs) {
            throw new TimeoutException("Operation timed out after " . round($elapsedMs) . "ms (limit: {$this->timeoutMs}ms).");
        }

        return $result;
    }

    public function withTimeout(int $ms) : self
    {
        return new self($ms);
    }
}
