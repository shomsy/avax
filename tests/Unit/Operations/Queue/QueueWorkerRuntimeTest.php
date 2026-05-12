<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\MemoryQueue\MemoryQueue;
use Avax\Components\Operations\Queue\System\Flows\RunWorkerLoop\RunWorkerLoop;
use PHPUnit\Framework\TestCase;

final class QueueWorkerRuntimeTest extends TestCase
{
    // -- MemoryQueue tests

    public function test_push_and_pop() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['action' => 'send_email', 'to' => 'user@example.com']);

        self::assertSame(1, $queue->size('default'));

        $job = $queue->pop('default');

        self::assertIsArray($job);
        self::assertSame('send_email', $job['job']['action']);
    }

    public function test_pop_empty_queue_returns_null() : void
    {
        $queue = new MemoryQueue();

        self::assertNull($queue->pop('default'));
    }

    public function test_size() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['id' => '1']);
        $queue->push('default', ['id' => '2']);
        $queue->push('other', ['id' => '3']);

        self::assertSame(2, $queue->size('default'));
        self::assertSame(1, $queue->size('other'));
    }

    public function test_remove_job() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['id' => 'job-1']);
        $queue->push('default', ['id' => 'job-2']);

        $queue->remove('default', 'job-1');

        self::assertSame(1, $queue->size('default'));
        $job = $queue->pop('default');
        self::assertIsArray($job);
        self::assertSame('job-2', $job['job']['id']);
    }

    public function test_clear_queue() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['id' => '1']);
        $queue->push('default', ['id' => '2']);

        $queue->clear('default');

        self::assertSame(0, $queue->size('default'));
    }

    public function test_clear_all() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['id' => '1']);
        $queue->push('other', ['id' => '2']);

        $queue->clearAll();

        self::assertSame(0, $queue->size('default'));
        self::assertSame(0, $queue->size('other'));
    }

    // -- Dead letter tests

    public function test_failed_job_goes_to_dead_letter_after_max_attempts() : void
    {
        $queue = new MemoryQueue(defaultMaxAttempts: 2);
        $queue->push('default', ['id' => 'failing-job', 'max_attempts' => 2]);

        $job = $queue->pop('default');
        self::assertIsArray($job);
        $queue->retry('default', $job, 'first failure');

        self::assertSame(1, $queue->size('default'));

        $job = $queue->pop('default');
        self::assertIsArray($job);
        $queue->retry('default', $job, 'second failure');

        self::assertSame(0, $queue->size('default'));
        self::assertSame(1, $queue->deadLetterCount('default'));

        $dead = $queue->deadLetters('default');
        self::assertSame('second failure', $dead[0]['reason']);
    }

    public function test_failed_job_requeues_under_max_attempts() : void
    {
        $queue = new MemoryQueue(defaultMaxAttempts: 3);
        $queue->push('default', ['id' => 'retry-job', 'max_attempts' => 3]);

        $job = $queue->pop('default');
        self::assertIsArray($job);
        $queue->retry('default', $job, 'first failure');

        self::assertSame(1, $queue->size('default'));
        self::assertSame(0, $queue->deadLetterCount('default'));

        $job = $queue->pop('default');
        self::assertIsArray($job);
        self::assertSame(1, $job['attempts']);
    }

    public function test_dead_letter_count_across_queues() : void
    {
        $queue = new MemoryQueue(defaultMaxAttempts: 1);
        $queue->push('q1', ['id' => '1', 'max_attempts' => 1]);
        $queue->push('q2', ['id' => '2', 'max_attempts' => 1]);

        $job = $queue->pop('q1');
        self::assertIsArray($job);
        $queue->retry('q1', $job, 'fail');

        $job = $queue->pop('q2');
        self::assertIsArray($job);
        $queue->retry('q2', $job, 'fail');

        self::assertSame(2, $queue->deadLetterCount());
    }

    public function test_clear_dead_letters() : void
    {
        $queue = new MemoryQueue(defaultMaxAttempts: 1);
        $queue->push('default', ['id' => '1', 'max_attempts' => 1]);

        $job = $queue->pop('default');
        self::assertIsArray($job);
        $queue->retry('default', $job, 'fail');

        self::assertSame(1, $queue->deadLetterCount());
        $queue->clearDeadLetters();
        self::assertSame(0, $queue->deadLetterCount());
    }

    // -- RunWorkerLoop tests

    public function test_worker_processes_jobs() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['value' => 1]);
        $queue->push('default', ['value' => 2]);

        $processed = [];
        $worker = new RunWorkerLoop(
            broker: $queue,
            handler: static function (array $job) use (&$processed) : void {
                $processed[] = $job['value'];
            },
        );

        $result = $worker->runOnce('default');

        self::assertSame(2, $result['processed']);
        self::assertSame([1, 2], $processed);
    }

    public function test_worker_handles_failure_and_retries() : void
    {
        $queue = new MemoryQueue(defaultMaxAttempts: 1);
        $queue->push('default', ['id' => 'bad', 'max_attempts' => 1]);

        $worker = new RunWorkerLoop(
            broker: $queue,
            handler: static fn () => throw new \RuntimeException('boom'),
        );

        $result = $worker->runOnce('default');

        self::assertSame(1, $result['failed']);
        self::assertSame(1, $result['dead_lettered']);
    }

    public function test_worker_respects_max_jobs() : void
    {
        $queue = new MemoryQueue();
        $queue->push('default', ['v' => 1]);
        $queue->push('default', ['v' => 2]);
        $queue->push('default', ['v' => 3]);

        $worker = new RunWorkerLoop(
            broker: $queue,
            handler: static fn () => null,
        );

        $result = $worker->runOnce('default', maxJobs: 2);

        self::assertSame(2, $result['processed']);
        self::assertSame(1, $queue->size('default'));
    }

    public function test_worker_stops_on_empty_queue() : void
    {
        $queue = new MemoryQueue();

        $worker = new RunWorkerLoop(
            broker: $queue,
            handler: static fn () => null,
        );

        $result = $worker->runOnce('default');

        self::assertSame(0, $result['processed']);
        self::assertSame(0, $result['failed']);
    }
}
