<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary\DeadLetterQueueIntegrationTest;

use Avax\Components\Operations\Queue\System\Capabilities\Queue\FailedJobs\InMemoryFailedJobsStore;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\SendFailureToDeadLetter\SendFailureToDeadLetter;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Proves V5.6-Y2: DeadLetter uses canonical Queue FailedJobsStore transport.
 *
 * @no-named-arguments
 */
final class DeadLetterQueueIntegrationTest extends TestCase
{
    #[Test]
    public function deadLetterRecordsThroughFailedJobsStore() : void
    {
        $store      = new InMemoryFailedJobsStore();
        $deadLetter = new SendFailureToDeadLetter($store);

        $policy  = new FailurePolicy(deadLetterQueue: 'test_queue');
        $context = FailureContext::forQueue('TestHandler', 'handle');
        $failure = new RuntimeException('store test');

        $result = $deadLetter->send($failure, $context, $policy);

        self::assertNull($result->value);
        self::assertCount(1, $store->list('test_queue'));

        $job = $store->list('test_queue')[0];
        self::assertStringContainsString('RuntimeException', $job['reason']);
        self::assertStringContainsString('store test', $job['reason']);
        self::assertSame('test_queue', $job['queue'] ?? 'test_queue');
    }

    #[Test]
    public function deadLetterFallsBackToErrorLogWithoutStore() : void
    {
        $deadLetter = new SendFailureToDeadLetter(null);

        $policy  = new FailurePolicy(deadLetterQueue: 'fallback_queue');
        $context = FailureContext::forQueue('FallbackHandler', 'handle');
        $failure = new RuntimeException('fallback test');

        // Should not throw — error_log fallback completes
        $result = $deadLetter->send($failure, $context, $policy);

        self::assertNull($result->value);
    }

    #[Test]
    public function deadLetterThroughBoundaryWithFailedJobsStore() : void
    {
        $store    = new InMemoryFailedJobsStore();
        $policy   = new FailurePolicy(deadLetterQueue: 'boundary_queue');
        $compiled = new CompiledMethodPolicy(
            targetClass : 'BoundaryDeadLetterHandler',
            targetMethod: 'handle',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('BoundaryDeadLetterHandler::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build(failedJobsStore: $store);

        $result = $boundary->run(
            action : static fn () => throw new RuntimeException('boundary dead letter'),
            context: FailureContext::forQueue('BoundaryDeadLetterHandler', 'handle'),
        );

        self::assertNull($result);
        self::assertCount(1, $store->list('boundary_queue'));

        $job = $store->list('boundary_queue')[0];
        self::assertStringContainsString('boundary dead letter', $job['reason']);
    }

    #[Test]
    public function deadLetterEnvelopeContainsRequiredFields() : void
    {
        $store      = new InMemoryFailedJobsStore();
        $deadLetter = new SendFailureToDeadLetter($store);

        $policy  = new FailurePolicy(deadLetterQueue: 'envelope_test');
        $context = FailureContext::forHttp(
            new ServerRequest('POST', 'http://localhost/test'),
            'EnvelopeController',
            'handle',
        );
        $failure = new RuntimeException('envelope fields test');

        $deadLetter->send($failure, $context, $policy);

        $job = $store->list('envelope_test')[0];
        self::assertArrayHasKey('reason', $job);
        self::assertArrayHasKey('failed_at', $job);
        // The envelope payload is stored merged with reason/failed_at
        self::assertArrayHasKey('type', $job);
        self::assertSame('dead_letter', $job['type']);
        self::assertSame(1, $job['version']);
        self::assertArrayHasKey('failure', $job);
        self::assertSame('RuntimeException', $job['failure']['class']);
        self::assertSame('envelope fields test', $job['failure']['message']);
        self::assertArrayHasKey('context', $job);
        self::assertSame('http', $job['context']['kind']);
        self::assertSame('EnvelopeController', $job['context']['target_class']);
        self::assertSame('handle', $job['context']['target_method']);
    }

    protected function tearDown() : void
    {
        CompiledPolicyCache::clear();
    }
}
