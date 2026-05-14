<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\EnforceTimeout;

use Avax\Components\Operations\Resilience\System\Capabilities\Timeout\Timeout;
use Closure;

/**
 * EnforceTimeout — Wraps an action with timeout enforcement using Resilience Timeout.
 *
 * When timeoutMs is configured, the action runs under the Resilience Timeout boundary.
 * When timeoutMs is null, the action runs without timeout enforcement.
 */
final readonly class EnforceTimeout
{
    /**
     * Execute the action with optional timeout enforcement.
     *
     * @param Closure(): mixed $action
     * @param int|null         $timeoutMs Timeout in milliseconds. Null means no timeout.
     */
    public function run(Closure $action, int|null $timeoutMs) : mixed
    {
        if ($timeoutMs === null || $timeoutMs <= 0) {
            return $action();
        }

        $timeout = new Timeout($timeoutMs);

        return $timeout->run($action);
    }
}
