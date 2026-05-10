<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\DatabaseQueue\DatabaseQueue;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\DatabaseQueue\DatabaseQueueSchema;
use PDO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class DatabaseQueueTest extends TestCase
{
    private PDO $pdo;
    private DatabaseQueue $queue;

    protected function setUp() : void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        DatabaseQueueSchema::createTable($this->pdo, 'test_queue_jobs');
        DatabaseQueueSchema::createDeadLetterTable($this->pdo, 'test_queue_jobs_dead');

        $this->queue = new DatabaseQueue($this->pdo, 'test_queue_jobs');
    }

    #[Test]
    public function push_adds_job_to_queue() : void
    {
        $this->queue->push('default', ['job' => 'TestJob', 'data' => ['foo' => 'bar']]);

        self::assertSame(1, $this->queue->size('default'));
    }

    #[Test]
    public function pop_returns_first_job() : void
    {
        $this->queue->push('default', ['job' => 'TestJob', 'data' => ['id' => 1]]);
        $this->queue->push('default', ['job' => 'TestJob', 'data' => ['id' => 2]]);

        $job = $this->queue->pop('default');

        self::assertNotNull($job);
        self::assertSame('TestJob', $job['job']['job']);
    }

    #[Test]
    public function pop_returns_null_when_empty() : void
    {
        self::assertNull($this->queue->pop('default'));
    }

    #[Test]
    public function pop_marks_job_as_reserved() : void
    {
        $this->queue->push('default', ['job' => 'TestJob']);

        $this->queue->pop('default');

        // Second pop should return null since job is reserved
        self::assertNull($this->queue->pop('default'));
    }

    #[Test]
    public function remove_deletes_job() : void
    {
        $this->queue->push('default', ['job' => 'TestJob']);
        $job = $this->queue->pop('default');

        self::assertNotNull($job);
        $this->queue->remove('default', $job['id']);

        self::assertSame(0, $this->queue->size('default'));
    }

    #[Test]
    public function clear_removes_all_jobs() : void
    {
        $this->queue->push('default', ['job' => 'Job1']);
        $this->queue->push('default', ['job' => 'Job2']);

        $this->queue->clear('default');

        self::assertSame(0, $this->queue->size('default'));
    }

    #[Test]
    public function release_makes_job_visible_again() : void
    {
        $this->queue->push('default', ['job' => 'TestJob']);
        $job = $this->queue->pop('default');

        self::assertNotNull($job);
        $this->queue->release($job['id']);

        $released = $this->queue->pop('default');
        self::assertNotNull($released);
        self::assertSame($job['id'], $released['id']);
    }

    #[Test]
    public function release_increments_attempts() : void
    {
        $this->queue->push('default', ['job' => 'TestJob']);
        $job = $this->queue->pop('default');

        self::assertNotNull($job);
        self::assertSame(0, $job['attempts']);

        $this->queue->release($job['id']);
        $released = $this->queue->pop('default');

        self::assertNotNull($released);
        self::assertSame(1, $released['attempts']);
    }

    #[Test]
    public function dead_letter_moves_job_to_dead_table() : void
    {
        $this->queue->push('default', ['job' => 'TestJob']);
        $job = $this->queue->pop('default');

        self::assertNotNull($job);
        $this->queue->deadLetter($job['id'], 'max attempts exceeded');

        // Original queue should be empty
        self::assertSame(0, $this->queue->size('default'));

        // Dead letter table should have the job
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_queue_jobs_dead");
        self::assertNotFalse($stmt);
        self::assertSame(1, (int) $stmt->fetchColumn());
    }

    #[Test]
    public function separate_queues_are_isolated() : void
    {
        $this->queue->push('emails', ['job' => 'SendEmail']);
        $this->queue->push('reports', ['job' => 'GenerateReport']);

        self::assertSame(1, $this->queue->size('emails'));
        self::assertSame(1, $this->queue->size('reports'));

        $emailJob = $this->queue->pop('emails');
        self::assertNotNull($emailJob);
        self::assertSame('SendEmail', $emailJob['job']['job'] ?? null);
    }
}
