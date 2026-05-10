<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;
use Avax\Components\Operations\Queue\System\PublicSurface\Dispatcher;
use Avax\Components\Operations\Queue\System\PublicSurface\JobId;
use Avax\Components\Operations\Queue\System\PublicSurface\JobResult;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DispatcherTest extends TestCase
{
    private Dispatcher $dispatcher;

    private JobRegistry $jobRegistry;

    private QueueTestBroker $queueBroker;

    #[Test]
    public function it_dispatches_job_to_queue() : void
    {
        $jobDefinition = new JobDefinition(
            handler: 'SendEmail',
            payload: ['to' => 'user@example.com'],
            queue  : 'emails',
        );

        $jobId = $this->dispatcher->dispatch($jobDefinition);

        self::assertInstanceOf(JobId::class, $jobId);
        self::assertStringStartsWith('job-', $jobId->value);
        self::assertSame('emails', $jobId->queue);
        self::assertCount(1, $this->queueBroker->pushedJobs);
        self::assertSame('emails', $this->queueBroker->pushedJobs[0]['queue']);
        self::assertSame('SendEmail', $this->queueBroker->pushedJobs[0]['job']['handler']);
    }

    #[Test]
    public function it_dispatches_sync_and_returns_result() : void
    {
        $this->jobRegistry->register(
            'SyncHandler',
            static fn (array $payload) : array => ['processed' => $payload['data']],
        );

        $jobDefinition = new JobDefinition(
            handler: 'SyncHandler',
            payload: ['data' => 'test-value'],
        );

        $result = $this->dispatcher->dispatchSync($jobDefinition);

        self::assertInstanceOf(JobResult::class, $result);
        self::assertTrue($result->success);
        self::assertSame(['processed' => 'test-value'], $result->result);
    }

    #[Test]
    public function it_dispatches_sync_failure() : void
    {
        $this->jobRegistry->register(
            'FailingSync',
            static fn () : never => throw new RuntimeException('sync failed'),
        );

        $jobDefinition = new JobDefinition(handler: 'FailingSync');

        $result = $this->dispatcher->dispatchSync($jobDefinition);

        self::assertFalse($result->success);
        self::assertSame('sync failed', $result->error);
    }

    #[Test]
    public function it_schedules_job_for_later() : void
    {
        $jobDefinition = new JobDefinition(
            handler: 'DelayedTask',
            queue  : 'scheduled',
        );

        $delay = new DateTimeImmutable('+1 hour');

        $jobId = $this->dispatcher->later($jobDefinition, $delay);

        self::assertInstanceOf(JobId::class, $jobId);
        self::assertSame('scheduled', $jobId->queue);
        self::assertCount(1, $this->queueBroker->pushedJobs);
        self::assertArrayHasKey('executeAt', $this->queueBroker->pushedJobs[0]['job']);
        self::assertSame($delay->getTimestamp(), $this->queueBroker->pushedJobs[0]['job']['executeAt']);
    }

    #[Test]
    public function it_dispatches_bulk_jobs() : void
    {
        $jobs = [
            new JobDefinition(handler: 'JobA', queue: 'bulk'),
            new JobDefinition(handler: 'JobB', queue: 'bulk'),
            new JobDefinition(handler: 'JobC', queue: 'bulk'),
        ];

        $jobIds = $this->dispatcher->bulk($jobs);

        self::assertCount(3, $jobIds);
        self::assertCount(3, $this->queueBroker->pushedJobs);

        foreach ($jobIds as $jobId) {
            self::assertInstanceOf(JobId::class, $jobId);
        }
    }

    #[Test]
    public function it_skips_non_job_items_in_bulk() : void
    {
        $jobs = [
            new JobDefinition(handler: 'ValidJob'),
            'not_a_job',
            123,
        ];

        $jobIds = $this->dispatcher->bulk($jobs);

        self::assertCount(1, $jobIds);
        self::assertCount(1, $this->queueBroker->pushedJobs);
    }

    #[Test]
    public function it_returns_empty_array_for_empty_bulk() : void
    {
        $jobIds = $this->dispatcher->bulk([]);

        self::assertSame([], $jobIds);
    }

    #[Test]
    public function it_uses_default_queue_when_not_specified() : void
    {
        $jobDefinition = new JobDefinition(handler: 'DefaultQueue');

        $jobId = $this->dispatcher->dispatch($jobDefinition);

        self::assertSame('default', $jobId->queue);
        self::assertSame('default', $this->queueBroker->pushedJobs[0]['queue']);
    }

    #[Test]
    public function it_generates_unique_job_ids() : void
    {
        $job1 = new JobDefinition(handler: 'Job1');
        $job2 = new JobDefinition(handler: 'Job2');

        $id1 = $this->dispatcher->dispatch($job1);
        $id2 = $this->dispatcher->dispatch($job2);

        self::assertNotSame($id1->value, $id2->value);
    }

    protected function setUp() : void
    {
        $this->jobRegistry = new JobRegistry();
        $this->queueBroker = new QueueTestBroker();
        $dispatchJob       = new DispatchJob(jobRegistry: $this->jobRegistry);
        $this->dispatcher  = new Dispatcher(dispatchJob: $dispatchJob, queueBroker: $this->queueBroker);
    }
}

final class QueueTestBroker implements QueueBroker
{
    /** @var array<int, array{queue: string, job: array}> */
    public array $pushedJobs = [];

    /** @var array<string, list<array>> */
    private array $queues = [];

    public function push(string $queue, array $job) : void
    {
        $this->pushedJobs[]     = ['queue' => $queue, 'job' => $job];
        $this->queues[$queue][] = $job;
    }

    public function pop(string $queue) : ?array
    {
        if (empty($this->queues[$queue])) {
            return null;
        }

        return array_shift($this->queues[$queue]);
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

        $this->queues[$queue] = array_values(array_filter(
                                                 $this->queues[$queue],
                                                 static fn (array $job) : bool => ($job['id'] ?? null) !== $jobId,
                                             ));
    }

    public function clear(string $queue) : void
    {
        unset($this->queues[$queue]);
    }
}
