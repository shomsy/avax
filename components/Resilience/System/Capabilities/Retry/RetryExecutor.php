<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Capabilities\Retry;

use Closure;
use Throwable;

final readonly class RetryOptions
{
    public function __construct(
        public int      $attempts,
        public int      $backoffMs,
        public int|null $timeoutMs = null,
    ) {}

    public function withAttempts(int $attempts) : self
    {
        return new self($attempts, $this->backoffMs, $this->timeoutMs);
    }

    public function withBackoff(int $backoffMs) : self
    {
        return new self($this->attempts, $backoffMs, $this->timeoutMs);
    }
}

final readonly class RetryExecutor
{
    private int $attemptNumber = 0;

    public function __construct(
        private Closure     $operation,
        private RetryOptions $options,
    ) {}

    public function execute() : RetryResult
    {
        $lastException = null;

        while ( $this->attemptNumber < $this->options->attempts ) {
            $this->attemptNumber++;

            try {
                $result = ($this->operation)();

                return new RetryResult(
                    success      : true,
                    result       : $result,
                    attempts     : $this->attemptNumber,
                    lastException: null,
                );
            } catch (Throwable $e) {
                $lastException = $e;

                if ($this->attemptNumber < $this->options->attempts) {
                    $this->sleep();
                }
            }
        }

        return new RetryResult(
            success      : false,
            result       : null,
            attempts     : $this->attemptNumber,
            lastException: $lastException,
        );
    }

    private function sleep() : void
    {
        $delay = (int) ($this->options->backoffMs * (1 + random_int(0, 100) / 100));
        usleep($delay * 1000);
    }
}

final readonly class RetryResult
{
    public function __construct(
        public bool           $success,
        public mixed          $result,
        public int            $attempts,
        public Throwable|null $lastException,
    ) {}
}