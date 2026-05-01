<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

use Closure;
use Throwable;

final class RetryExecutor
{
    public function __construct(
        private Closure     $operation,
        private RetryOptions $options,
    ) {}

    public function execute() : RetryResult
    {
        $lastException = null;
        $attemptNumber = 0;

        while ( $attemptNumber < $this->options->attempts ) {
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

                if ($attemptNumber < $this->options->attempts) {
                    $this->sleep();
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

    private function sleep() : void
    {
        $delay = (int) ($this->options->backoffMs * (1 + random_int(0, 100) / 100));
        usleep($delay * 1000);
    }
}
