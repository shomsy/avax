<?php

declare(strict_types=1);

namespace Avax\Components\Resilience\System\Flows\RetryOperation;

use Avax\Components\Resilience\System\Capabilities\Retry\RetryExecutor;
use Avax\Components\Resilience\System\Capabilities\Retry\RetryOptions;
use Avax\Components\Resilience\System\Capabilities\Retry\RetryResult;
use Closure;

final readonly class RetryOperation
{
    public function retry(Closure $operation, int $attempts = 3, int $backoffMs = 200) : RetryResult
    {
        return (new RetryExecutor(
            operation: $operation,
            options  : new RetryOptions(attempts: $attempts, backoffMs: $backoffMs),
        ))->execute();
    }
}
