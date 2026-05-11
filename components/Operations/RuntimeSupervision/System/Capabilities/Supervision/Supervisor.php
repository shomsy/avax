<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRecord;
use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRegistry;

class Supervisor
{
    private ProcessRegistry $registry;

    private int $failureThreshold = 3;

    private int $cooldownSeconds = 30;

    private int $failureCount = 0;

    private int|null $lastFailureAt = null;

    private int|null $startedAt = null;

    private int $restartCount = 0;

    public function __construct(private readonly string $name)
    {
        $this->registry = new ProcessRegistry();
    }

    public function withFailureThreshold(int $threshold): self
    {
        $clone                   = clone $this;
        $clone->failureThreshold = $threshold;

        return $clone;
    }

    public function withCooldown(int $seconds): self
    {
        $clone                  = clone $this;
        $clone->cooldownSeconds = $seconds;

        return $clone;
    }

    public function start(ProcessRecord $processRecord): void
    {
        $this->registry->register(
            new ProcessRecord(
                id       : $processRecord->id,
                name     : $processRecord->name,
                status   : 'running',
                pid      : $processRecord->pid,
                startedAt: hrtime(true) / 1e9,
            ),
        );

        if ($this->startedAt === null) {
            $this->startedAt = time();
        }

        $this->failureCount  = 0;
        $this->lastFailureAt = null;
    }

    public function stop(string $processId): void
    {
        $process = $this->registry->find($processId);

        if ($process !== null) {
            $this->registry->register(
                new ProcessRecord(
                    id       : $process->id,
                    name     : $process->name,
                    status   : 'stopped',
                    pid      : $process->pid,
                    startedAt: $process->startedAt,
                    endedAt  : hrtime(true) / 1e9,
                ),
            );
        }
    }

    public function restart(string $processId): void
    {
        $process = $this->registry->find($processId);

        if ($process !== null) {
            $this->stop($processId);
            $this->start(
                new ProcessRecord(
                    id       : $process->id,
                    name     : $process->name,
                    status   : 'running',
                    pid      : $process->pid,
                    startedAt: hrtime(true) / 1e9,
                ),
            );

            $this->restartCount++;
        }
    }

    public function recordFailure() : void
    {
        $this->failureCount++;
        $this->lastFailureAt = time();
    }

    public function canRestart() : bool
    {
        if ($this->failureCount >= $this->failureThreshold) {
            if ($this->lastFailureAt === null) {
                return true;
            }

            return (time() - $this->lastFailureAt) >= $this->cooldownSeconds;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function monitor(): array
    {
        return [
            'name' => $this->name,
            'status'        => $this->getStatus(),
            'processes'     => $this->registry->all(),
            'failure_threshold' => $this->failureThreshold,
            'failure_count' => $this->failureCount,
            'cooldown_seconds' => $this->cooldownSeconds,
            'restart_count' => $this->restartCount,
            'uptime'        => $this->startedAt !== null ? time() - $this->startedAt : 0,
        ];
    }

    public function registry() : ProcessRegistry
    {
        return $this->registry;
    }

    private function getStatus() : string
    {
        if ($this->startedAt === null) {
            return 'stopped';
        }

        if ($this->failureCount >= $this->failureThreshold) {
            return 'degraded';
        }

        return 'running';
    }
}
