<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\RegisterDeferredDependency;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\RegisterDependency;
use Avax\Components\Application\Container\DI\ContainerInterface;
use Avax\Components\Application\Container\DI\Flows\BootProviders\BootProviders;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

interface DeferredProvidedContract
{
    public function id(): string;
}

final class BootProvidersSmokeTest
{
    /** @var list<string> */
    public static array $events = [];

    public function __construct(public string $message = 'registered')
    {
    }
}

final readonly class BaseProvider implements RegisterDependency
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dependsOn(): array
    {
        return [];
    }

    public function register(): void
    {
        ProviderState::$events[] = 'base-register';
        $this->container->instance(abstract: ProviderState::class, instance: new ProviderState(message: 'base-register'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        ProviderState::$events[]                           = 'base-boot';
        $this->container->get(id: ProviderState::class)->message = 'base-booted';
    }
}

final readonly class DemoProvider implements RegisterDependency
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dependsOn(): array
    {
        return [BaseProvider::class];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function register(): void
    {
        ProviderState::$events[] = 'demo-register';
        assertSame(
            expected: 'base-register',
            actual  : $this->container->get(id: ProviderState::class)->message,
            message : 'Dependent providers should register after their dependencies.',
        );
        $this->container->get(id: ProviderState::class)->message = 'demo-registered';
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function boot(): void
    {
        ProviderState::$events[] = 'demo-boot';
        assertSame(
            expected: 'base-booted',
            actual  : $this->container->get(id: ProviderState::class)->message,
            message : 'Dependent providers should boot after their dependencies.',
        );
        $this->container->get(id: ProviderState::class)->message = 'demo-booted';
    }
}

final class DeferredProvidedService implements DeferredProvidedContract
{
    public function id(): string
    {
        return 'deferred-provider';
    }
}

final class DeferredProviderConsumer
{
    public function __construct(public DeferredProvidedContract $dependency)
    {
    }
}

final readonly class DeferredDemoProvider implements RegisterDeferredDependency
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dependsOn(): array
    {
        return [];
    }

    public function deferred(): bool
    {
        return true;
    }

    public function provides(): array
    {
        return [DeferredProvidedContract::class];
    }

    public function register(): void
    {
        ProviderState::$events[] = 'deferred-register';
        $this->container->singleton(abstract: DeferredProvidedContract::class, concrete: DeferredProvidedService::class);
    }

    public function boot(): void
    {
        ProviderState::$events[] = 'deferred-boot';
    }
}

final readonly class CycleProviderA implements RegisterDependency
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dependsOn(): array
    {
        return [CycleProviderB::class];
    }

    public function register(): void
    {
        $this->container->instance(abstract: 'cycle-a', instance: new stdClass());
    }
}

final readonly class CycleProviderB implements RegisterDependency
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function dependsOn(): array
    {
        return [CycleProviderA::class];
    }

    public function register(): void
    {
        $this->container->instance(abstract: 'cycle-b', instance: new stdClass());
    }
}

ProviderState::$events = [];
$container             = makeTestContainer();

new BootProviders(container: $container)->boot(providers: [DemoProvider::class]);

$state = $container->get(id: ProviderState::class);
assertSame(
    expected: ['base-register', 'demo-register', 'base-boot', 'demo-boot'],
    actual  : ProviderState::$events,
    message : 'Providers must compose deterministically and boot in dependency order.',
);
assertSame(expected: 'demo-booted', actual: $state->message, message: 'Boot phase should run against the dependency-composed service graph.');
assertTrue(
    condition: str_contains(haystack: $container->exportMetrics(), needle: 'container_provider_boot_total 2'),
    message  : 'Provider boot metrics should record each booted provider.',
);
assertTrue(
    condition: str_contains(haystack: $container->exportMetrics(), needle: 'container_provider_register_total 2'),
    message  : 'Provider register metrics should record each registered provider.',
);

ProviderState::$events = [];
$deferredContainer     = makeTestContainer();

new BootProviders(container: $deferredContainer)->boot(providers: [DeferredDemoProvider::class]);

$deferredDescription = $deferredContainer->describeService(id: DeferredProvidedContract::class);
assertSame(expected: [], actual: ProviderState::$events, message: 'Deferred providers must not register or boot during eager provider boot flow.');
assertTrue(condition: $deferredDescription['deferred'], message: 'Deferred provider services should report deferred state before first resolve.');
assertSame(
    expected: DeferredDemoProvider::class,
    actual  : $deferredDescription['deferredProvider'],
    message : 'Service diagnostics should expose the deferred provider owner.',
);

$deferredService = $deferredContainer->get(id: DeferredProvidedContract::class);

assertSame(expected: 'deferred-provider', actual: $deferredService->id(), message: 'Deferred providers should register and boot on first service resolve.');
assertSame(
    expected: ['deferred-register', 'deferred-boot'],
    actual  : ProviderState::$events,
    message : 'Deferred providers should register and boot exactly once on first resolve.',
);
assertTrue(
    condition: str_contains(haystack: $deferredContainer->exportMetrics(), needle: 'container_provider_deferred_boot_total 1'),
    message  : 'Deferred provider metrics should record lazy provider boots.',
);

ProviderState::$events     = [];
$compiledDeferredContainer = makeTestContainer();
$compiledDeferredContainer->singleton(abstract: DeferredProviderConsumer::class, concrete: DeferredProviderConsumer::class);

new BootProviders(container: $compiledDeferredContainer)->boot(providers: [DeferredDemoProvider::class]);
$compiledDeferredContainer->compileContainer(serviceIds: [DeferredProviderConsumer::class]);

$compiledDeferredReport = $compiledDeferredContainer->compileReport(serviceIds: [
                                                                                    DeferredProviderConsumer::class,
                                                                                    DeferredProvidedContract::class,
                                                                                ]);

assertSame(
    expected: ['deferred-register', 'deferred-boot'],
    actual  : ProviderState::$events,
    message : 'Explicit compile paths must boot deferred providers when compiled services depend on them.',
);
assertTrue(
    condition: $compiledDeferredReport !== null && in_array(needle: DeferredProvidedContract::class, haystack: $compiledDeferredReport->entries, strict: true),
    message  : 'Deferred provider services required by compiled dependencies must be compiled into the artifact.',
);
assertTrue(
    condition: $compiledDeferredContainer->isCompiled(id: DeferredProvidedContract::class),
    message  : 'Deferred provider services required by compiled dependencies should report compiled state.',
);

assertThrows(
    expectedClass: LogicException::class,
    callback     : static function () use ($container): void {
        new BootProviders(container: $container)->boot(providers: [CycleProviderA::class]);
    },
    message      : 'Provider dependency cycles should fail fast.',
);

echo basename(path: __FILE__) . " ok\n";
