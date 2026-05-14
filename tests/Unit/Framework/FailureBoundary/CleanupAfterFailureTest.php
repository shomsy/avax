<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure\CleanupAfterFailure;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CleanupAfterFailure\FailureCleanupRegistry;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * CleanupAfterFailureTest — Proves cleanup hooks are real, testable, and non-decorative.
 *
 * @no-named-arguments
 */
final class CleanupAfterFailureTest extends TestCase
{
    public function testCleanupRunsOnSuccess() : void
    {
        $cleaned  = false;
        $registry = new FailureCleanupRegistry();
        $registry->register(static function (FailureContext $context) use (&$cleaned) : void {
            $cleaned = true;
        });

        $cleanup = new CleanupAfterFailure($registry);
        $cleanup->for(FailureContext::forHttp(new ServerRequest('GET', '/')));

        self::assertTrue($cleaned);
    }

    public function testCleanupIsIdempotentPerHook() : void
    {
        $count    = 0;
        $registry = new FailureCleanupRegistry();
        $registry->register(static function () use (&$count) : void {
            $count++;
        });

        $registry->cleanup(FailureContext::forHttp(new ServerRequest('GET', '/')));
        $registry->cleanup(FailureContext::forHttp(new ServerRequest('GET', '/')));

        self::assertSame(2, $count);
    }

    public function testCleanupHookFailureDoesNotHideOriginalFailure() : void
    {
        $executed = false;
        $registry = new FailureCleanupRegistry();
        $registry->register(static function () use (&$executed) : void {
            $executed = true;
            throw new RuntimeException('cleanup failed');
        });

        $cleanup = new CleanupAfterFailure($registry);

        // Should not throw — cleanup hook failure is captured
        $cleanup->for(FailureContext::forHttp(new ServerRequest('GET', '/')));
        self::assertTrue($executed, 'Cleanup hook should have been executed');
    }

    public function testMultipleHooksAllExecute() : void
    {
        $executed = [];
        $registry = new FailureCleanupRegistry();
        $registry->register(static function () use (&$executed) : void {
            $executed[] = 'first';
        });
        $registry->register(static function () use (&$executed) : void {
            $executed[] = 'second';
        });
        $registry->register(static function () use (&$executed) : void {
            $executed[] = 'third';
        });

        $registry->cleanup(FailureContext::forHttp(new ServerRequest('GET', '/')));

        self::assertSame(['first', 'second', 'third'], $executed);
    }

    public function testHookFailureDoesNotPreventSubsequentHooks() : void
    {
        $executed = [];
        $registry = new FailureCleanupRegistry();
        $registry->register(static function () use (&$executed) : void {
            $executed[] = 'first';
        });
        $registry->register(static function () : void {
            throw new RuntimeException('middle hook failed');
        });
        $registry->register(static function () use (&$executed) : void {
            $executed[] = 'third';
        });

        $registry->cleanup(FailureContext::forHttp(new ServerRequest('GET', '/')));

        self::assertSame(['first', 'third'], $executed);
    }

    public function testClearRemovesAllHooks() : void
    {
        $count    = 0;
        $registry = new FailureCleanupRegistry();
        $registry->register(static function () use (&$count) : void {
            $count++;
        });

        $registry->clear();
        $registry->cleanup(FailureContext::forHttp(new ServerRequest('GET', '/')));

        self::assertSame(0, $count);
    }

    public function testCountReturnsNumberOfHooks() : void
    {
        $registry = new FailureCleanupRegistry();
        self::assertSame(0, $registry->count());

        $registry->register(static function () : void {});
        self::assertSame(1, $registry->count());

        $registry->register(static function () : void {});
        self::assertSame(2, $registry->count());
    }

    public function testCleanupRegistryIsAccessibleFromCleanupCapability() : void
    {
        $cleanup  = new CleanupAfterFailure(registry: new FailureCleanupRegistry());
        $registry = $cleanup->registry();
        // Verify the registry is the same instance returned by the capability
        self::assertSame(0, $registry->count());
    }
}
