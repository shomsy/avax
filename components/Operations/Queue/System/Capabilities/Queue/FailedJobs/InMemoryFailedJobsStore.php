<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs;

/**
 * In-memory failed jobs store for testing and lightweight runtimes.
 *
 * Instance-scoped — does not leak between tests when fresh instances are created.
 * Reset-safe via clear().
 */
final class InMemoryFailedJobsStore implements FailedJobsStore
{
    /** @var array<string, list<array<string, mixed>>> */
    private array $failedJobs = [];

    public function record(string $queue, array $payload, string $reason, string $failedAt) : void
    {
        $this->failedJobs[$queue][] = [
            ...$payload,
            'reason'    => $reason,
            'failed_at' => $failedAt,
        ];
    }

    public function count(string $queue = '') : int
    {
        return count($this->list($queue));
    }

    public function list(string $queue = '') : array
    {
        if ($queue !== '') {
            return $this->failedJobs[$queue] ?? [];
        }

        $all = [];

        foreach ($this->failedJobs as $queueJobs) {
            array_push($all, ...$queueJobs);
        }

        return $all;
    }

    public function clear(string $queue = '') : void
    {
        if ($queue !== '') {
            unset($this->failedJobs[$queue]);
        } else {
            $this->failedJobs = [];
        }
    }
}
