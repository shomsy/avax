<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\ContainerInterface;
use Avax\Container\DI\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Container\DI\Capabilities\Declaration\Providers\DeferredProviderInterface;

final class PoolingSequence
{
    public static int $ids = 0;
}

interface PoolingContract
{
    public function id() : int;
}

final class PoolingService implements PoolingContract
{
    public bool $decorated = false;

    public function __construct(private int $id = 0)
    {
        $this->id = ++PoolingSequence::$ids;
    }

    public function id() : int
    {
        return $this->id;
    }
}

final class PoolingDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ContainerInterface|null $container = null) : mixed
    {
        assertInstanceOf(PoolingService::class, $instance, 'Pooling decorators should receive the resolved singleton instance.');
        $instance->decorated = true;

        return $instance;
    }
}

interface DeferredPoolingContract
{
    public function id() : int;
}

final class DeferredPoolingService implements DeferredPoolingContract
{
    private int $id;

    public function __construct()
    {
        $this->id = ++PoolingSequence::$ids;
    }

    public function id() : int
    {
        return $this->id;
    }
}

final class DeferredPoolingProvider implements DeferredProviderInterface
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
        return [DeferredPoolingContract::class];
    }

    public function register() : void
    {
        $this->app->singleton(DeferredPoolingContract::class, DeferredPoolingService::class);
    }

    public function boot() : void
    {
    }
}

$container = makeTestContainer();
$container->singleton(PoolingContract::class, PoolingService::class);
$container->alias('pooling.alias', PoolingContract::class);
$container->decorate(PoolingContract::class, new PoolingDecorator());
$container->bootProviders([DeferredPoolingProvider::class]);

$first = $container->get(PoolingContract::class);
$aliased = $container->get('pooling.alias');
$deferredFirst = $container->get(DeferredPoolingContract::class);
$deferredSecond = $container->get(DeferredPoolingContract::class);

assertSame($first, $aliased, 'Aliased singleton resolution should reuse the same pooled instance.');
assertTrue($first->decorated, 'Decorated singletons should be stored in pooled form after decoration.');
assertSame($deferredFirst, $deferredSecond, 'Deferred provider singletons should reuse the same pooled instance.');

$container->reset();

$afterReset = $container->get(PoolingContract::class);
$afterResetAlias = $container->get('pooling.alias');
$afterResetDeferred = $container->get(DeferredPoolingContract::class);

assertNotSame($first, $afterReset, 'Reset should clear shared pooled instances so singletons rebuild cleanly.');
assertSame($afterReset, $afterResetAlias, 'Alias resolution should still reuse the rebuilt pooled singleton after reset.');
assertNotSame($deferredFirst, $afterResetDeferred, 'Reset should also clear deferred-provider singleton instances from the pool.');
assertTrue($afterReset->decorated, 'Rebuilt pooled singletons should keep decoration semantics after reset.');

echo basename(__FILE__) . " ok\n";
