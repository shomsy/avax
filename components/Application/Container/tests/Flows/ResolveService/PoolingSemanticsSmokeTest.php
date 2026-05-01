<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\RegisterDeferredDependency;
use Avax\Components\Application\Container\DI\ContainerInterface;

interface PoolingContract
{
    public function id() : int;
}

interface DeferredPoolingContract
{
    public function id() : int;
}

final class PoolingSequence
{
    public static int $ids = 0;
}

final class PoolingService implements PoolingContract
{
    public bool $decorated = false;

    public function __construct(private int $id = 0)
    {
        $this->id = ++PoolingSequence::$ids;
    }

    #[Override]
    public function id() : int
    {
        return $this->id;
    }
}

final class PoolingDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ?ContainerInterface $container = null) : mixed
    {
        assertInstanceOf(expectedClass: PoolingService::class, value: $instance, message: 'Pooling decorators should receive the resolved singleton instance.');
        $instance->decorated = true;

        return $instance;
    }
}

final readonly class DeferredPoolingService implements DeferredPoolingContract
{
    private int $id;

    public function __construct()
    {
        $this->id = ++PoolingSequence::$ids;
    }

    #[Override]
    public function id() : int
    {
        return $this->id;
    }
}

final readonly class DeferredPoolingProvider implements RegisterDeferredDependency
{
    public function __construct(private ContainerInterface $container) {}

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
        $this->container->singleton(abstract: DeferredPoolingContract::class, concrete: DeferredPoolingService::class);
    }
}

$container = makeTestContainer();
$container->singleton(abstract: PoolingContract::class, concrete: PoolingService::class);
$container->alias(alias: 'pooling.alias', abstract: PoolingContract::class);
$container->decorate(abstract: PoolingContract::class, decorator: new PoolingDecorator());
$container->bootProviders(providers: [DeferredPoolingProvider::class]);

$first         = $container->get(id: PoolingContract::class);
$aliased       = $container->get(id: 'pooling.alias');
$deferredFirst = $container->get(id: DeferredPoolingContract::class);
$deferredSecond = $container->get(id: DeferredPoolingContract::class);

assertSame(expected: $first, actual: $aliased, message: 'Aliased singleton resolution should reuse the same pooled instance.');
assertTrue(condition: $first->decorated, message: 'Decorated singletons should be stored in pooled form after decoration.');
assertSame(expected: $deferredFirst, actual: $deferredSecond, message: 'Deferred provider singletons should reuse the same pooled instance.');

$container->reset();

$afterReset      = $container->get(id: PoolingContract::class);
$afterResetAlias = $container->get(id: 'pooling.alias');
$afterResetDeferred = $container->get(id: DeferredPoolingContract::class);

assertNotSame(expected: $first, actual: $afterReset, message: 'Reset should clear shared pooled instances so singletons rebuild cleanly.');
assertSame(expected: $afterReset, actual: $afterResetAlias, message: 'Alias resolution should still reuse the rebuilt pooled singleton after reset.');
assertNotSame(expected: $deferredFirst, actual: $afterResetDeferred, message: 'Reset should also clear deferred-provider singleton instances from the pool.');
assertTrue(condition: $afterReset->decorated, message: 'Rebuilt pooled singletons should keep decoration semantics after reset.');

echo basename(path: __FILE__) . " ok\n";
