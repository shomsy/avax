<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Declaration\Providers\DeferredProviderInterface;
use Avax\Container\DI\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Container\DI\Capabilities\Runtime\LazyProxy;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ResettableInterface;
use Avax\Container\DI\Container;
use Avax\Container\DI\ContainerInterface;

const BENCHMARK_DOCKER_IMAGE  = 'php:8.3-cli';
const BENCHMARK_SUITE_VERSION = '2026-04-08';

interface BenchDeferredProviderContract
{
    public function value() : string;
}

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

final class BenchPooledService implements ResettableInterface
{
    public function value() : string
    {
        return 'pooled';
    }

    public function reset() : void {}
}

final class BenchLazyService
{
    public function value() : string
    {
        return 'lazy';
    }
}

final class BenchDeferredService
{
    public function value() : string
    {
        return 'deferred';
    }
}

final class BenchDeferredProviderService implements BenchDeferredProviderContract
{
    public function value() : string
    {
        return 'deferred-provider';
    }
}

final class BenchDeferredProvider implements DeferredProviderInterface
{
    public function __construct(private ContainerInterface $app) {}

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
        return [BenchDeferredProviderContract::class];
    }

    public function register() : void
    {
        $this->app->singleton(BenchDeferredProviderContract::class, BenchDeferredProviderService::class);
    }

    public function boot() : void {}
}

final class BenchDeep5
{
    public function __construct(public BenchDeep4 $next) {}
}

final class BenchDeep4
{
    public function __construct(public BenchDeep3 $next) {}
}

final class BenchDeep3
{
    public function __construct(public BenchDeep2 $next) {}
}

final class BenchDeep2
{
    public function __construct(public BenchDeep1 $next) {}
}

final class BenchDeep1
{
    public function __construct(public BenchSharedService $shared) {}
}

final class BenchWideRoot
{
    public function __construct(
        public BenchWide1 $one,
        public BenchWide2 $two,
        public BenchWide3 $three,
        public BenchWide4 $four,
        public BenchWide5 $five
    ) {}
}

final class BenchWide1 {}

final class BenchWide2 {}

final class BenchWide3 {}

final class BenchWide4 {}

final class BenchWide5 {}

final class BenchInjectionTarget
{
    #[Inject]
    public BenchSharedService $property;

    public BenchSharedService|null $methodDependency = null;

    #[Inject]
    public function wire(BenchSharedService $shared) : void
    {
        $this->methodDependency = $shared;
    }
}

/**
 * @return array{time_ms: float, peak_mb: float}
 */
function benchmark(callable $callback, int $iterations = 1) : array
{
    gc_collect_cycles();
    $peakBefore = memory_get_peak_usage(true);
    $start      = hrtime(true);

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
function benchContainer(
    array|null  $settings = null,
    string|null $compileMode = null,
    bool|null   $debug = null,
    string|null $diagnosticsMode = null,
    string      $executionMode = CreateContainerConfig::EXECUTION_MODE_COMPILED
) : Container
{
    $settings        ??= [];
    $compileMode     ??= CreateContainerConfig::COMPILE_MODE_PRODUCTION;
    $debug           ??= false;
    $diagnosticsMode ??= CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL;

    return makeTestContainer(CreateContainerConfig::create(
        cacheDir       : sys_get_temp_dir() . '/container-bench-' . uniqid(),
        cacheVersion   : 'bench-' . uniqid(),
        debug          : $debug,
        settings       : $settings,
        compileMode    : $compileMode,
        diagnosticsMode: $diagnosticsMode,
        executionMode  : $executionMode
    ));
}

/**
 * @param array{name: string, iterations: int, callback: callable(): void} $scenario
 *
 * @return array<string, mixed>
 */
function measureScenario(array $scenario) : array
{
    $result     = benchmark(
        callback  : $scenario['callback'],
        iterations: $scenario['iterations']
    );
    $timeMs     = $result['time_ms'];
    $iterations = max(1, $scenario['iterations']);

    return [
        'iterations'       => $iterations,
        'time_ms'          => $timeMs,
        'ops_per_s'        => $timeMs > 0 ? ($iterations / $timeMs) * 1000 : 0.0,
        'peak_mb'          => $result['peak_mb'],
        'memory_per_op_kb' => ($result['peak_mb'] * 1024) / $iterations,
    ];
}

/**
 * @param array{name: string, iterations: int, callback: callable(): void} $scenario
 *
 * @return array<string, mixed>
 */
function measureScenarioForGuard(array $scenario, int $runs = 3) : array
{
    $samples = [];

    for ($index = 0; $index < $runs; $index++) {
        $samples[] = measureScenario(scenario: $scenario);
    }

    $timeSamples = array_values(array_map(
                                    static fn (array $sample) : float => (float) $sample['time_ms'],
                                    $samples
                                ));
    sort($timeSamples);

    $peakSamples = array_values(array_map(
                                    static fn (array $sample) : float => (float) $sample['peak_mb'],
                                    $samples
                                ));
    sort($peakSamples);

    $iterations = max(1, $scenario['iterations']);
    $middle     = intdiv(count($timeSamples), 2);
    $timeMs     = $timeSamples[$middle] ?? 0.0;
    $peakMb     = $peakSamples[$middle] ?? 0.0;

    return [
        'iterations'       => $iterations,
        'runs'             => $runs,
        'time_ms'          => $timeMs,
        'ops_per_s'        => $timeMs > 0 ? ($iterations / $timeMs) * 1000 : 0.0,
        'peak_mb'          => $peakMb,
        'memory_per_op_kb' => ($peakMb * 1024) / $iterations,
    ];
}

function benchmarkScenarios() : array
{
    return [
        [
            'name'       => 'cold_boot',
            'iterations' => 1,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bind(BenchSharedService::class, BenchSharedService::class);
                $container->get(BenchSharedService::class);
            },
        ],
        [
            'name'       => 'warm_boot',
            'iterations' => 1,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(BenchSharedService::class, BenchSharedService::class);
                $container->warmCompiled([BenchSharedService::class]);
            },
        ],
        [
            'name'       => 'cached_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(BenchSharedService::class, BenchSharedService::class);
                $container->get(BenchSharedService::class);
                $container->get(BenchSharedService::class);
            },
        ],
        [
            'name'       => 'worker_cached_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer();
                    $container->singleton(BenchSharedService::class, BenchSharedService::class);
                }

                $container->get(BenchSharedService::class);
            },
        ],
        [
            'name'       => 'uncached_resolve',
            'iterations' => 10000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bind(BenchTransientService::class, BenchTransientService::class);
                $container->make(BenchTransientService::class);
            },
        ],
        [
            'name'       => 'request_lifecycle',
            'iterations' => 5000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer();
                    $container->scoped(BenchScopedService::class, BenchScopedService::class);
                }

                $container->openScope();
                $container->get(BenchScopedService::class);
                $container->closeScope();
            },
        ],
        [
            'name'       => 'deep_graph',
            'iterations' => 1000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->get(BenchDeep5::class);
            },
        ],
        [
            'name'       => 'wide_graph',
            'iterations' => 1000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->get(BenchWideRoot::class);
            },
        ],
        [
            'name'       => 'scoped_service',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->scoped(BenchScopedService::class, BenchScopedService::class);
                $container->openScope();
                $container->get(BenchScopedService::class);
                $container->closeScope();
            },
        ],
        [
            'name'       => 'pooled_service',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bind(BenchPooledService::class, BenchPooledService::class)->pooled(maxSize: 8);
                $container->openScope();
                $container->get(BenchPooledService::class);
                $container->closeScope();
            },
        ],
        [
            'name'       => 'lazy_service',
            'iterations' => 10000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(BenchLazyService::class, BenchLazyService::class);
                /** @var LazyProxy $lazy */
                $lazy = $container->lazy(BenchLazyService::class);
                $lazy->value();
            },
        ],
        [
            'name'       => 'deferred_service',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->defer(BenchDeferredService::class, BenchDeferredService::class);
                $container->get(BenchDeferredService::class);
            },
        ],
        [
            'name'       => 'deferred_provider',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bootProviders([BenchDeferredProvider::class]);
                $container->get(BenchDeferredProviderContract::class);
            },
        ],
        [
            'name'       => 'function_call_injection',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(BenchSharedService::class, BenchSharedService::class);
                $container->call(static fn (BenchSharedService $shared) : string => $shared->value());
            },
        ],
        [
            'name'       => 'property_injection',
            'iterations' => 2000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(BenchSharedService::class, BenchSharedService::class);
                $container->injectInto(new BenchInjectionTarget());
            },
        ],
        [
            'name'       => 'method_injection',
            'iterations' => 2000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(BenchSharedService::class, BenchSharedService::class);
                $target = new BenchInjectionTarget();
                $container->injectInto($target);
                $target->methodDependency?->value();
            },
        ],
        [
            'name'       => 'compile_time',
            'iterations' => 1,
            'callback'   => static function () : void {
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
            },
        ],
        [
            'name'       => 'prod_compiled_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer(
                        compileMode: CreateContainerConfig::COMPILE_MODE_PRODUCTION
                    );
                    $container->singleton(BenchSharedService::class, BenchSharedService::class);
                    $container->warmCompiled([BenchSharedService::class]);
                }

                $container->get(BenchSharedService::class);
            },
        ],
        [
            'name'       => 'generated_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer(
                        compileMode  : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
                        executionMode: CreateContainerConfig::EXECUTION_MODE_GENERATED
                    );
                    $container->singleton(BenchSharedService::class, BenchSharedService::class);
                    $container->warmCompiled([BenchSharedService::class]);
                }

                $container->get(BenchSharedService::class);
            },
        ],
        [
            'name'       => 'dev_compiled_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer(
                        compileMode    : CreateContainerConfig::COMPILE_MODE_DEV,
                        debug          : true,
                        diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED
                    );
                    $container->singleton(BenchSharedService::class, BenchSharedService::class);
                    $container->warmCompiled([BenchSharedService::class]);
                }

                $container->get(BenchSharedService::class);
            },
        ],
    ];
}

/**
 * @return array<string, string|int|false>
 */
function benchmarkPhpSettings() : array
{
    return [
        'memory_limit'       => (string) ini_get('memory_limit'),
        'opcache.enable_cli' => (string) ini_get('opcache.enable_cli'),
        'zend.assertions'    => (string) ini_get('zend.assertions'),
    ];
}

/**
 * @param array<string, array{max_time_ms: float, max_peak_mb: float}> $thresholds
 * @param array<string, array<string, mixed>>                          $results
 */
function assertThresholds(array $thresholds, array $results) : void
{
    $failures = [];

    foreach ($thresholds as $name => $threshold) {
        if (! isset($results[$name])) {
            $failures[] = "Missing benchmark result for [{$name}].";
            continue;
        }

        if ($results[$name]['time_ms'] > $threshold['max_time_ms']) {
            $failures[] = "[{$name}] exceeded max_time_ms {$threshold['max_time_ms']} with {$results[$name]['time_ms']}.";
        }

        if ($results[$name]['peak_mb'] > $threshold['max_peak_mb']) {
            $failures[] = "[{$name}] exceeded max_peak_mb {$threshold['max_peak_mb']} with {$results[$name]['peak_mb']}.";
        }
    }

    if ($failures !== []) {
        throw new RuntimeException("Benchmark guard failed:\n- " . implode("\n- ", $failures));
    }
}

$jsonOutput = in_array('--json', $argv, true);
$guard      = in_array('--guard', $argv, true);
$outputPath = null;

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output=')) {
        $outputPath = substr($argument, strlen('--output='));
    }
}

$results = [];
foreach (benchmarkScenarios() as $scenario) {
    $results[$scenario['name']] = $guard
        ? measureScenarioForGuard(scenario: $scenario)
        : measureScenario(scenario: $scenario);
}

if ($guard) {
    /** @var array<string, array{max_time_ms: float, max_peak_mb: float}> $thresholds */
    $thresholds = require __DIR__ . '/thresholds.php';
    assertThresholds(thresholds: $thresholds, results: $results);
}

if ($jsonOutput || is_string($outputPath)) {
    $payload = json_encode([
                               'meta' => [
                                   'php' => PHP_VERSION,
                                                                                                                                                                                                                    'sapi' => PHP_SAPI,
                                                                                                                                                                                                                                                     'timestamp' => gmdate('c'),
                                                                                                                                                                                                                                                                                                           'dockerImage' => BENCHMARK_DOCKER_IMAGE,
                                                                                                                                                                                                                                                                                                                                                                             'phpSettings' => benchmarkPhpSettings(),
                                                                                                                                                                                                                                                                                                                                                                                                     'suiteVersion' => BENCHMARK_SUITE_VERSION,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          'buildMarker' => (string) (getenv('BENCHMARK_BUILD_MARKER') ?: ''),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 'guard' => $guard,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      'scenarioCount' => count($results),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 'scenarios' => array_keys($results),
                               ],
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         'results' => $results,
                           ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;

    if (is_string($outputPath) && $outputPath !== '') {
        $directory = dirname($outputPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException("Cannot create benchmark artifact directory [{$directory}].");
        }

        if (file_put_contents($outputPath, $payload, LOCK_EX) === false) {
            throw new RuntimeException("Cannot write benchmark artifact [{$outputPath}].");
        }
    }

    if ($jsonOutput) {
        echo $payload;
        exit(0);
    }
}

foreach ($results as $name => $result) {
    fwrite(
        STDOUT,
        str_pad($name, 24)
        . str_pad(number_format($result['time_ms'], 2) . ' ms', 14)
        . str_pad(number_format($result['ops_per_s'], 2) . ' ops/s', 18)
        . number_format($result['peak_mb'], 2) . " MB\n"
    );
}

echo "benchmarks ok\n";
