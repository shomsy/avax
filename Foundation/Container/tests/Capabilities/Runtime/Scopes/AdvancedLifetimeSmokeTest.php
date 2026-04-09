<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Runtime\Scopes\DisposableInterface;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;
use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;

final class AdvancedLifetimeSequence
{
    public static int $warmSingletons = 0;

    public static int $lazySingletons = 0;

    public static int $requestDisposals = 0;

    public static int $sharedDisposals = 0;
}

final class WarmSingletonService
{
    public function __construct()
    {
        AdvancedLifetimeSequence::$warmSingletons++;
    }
}

final class LazySingletonService
{
    public function __construct()
    {
        AdvancedLifetimeSequence::$lazySingletons++;
    }
}

final class RequestScopedDisposableService implements DisposableInterface
{
    public function dispose() : void
    {
        AdvancedLifetimeSequence::$requestDisposals++;
    }
}

final class SharedDisposableService implements DisposableInterface
{
    public function dispose() : void
    {
        AdvancedLifetimeSequence::$sharedDisposals++;
    }
}

final class PlainTransientDependency
{
}

final class SharedCapturesTransientService
{
    public function __construct(public PlainTransientDependency $dependency)
    {
    }
}

final class SharedCapturesRequestScopedService
{
    public function __construct(public RequestScopedDisposableService $dependency)
    {
    }
}

final class InvalidDisposableService
{
}

final class InvalidTransientDisposableService implements DisposableInterface
{
    public function dispose() : void
    {
    }
}

final class JobScopedService
{
}

$cacheDir = sys_get_temp_dir() . '/container-advanced-lifetimes-' . uniqid('', true);
$config = CreateContainerConfig::create(cacheDir: $cacheDir);
$container = makeTestContainer($config);

$container->singleton(WarmSingletonService::class, WarmSingletonService::class)->warm();
$container->singleton(LazySingletonService::class, LazySingletonService::class)->warm()->lazy();
$container->scoped(RequestScopedDisposableService::class, RequestScopedDisposableService::class)->request()->dispose();
$container->singleton(SharedDisposableService::class, SharedDisposableService::class)->dispose();
$container->bind(PlainTransientDependency::class, PlainTransientDependency::class);
$container->singleton(SharedCapturesTransientService::class, SharedCapturesTransientService::class);
$container->singleton(SharedCapturesRequestScopedService::class, SharedCapturesRequestScopedService::class);
$container->singleton(InvalidDisposableService::class, InvalidDisposableService::class)->dispose();
$container->bind(InvalidTransientDisposableService::class, InvalidTransientDisposableService::class)->dispose();
$container->scoped(JobScopedService::class, JobScopedService::class)->job();

$issues = implode("\n", $container->validate([
    SharedCapturesTransientService::class,
    SharedCapturesRequestScopedService::class,
    InvalidDisposableService::class,
]));

assertTrue(
    str_contains($issues, 'captures transient dependency [' . PlainTransientDependency::class . ']'),
    'Validation should detect captured transient dependencies.'
);
assertTrue(
    str_contains($issues, 'captures scoped dependency [' . RequestScopedDisposableService::class . ']'),
    'Validation should detect shared services that capture scoped dependencies.'
);
assertTrue(
    str_contains($issues, 'does not expose dispose() or implement DisposableInterface'),
    'Validation should reject invalid disposable registrations.'
);
assertTrue(
    str_contains($issues, 'uses transient lifetime, so the container cannot own its disposal boundary'),
    'Validation should reject disposable transient services because the container cannot own their disposal boundary.'
);

$container->warmCompiled([WarmSingletonService::class, LazySingletonService::class]);

assertSame(1, AdvancedLifetimeSequence::$warmSingletons, 'Warm shared services should build during warmCompiled().');
assertSame(0, AdvancedLifetimeSequence::$lazySingletons, 'Lazy shared services should not build during warmCompiled().');

$container->get(LazySingletonService::class);
assertSame(1, AdvancedLifetimeSequence::$lazySingletons, 'Lazy shared services should build on first resolve.');

assertThrows(
/**
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */ ContainerException::class,
    static fn() => $container->get(RequestScopedDisposableService::class),
    'Request-scoped services should require an active request scope.'
);

$container->openScope(ScopeKind::REQUEST, 'request-1');
$requestFirst = $container->get(RequestScopedDisposableService::class);
$requestSecond = $container->get(RequestScopedDisposableService::class);
assertSame($requestFirst, $requestSecond, 'Request-scoped services should reuse the same instance inside one request scope.');
$container->closeScope(ScopeKind::REQUEST);

assertSame(1, AdvancedLifetimeSequence::$requestDisposals, 'Closing a request scope should dispose request-owned disposable services.');

assertThrows(
/**
 * @throws \Psr\Container\ContainerExceptionInterface
 * @throws \Psr\Container\NotFoundExceptionInterface
 */ ContainerException::class,
    static fn() => $container->get(JobScopedService::class),
    'Job-scoped services should require an active job scope.'
);

$container->openScope(ScopeKind::JOB, 'job-1');
$jobFirst = $container->get(JobScopedService::class);
$jobSecond = $container->get(JobScopedService::class);
assertSame($jobFirst, $jobSecond, 'Job-scoped services should reuse the same instance inside one job scope.');
$container->closeScope(ScopeKind::JOB);

$container->get(SharedDisposableService::class);
$container->reset();

assertSame(1, AdvancedLifetimeSequence::$sharedDisposals, 'Reset should dispose explicitly disposable shared services.');

@rmdir($cacheDir);

echo basename(__FILE__) . " ok\n";
