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

assertThrows(
    LogicException::class,
    static function () use ($container) : void {
        (new BootProviders($container))->boot([CycleProviderA::class]);
    },
    'Provider dependency cycles should fail fast.'
);

echo basename(__FILE__) . " ok\n";
