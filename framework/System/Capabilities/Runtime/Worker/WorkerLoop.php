<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

final class WorkerLoop
{
    private bool $running = false;

    public function start(): void
    {
        $this->running = true;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function tick(): void
    {
    }
}
