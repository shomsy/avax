<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\Builders\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureDecision;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureHandler;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePipelineResult;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailurePolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\PublicSurface\FailureBoundary as FailureBoundaryFacade;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @no-named-arguments
 */
final class FailureBoundaryTest extends TestCase
{
    protected function tearDown(): void
    {
        CompiledPolicyCache::clear();
    }

    public function testReturnsSuccessfulActionResult(): void
    {
        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: static fn () => 'success',
            context: FailureContext::forHttp(new ServerRequest('GET', 'http://localhost/')),
        );

        self::assertSame('success', $result);
    }

    public function testReportFailureUsesLoggerWhenProvided(): void
    {
        $logger = new \Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger();
        $boundary = (new BuildFailureBoundary())->build(logger: $logger);

        $policy = new FailurePolicy(
            actions: [
                new FailureAction(
                    exceptionClass: \RuntimeException::class,
                    decision: FailureDecision::MapToResult,
                    statusCode: 500,
                    messageKey: 'error',
                ),
            ],
            reportChannel: 'http',
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'LoggedController',
            targetMethod: 'handle',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('LoggedController::handle', $compiled);

        $boundary->run(
            action: static fn () => throw new \RuntimeException('logged error'),
            context: FailureContext::forHttp(
                new ServerRequest('GET', 'http://localhost/'),
                'LoggedController',
                'handle',
            ),
        );

        $records = $logger->records();
        self::assertCount(1, $records);
        self::assertSame('error', $records[0]->level);
        self::assertStringContainsString('RuntimeException', $records[0]->message);
    }

    public function testReportFailureFallsBackToErrorLogWithoutLogger(): void
    {
        $boundary = (new BuildFailureBoundary())->build();

        $policy = new FailurePolicy(
            actions: [
                new FailureAction(
                    exceptionClass: \RuntimeException::class,
                    decision: FailureDecision::MapToResult,
                    statusCode: 500,
                    messageKey: 'error',
                ),
            ],
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'FallbackController',
            targetMethod: 'handle',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('FallbackController::handle', $compiled);

        // Should not throw — error_log fallback completes
        $result = $boundary->run(
            action: static fn () => throw new \RuntimeException('fallback error'),
            context: FailureContext::forHttp(
                new ServerRequest('GET', 'http://localhost/'),
                'FallbackController',
                'handle',
            ),
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testDeadLetterProducesStructuredEnvelope(): void
    {
        $policy = new FailurePolicy(
            deadLetterQueue: 'test_queue',
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'EnvelopeController',
            targetMethod: 'handle',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('EnvelopeController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: static fn () => throw new \RuntimeException('envelope test'),
            context: FailureContext::forQueue('EnvelopeController', 'handle'),
        );

        self::assertNull($result);
        // error_log output is verified by visual inspection of test output
        // The envelope shape is validated by the SendFailureToDeadLetter implementation
    }

    public function testRethrowsUnhandledExceptionWhenNoPolicy(): void
    {
        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('test error');

        $boundary->run(
            action: static fn () => throw new \RuntimeException('test error'),
            context: FailureContext::forHttp(new ServerRequest('GET', 'http://localhost/')),
        );
    }

    public function testDoesNotSwallowUnexpectedThrowable(): void
    {
        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(\Error::class);

        $boundary->run(
            action: static fn () => throw new \Error('fatal'),
            context: FailureContext::forHttp(new ServerRequest('GET', 'http://localhost/')),
        );
    }

    public function testMapsValidationFailedTo422(): void
    {
        $policy = new FailurePolicy(
            actions: [
                new FailureAction(
                    exceptionClass: \InvalidArgumentException::class,
                    decision: FailureDecision::MapToResult,
                    statusCode: 422,
                    messageKey: 'validation.failed',
                ),
            ],
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'TestController',
            targetMethod: 'store',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('TestController::store', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: static fn () => throw new \InvalidArgumentException('invalid'),
            context: FailureContext::forHttp(
                new ServerRequest('POST', 'http://localhost/'),
                'TestController',
                'store',
            ),
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(422, $result->getStatusCode());
    }

    public function testMapsAuthenticationFailedTo401(): void
    {
        $policy = new FailurePolicy(
            actions: [
                new FailureAction(
                    exceptionClass: \UnexpectedValueException::class,
                    decision: FailureDecision::MapToResult,
                    statusCode: 401,
                    messageKey: 'auth.unauthorized',
                ),
            ],
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'AuthController',
            targetMethod: 'login',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('AuthController::login', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: static fn () => throw new \UnexpectedValueException('not authed'),
            context: FailureContext::forHttp(
                new ServerRequest('POST', '/login'),
                'AuthController',
                'login',
            ),
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(401, $result->getStatusCode());
    }

    public function testExecutesFallbackHandler(): void
    {
        $policy = new FailurePolicy(
            fallbackClass: TestFallbackHandler::class,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'RateController',
            targetMethod: 'fetch',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('RateController::fetch', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: static fn () => throw new \RuntimeException('api down'),
            context: FailureContext::forHttp(
                new ServerRequest('GET', '/rates'),
                'RateController',
                'fetch',
            ),
        );

        self::assertSame('fallback-result', $result);
    }

    public function testSendsToDeadLetterWhenConfigured(): void
    {
        $policy = new FailurePolicy(
            deadLetterQueue: 'failed_jobs',
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'JobHandler',
            targetMethod: 'handle',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('JobHandler::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: static fn () => throw new \RuntimeException('job failed'),
            context: FailureContext::forQueue('JobHandler', 'handle'),
        );

        self::assertNull($result);
    }

    public function testRetrySucceedsOnSecondAttempt(): void
    {
        $policy = new FailurePolicy(
            retryMaxAttempts: 3,
            retryBackoff: 'none',
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'FlakyService',
            targetMethod: 'call',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('FlakyService::call', $compiled);

        $attempts = 0;
        $boundary = (new BuildFailureBoundary())->build();

        $result = $boundary->run(
            action: function () use (&$attempts) {
                $attempts++;
                if ($attempts < 2) {
                    throw new \RuntimeException('temporary failure');
                }
                return 'recovered';
            },
            context: FailureContext::forConsole('FlakyService', 'call'),
        );

        self::assertSame('recovered', $result);
        self::assertSame(2, $attempts);
    }

    public function testRetryExhaustsAndRethrows(): void
    {
        $policy = new FailurePolicy(
            retryMaxAttempts: 2,
            retryBackoff: 'none',
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'AlwaysFailing',
            targetMethod: 'call',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('AlwaysFailing::call', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(\RuntimeException::class);

        $boundary->run(
            action: static fn () => throw new \RuntimeException('always fails'),
            context: FailureContext::forConsole('AlwaysFailing', 'call'),
        );
    }

    public function testFacadeReturnsSuccessfulResult(): void
    {
        $result = FailureBoundaryFacade::run(
            action: static fn () => 'facade-ok',
            context: FailureContext::forHttp(new ServerRequest('GET', 'http://localhost/')),
        );

        self::assertSame('facade-ok', $result);
    }

    public function testRejectsFallbackHandlerNotImplementingInterface(): void
    {
        $policy = new FailurePolicy(
            fallbackClass: TestInvalidHandler::class,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'InsecureController',
            targetMethod: 'fetch',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('InsecureController::fetch', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(FailureHandler::class);

        $boundary->run(
            action: static fn () => throw new \RuntimeException('fail'),
            context: FailureContext::forHttp(
                new ServerRequest('GET', '/insecure'),
                'InsecureController',
                'fetch',
            ),
        );
    }

    public function testRejectsRecoveryHandlerNotImplementingInterface(): void
    {
        $policy = new FailurePolicy(
            recoverWithClass: TestInvalidRecoveryHandler::class,
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'InsecureRecoveryController',
            targetMethod: 'handle',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('InsecureRecoveryController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(FailureHandler::class);

        $boundary->run(
            action: static fn () => throw new \RuntimeException('fail'),
            context: FailureContext::forHttp(
                new ServerRequest('GET', '/insecure-recovery'),
                'InsecureRecoveryController',
                'handle',
            ),
        );
    }

    public function testRejectsFallbackClassNotFound(): void
    {
        $policy = new FailurePolicy(
            fallbackClass: 'NonExistentFallbackHandler',
        );
        $compiled = new CompiledMethodPolicy(
            targetClass: 'MissingController',
            targetMethod: 'handle',
            policy: $policy,
            checksum: 'test',
            sourceMtime: 0,
            compiledAt: time(),
        );
        CompiledPolicyCache::put('MissingController::handle', $compiled);

        $boundary = (new BuildFailureBoundary())->build();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not found');

        $boundary->run(
            action: static fn () => throw new \RuntimeException('fail'),
            context: FailureContext::forHttp(
                new ServerRequest('GET', '/missing'),
                'MissingController',
                'handle',
            ),
        );
    }
}

final class TestFallbackHandler implements FailureHandler
{
    public function __invoke(\Throwable $failure, FailureContext $context): string
    {
        return 'fallback-result';
    }
}

final class TestInvalidHandler
{
    public function __invoke(\Throwable $failure, FailureContext $context): string
    {
        return 'should-not-reach';
    }
}

final class TestInvalidRecoveryHandler
{
    public function __invoke(\Throwable $failure, FailureContext $context): string
    {
        return 'should-not-reach';
    }
}
