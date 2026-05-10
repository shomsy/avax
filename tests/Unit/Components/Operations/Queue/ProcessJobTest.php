<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Queue;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobRegistry;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Process\ProcessJob;
use Avax\Components\Operations\Queue\System\PublicSurface\JobResult;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ProcessJobTest extends TestCase
{
    private ProcessJob $processJob;

    private JobRegistry $jobRegistry;

    private QueueBroker $queueBroker;

    #[Test]
    public function it_processes_job_successfully() : void
    {
        $this->jobRegistry->register(
            'SuccessHandler',
            static fn (array $payload) : string => 'processed: ' . ($payload['name'] ?? 'unknown'),
        );

        $jobData = [
            'handler'     => 'SuccessHandler',
            'payload'     => ['name' => 'test-job'],
            'queue'       => 'default',
            'id'          => 'job-001',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
        ];

        $removeCalled = false;
        $this->queueBroker
            ->method('remove')
            ->willReturnCallback(static function () use (&$removeCalled) : void {
                $removeCalled = true;
            });

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertInstanceOf(JobResult::class, $result);
        self::assertTrue($result->success);
        self::assertSame('processed: test-job', $result->result);
        self::assertSame(1, $result->attempts);
        self::assertNull($result->error);
        self::assertTrue($removeCalled);
    }

    #[Test]
    public function it_processes_job_on_first_attempt() : void
    {
        $this->jobRegistry->register('FirstTry', static fn () : string => 'done');

        $jobData = [
            'handler'     => 'FirstTry',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-first',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
        ];

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertTrue($result->success);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_tracks_attempt_count() : void
    {
        $this->jobRegistry->register('RetryHandler', static fn () : string => 'success');

        $jobData = [
            'handler'     => 'RetryHandler',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-retry',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
            'attempts'    => 2,
        ];

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertTrue($result->success);
        self::assertSame(3, $result->attempts);
    }

    #[Test]
    public function it_fails_when_max_attempts_exceeded() : void
    {
        $jobData = [
            'handler'     => 'AnyHandler',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-exceeded',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
            'attempts'    => 3,
        ];

        $removeCalled = false;
        $this->queueBroker
            ->method('remove')
            ->willReturnCallback(static function () use (&$removeCalled) : void {
                $removeCalled = true;
            });

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertFalse($result->success);
        self::assertSame('Max attempts exceeded', $result->error);
        self::assertSame(4, $result->attempts);
        self::assertTrue($removeCalled);
    }

    #[Test]
    public function it_requeues_on_failure_with_retry_delay() : void
    {
        $this->jobRegistry->register(
            'FailingHandler',
            static fn () : never => throw new RuntimeException('Handler failed'),
        );

        $jobData = [
            'handler'     => 'FailingHandler',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-requeue',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 10,
            'attempts'    => 0,
        ];

        $pushedJob = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue, array $job) use (&$pushedJob) : void {
                $pushedJob = ['queue' => $queue, 'job' => $job];
            });

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertFalse($result->success);
        self::assertSame('Handler failed', $result->error);
        self::assertSame(1, $result->attempts);
        self::assertNotNull($pushedJob);
        self::assertSame('default', $pushedJob['queue']);
        self::assertSame(1, $pushedJob['job']['attempts']);
        self::assertArrayHasKey('executeAt', $pushedJob['job']);
        self::assertGreaterThan(time(), $pushedJob['job']['executeAt']);
    }

    #[Test]
    public function it_does_not_requeue_on_last_attempt() : void
    {
        $this->jobRegistry->register(
            'LastAttemptFailing',
            static fn () : never => throw new RuntimeException('Final failure'),
        );

        $jobData = [
            'handler'     => 'LastAttemptFailing',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-last',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 10,
            'attempts'    => 2,
        ];

        $pushCalled = false;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function () use (&$pushCalled) : void {
                $pushCalled = true;
            });

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertFalse($result->success);
        self::assertSame(3, $result->attempts);
        self::assertFalse($pushCalled);
    }

    #[Test]
    public function it_does_not_requeue_when_retry_delay_is_zero() : void
    {
        $this->jobRegistry->register(
            'NoDelayFailing',
            static fn () : never => throw new RuntimeException('No delay'),
        );

        $jobData = [
            'handler'     => 'NoDelayFailing',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-nodelay',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
        ];

        $pushCalled = false;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function () use (&$pushCalled) : void {
                $pushCalled = true;
            });

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertFalse($result->success);
        self::assertFalse($pushCalled);
    }

    #[Test]
    public function it_returns_failure_for_unregistered_handler() : void
    {
        $jobData = [
            'handler'     => 'NonExistentHandler',
            'payload'     => [],
            'queue'       => 'default',
            'id'          => 'job-unreg',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
        ];

        $result = $this->processJob->process($jobData, $this->queueBroker);

        self::assertFalse($result->success);
        self::assertStringContainsString('No handler registered for job', $result->error);
        self::assertSame(1, $result->attempts);
    }

    #[Test]
    public function it_processes_all_jobs_from_queue() : void
    {
        $this->jobRegistry->register('ProcessAll', static fn () : string => 'processed');

        $callCount = 0;
        $this->queueBroker
            ->method('pop')
            ->willReturnCallback(static function () use (&$callCount) : ?array {
                $callCount++;
                if ($callCount <= 3) {
                    return [
                        'handler'     => 'ProcessAll',
                        'payload'     => [],
                        'queue'       => 'default',
                        'id'          => 'job-' . $callCount,
                        'maxAttempts' => 3,
                        'timeout'     => 60,
                        'retryDelay'  => 0,
                    ];
                }

                return null;
            });

        $results = $this->processJob->processAll($this->queueBroker, 'default');

        self::assertCount(3, $results);
        foreach ($results as $result) {
            self::assertTrue($result->success);
        }
    }

    #[Test]
    public function it_processes_empty_queue() : void
    {
        $this->queueBroker
            ->method('pop')
            ->willReturn(null);

        $results = $this->processJob->processAll($this->queueBroker, 'empty');

        self::assertSame([], $results);
    }

    #[Test]
    public function it_processes_queue_with_limit() : void
    {
        $this->jobRegistry->register('LimitedProcess', static fn () : int => 1);

        $popCount = 0;
        $this->queueBroker
            ->method('pop')
            ->willReturnCallback(static function () use (&$popCount) : ?array {
                $popCount++;

                return [
                    'handler'     => 'LimitedProcess',
                    'payload'     => [],
                    'queue'       => 'limited',
                    'id'          => 'job-' . $popCount,
                    'maxAttempts' => 3,
                    'timeout'     => 60,
                    'retryDelay'  => 0,
                ];
            });

        $results = $this->processJob->processQueue('limited', $this->queueBroker, limit: 5);

        self::assertCount(5, $results);
        self::assertSame(5, $popCount);
    }

    #[Test]
    public function it_stops_processing_when_queue_is_empty_before_limit() : void
    {
        $this->jobRegistry->register('PartialProcess', static fn () : int => 1);

        $popCount = 0;
        $this->queueBroker
            ->method('pop')
            ->willReturnCallback(static function () use (&$popCount) : ?array {
                $popCount++;

                if ($popCount <= 2) {
                    return [
                        'handler'     => 'PartialProcess',
                        'payload'     => [],
                        'queue'       => 'partial',
                        'id'          => 'job-' . $popCount,
                        'maxAttempts' => 3,
                        'timeout'     => 60,
                        'retryDelay'  => 0,
                    ];
                }

                return null;
            });

        $results = $this->processJob->processQueue('partial', $this->queueBroker, limit: 10);

        self::assertCount(2, $results);
        self::assertSame(3, $popCount);
    }

    #[Test]
    public function it_defers_future_jobs_and_breaks_loop() : void
    {
        $this->jobRegistry->register('DeferredProcess', static fn () : int => 1);

        $futureTime = time() + 300;
        $pushCalled = false;

        $this->queueBroker
            ->method('pop')
            ->willReturn([
                             'handler'     => 'DeferredProcess',
                             'payload'     => [],
                             'queue'       => 'deferred',
                             'id'          => 'job-future',
                             'maxAttempts' => 3,
                             'timeout'     => 60,
                             'retryDelay'  => 0,
                             'executeAt'   => $futureTime,
                         ]);

        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function () use (&$pushCalled) : void {
                $pushCalled = true;
            });

        $results = $this->processJob->processQueue('deferred', $this->queueBroker, limit: 10);

        self::assertSame([], $results);
        self::assertTrue($pushCalled);
    }

    #[Test]
    public function it_processes_future_job_when_execute_time_has_passed() : void
    {
        $this->jobRegistry->register('ReadyProcess', static fn () : string => 'ready');

        $pastTime = time() - 10;
        $this->queueBroker
            ->method('pop')
            ->willReturnCallback(static function () use ($pastTime) : ?array {
                static $called = false;
                if ($called) {
                    return null;
                }
                $called = true;

                return [
                    'handler'     => 'ReadyProcess',
                    'payload'     => [],
                    'queue'       => 'ready',
                    'id'          => 'job-ready',
                    'maxAttempts' => 3,
                    'timeout'     => 60,
                    'retryDelay'  => 0,
                    'executeAt'   => $pastTime,
                ];
            });

        $results = $this->processJob->processQueue('ready', $this->queueBroker, limit: 10);

        self::assertCount(1, $results);
        self::assertTrue($results[0]->success);
        self::assertSame('ready', $results[0]->result);
    }

    #[Test]
    public function it_uses_default_queue_when_job_queue_is_null() : void
    {
        $this->jobRegistry->register('NullQueueHandler', static fn () : string => 'ok');

        $jobData = [
            'handler'     => 'NullQueueHandler',
            'payload'     => [],
            'id'          => 'job-nullqueue',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
        ];

        $removedQueue = null;
        $this->queueBroker
            ->method('remove')
            ->willReturnCallback(static function (string $queue) use (&$removedQueue) : void {
                $removedQueue = $queue;
            });

        $this->processJob->process($jobData, $this->queueBroker);

        self::assertSame('default', $removedQueue);
    }

    #[Test]
    public function it_removes_job_from_correct_queue_on_success() : void
    {
        $this->jobRegistry->register('CustomQueueSuccess', static fn () : string => 'ok');

        $jobData = [
            'handler'     => 'CustomQueueSuccess',
            'payload'     => [],
            'queue'       => 'custom-queue',
            'id'          => 'job-custom',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 0,
        ];

        $removedQueue = null;
        $removedId    = null;
        $this->queueBroker
            ->method('remove')
            ->willReturnCallback(static function (string $queue, string $jobId) use (&$removedQueue, &$removedId) : void {
                $removedQueue = $queue;
                $removedId    = $jobId;
            });

        $this->processJob->process($jobData, $this->queueBroker);

        self::assertSame('custom-queue', $removedQueue);
        self::assertSame('job-custom', $removedId);
    }

    #[Test]
    public function it_removes_job_on_max_attempts_exceeded() : void
    {
        $jobData = [
            'handler'     => 'MaxExceededHandler',
            'payload'     => [],
            'queue'       => 'max-queue',
            'id'          => 'job-max',
            'maxAttempts' => 2,
            'timeout'     => 60,
            'retryDelay'  => 5,
            'attempts'    => 2,
        ];

        $removedQueue = null;
        $removedId    = null;
        $this->queueBroker
            ->method('remove')
            ->willReturnCallback(static function (string $queue, string $jobId) use (&$removedQueue, &$removedId) : void {
                $removedQueue = $queue;
                $removedId    = $jobId;
            });

        $this->processJob->process($jobData, $this->queueBroker);

        self::assertSame('max-queue', $removedQueue);
        self::assertSame('job-max', $removedId);
    }

    #[Test]
    public function it_requeues_to_correct_queue_on_failure() : void
    {
        $this->jobRegistry->register(
            'CustomQueueFail',
            static fn () : never => throw new RuntimeException('fail'),
        );

        $jobData = [
            'handler'     => 'CustomQueueFail',
            'payload'     => [],
            'queue'       => 'retry-queue',
            'id'          => 'job-retryq',
            'maxAttempts' => 3,
            'timeout'     => 60,
            'retryDelay'  => 15,
        ];

        $pushedQueue = null;
        $this->queueBroker
            ->method('push')
            ->willReturnCallback(static function (string $queue) use (&$pushedQueue) : void {
                $pushedQueue = $queue;
            });

        $this->processJob->process($jobData, $this->queueBroker);

        self::assertSame('retry-queue', $pushedQueue);
    }

    protected function setUp() : void
    {
        $this->jobRegistry = new JobRegistry();
        $this->queueBroker = $this->createMock(QueueBroker::class);
        $this->processJob  = new ProcessJob(jobRegistry: $this->jobRegistry);
    }
}
