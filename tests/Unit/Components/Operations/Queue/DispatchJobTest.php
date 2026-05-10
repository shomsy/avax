<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;
use Avax\Components\Operations\Queue\System\PublicSurface\JobId;
use Avax\Components\Operations\Queue\System\PublicSurface\JobResult;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DispatchJobTest extends TestCase
{
    private DispatchJob $dispatchJob;

    private JobRegistry $jobRegistry;

    private QueueBroker $queueBroker;

    #[Test]
    public function it_dispatches_sync_successfully() : void
    {
        $this->jobRegistry->register(
            'SyncHandler',
            static fn (array $payload) : array => ['processed' => $payload['data']],
        );

        $jobDefinition = new JobDefinition(
            handler: 'SyncHandler',
            payload: ['data' => 'test-value'],
        );

        $result = $this->dispatchJob->dispatchSync($jobDefinition);

        self::assertInstanceOf(JobResult::class, $result);
        self::assertTrue($result->success);
        self::assertSame(['processed' => 'test-value'], $result->result);
        self::assertNull($result->error);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_returns_failure_on_sync_handler_exception() : void
    {
        $this->jobRegistry->register(
            'FailingSyncHandler',
            static fn () : never => throw new RuntimeException('Sync handler failed'),
        );

        $jobDefinition = new JobDefinition(handler: 'FailingSyncHandler');

        $result = $this->dispatchJob->dispatchSync($jobDefinition);

        self::assertInstanceOf(JobResult::class, $result);
        self::assertFalse($result->success);
        self::assertSame('Sync handler failed', $result->error);
        self::assertNull($result->result);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_returns_failure_on_unregistered_handler() : void
    {
        $jobDefinition = new JobDefinition(handler: 'NonExistentHandler');

        $result = $this->dispatchJob->dispatchSync($jobDefinition);

        self::assertFalse($result->success);
        self::assertStringContainsString('No handler registered for job', $result->error);
    }

    #[Test]
    public function it_dispatches_job_to_queue_broker() : void
    {
        $jobDefinition = new JobDefinition(
            handler: 'QueuedHandler',
            payload: ['key' => 'value'],
            queue  : 'default',
        );

        $pushedJob = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue, array $job) use (&$pushedJob) : void {
                $pushedJob = ['queue' => $queue, 'job' => $job];
            });

        $jobId = $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);

        self::assertInstanceOf(JobId::class, $jobId);
        self::assertStringStartsWith('job-', $jobId->value);
        self::assertSame('default', $jobId->queue);
        self::assertNotNull($pushedJob);
        self::assertSame('default', $pushedJob['queue']);
        self::assertSame('QueuedHandler', $pushedJob['job']['handler']);
        self::assertSame(['key' => 'value'], $pushedJob['job']['payload']);
        self::assertArrayHasKey('id', $pushedJob['job']);
        self::assertSame($jobId->value, $pushedJob['job']['id']);
    }

    #[Test]
    public function it_dispatches_to_custom_queue() : void
    {
        $jobDefinition = new JobDefinition(
            handler: 'CustomQueueHandler',
            queue  : 'priority-queue',
        );

        $pushedQueue = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue) use (&$pushedQueue) : void {
                $pushedQueue = $queue;
            });

        $jobId = $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);

        self::assertSame('priority-queue', $pushedQueue);
        self::assertSame('priority-queue', $jobId->queue);
    }

    #[Test]
    public function it_uses_default_queue_when_not_specified() : void
    {
        $jobDefinition = new JobDefinition(handler: 'DefaultQueueHandler');

        $pushedQueue = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue) use (&$pushedQueue) : void {
                $pushedQueue = $queue;
            });

        $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);

        self::assertSame('default', $pushedQueue);
    }

    #[Test]
    public function it_schedules_job_for_later_execution() : void
    {
        $jobDefinition = new JobDefinition(
            handler: 'DelayedHandler',
            payload: ['delayed' => true],
            queue  : 'scheduled',
        );

        $delay = new DateTimeImmutable('+30 minutes');

        $pushedJob = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue, array $job) use (&$pushedJob) : void {
                $pushedJob = ['queue' => $queue, 'job' => $job];
            });

        $jobId = $this->dispatchJob->later($jobDefinition, $delay, $this->queueBroker);

        self::assertInstanceOf(JobId::class, $jobId);
        self::assertSame('scheduled', $jobId->queue);
        self::assertNotNull($pushedJob);
        self::assertArrayHasKey('executeAt', $pushedJob['job']);
        self::assertSame($delay->getTimestamp(), $pushedJob['job']['executeAt']);
        self::assertSame('DelayedHandler', $pushedJob['job']['handler']);
    }

    #[Test]
    public function it_dispatches_bulk_jobs() : void
    {
        $jobs = [
            new JobDefinition(handler: 'BulkJob1', queue: 'bulk'),
            new JobDefinition(handler: 'BulkJob2', queue: 'bulk'),
            new JobDefinition(handler: 'BulkJob3', queue: 'bulk'),
        ];

        $pushCount = 0;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function () use (&$pushCount) : void {
                $pushCount++;
            });

        $jobIds = $this->dispatchJob->bulk($jobs, $this->queueBroker);

        self::assertCount(3, $jobIds);
        self::assertSame(3, $pushCount);

        foreach ($jobIds as $jobId) {
            self::assertInstanceOf(JobId::class, $jobId);
            self::assertStringStartsWith('job-', $jobId->value);
        }
    }

    #[Test]
    public function it_skips_non_job_definition_items_in_bulk() : void
    {
        $jobs = [
            new JobDefinition(handler: 'ValidJob1'),
            'not_a_job',
            new JobDefinition(handler: 'ValidJob2'),
            123,
            null,
            new JobDefinition(handler: 'ValidJob3'),
        ];

        $pushCount = 0;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function () use (&$pushCount) : void {
                $pushCount++;
            });

        $jobIds = $this->dispatchJob->bulk($jobs, $this->queueBroker);

        self::assertCount(3, $jobIds);
        self::assertSame(3, $pushCount);
    }

    #[Test]
    public function it_returns_empty_array_for_empty_bulk() : void
    {
        $jobIds = $this->dispatchJob->bulk([], $this->queueBroker);

        self::assertSame([], $jobIds);
    }

    #[Test]
    public function it_includes_job_metadata_in_pushed_data() : void
    {
        $jobDefinition = new JobDefinition(
            handler      : 'MetadataJob',
            payload      : ['item' => 'data'],
            queue        : 'meta',
            maxAttempts  : 5,
            timeout      : 120,
            retryDelay   : 10,
            correlationId: 'corr-meta-001',
        );

        $pushedJob = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue, array $job) use (&$pushedJob) : void {
                $pushedJob = $job;
            });

        $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);

        self::assertNotNull($pushedJob);
        self::assertSame('MetadataJob', $pushedJob['handler']);
        self::assertSame(['item' => 'data'], $pushedJob['payload']);
        self::assertSame('meta', $pushedJob['queue']);
        self::assertSame(5, $pushedJob['maxAttempts']);
        self::assertSame(120, $pushedJob['timeout']);
        self::assertSame(10, $pushedJob['retryDelay']);
        self::assertSame('corr-meta-001', $pushedJob['correlationId']);
        self::assertArrayHasKey('createdAt', $pushedJob);
    }

    #[Test]
    public function it_generates_unique_job_ids_for_each_dispatch() : void
    {
        $jobDefinition = new JobDefinition(handler: 'UniqueJob');

        $jobId1 = $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);
        $jobId2 = $this->dispatchJob->dispatch($jobDefinition, $this->queueBroker);

        self::assertNotSame($jobId1->value, $jobId2->value);
    }

    #[Test]
    public function it_dispatches_sync_with_empty_payload() : void
    {
        $this->jobRegistry->register(
            'EmptyPayloadHandler',
            static fn (array $payload) : array => $payload,
        );

        $jobDefinition = new JobDefinition(handler: 'EmptyPayloadHandler');

        $result = $this->dispatchJob->dispatchSync($jobDefinition);

        self::assertTrue($result->success);
        self::assertSame([], $result->result);
    }

    #[Test]
    public function it_dispatches_sync_handler_returning_scalar() : void
    {
        $this->jobRegistry->register(
            'ScalarHandler',
            static fn () : int => 42,
        );

        $jobDefinition = new JobDefinition(handler: 'ScalarHandler');

        $result = $this->dispatchJob->dispatchSync($jobDefinition);

        self::assertTrue($result->success);
        self::assertSame(42, $result->result);
    }

    protected function setUp() : void
    {
        $this->jobRegistry = new JobRegistry();
        $this->queueBroker = $this->createMock(QueueBroker::class);
        $this->dispatchJob = new DispatchJob(jobRegistry: $this->jobRegistry);
    }
}
