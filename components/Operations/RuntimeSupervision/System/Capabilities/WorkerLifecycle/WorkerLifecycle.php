<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\WorkerLifecycle;

final class WorkerLifecycle
{
    /**
     * @var array<string, WorkerRecord>
     */
    private array $workers = [];

    public function register(string $name) : WorkerRecord
    {
        $id                 = bin2hex(random_bytes(4));
        $worker             = new WorkerRecord(id: $id, name: $name, status: 'starting');
        $this->workers[$id] = $worker;

        return $worker;
    }

    public function start(string $id) : void
    {
        if (isset($this->workers[$id])) {
            $this->workers[$id] = new WorkerRecord(
                id       : $this->workers[$id]->id,
                name     : $this->workers[$id]->name,
                status   : 'running',
                startedAt: $this->workers[$id]->startedAt,
            );
        }
    }

    public function stop(string $id) : void
    {
        if (isset($this->workers[$id])) {
            $this->workers[$id] = new WorkerRecord(
                id           : $this->workers[$id]->id,
                name         : $this->workers[$id]->name,
                status       : 'stopped',
                startedAt    : $this->workers[$id]->startedAt,
                processedJobs: $this->workers[$id]->processedJobs,
                failedJobs   : $this->workers[$id]->failedJobs,
            );
        }
    }

    public function heartbeat(string $id) : void
    {
        if (isset($this->workers[$id])) {
            $this->workers[$id] = $this->workers[$id]->heartbeat();
        }
    }

    public function recordJob(string $id, bool $success) : void
    {
        if (isset($this->workers[$id])) {
            $worker = $this->workers[$id];

            if ($success) {
                $this->workers[$id] = new WorkerRecord(
                    id           : $worker->id,
                    name         : $worker->name,
                    status       : $worker->status,
                    startedAt    : $worker->startedAt,
                    lastHeartbeat: $worker->lastHeartbeat,
                    processedJobs: $worker->processedJobs + 1,
                    failedJobs   : $worker->failedJobs,
                );
            } else {
                $this->workers[$id] = new WorkerRecord(
                    id           : $worker->id,
                    name         : $worker->name,
                    status       : $worker->status,
                    startedAt    : $worker->startedAt,
                    lastHeartbeat: $worker->lastHeartbeat,
                    processedJobs: $worker->processedJobs,
                    failedJobs   : $worker->failedJobs + 1,
                );
            }
        }
    }

    /**
     * @return array<string, WorkerRecord>
     */
    public function workers() : array
    {
        return $this->workers;
    }

    public function find(string $id) : ?WorkerRecord
    {
        return $this->workers[$id] ?? null;
    }

    /**
     * @return list<WorkerRecord>
     */
    public function staleWorkers(int $thresholdSeconds = 30) : array
    {
        return array_values(array_filter($this->workers, fn (WorkerRecord $w) : bool => $w->isStale($thresholdSeconds)));
    }
}
