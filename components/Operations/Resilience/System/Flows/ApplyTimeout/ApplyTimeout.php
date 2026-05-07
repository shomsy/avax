<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\ApplyTimeout;

use Avax\Components\Operations\Resilience\System\Capabilities\Timeout\Timeout;
use Closure;

final readonly class ApplyTimeout
{
    /**
     * @template TResult
     * @param Closure(): TResult $operation
     *
     * @return TResult
     */
    public function apply(Closure $operation, int $timeoutMs = 5000) : mixed
    {
        return (new Timeout($timeoutMs))->run($operation);
    }
}
