<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

use Closure;

final class RetryBuilder
{
    private RetryOptions $retryOptions;

    public function __construct(
        private readonly Closure $operation,
    )
    {
        $this->retryOptions = new RetryOptions(
            attempts : 3,
            backoffMs: 200,
        );
    }

    public function times(int $attempts) : self
    {
        $this->retryOptions = $this->retryOptions->withAttempts($attempts);

        return $this;
    }

    public function backoff(int $milliseconds) : self
    {
        $this->retryOptions = $this->retryOptions->withBackoff($milliseconds);

        return $this;
    }

    public function run() : RetryResult
    {
        $retryExecutor = new RetryExecutor($this->operation, $this->retryOptions);

        return $retryExecutor->execute();
    }
}
