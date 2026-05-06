<?php

declare(strict_types=1);

require_once dirname(3, path: __DIR__).'/bootstrap.php';

use Avax\Components\Application\Container\System\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\DisposableInterface;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeKind;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

final class AdvancedLifetimeSmokeTest
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
    public function dispose(): void
    {
        AdvancedLifetimeSequence::$requestDisposals++;
    }
}

final class SharedDisposableService implements DisposableInterface
{
    public function dispose(): void
    {
        AdvancedLifetimeSequence::$sharedDisposals++;
    }
}

final class PlainTransientDependency
{
}

final class SharedCapturesTransientService
{
    public function __construct(public PlainTransientDependency $plainTransientDependency)
    {
    }
}

final class SharedCapturesRequestScopedService
{
    public function __construct(public RequestScopedDisposableService $requestScopedDisposableService)
    {
    }
}

final class InvalidDisposableService
{
}

final class InvalidTransientDisposableService implements DisposableInterface
{
    public function dispose() : void {}
}

final class JobScopedService
{
}

$cacheDir = sys_get_temp_dir().'/container-advanced-lifetimes-'.uniqid(prefix: '', more_entropy: true);
$config = CreateContainerConfig::create(cacheDir: $cacheDir);
$container = makeTestContainer(config: $config);

$container->singleton(abstract: WarmSingletonService::class, concrete: WarmSingletonService::class)->warm();
$container->singleton(abstract: LazySingletonService::class, concrete: LazySingletonService::class)->warm()->lazy();
$container->scoped(abstract: RequestScopedDisposableService::class, concrete: RequestScopedDisposableService::class)->request()->dispose();
$container->singleton(abstract: SharedDisposableService::class, concrete: SharedDisposableService::class)->dispose();
$container->bind(abstract: PlainTransientDependency::class, concrete: PlainTransientDependency::class);
$container->singleton(abstract: SharedCapturesTransientService::class, concrete: SharedCapturesTransientService::class);
$container->singleton(abstract: SharedCapturesRequestScopedService::class, concrete: SharedCapturesRequestScopedService::class);
$container->singleton(abstract: InvalidDisposableService::class, concrete: InvalidDisposableService::class)->dispose();
$container->bind(abstract: InvalidTransientDisposableService::class, concrete: InvalidTransientDisposableService::class)->dispose();
$container->scoped(abstract: JobScopedService::class, concrete: JobScopedService::class)->job();

$issues = implode(separator: "\n", array: $container->validate(serviceIds: [
    SharedCapturesTransientService::class,
    SharedCapturesRequestScopedService::class,
    InvalidDisposableService::class,
]));

assertTrue(
    condition: str_contains(haystack: $issues, needle: 'captures transient dependency ['.PlainTransientDependency::class.']'),
    message  : 'Validation should detect captured transient dependencies.',
);
assertTrue(
    condition: str_contains(haystack: $issues, needle: 'captures scoped dependency ['.RequestScopedDisposableService::class.']'),
    message  : 'Validation should detect shared services that capture scoped dependencies.',
);
assertTrue(
    condition: str_contains(haystack: $issues, needle: 'does not expose dispose() or implement DisposableInterface'),
    message  : 'Validation should reject invalid disposable registrations.',
);
assertTrue(
    condition: str_contains(haystack: $issues, needle: 'uses transient lifetime, so the container cannot own its disposal boundary'),
    message  : 'Validation should reject disposable transient services because the container cannot own their disposal boundary.',
);

$container->warmCompiled(serviceIds: [WarmSingletonService::class, LazySingletonService::class]);

assertSame(expected: 1, actual: AdvancedLifetimeSequence::$warmSingletons, message: 'Warm shared services should build during warmCompiled().');
assertSame(expected: 0, actual: AdvancedLifetimeSequence::$lazySingletons, message: 'Lazy shared services should not build during warmCompiled().');

$container->get(id: LazySingletonService::class);
assertSame(expected: 1, actual: AdvancedLifetimeSequence::$lazySingletons, message: 'Lazy shared services should build on first resolve.');

assertThrows(
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */ /**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws Throwable
 */ /**
 * @throws Throwable
 */
    expectedClass: ContainerException::class,
    callback     : static fn () => $container->get(id: RequestScopedDisposableService::class),
    message      : 'ServerRequest-scoped services should require an active request scope.',
);

$container->openScope(kind: ScopeKind::REQUEST, scopeId: 'request-1');
$requestFirst = $container->get(id: RequestScopedDisposableService::class);
$requestSecond = $container->get(id: RequestScopedDisposableService::class);
assertSame(expected: $requestFirst, actual: $requestSecond, message: 'ServerRequest-scoped services should reuse the same instance inside one request scope.');
$container->closeScope(kind: ScopeKind::REQUEST);

assertSame(expected: 1, actual: AdvancedLifetimeSequence::$requestDisposals, message: 'Closing a request scope should dispose request-owned disposable services.');

assertThrows(
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */ /**
 * @throws ContainerExceptionInterface
 * @throws NotFoundExceptionInterface
 */ /**
 * @throws Throwable
 */ /**
 * @throws Throwable
 */
    expectedClass: ContainerException::class,
    callback     : static fn () => $container->get(id: JobScopedService::class),
    message      : 'Job-scoped services should require an active job scope.',
);

$container->openScope(kind: ScopeKind::JOB, scopeId: 'job-1');
$jobFirst = $container->get(id: JobScopedService::class);
$jobSecond = $container->get(id: JobScopedService::class);
assertSame(expected: $jobFirst, actual: $jobSecond, message: 'Job-scoped services should reuse the same instance inside one job scope.');
$container->closeScope(kind: ScopeKind::JOB);

$container->get(id: SharedDisposableService::class);
$container->reset();

assertSame(expected: 1, actual: AdvancedLifetimeSequence::$sharedDisposals, message: 'Reset should dispose explicitly disposable shared services.');

rmdir(directory: $cacheDir);

echo basename(path: __FILE__)." ok\n";
