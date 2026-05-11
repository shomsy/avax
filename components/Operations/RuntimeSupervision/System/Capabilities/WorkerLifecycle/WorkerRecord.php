<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\WorkerLifecycle;

final readonly class WorkerRecord
{
    public function __construct(
        public string $id,
        public string $name,
        public string $status = 'idle',
        public int|null $startedAt = null,
        public int|null $lastHeartbeat = null,
        public int    $processedJobs = 0,
        public int    $failedJobs = 0,
    ) {}

    public function withDefaults() : self
    {
        $now = time();

        return new self(
            id           : $this->id,
            name         : $this->name,
            status       : $this->status,
            startedAt    : $this->startedAt ?? $now,
            lastHeartbeat: $this->lastHeartbeat ?? $now,
            processedJobs: $this->processedJobs,
            failedJobs   : $this->failedJobs,
        );
    }

    public function heartbeat() : self
    {
        return new self(
            id           : $this->id,
            name         : $this->name,
            status       : $this->status,
            startedAt    : $this->startedAt,
            lastHeartbeat: time(),
            processedJobs: $this->processedJobs,
            failedJobs   : $this->failedJobs,
        );
    }

    public function isStale(int $thresholdSeconds = 30) : bool
    {
        return (time() - $this->lastHeartbeat) > $thresholdSeconds;
    }
}
