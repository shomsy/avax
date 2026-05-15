<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\RunRecoveryAction\RunRecoveryAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\Builders\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Proves V5.6-Y5: RecoverWith recovery handler is enforced at runtime.
 *
 * @no-named-arguments
 */
final class RecoverWithEnforcementTest extends TestCase
{
    #[Test]
    public function recoverWithHandlerIsInvokedOnFailure() : void
    {
        $policy   = new FailurePolicy(recoverWithClass: RecoverWithTestRecoveryHandler::class);
        $compiled = new CompiledMethodPolicy(
            targetClass : 'RecoveryTestController',
            targetMethod: 'handle',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('RecoveryTestController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action : static fn () => throw new RuntimeException('primary failed'),
            context: FailureContext::forConsole('RecoveryTestController', 'handle'),
        );

        self::assertSame('recovered:primary failed', $result);
    }

    #[Test]
    public function recoverWithTakesPrecedenceOverFallback() : void
    {
        // When both RecoverWith and Fallback are configured, RecoverWith wins
        $policy   = new FailurePolicy(
            recoverWithClass: RecoverWithTestRecoveryHandler::class,
            fallbackClass   : RecoverWithTestFallbackHandler::class,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass : 'PrecedenceController',
            targetMethod: 'handle',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('PrecedenceController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action : static fn () => throw new RuntimeException('test'),
            context: FailureContext::forConsole('PrecedenceController', 'handle'),
        );

        // Recovery handler should be used, not fallback
        self::assertSame('recovered:test', $result);
    }

    #[Test]
    public function recoveryHandlerReceivesFailureAndContext() : void
    {
        $policy   = new FailurePolicy(recoverWithClass: RecoverWithCapturingRecoveryHandler::class);
        $compiled = new CompiledMethodPolicy(
            targetClass : 'CaptureController',
            targetMethod: 'handle',
            policy      : $policy,
            checksum    : 'test',
            sourceMtime : 0,
            compiledAt  : time(),
        );
        CompiledPolicyCache::put('CaptureController::handle', $compiled);

        $boundary        = (new BuildFailureBoundary())->build();
        $expectedMessage = 'capture-this-failure';

        $boundary->run(
            action : static fn () => throw new RuntimeException($expectedMessage),
            context: FailureContext::forConsole('CaptureController', 'handle'),
        );

        self::assertSame($expectedMessage, RecoverWithCapturingRecoveryHandler::$lastFailureMessage);
        self::assertSame('CaptureController', RecoverWithCapturingRecoveryHandler::$lastTargetClass);
    }

    #[Test]
    public function recoveryClassNotFoundThrowsRuntimeException() : void
    {
        $action  = new RunRecoveryAction();
        $policy  = new FailurePolicy(recoverWithClass: 'NonExistentRecoveryHandler');
        $context = FailureContext::forConsole('Test', 'handle');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Recovery class not found: NonExistentRecoveryHandler');

        $action->execute(new RuntimeException('test'), $context, $policy);
    }

    #[Test]
    public function recoveryWithoutInvokeThrowsRuntimeException() : void
    {
        $action  = new RunRecoveryAction();
        $policy  = new FailurePolicy(recoverWithClass: RecoverWithInvalidRecoveryHandler::class);
        $context = FailureContext::forConsole('Test', 'handle');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Recovery class must implement __invoke');

        $action->execute(new RuntimeException('test'), $context, $policy);
    }

    protected function tearDown() : void
    {
        CompiledPolicyCache::clear();
    }
}

/** Test recovery handler that formats the failure message */
final class RecoverWithTestRecoveryHandler
{
    public function __invoke(Throwable $failure, mixed $context) : string
    {
        return 'recovered:' . $failure->getMessage();
    }
}

/** Test fallback handler (should not be used when RecoverWith is present) */
final class RecoverWithTestFallbackHandler
{
    public function __invoke(Throwable $failure, mixed $context) : string
    {
        return 'fallback:' . $failure->getMessage();
    }
}

/** Recovery handler that captures the failure details for assertions */
final class RecoverWithCapturingRecoveryHandler
{
    public static string $lastFailureMessage = '';
    public static string $lastTargetClass    = '';

    public function __invoke(Throwable $failure, FailureContext $context) : string
    {
        self::$lastFailureMessage = $failure->getMessage();
        self::$lastTargetClass    = $context->targetClass;

        return 'captured';
    }
}

/** Invalid recovery handler without __invoke */
final class RecoverWithInvalidRecoveryHandler {}
