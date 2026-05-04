<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

interface FreshnessDependencyContract
{
    public function version(): string;
}

final class CompiledFreshnessSmokeTest implements FreshnessDependencyContract
{
    #[Override]
    public function version(): string
    {
        return 'v1';
    }
}

final class FreshnessDependencyV2 implements FreshnessDependencyContract
{
    #[Override]
    public function version(): string
    {
        return 'v2';
    }
}

final class FreshnessConsumer
{
    public function __construct(public FreshnessDependencyContract $freshnessDependencyContract)
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-freshness-' . uniqid();
$version  = 'compiled-freshness-smoke';
$config   = CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
);

$compiled = makeTestContainer(config: $config);
$compiled->singleton(abstract: FreshnessDependencyContract::class, concrete: FreshnessDependencyV1::class);
$compiled->compileContainer(serviceIds: [FreshnessConsumer::class, FreshnessDependencyContract::class]);

$reloaded = makeTestContainer(config: $config);
$reloaded->singleton(abstract: FreshnessDependencyContract::class, concrete: FreshnessDependencyV2::class);

$report   = $reloaded->compileReport(serviceIds: [FreshnessConsumer::class, FreshnessDependencyContract::class]);
$debug    = $reloaded->debugService(id: FreshnessDependencyContract::class);
$resolved = $reloaded->get(id: FreshnessConsumer::class);

assertTrue(condition: $report !== null, message: 'Freshness checks should still expose compile reports.');
assertTrue(condition: ! $report->available, message: 'Stale compiled artifacts must not stay available.');
assertTrue(condition: $report->compatible, message: 'Stale artifacts should still report structural compatibility when only service signatures changed.');
assertSame(expected: 'stale', actual: $report->freshnessState, message: 'Stale service signatures must be reported explicitly.');
assertTrue(
    condition: in_array(needle: 'compiled artifact signatures are stale', haystack: $report->warnings, strict: true),
    message  : 'Compile reports should warn when compiled service signatures are stale.',
);
assertTrue(condition: ! $reloaded->isCompiled(id: FreshnessConsumer::class), message: 'Stale compiled artifacts must not report compiled availability.');
assertSame(expected: 'dynamic', actual: $debug['compiledState']['decision'], message: 'Debug diagnostics should expose dynamic fallback for stale artifacts.');
assertSame(
    expected: 'compiled artifact is stale',
    actual  : $debug['explain']['fallback']['reason'],
    message : 'Explain diagnostics should expose stale-artifact fallback reasons.',
);
assertSame(expected: 'v2', actual: $resolved->dependency->version(), message: 'Runtime should resolve the fresh dynamic graph after signature drift.');

echo basename(path: __FILE__) . " ok\n";
