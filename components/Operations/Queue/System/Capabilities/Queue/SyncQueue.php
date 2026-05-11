<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

final class SyncQueue implements QueueBroker
{
    private array $queues = [];

    public function push(string $queue, array $job): void
    {
        $this->queues[$queue][] = $job;
    }

    public function pop(string $queue) : array|null
    {
        return array_shift($this->queues[$queue] ?? []) ?? null;
    }

    public function size(string $queue): int
    {
        return count($this->queues[$queue] ?? []);
    }

    public function remove(string $queue, string $jobId): void
    {
        if (! isset($this->queues[$queue])) {
            return;
        }

        $this->queues[$queue] = array_filter(
            $this->queues[$queue],
            static fn (array $job): bool => ($job['id'] ?? '') !== $jobId,
        );
    }

    public function clear(string $queue): void
    {
        unset($this->queues[$queue]);
    }
}
