<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

final class LazyProxySmokeTest
{
    public int $value = 0;

    public function increment(): int
    {
        return ++$this->value;
    }
}

$container = makeTestContainer();
$container->singleton(abstract: LazyCounter::class, concrete: LazyCounter::class);

$lazy = $container->lazy(abstract: LazyCounter::class);

assertSame(expected: LazyCounter::class, actual: $lazy->serviceId(), message: 'Lazy proxy should keep the requested service id.');
assertSame(expected: 1, actual: $lazy->increment(), message: 'Lazy proxy should resolve on first method call.');
assertSame(expected: 2, actual: $lazy->increment(), message: 'Lazy proxy should reuse the same resolved service instance.');

echo basename(path: __FILE__) . " ok\n";
