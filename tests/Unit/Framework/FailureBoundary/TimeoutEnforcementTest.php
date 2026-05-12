<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Components\Operations\Resilience\System\Foundation\Failure\OperationTimedOut\OperationTimedOut;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\EnforceTimeout\EnforceTimeout;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves V5.6-Y4: Timeout is enforced using Resilience Timeout.
 *
 * @no-named-arguments
 */
final class TimeoutEnforcementTest extends TestCase
{
    #[Test]
    public function fastActionCompletesWithinTimeout() : void
    {
        $policy   = new FailurePolicy(timeoutMs: 5000);
        $compiled = new CompiledMethodPolicy(
            targetClass : 'FastController',
            targetMethod: 'handle',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('FastController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action : static fn () => 'fast-ok',
            context: FailureContext::forConsole('FastController', 'handle'),
        );

        self::assertSame('fast-ok', $result);
    }

    #[Test]
    public function slowActionTriggersTimeoutException() : void
    {
        $policy   = new FailurePolicy(timeoutMs: 10);
        $compiled = new CompiledMethodPolicy(
            targetClass : 'SlowController',
            targetMethod: 'handle',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('SlowController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(OperationTimedOut::class);

        $boundary->run(
            action : static function () {
                usleep(50_000); // 50ms

                return 'should-not-reach';
            },
            context: FailureContext::forConsole('SlowController', 'handle'),
        );
    }

    #[Test]
    public function noTimeoutWhenNotConfigured() : void
    {
        $enforce = new EnforceTimeout();

        $result = $enforce->run(
            action   : static fn () => 'no-timeout',
            timeoutMs: null,
        );

        self::assertSame('no-timeout', $result);
    }

    #[Test]
    public function enforceTimeoutDelegatesToResilienceTimeout() : void
    {
        $enforce = new EnforceTimeout();

        // 5ms timeout, action takes 50ms
        $this->expectException(OperationTimedOut::class);

        $enforce->run(
            action   : static function () {
                usleep(50_000);

                return 'too-slow';
            },
            timeoutMs: 5,
        );
    }

    #[Test]
    public function zeroTimeoutMeansNoTimeout() : void
    {
        $enforce = new EnforceTimeout();

        $result = $enforce->run(
            action   : static fn () => 'zero-timeout-ok',
            timeoutMs: 0,
        );

        self::assertSame('zero-timeout-ok', $result);
    }

    protected function tearDown() : void
    {
        CompiledPolicyCache::clear();
    }
}
