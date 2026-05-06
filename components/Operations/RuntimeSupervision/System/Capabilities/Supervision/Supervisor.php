<?php

declare(strict_types=1);

namespace Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Supervision;

use Avax\Components\Operations\RuntimeSupervision\System\Capabilities\Process\ProcessRecord;

class Supervisor
{
    private int $failureThreshold = 3;

    private int $cooldownSeconds = 30;

    public function __construct(private readonly string $name)
    {
    }

    public function withFailureThreshold(int $threshold): self
    {
        $this->failureThreshold = $threshold;

        return $this;
    }

    public function withCooldown(int $seconds): self
    {
        $this->cooldownSeconds = $seconds;

        return $this;
    }

    public function start(ProcessRecord $processRecord): void
    {
    }

    public function stop(string $processId): void
    {
    }

    public function restart(string $processId): void
    {
    }

    public function monitor(): array
    {
        return [
            'name' => $this->name,
            'failure_threshold' => $this->failureThreshold,
            'cooldown_seconds' => $this->cooldownSeconds,
        ];
    }
}
