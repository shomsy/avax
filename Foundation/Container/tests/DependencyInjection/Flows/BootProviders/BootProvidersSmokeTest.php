<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Flows\BootProviders;
use Avax\Container\DependencyInjection\Dependencies\Providers\ServiceProviderInterface;

final class ProviderState
{
    /** @var list<string> */
    public static array $events = [];

    public function __construct(public string $message = 'registered')
    {
    }
}

final class BaseProvider implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [];
    }

    public function register() : void
    {
        ProviderState::$events[] = 'base-register';
        $this->app->instance(ProviderState::class, new ProviderState('base-register'));
    }

    public function boot() : void
    {
        ProviderState::$events[] = 'base-boot';
        $this->app->get(ProviderState::class)->message = 'base-booted';
    }
}

final class DemoProvider implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [BaseProvider::class];
    }

    public function register() : void
    {
        ProviderState::$events[] = 'demo-register';
        assertSame(
            'base-register',
            $this->app->get(ProviderState::class)->message,
            'Dependent providers should register after their dependencies.'
        );
        $this->app->get(ProviderState::class)->message = 'demo-registered';
    }

    public function boot() : void
    {
        ProviderState::$events[] = 'demo-boot';
        assertSame(
            'base-booted',
            $this->app->get(ProviderState::class)->message,
            'Dependent providers should boot after their dependencies.'
        );
        $this->app->get(ProviderState::class)->message = 'demo-booted';
    }
}

interface DeferredProvidedContract
{
    public function id() : string;
}

final class DeferredProvidedService implements DeferredProvidedContract
{
    public function id() : string
    {
        return 'deferred-provider';
    }
}

final class DeferredDemoProvider implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [];
    }

    public function deferred() : bool
    {
        return true;
    }

    public function provides() : array
    {
        return [DeferredProvidedContract::class];
    }

    public function register() : void
    {
        ProviderState::$events[] = 'deferred-register';
        $this->app->singleton(DeferredProvidedContract::class, DeferredProvidedService::class);
    }

    public function boot() : void
    {
        ProviderState::$events[] = 'deferred-boot';
    }
}

final class CycleProviderA implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [CycleProviderB::class];
    }

    public function register() : void
    {
        $this->app->instance('cycle-a', new stdClass());
    }

    public function boot() : void
    {
    }
}

final class CycleProviderB implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function dependsOn() : array
    {
        return [CycleProviderA::class];
    }

    public function register() : void
    {
        $this->app->instance('cycle-b', new stdClass());
    }

    public function boot() : void
    {
    }
}

ProviderState::$events = [];
$container = makeTestContainer();

(new BootProviders($container))->boot([DemoProvider::class]);

$state = $container->get(ProviderState::class);
assertSame(
    ['base-register', 'demo-register', 'base-boot', 'demo-boot'],
    ProviderState::$events,
    'Providers must compose deterministically and boot in dependency order.'
);
assertSame('demo-booted', $state->message, 'Boot phase should run against the dependency-composed service graph.');
assertTrue(
    str_contains($container->exportMetrics(), 'container_provider_boot_total 2'),
    'Provider boot metrics should record each booted provider.'
);
assertTrue(
    str_contains($container->exportMetrics(), 'container_provider_register_total 2'),
    'Provider register metrics should record each registered provider.'
);

ProviderState::$events = [];
$deferredContainer = makeTestContainer();

(new BootProviders($deferredContainer))->boot([DeferredDemoProvider::class]);

$deferredDescription = $deferredContainer->describeService(DeferredProvidedContract::class);
assertSame([], ProviderState::$events, 'Deferred providers must not register or boot during eager provider boot flow.');
assertTrue($deferredDescription['deferred'], 'Deferred provider services should report deferred state before first resolve.');
assertSame(
    DeferredDemoProvider::class,
    $deferredDescription['deferredProvider'],
    'Service diagnostics should expose the deferred provider owner.'
);

$deferredService = $deferredContainer->get(DeferredProvidedContract::class);

assertSame('deferred-provider', $deferredService->id(), 'Deferred providers should register and boot on first service resolve.');
assertSame(
    ['deferred-register', 'deferred-boot'],
    ProviderState::$events,
    'Deferred providers should register and boot exactly once on first resolve.'
);
assertTrue(
    str_contains($deferredContainer->exportMetrics(), 'container_provider_deferred_boot_total 1'),
    'Deferred provider metrics should record lazy provider boots.'
);

assertThrows(
    LogicException::class,
    static function () use ($container) : void {
        (new BootProviders($container))->boot([CycleProviderA::class]);
    },
    'Provider dependency cycles should fail fast.'
);

echo basename(__FILE__) . " ok\n";
