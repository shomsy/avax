<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\Flows\RunWorkerLoop;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\MemoryQueue\MemoryQueue;
use Closure;
use Throwable;

/**
 * Worker loop that continuously processes jobs from a queue.
 *
 * Supports max attempts, dead letter routing, and custom job handlers.
 */
final readonly class RunWorkerLoop
{
    /**
     * @param Closure(array<string, mixed>): void $handler  Function that processes a job payload
     */
    public function __construct(
        private MemoryQueue $broker,
        private Closure $handler,
        private int $sleepMicroseconds = 100_000,
    ) {}

    /**
     * Process jobs until the queue is empty or $maxJobs is reached.
     *
     * @return array{processed: int, failed: int, dead_lettered: int}
     */
    public function runOnce(string $queue = 'default', int|null $maxJobs = null) : array
    {
        $processed = 0;
        $failed = 0;
        $deadLettered = 0;

        while ($maxJobs === null || $processed + $failed < $maxJobs) {
            $job = $this->broker->pop($queue);

            if ($job === null) {
                break;
            }

            try {
                ($this->handler)($job['job']);
                $processed++;
            } catch (Throwable $e) {
                $failed++;

                if ($this->isDeadLetter($job)) {
                    $this->broker->retry($queue, $job, $e->getMessage());
                    $deadLettered++;
                }
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
            'dead_lettered' => $deadLettered,
        ];
    }

    /**
     * @param array<string, mixed> $job
     */
    private function isDeadLetter(array $job) : bool
    {
        $attempts = ($job['attempts'] ?? 0) + 1;
        $max = $job['max_attempts'] ?? 3;

        return $attempts >= $max;
    }
}
