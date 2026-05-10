<?php

declare(strict_types=1);

namespace Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsSchema;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\FailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\PdoFailedJobsStore;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\MemoryQueue\MemoryQueue;
use Avax\Components\Operations\Queue\System\Flows\RunWorkerLoop\RunWorkerLoop;
use Avax\Framework\System\Capabilities\Queue\RegisterQueueCommands;
use PDO;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\WithoutMemoryLeak;
use PHPUnit\Framework\TestCase;

#[CoversClass(RegisterQueueCommands::class)]
#[CoversClass(FailedJobsStore::class)]
final class QueueCommandsTest extends TestCase
{
    private string $dbFile;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->dbFile = sys_get_temp_dir().'/avax_queue_test_'.uniqid().'.sqlite';
        $this->pdo = new PDO('sqlite:'.$this->dbFile);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(FailedJobsSchema::createTableSql());
    }

    protected function tearDown(): void
    {
        if (file_exists($this->dbFile)) {
            unlink($this->dbFile);
        }
    }

    public function test_queue_work_once_processes_one_job(): void
    {
        $commands = (new RegisterQueueCommands(dbPath: $this->dbFile))();
        self::assertArrayHasKey('queue:work', $commands);

        $workCommand = $commands['queue:work'];
        $output = $workCommand(['--once']);

        self::assertStringContainsString('Queue Worker', $output);
        self::assertStringContainsString('No jobs in queue', $output);
    }

    public function test_queue_failed_lists_no_failed_jobs(): void
    {
        $commands = (new RegisterQueueCommands(dbPath: $this->dbFile))();
        $failedCommand = $commands['queue:failed'];
        $output = $failedCommand([]);

        self::assertStringContainsString('No failed jobs', $output);
    }

    public function test_failed_job_is_recorded_and_listed(): void
    {
        $store = new PdoFailedJobsStore($this->pdo);
        $store->record(
            queue: 'default',
            payload: ['handler' => 'TestJob', 'data' => ['key' => 'value']],
            reason: 'RuntimeException: Test failure',
            failedAt: '2026-05-10 12:00:00',
        );

        $commands = (new RegisterQueueCommands(dbPath: $this->dbFile))();
        $failedCommand = $commands['queue:failed'];
        $output = $failedCommand([]);

        self::assertStringContainsString('Test failure', $output);
        self::assertStringContainsString('Total: 1 failed job', $output);
    }

    public function test_queue_retries_failed_job(): void
    {
        $store = new PdoFailedJobsStore($this->pdo);
        $store->record(
            queue: 'default',
            payload: ['handler' => 'TestJob', 'payload' => []],
            reason: 'RuntimeException: Test failure',
            failedAt: '2026-05-10 12:00:00',
        );

        $commands = (new RegisterQueueCommands(dbPath: $this->dbFile))();
        $retryCommand = $commands['queue:retry'];
        $output = $retryCommand(['--id=1']);

        self::assertStringContainsString('retried', $output);
        self::assertSame(0, $store->count());
    }

    public function test_queue_retry_missing_id_fails_gracefully(): void
    {
        $commands = (new RegisterQueueCommands(dbPath: $this->dbFile))();
        $retryCommand = $commands['queue:retry'];
        $output = $retryCommand(['--id=999']);

        self::assertStringContainsString('not found', $output);
    }

    public function test_queue_flush_failed_clears_all(): void
    {
        $store = new PdoFailedJobsStore($this->pdo);
        $store->record('default', ['handler' => 'Job1'], 'Exception 1', '2026-05-10 12:00:00');
        $store->record('default', ['handler' => 'Job2'], 'Exception 2', '2026-05-10 12:01:00');

        $commands = (new RegisterQueueCommands(dbPath: $this->dbFile))();
        $flushCommand = $commands['queue:flush-failed'];
        $output = $flushCommand([]);

        self::assertStringContainsString('Flushed 2 failed job', $output);
        self::assertSame(0, $store->count());
    }

    public function test_queue_work_processes_job_through_worker_loop(): void
    {
        $broker = new MemoryQueue();
        $broker->push('default', [
            'handler' => self::class.'@fakeHandler',
            'payload' => ['processed' => true],
            'max_attempts' => 3,
        ]);

        $processed = false;
        $worker = new RunWorkerLoop(
            broker: $broker,
            handler: static function (array $job) use (&$processed): void {
                $processed = true;
            },
            sleepMicroseconds: 10_000,
        );

        $result = $worker->runOnce(queue: 'default', maxJobs: 1);

        self::assertSame(1, $result['processed']);
        self::assertTrue($processed);
    }
}
