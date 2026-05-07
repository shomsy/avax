<?php

declare(strict_types=1);

namespace Avax\Components\Operations\BackgroundProcesses\System\Capabilities\RestartPolicy;

final class RestartPolicy
{
    public function __construct(
        private readonly int $maxRestarts = 5,
        private readonly int $windowSeconds = 60,
    ) {}

    public function canRestart(int $restartCount, int $lastRestartTime = 0) : bool
    {
        if ($restartCount >= $this->maxRestarts) {
            return false;
        }

        return true;
    }

    public function delaySeconds(int $restartCount) : int
    {
        return min($restartCount * 2, 30);
    }
}
