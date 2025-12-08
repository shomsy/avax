<?php
declare(strict_types=1);

namespace Foundation\HTTP\Session\Locks;

use Foundation\HTTP\Session\Observability\MetricCollectorInterface;

final class LockMetricsCollector
{
    public function __construct(private MetricCollectorInterface $metrics) {}

    public function recordLockWaitTime(string $sessionId, float $seconds) : void
    {
        $this->metrics->observe('session_lock_wait_seconds', $seconds, ['session_id' => $sessionId]);
    }
}
