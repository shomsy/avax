<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\WorkerRestart;

final readonly class WorkerRestartPolicy
{
    public function __construct(
        public int $maxRestartsPerHour = 10,
        public int $backoffSeconds = 5,
    ) {}

    public function shouldRestart(int $restartCountInHour, int $consecutiveFailures) : bool
    {
        if ($restartCountInHour >= $this->maxRestartsPerHour) {
            return false;
        }

        return $consecutiveFailures < 10;
    }

    public function backoffDelay(int $attemptNumber) : int
    {
        return $this->backoffSeconds * $attemptNumber;
    }
}
