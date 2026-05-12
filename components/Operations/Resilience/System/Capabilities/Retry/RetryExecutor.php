<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

use Closure;
use Throwable;

final readonly class RetryExecutor
{
    public function __construct(
        private Closure $operation,
        private RetryOptions $retryOptions,
    ) {
    }

    public function execute(): RetryResult
    {
        $lastException = null;
        $attemptNumber = 0;

        while ($attemptNumber < $this->retryOptions->attempts) {
            $attemptNumber++;

            try {
                $result = ($this->operation)();

                return new RetryResult(
                    success      : true,
                    result       : $result,
                    attempts     : $attemptNumber,
                    lastException: null,
                );
            } catch (Throwable $e) {
                $lastException = $e;

                if ($attemptNumber < $this->retryOptions->attempts) {
                    $this->sleep($attemptNumber);
                }
            }
        }

        return new RetryResult(
            success      : false,
            result       : null,
            attempts     : $attemptNumber,
            lastException: $lastException,
        );
    }

    private function sleep(int $attempt) : void
    {
        $delay = $this->calculateDelay($attempt);
        usleep($delay * 1000);
    }

    private function calculateDelay(int $attempt) : int
    {
        $delayMs = match ($this->retryOptions->backoffStrategy) {
            'exponential' => $this->retryOptions->backoffMs * (2 ** ($attempt - 1)),
            'linear'      => $this->retryOptions->backoffMs * $attempt,
            'none'        => 0,
            default       => $this->retryOptions->backoffMs,
        };

        if ($this->retryOptions->jitter && $delayMs > 0) {
            $delayMs = (int) ($delayMs * (0.5 + (mt_rand() / mt_getrandmax()) * 0.5));
        }

        return $delayMs;
    }
}
