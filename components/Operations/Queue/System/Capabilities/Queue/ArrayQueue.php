<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

final class ArrayQueue implements QueueBroker
{
    private array $queues = [];

    public function __construct(private readonly string $prefix = 'queue:')
    {
    }

    public function push(string $queue, array $job): void
    {
        $key = $this->prefix.$queue;
        $this->queues[$key][] = $job;
    }

    public function pop(string $queue) : array|null
    {
        $key = $this->prefix.$queue;

        return array_shift($this->queues[$key] ?? []) ?? null;
    }

    public function size(string $queue): int
    {
        $key = $this->prefix.$queue;

        return count($this->queues[$key] ?? []);
    }

    public function remove(string $queue, string $jobId): void
    {
        $key = $this->prefix.$queue;
        if (! isset($this->queues[$key])) {
            return;
        }

        $this->queues[$key] = array_filter(
            $this->queues[$key],
            static fn (array $job): bool => ($job['id'] ?? '') !== $jobId,
        );
    }

    public function clear(string $queue): void
    {
        unset($this->queues[$this->prefix.$queue]);
    }
}
