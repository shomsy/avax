<?php

declare(strict_types=1);

require_once dirname(path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Capabilities\Declaration\Providers\DeferredProviderInterface;
use Avax\Components\Application\Container\DI\Capabilities\Execution\Injection\Attributes\Inject;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\ResettableInterface;
use Avax\Components\Application\Container\DI\Container;
use Avax\Components\Application\Container\DI\ContainerInterface;

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
    private ContainerInterface $app;

    public function __construct(ContainerInterface $app) { $this->app = $app; }

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
        $this->app->singleton(abstract: BenchDeferredProviderContract::class, concrete: BenchDeferredProviderService::class);
    }

    public function boot() : void {}
}

final class BenchDeep5
{
    public BenchDeep4 $next;

    public function __construct(BenchDeep4 $next) { $this->next = $next; }
}

final class BenchDeep4
{
    public BenchDeep3 $next;

    public function __construct(BenchDeep3 $next) { $this->next = $next; }
}

final class BenchDeep3
{
    public BenchDeep2 $next;

    public function __construct(BenchDeep2 $next) { $this->next = $next; }
}

final class BenchDeep2
{
    public BenchDeep1 $next;

    public function __construct(BenchDeep1 $next) { $this->next = $next; }
}

final class BenchDeep1
{
    public BenchSharedService $shared;

    public function __construct(BenchSharedService $shared) { $this->shared = $shared; }
}

final class BenchWideRoot
{
    public BenchWide5 $five;
    public BenchWide4 $four;
    public BenchWide3 $three;
    public BenchWide2 $two;
    public BenchWide1 $one;

    public function __construct(
        BenchWide1 $one,
        BenchWide2 $two,
        BenchWide3 $three,
        BenchWide4 $four,
        BenchWide5 $five
    )
    {
        $this->one   = $one;
        $this->two   = $two;
        $this->three = $three;
        $this->four  = $four;
        $this->five  = $five;
    }
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
    $peakBefore = memory_get_peak_usage(real_usage: true);
    $start      = hrtime(as_number: true);

    for ($i = 0; $i < $iterations; $i++) {
        $callback();
    }

    $elapsedMs = (hrtime(as_number: true) - $start) / 1_000_000;
    $peakAfter = memory_get_peak_usage(real_usage: true);

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

    return makeTestContainer(config: CreateContainerConfig::create(
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

    $timeSamples = array_values(array: array_map(
                                           callback: static fn (array $sample) : float => (float) $sample['time_ms'],
                                           array   : $samples
                                       ));
    sort(array: $timeSamples);

    $peakSamples = array_values(array: array_map(
                                           callback: static fn (array $sample) : float => (float) $sample['peak_mb'],
                                           array   : $samples
                                       ));
    sort(array: $peakSamples);

    $iterations = max(1, $scenario['iterations']);
    $middle     = intdiv(num1: count(value: $timeSamples), num2: 2);
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
                $container->bind(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $container->get(id: BenchSharedService::class);
            },
        ],
        [
            'name'       => 'warm_boot',
            'iterations' => 1,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $container->warmCompiled(serviceIds: [BenchSharedService::class]);
            },
        ],
        [
            'name'       => 'cached_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $container->get(id: BenchSharedService::class);
                $container->get(id: BenchSharedService::class);
            },
        ],
        [
            'name'       => 'worker_cached_get',
            'iterations' => 10000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer();
                    $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                }

                $container->get(id: BenchSharedService::class);
            },
        ],
        [
            'name'       => 'uncached_resolve',
            'iterations' => 10000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bind(abstract: BenchTransientService::class, concrete: BenchTransientService::class);
                $container->make(abstract: BenchTransientService::class);
            },
        ],
        [
            'name'       => 'request_lifecycle',
            'iterations' => 5000,
            'callback'   => static function () : void {
                static $container = null;

                if ($container === null) {
                    $container = benchContainer();
                    $container->scoped(abstract: BenchScopedService::class, concrete: BenchScopedService::class);
                }

                $container->openScope();
                $container->get(id: BenchScopedService::class);
                $container->closeScope();
            },
        ],
        [
            'name'       => 'deep_graph',
            'iterations' => 1000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->get(id: BenchDeep5::class);
            },
        ],
        [
            'name'       => 'wide_graph',
            'iterations' => 1000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->get(id: BenchWideRoot::class);
            },
        ],
        [
            'name'       => 'scoped_service',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->scoped(abstract: BenchScopedService::class, concrete: BenchScopedService::class);
                $container->openScope();
                $container->get(id: BenchScopedService::class);
                $container->closeScope();
            },
        ],
        [
            'name'       => 'pooled_service',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bind(abstract: BenchPooledService::class, concrete: BenchPooledService::class)->pooled(maxSize: 8);
                $container->openScope();
                $container->get(id: BenchPooledService::class);
                $container->closeScope();
            },
        ],
        [
            'name'       => 'lazy_service',
            'iterations' => 10000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchLazyService::class, concrete: BenchLazyService::class);
                $lazy = $container->lazy(abstract: BenchLazyService::class);
                $lazy->value();
            },
        ],
        [
            'name'       => 'deferred_service',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->defer(abstract: BenchDeferredService::class, concrete: BenchDeferredService::class);
                $container->get(id: BenchDeferredService::class);
            },
        ],
        [
            'name'       => 'deferred_provider',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->bootProviders(providers: [BenchDeferredProvider::class]);
                $container->get(id: BenchDeferredProviderContract::class);
            },
        ],
        [
            'name'       => 'function_call_injection',
            'iterations' => 5000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $container->call(callable: static fn (BenchSharedService $shared) : string => $shared->value());
            },
        ],
        [
            'name'       => 'property_injection',
            'iterations' => 2000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $container->injectInto(target: new BenchInjectionTarget());
            },
        ],
        [
            'name'       => 'method_injection',
            'iterations' => 2000,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $target = new BenchInjectionTarget();
                $container->injectInto(target: $target);
                $target->methodDependency?->value();
            },
        ],
        [
            'name'       => 'compile_time',
            'iterations' => 1,
            'callback'   => static function () : void {
                $container = benchContainer();
                $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                $container->get(id: BenchDeep5::class);
                $container->compileContainer(serviceIds: [
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
                    $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                    $container->warmCompiled(serviceIds: [BenchSharedService::class]);
                }

                $container->get(id: BenchSharedService::class);
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
                    $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                    $container->warmCompiled(serviceIds: [BenchSharedService::class]);
                }

                $container->get(id: BenchSharedService::class);
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
                    $container->singleton(abstract: BenchSharedService::class, concrete: BenchSharedService::class);
                    $container->warmCompiled(serviceIds: [BenchSharedService::class]);
                }

                $container->get(id: BenchSharedService::class);
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
        'memory_limit'       => (string) ini_get(option: 'memory_limit'),
        'opcache.enable_cli' => (string) ini_get(option: 'opcache.enable_cli'),
        'zend.assertions'    => (string) ini_get(option: 'zend.assertions'),
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
        throw new RuntimeException(message: "Benchmark guard failed:\n- " . implode(separator: "\n- ", array: $failures));
    }
}

$jsonOutput = in_array(needle: '--json', haystack: $argv, strict: true);
$guard      = in_array(needle: '--guard', haystack: $argv, strict: true);
$outputPath = null;

foreach ($argv as $argument) {
    if (str_starts_with(haystack: $argument, needle: '--output=')) {
        $outputPath = substr(string: $argument, offset: strlen(string: '--output='));
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

if ($jsonOutput || is_string(value: $outputPath)) {
    $payload = json_encode(value: [
                                      'meta' => [
                                          'php' => PHP_VERSION,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              'sapi' => PHP_SAPI,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              'timestamp' => gmdate(format: 'c'),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   'dockerImage' => BENCHMARK_DOCKER_IMAGE,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              'phpSettings' => benchmarkPhpSettings(),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                'suiteVersion' => BENCHMARK_SUITE_VERSION,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   'buildMarker' => (string) (getenv(name: 'BENCHMARK_BUILD_MARKER') ?: ''),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  'guard' => $guard,
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               'scenarioCount' => count(value: $results),
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           'scenarios' => array_keys(array: $results),
                                      ],
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              'results' => $results,
                                  ], flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;

    if (is_string(value: $outputPath) && $outputPath !== '') {
        $directory = dirname(path: $outputPath);
        if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0775, recursive: true) && ! is_dir(filename: $directory)) {
            throw new RuntimeException(message: "Cannot create benchmark artifact directory [{$directory}].");
        }

        if (file_put_contents(filename: $outputPath, data: $payload, flags: LOCK_EX) === false) {
            throw new RuntimeException(message: "Cannot write benchmark artifact [{$outputPath}].");
        }
    }

    if ($jsonOutput) {
        echo $payload;
        exit(0);
    }
}

foreach ($results as $name => $result) {
    fwrite(
        stream: STDOUT,
        data  : str_pad(string: $name, length: 24)
                . str_pad(string: number_format(num: $result['time_ms'], decimals: 2) . ' ms', length: 14)
                . str_pad(string: number_format(num: $result['ops_per_s'], decimals: 2) . ' ops/s', length: 18)
                . number_format(num: $result['peak_mb'], decimals: 2) . " MB\n"
    );
}

echo "benchmarks ok\n";
