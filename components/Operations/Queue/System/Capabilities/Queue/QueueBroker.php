<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue;

interface QueueBroker
{
    public function push(string $queue, array $job) : void;

    public function pop(string $queue) : ?array;

    public function size(string $queue) : int;

    public function remove(string $queue, string $jobId) : void;

    public function clear(string $queue) : void;
}

final class SyncQueue implements QueueBroker
{
    private array $queues = [];

    public function push(string $queue, array $job) : void
    {
        $this->queues[$queue][] = $job;
    }

    public function pop(string $queue) : ?array
    {
        return array_shift($this->queues[$queue] ?? []) ?? null;
    }

    public function size(string $queue) : int
    {
        return count($this->queues[$queue] ?? []);
    }

    public function remove(string $queue, string $jobId) : void
    {
        if (! isset($this->queues[$queue])) {
            return;
        }

        $this->queues[$queue] = array_filter(
            $this->queues[$queue],
            static fn (array $job) : bool => ($job['id'] ?? '') !== $jobId,
        );
    }

    public function clear(string $queue) : void
    {
        unset($this->queues[$queue]);
    }
}

final class ArrayQueue implements QueueBroker
{
    private array $queues = [];

    public function __construct(private readonly string $prefix = 'queue:') {}

    public function push(string $queue, array $job) : void
    {
        $key = $this->prefix . $queue;
        $this->queues[$key][] = $job;
    }

    public function pop(string $queue) : ?array
    {
        $key = $this->prefix . $queue;

        return array_shift($this->queues[$key] ?? []) ?? null;
    }

    public function size(string $queue) : int
    {
        $key = $this->prefix . $queue;

        return count($this->queues[$key] ?? []);
    }

    public function remove(string $queue, string $jobId) : void
    {
        $key = $this->prefix . $queue;
        if (! isset($this->queues[$key])) {
            return;
        }

        $this->queues[$key] = array_filter(
            $this->queues[$key],
            static fn (array $job) : bool => ($job['id'] ?? '') !== $jobId,
        );
    }

    public function clear(string $queue) : void
    {
        unset($this->queues[$this->prefix . $queue]);
    }
}
