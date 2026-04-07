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

    public string $message = 'registered';
}

final class DemoProvider implements ServiceProviderInterface
{
    public function __construct(private ContainerInterface $app)
    {
    }

    public function register() : void
    {
        ProviderState::$events[] = 'register';
        $this->app->instance(ProviderState::class, new ProviderState());
    }

    public function boot() : void
    {
        ProviderState::$events[] = 'boot';
        $this->app->get(ProviderState::class)->message = 'booted';
    }
}

ProviderState::$events = [];
$container = makeTestContainer();

(new BootProviders($container))->boot([DemoProvider::class]);

$state = $container->get(ProviderState::class);
assertSame(['register', 'boot'], ProviderState::$events, 'Providers must register before booting.');
assertSame('booted', $state->message, 'Boot phase should run against registered services.');

echo basename(__FILE__) . " ok\n";
