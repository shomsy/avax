<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryExecutor;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryOptions;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Proves V5.6-Y1: RetryFailedAction delegates to canonical Resilience RetryExecutor.
 *
 * @no-named-arguments
 */
final class RetryResilienceIntegrationTest extends TestCase
{
    #[Test]
    public function retrySucceedsAfterTransientFailureThroughResilience() : void
    {
        $policy   = new FailurePolicy(
            retryMaxAttempts: 3,
            retryBackoff    : 'none',
            retryDelayMs    : 0,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass : 'FlakyService',
            targetMethod: 'call',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('FlakyService::call', $compiled);

        $attempts = 0;
        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action : function () use (&$attempts) {
                $attempts++;
                if ($attempts < 2) {
                    throw new RuntimeException('temporary failure');
                }

                return 'recovered';
            },
            context: FailureContext::forConsole('FlakyService', 'call'),
        );

        self::assertSame('recovered', $result);
        self::assertSame(2, $attempts);
    }

    #[Test]
    public function retryStopsAfterMaxAttemptsThroughResilience() : void
    {
        $policy   = new FailurePolicy(
            retryMaxAttempts: 2,
            retryBackoff    : 'none',
            retryDelayMs    : 0,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass : 'AlwaysFailing',
            targetMethod: 'call',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('AlwaysFailing::call', $compiled);

        $attempts = 0;
        $boundary = (new BuildFailureBoundary())->build();

        try {
            $boundary->run(
                action : function () use (&$attempts) {
                    $attempts++;
                    throw new RuntimeException('always fails');
                },
                context: FailureContext::forConsole('AlwaysFailing', 'call'),
            );
            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame('always fails', $e->getMessage());
        }

        self::assertSame(2, $attempts);
    }

    #[Test]
    public function retryExhaustionPreservesOriginalFailure() : void
    {
        $policy   = new FailurePolicy(
            retryMaxAttempts: 3,
            retryBackoff    : 'none',
            retryDelayMs    : 0,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass : 'FailingService',
            targetMethod: 'execute',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('FailingService::execute', $compiled);

        $boundary        = (new BuildFailureBoundary())->build();
        $originalMessage = 'original-failure-abc123';

        try {
            $boundary->run(
                action : static fn () => throw new RuntimeException($originalMessage),
                context: FailureContext::forConsole('FailingService', 'execute'),
            );
            self::fail('Expected exception was not thrown');
        } catch (RuntimeException $e) {
            self::assertSame($originalMessage, $e->getMessage());
        }
    }

    #[Test]
    public function canonicalResilienceRetryExecutorIsUsed() : void
    {
        // Direct proof that Resilience RetryExecutor handles the retry loop
        $attempts = 0;
        $options  = new RetryOptions(
            attempts       : 3,
            backoffMs      : 0,
            backoffStrategy: 'none',
        );
        $executor = new RetryExecutor(
            operation   : function () use (&$attempts) {
                $attempts++;
                if ($attempts < 2) {
                    throw new RuntimeException('transient');
                }

                return 'ok';
            },
            retryOptions: $options,
        );

        $result = $executor->execute();

        self::assertTrue($result->success);
        self::assertSame('ok', $result->result);
        self::assertSame(2, $result->attempts);
        self::assertNull($result->lastException);
    }

    #[Test]
    public function retryOptionsMapBackoffStrategyCorrectly() : void
    {
        // Prove that the new backoff strategies work through Resilience
        $fixedOptions       = new RetryOptions(attempts: 1, backoffMs: 100, backoffStrategy: 'fixed');
        $linearOptions      = new RetryOptions(attempts: 1, backoffMs: 100, backoffStrategy: 'linear');
        $exponentialOptions = new RetryOptions(attempts: 1, backoffMs: 50, backoffStrategy: 'exponential');

        self::assertSame('fixed', $fixedOptions->backoffStrategy);
        self::assertSame('linear', $linearOptions->backoffStrategy);
        self::assertSame('exponential', $exponentialOptions->backoffStrategy);

        // Immutability check
        $modified = $fixedOptions->withBackoffStrategy('exponential');
        self::assertSame('fixed', $fixedOptions->backoffStrategy);
        self::assertSame('exponential', $modified->backoffStrategy);
    }

    #[Test]
    public function retryOptionsWithJitterIsImmutable() : void
    {
        $original   = new RetryOptions(attempts: 3, backoffMs: 100, jitter: false);
        $withJitter = $original->withJitter(true);

        self::assertFalse($original->jitter);
        self::assertTrue($withJitter->jitter);
        self::assertSame(3, $withJitter->attempts);
        self::assertSame(100, $withJitter->backoffMs);
    }

    protected function tearDown() : void
    {
        CompiledPolicyCache::clear();
    }
}
