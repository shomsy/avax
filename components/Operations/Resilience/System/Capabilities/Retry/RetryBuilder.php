<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Capabilities\Retry;

use Closure;

final class RetryBuilder
{
    private RetryOptions $options;

    public function __construct(
        private readonly Closure $operation,
    )
    {
        $this->options = new RetryOptions(
            attempts : 3,
            backoffMs: 200,
        );
    }

    public function times(int $attempts) : self
    {
        $this->options = $this->options->withAttempts($attempts);

        return $this;
    }

    public function backoff(int $milliseconds) : self
    {
        $this->options = $this->options->withBackoff($milliseconds);

        return $this;
    }

    public function run() : RetryResult
    {
        $executor = new RetryExecutor($this->operation, $this->options);

        return $executor->execute();
    }
}
