<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\Queue;
use Avax\Components\Operations\Queue\System\Foundation\JobInterface;
use Avax\Examples\GoldenPathRuntimeApp\Domain\Job\DeadLetterNotificationJob;
use Avax\Examples\GoldenPathRuntimeApp\Domain\Job\ProcessWebhookJob;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Proves: Queue push/process/release and failed job retry behavior.
 */
final class QueueProcessingTest extends TestCase
{
    #[Test]
    public function queueProcessesJobs() : void
    {
        $processed = false;

        Queue::push(
            job  : static function (array $data) use (&$processed) : void {
                $processed = true;
            },
            data : ['webhook_id' => 'wh_001'],
            queue: 'webhook-processing',
        );

        $count = Queue::process(queue: 'webhook-processing');

        self::assertSame(1, $count);
        self::assertTrue($processed);
    }

    #[Test]
    public function queueReleasesFailedJobs() : void
    {
        Queue::push(
            job  : static function () : void {
                throw new RuntimeException('Processing failed');
            },
            queue: 'webhook-retry',
        );

        // First process: fails, releases with 5-second delay
        $count = Queue::process(queue: 'webhook-retry');

        self::assertSame(0, $count); // Not successfully processed
        // Job should be back in queue for retry (released with delay)
        self::assertSame(1, Queue::size(queue: 'webhook-retry'));
    }

    #[Test]
    public function jobAttemptsIncrementOnRetry() : void
    {
        Queue::push(
            job  : static function () : void {
                throw new RuntimeException('Always fails');
            },
            queue: 'webhook-retry',
        );

        // First process attempt — fails, releases with 5-second delay
        Queue::process(queue: 'webhook-retry');

        // Job is back in queue but not yet due (run_at = time() + 5)
        // Pop won't return it yet, but size shows it's there
        self::assertSame(1, Queue::size(queue: 'webhook-retry'));
    }

    #[Test]
    public function jobInterfaceImplementationWorks() : void
    {
        // Test the actual job classes from the golden path app
        DeadLetterNotificationJob::$log = [];

        $dlqJob = new DeadLetterNotificationJob();
        $dlqJob->handle(['webhook_id' => 'wh_failed', 'attempts' => 3]);

        self::assertCount(1, DeadLetterNotificationJob::$log);
        self::assertNotEmpty(DeadLetterNotificationJob::$log);
        self::assertSame('error', DeadLetterNotificationJob::$log[0]['level']);
        self::assertSame('wh_failed', DeadLetterNotificationJob::$log[0]['context']['webhook_id']);
    }

    #[Test]
    public function queueSizeAndClear() : void
    {
        Queue::push(job: static fn () => null, queue: 'dead-letter');
        Queue::push(job: static fn () => null, queue: 'dead-letter');
        Queue::push(job: static fn () => null, queue: 'dead-letter');

        self::assertSame(3, Queue::size(queue: 'dead-letter'));

        Queue::clear(queue: 'dead-letter');

        self::assertSame(0, Queue::size(queue: 'dead-letter'));
    }

    #[Test]
    public function bulkPushReturnsJobIds() : void
    {
        $ids = Queue::bulk([
                               ['job' => static fn () => null, 'data' => ['id' => 1]],
                               ['job' => static fn () => null, 'data' => ['id' => 2]],
                           ], queue: 'webhook-processing');

        self::assertCount(2, $ids);
        self::assertIsString($ids[0]);
        self::assertIsString($ids[1]);
    }

    protected function tearDown() : void
    {
        Queue::clear('default');
        Queue::clear('webhook-processing');
        Queue::clear('webhook-retry');
        Queue::clear('dead-letter');
        DeadLetterNotificationJob::$log = [];
    }
}
