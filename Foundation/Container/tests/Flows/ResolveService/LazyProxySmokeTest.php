<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

final class LazyCounter
{
    public int $value = 0;

    public function increment() : int
    {
        return ++$this->value;
    }
}

$container = makeTestContainer();
$container->singleton(LazyCounter::class, LazyCounter::class);

$lazy = $container->lazy(LazyCounter::class);

assertSame(LazyCounter::class, $lazy->serviceId(), 'Lazy proxy should keep the requested service id.');
assertSame(1, $lazy->increment(), 'Lazy proxy should resolve on first method call.');
assertSame(2, $lazy->increment(), 'Lazy proxy should reuse the same resolved service instance.');

echo basename(__FILE__) . " ok\n";
