<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Driver;

use Avax\Components\Operations\Queue\System\Capabilities\Job;
use Avax\Components\Operations\Queue\System\Capabilities\QueueDriverInterface;
use DateTimeInterface;

/**
 * Sync queue driver - executes jobs immediately.
 */
final class SyncDriver implements QueueDriverInterface
{
    /** @var array<string, array{job: string, data: array, time: int}> */
    private array $pending = [];
    private int $counter = 0;

    public function later(DateTimeInterface $delay, string $job, array $data = []) : string
    {
        // Sync driver ignores delay - executes immediately
        return $this->push($job, $data);
    }

    public function push(string $job, array $data = []) : string
    {
        $id = 'sync-' . (++$this->counter);

        // Execute immediately
        if (class_exists($job)) {
            $instance = new $job();
            if (method_exists($instance, 'handle')) {
                $instance->handle($data);
            }
        }

        $this->pending[$id] = [
            'job'  => $job,
            'data' => $data,
            'time' => time(),
        ];

        return $id;
    }

    public function pop() : ?Job
    {
        if (empty($this->pending)) {
            return null;
        }

        $item = array_shift($this->pending);

        return new SyncJob(
            id  : 'sync-' . $this->counter,
            job : $item['job'],
            data: $item['data'],
        );
    }

    public function size(string $queue = 'default') : int
    {
        return count($this->pending);
    }

    public function bulk(array $jobs, string $queue = 'default') : void
    {
        foreach ($jobs as $job) {
            if (is_string($job)) {
                $this->push($job);
            } elseif (is_array($job) && isset($job['job'])) {
                $this->push($job['job'], $job['data'] ?? []);
            }
        }
    }

    public function clear() : void
    {
        $this->pending = [];
    }

    /** @return array<string, array{job: string, data: array, time: int}> */
    public function getPending() : array
    {
        return $this->pending;
    }
}

/**
 * Sync job implementation.
 */
final readonly class SyncJob implements Job
{
    public function __construct(
        private string $id,
        private string $job,
        private array $data,
        private int   $attempts = 1,
    ) {}

    public function getId() : string
    {
        return $this->id;
    }

    public function getPayload() : array
    {
        return [
            'job'  => $this->job,
            'data' => $this->data,
        ];
    }

    public function attempts() : int
    {
        return $this->attempts;
    }

    public function release(int $delay = 0) : void
    {
        // No-op for sync driver
    }

    public function delete() : void
    {
        // No-op for sync driver
    }
}
