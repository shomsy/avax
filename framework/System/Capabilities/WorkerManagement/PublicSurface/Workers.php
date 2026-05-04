<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\WorkerManagement\PublicSurface;

use Avax\Components\Application\Text\System\Capabilities\CaseConversion\Stringable;
use Avax\Framework\System\Capabilities\WorkerManagement\Capabilities\Workers\WorkerProcess;

final readonly class Workers
{
    public function __construct(private WorkerPool $workerPool)
    {
    }

    /**
     * @param array{max_memory?: int} $options
     */
    public function start(int $processes = 1, array $options = []): void
    {
        $this->workerPool->start($processes, $options);
    }

    public function stop(): void
    {
        $this->workerPool->stop();
    }

    public function restart(bool $graceful = true): void
    {
        $this->workerPool->restart($graceful);
    }

    public function status(): WorkerStatus
    {
        return $this->workerPool->status();
    }

    public function drain(): void
    {
        $this->workerPool->drain();
    }
}
