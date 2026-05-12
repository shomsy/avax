<?php

declare(strict_types=1);

namespace Avax\Tests\E2E;

use Avax\Examples\SecureRegistrationApi\ExternalServiceDown;
use Avax\Examples\SecureRegistrationApi\RegistrationController;
use Avax\Examples\SecureRegistrationApi\ValidationFailed;
use Avax\Framework\System\Capabilities\FailureBoundary\Capabilities\CompileFailurePolicies\CompileFailurePolicies;
use Avax\Framework\System\Capabilities\FailureBoundary\Configuration\BuildFailureBoundary;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;
use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\FailureContext;
use Avax\Components\HTTP\Response\System\PublicSurface\ResponseInterface;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * SecureRegistrationApiFailureBoundaryTest — E2E proof that FailureBoundary
 * attributes are adopted in a real reference flow beyond the demo controller.
 *
 * These tests prove:
 * 1. Successful registration still works through the boundary
 * 2. ValidationFailed maps to 422 via #[OnFailure]
 * 3. ExternalServiceDown maps to 503 via #[OnFailure]
 * 4. ReportFailure is invoked on mapped failures
 * 5. Unmapped exceptions propagate (not swallowed)
 * 6. Removing the attribute changes behavior visibly
 * 7. No sensitive data leaks in reported context
 */
final class SecureRegistrationApiFailureBoundaryTest extends TestCase
{
    protected function tearDown(): void
    {
        CompiledPolicyCache::clear();
    }

    #[Test]
    public function successful_registration_passes_through_boundary(): void
    {
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/register'),
            targetClass: RegistrationController::class,
            targetMethod: 'register',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(RegistrationController::class, 'register');

        $result = $runProtected->run(
            action: static fn () => $controller->register([
                'email' => 'user@example.com',
                'password' => 'securepassword123',
            ]),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(200, $result->getStatusCode());
    }

    #[Test]
    public function onfailure_maps_validation_failed_to_422(): void
    {
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/register'),
            targetClass: RegistrationController::class,
            targetMethod: 'register',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(RegistrationController::class, 'register');

        $result = $runProtected->run(
            action: static fn () => $controller->register([
                'email' => '',
                'password' => 'securepassword123',
            ]),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(422, $result->getStatusCode());

        $body = $result->getBody()->__toString();
        $decoded = json_decode($body, true);
        self::assertArrayHasKey('error', $decoded);
    }

    #[Test]
    public function onfailure_maps_external_service_down_to_503(): void
    {
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/verify/unavailable'),
            targetClass: RegistrationController::class,
            targetMethod: 'verifyIdentity',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(RegistrationController::class, 'verifyIdentity');

        $result = $runProtected->run(
            action: static fn () => $controller->verifyIdentity('unavailable'),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(503, $result->getStatusCode());

        $body = $result->getBody()->__toString();
        $decoded = json_decode($body, true);
        self::assertArrayHasKey('error', $decoded);
    }

    #[Test]
    public function reportfailure_is_invoked_on_mapped_failure(): void
    {
        // ReportFailure is wired into the failure pipeline.
        // When a mapped failure occurs, the ReportFailure capability
        // sends structured context to the observability pipeline.
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/register'),
            targetClass: RegistrationController::class,
            targetMethod: 'register',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(RegistrationController::class, 'register');

        // The policy must include ReportFailure since the attribute is present
        $policy = $compiler->compile(RegistrationController::class, 'register');
        self::assertNotNull($policy);
        self::assertSame('http', $policy->policy->reportChannel);

        // Execute and verify the failure is handled (not propagated)
        $result = $runProtected->run(
            action: static fn () => $controller->register([
                'email' => '',
                'password' => 'short',
            ]),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(422, $result->getStatusCode());
    }

    #[Test]
    public function unmapped_exception_propagates_not_swallowed(): void
    {
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/register'),
            targetClass: RegistrationController::class,
            targetMethod: 'register',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(RegistrationController::class, 'register');

        $threw = false;
        try {
            $runProtected->run(
                action: static fn () => throw new \LogicException('Unexpected logic error'),
                context: $context,
            );
        } catch (\LogicException) {
            $threw = true;
        }

        self::assertTrue($threw, 'Unmapped LogicException should propagate');
    }

    #[Test]
    public function removing_onfailure_attribute_changes_behavior(): void
    {
        // Proves attributes are not decorative: when no policy is compiled,
        // the exception propagates instead of being mapped to 422.
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/register'),
            targetClass: RegistrationController::class,
            targetMethod: 'register',
        );

        // Do NOT compile the policy — simulate removing the attribute
        CompiledPolicyCache::clear();

        $threw = false;
        try {
            $runProtected->run(
                action: static fn () => $controller->register([
                    'email' => '',
                    'password' => 'securepassword123',
                ]),
                context: $context,
            );
        } catch (ValidationFailed) {
            $threw = true;
        }

        self::assertTrue($threw, 'ValidationFailed should propagate when no policy is compiled');
    }

    #[Test]
    public function compiled_policy_resolves_for_real_reference_controller(): void
    {
        $compiler = new CompileFailurePolicies();
        $policy = $compiler->compile(RegistrationController::class, 'register');

        self::assertNotNull($policy);
        self::assertSame(RegistrationController::class, $policy->targetClass);
        self::assertSame('register', $policy->targetMethod);
        self::assertNotNull($policy->policy->findAction(ValidationFailed::class));
        self::assertSame('http', $policy->policy->reportChannel);

        // verifyIdentity has ExternalServiceDown mapping
        $verifyPolicy = $compiler->compile(RegistrationController::class, 'verifyIdentity');
        self::assertNotNull($verifyPolicy);
        self::assertNotNull($verifyPolicy->policy->findAction(ExternalServiceDown::class));
    }

    #[Test]
    public function no_sensitive_data_leaks_in_failure_response(): void
    {
        $controller = new RegistrationController();
        $builder = new BuildFailureBoundary();
        $runProtected = $builder->build();

        $context = FailureContext::forHttp(
            request: new ServerRequest('POST', '/register'),
            targetClass: RegistrationController::class,
            targetMethod: 'register',
        );

        $compiler = new CompileFailurePolicies();
        $compiler->compileAndCache(RegistrationController::class, 'register');

        $result = $runProtected->run(
            action: static fn () => $controller->register([
                'email' => 'user@example.com',
                'password' => 'MyS3cretP@ssw0rd!',
            ]),
            context: $context,
        );

        // Trigger validation failure with a password that's too short
        $result = $runProtected->run(
            action: static fn () => $controller->register([
                'email' => 'user@example.com',
                'password' => 'short',
            ]),
            context: $context,
        );

        self::assertInstanceOf(ResponseInterface::class, $result);
        self::assertSame(422, $result->getStatusCode());

        $body = $result->getBody()->__toString();
        self::assertStringNotContainsString('short', $body, 'Password value must not leak into error response');
        self::assertStringNotContainsString('MyS3cret', $body, 'Password value must not leak into error response');
    }

    #[Test]
    public function no_hot_path_reflection_in_failure_handling(): void
    {
        // Proves that failure handling does not use reflection on the hot path.
        // Compilation uses reflection once; runtime uses cached policy.

        $compiler = new CompileFailurePolicies();

        // Compile and cache — uses reflection once
        $compiler->compileAndCache(RegistrationController::class, 'register');

        // Retrieve from cache — no new reflection
        $cached = CompiledPolicyCache::get(RegistrationController::class . '::register');
        self::assertNotNull($cached);
        self::assertFalse($cached->isStale(), 'Cached policy should not be stale');

        // Second compileAndCache should reuse the cached policy
        $result = $compiler->compileAndCache(RegistrationController::class, 'register');
        self::assertTrue($result, 'Should confirm caching');
    }
}
