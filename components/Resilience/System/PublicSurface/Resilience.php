<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\PublicSurface;

use Avax\Components\Resilience\System\Capabilities\Retry\RetryExecutor;
use Avax\Components\Resilience\System\Capabilities\Retry\RetryOptions;
use Avax\Components\Resilience\System\Capabilities\Retry\RetryResult;
use Closure;

final readonly class Resilience
{
    public static function retry(Closure $operation) : RetryBuilder
    {
        return new RetryBuilder($operation);
    }
}

final readonly class RetryBuilder
{
    private RetryOptions $options;

    public function __construct(
        private Closure $operation,
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