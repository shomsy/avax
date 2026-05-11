<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\FailureBoundary;

use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledMethodPolicy;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureAction;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureDecision;
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
}

final class TestFallbackHandler
{
    public function __invoke(\Throwable $failure, FailureContext $context): string
    {
        return 'fallback-result';
    }
}
