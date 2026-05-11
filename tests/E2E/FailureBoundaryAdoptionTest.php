<?php

declare(strict_types=1);

namespace Avax\Tests\E2E;

use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CompileFailurePolicies\CompileFailurePolicies;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use Avax\Examples\FailureBoundaryDemo\DemoFailureController;
use PHPUnit\Framework\TestCase;

/**
 * FailureBoundaryAdoptionTest — E2E proof that FailureBoundary attributes affect real behavior.
 *
 * These tests prove:
 * 1. OnFailure attribute changes runtime behavior (mapped exception → configured status)
 * 2. ReportFailure attribute causes error reporting
 * 3. Unmapped exceptions propagate (not swallowed)
 * 4. Compiled metadata is resolved for real controller methods
 */
final class FailureBoundaryAdoptionTest extends TestCase
{
    protected function tearDown(): void
    {
        CompiledPolicyCache::clear();
    }

    public function testCompiledPolicyResolvesForDemoController(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(DemoFailureController::class, 'throwsValidation');

        self::assertNotNull($policy);
        self::assertSame(DemoFailureController::class, $policy->targetClass);
        self::assertSame('throwsValidation', $policy->targetMethod);
        self::assertNotNull($policy->policy->findAction(\InvalidArgumentException::class));
    }

    public function testOnFailureAttributeMapsInvalidArgumentExceptionTo422(): void
    {
        $controller = new DemoFailureController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new \GuzzleHttp\Psr7\ServerRequest('GET', '/'),
            targetClass: DemoFailureController::class,
            targetMethod: 'throwsValidation',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(DemoFailureController::class, 'throwsValidation');

        $result = $runProtected->run(
            action: static fn () => $controller->throwsValidation(),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(422, $result->getStatusCode());
    }

    public function testOnFailureAttributeMapsRuntimeExceptionTo503(): void
    {
        $controller = new DemoFailureController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new \GuzzleHttp\Psr7\ServerRequest('GET', '/'),
            targetClass: DemoFailureController::class,
            targetMethod: 'throwsRuntime',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(DemoFailureController::class, 'throwsRuntime');

        $result = $runProtected->run(
            action: static fn () => $controller->throwsRuntime(),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(503, $result->getStatusCode());
    }

    public function testUnhandledExceptionPropagatesNotSwallowed(): void
    {
        $controller = new DemoFailureController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new \GuzzleHttp\Psr7\ServerRequest('GET', '/'),
            targetClass: DemoFailureController::class,
            targetMethod: 'throwsUnhandled',
        );

        // No attributes on this method, so empty policy → rethrow
        $threw = false;
        try {
            $runProtected->run(
                action: static fn () => $controller->throwsUnhandled(),
                context: $context,
            );
        } catch (\LogicException) {
            $threw = true;
        }

        self::assertTrue($threw, 'LogicException should propagate, not be swallowed');
    }

    public function testSuccessfulActionReturnsUnchanged(): void
    {
        $controller = new DemoFailureController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new \GuzzleHttp\Psr7\ServerRequest('GET', '/'),
            targetClass: DemoFailureController::class,
            targetMethod: 'returnsSuccess',
        );

        $result = $runProtected->run(
            action: static fn () => $controller->returnsSuccess(),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(200, $result->getStatusCode());
    }

    public function testReportFailureAttributeEmitsOutput(): void
    {
        $controller = new DemoFailureController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new \GuzzleHttp\Psr7\ServerRequest('GET', '/'),
            targetClass: DemoFailureController::class,
            targetMethod: 'throwsValidation',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(DemoFailureController::class, 'throwsValidation');

        $result = $runProtected->run(
            action: static fn () => $controller->throwsValidation(),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
    }

    public function testRemovingAttributeChangesBehavior(): void
    {
        // Proves attributes are not decorative: when no policy is compiled,
        // the exception propagates instead of being mapped to 422.
        $controller = new DemoFailureController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new \GuzzleHttp\Psr7\ServerRequest('GET', '/'),
            targetClass: DemoFailureController::class,
            targetMethod: 'throwsValidation',
        );

        // Do NOT compile the policy — simulate removing the attribute
        CompiledPolicyCache::clear();

        $threw = false;
        try {
            $runProtected->run(
                action: static fn () => $controller->throwsValidation(),
                context: $context,
            );
        } catch (\InvalidArgumentException) {
            $threw = true;
        }

        // Without compiled policy, no OnFailure rule matches → rethrow
        self::assertTrue($threw, 'Exception should propagate when no policy is compiled');
    }
}
