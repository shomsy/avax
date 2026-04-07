<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;
use Avax\Container\Runtime\LazyProxy;

final class BenchSharedService
{
    public function value() : string
    {
        return 'shared';
    }
}

final class BenchTransientService
{
    public function value() : string
    {
        return 'transient';
    }
}

final class BenchScopedService
{
    public function value() : string
    {
        return 'scoped';
    }
}

final class BenchLazyService
{
    public function value() : string
    {
        return 'lazy';
    }
}

final class BenchDeep5
{
    public function __construct(public BenchDeep4 $next)
    {
    }
}

final class BenchDeep4
{
    public function __construct(public BenchDeep3 $next)
    {
    }
}

final class BenchDeep3
{
    public function __construct(public BenchDeep2 $next)
    {
    }
}

final class BenchDeep2
{
    public function __construct(public BenchDeep1 $next)
    {
    }
}

final class BenchDeep1
{
    public function __construct(public BenchSharedService $shared)
    {
    }
}

final class BenchWideRoot
{
    public function __construct(
        public BenchWide1 $one,
        public BenchWide2 $two,
        public BenchWide3 $three,
        public BenchWide4 $four,
        public BenchWide5 $five
    ) {
    }
}

final class BenchWide1 {}
final class BenchWide2 {}
final class BenchWide3 {}
final class BenchWide4 {}
final class BenchWide5 {}

/**
 * @return array{time_ms: float, peak_mb: float}
 */
function benchmark(callable $callback, int $iterations = 1) : array
{
    gc_collect_cycles();
    $peakBefore = memory_get_peak_usage(true);
    $start = hrtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $callback();
    }

    $elapsedMs = (hrtime(true) - $start) / 1_000_000;
    $peakAfter = memory_get_peak_usage(true);

    return [
        'time_ms' => $elapsedMs,
        'peak_mb' => max(0, $peakAfter - $peakBefore) / 1024 / 1024,
    ];
}

/**
 * @param array<string, mixed> $settings
 */
function benchContainer(array $settings = []) : \Avax\Container\Container
{
    return makeTestContainer(CreateContainerConfig::create(
        cacheDir: sys_get_temp_dir() . '/container-bench-' . uniqid(),
        cacheVersion: 'bench-' . uniqid(),
        settings: $settings
    ));
}

$results = [];

$results['cold_boot'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->bind(BenchSharedService::class, BenchSharedService::class);
    $container->get(BenchSharedService::class);
});

$results['warm_boot'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->singleton(BenchSharedService::class, BenchSharedService::class);
    $container->warmCompiled([BenchSharedService::class]);
});

$results['cached_get'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->singleton(BenchSharedService::class, BenchSharedService::class);
    $container->get(BenchSharedService::class);
    $container->get(BenchSharedService::class);
}, 10000);

$results['uncached_resolve'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->bind(BenchTransientService::class, BenchTransientService::class);
    $container->make(BenchTransientService::class);
}, 10000);

$results['deep_graph'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->get(BenchDeep5::class);
}, 1000);

$results['wide_graph'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->get(BenchWideRoot::class);
}, 1000);

$results['scoped_service'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->scoped(BenchScopedService::class, BenchScopedService::class);
    $container->openScope();
    $container->get(BenchScopedService::class);
    $container->closeScope();
}, 5000);

$results['lazy_service'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->singleton(BenchLazyService::class, BenchLazyService::class);
    /** @var LazyProxy $lazy */
    $lazy = $container->lazy(BenchLazyService::class);
    $lazy->value();
}, 10000);

$results['compile_time'] = benchmark(static function () : void {
    $container = benchContainer();
    $container->singleton(BenchSharedService::class, BenchSharedService::class);
    $container->get(BenchDeep5::class);
    $container->compileContainer([
        BenchSharedService::class,
        BenchDeep1::class,
        BenchDeep2::class,
        BenchDeep3::class,
        BenchDeep4::class,
        BenchDeep5::class,
        BenchWideRoot::class,
    ]);
});

foreach ($results as $name => $result) {
    fwrite(
        STDOUT,
        str_pad($name, 16) . ' ' .
        str_pad(number_format($result['time_ms'], 2) . ' ms', 12) . ' ' .
        number_format($result['peak_mb'], 2) . " MB\n"
    );
}

echo "benchmarks ok\n";
