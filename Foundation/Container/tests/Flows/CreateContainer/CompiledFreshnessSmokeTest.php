<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

interface FreshnessDependencyContract
{
    public function version() : string;
}

final class FreshnessDependencyV1 implements FreshnessDependencyContract
{
    public function version() : string
    {
        return 'v1';
    }
}

final class FreshnessDependencyV2 implements FreshnessDependencyContract
{
    public function version() : string
    {
        return 'v2';
    }
}

final class FreshnessConsumer
{
    public function __construct(public FreshnessDependencyContract $dependency)
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-freshness-' . uniqid();
$version = 'compiled-freshness-smoke';
$config = CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION
);

$compiled = makeTestContainer($config);
$compiled->singleton(FreshnessDependencyContract::class, FreshnessDependencyV1::class);
$compiled->compileContainer([FreshnessConsumer::class, FreshnessDependencyContract::class]);

$reloaded = makeTestContainer($config);
$reloaded->singleton(FreshnessDependencyContract::class, FreshnessDependencyV2::class);

$report = $reloaded->compileReport([FreshnessConsumer::class, FreshnessDependencyContract::class]);
$debug = $reloaded->debugService(FreshnessDependencyContract::class);
$resolved = $reloaded->get(FreshnessConsumer::class);

assertTrue($report !== null, 'Freshness checks should still expose compile reports.');
assertTrue(! $report->available, 'Stale compiled artifacts must not stay available.');
assertTrue($report->compatible, 'Stale artifacts should still report structural compatibility when only service signatures changed.');
assertSame('stale', $report->freshnessState, 'Stale service signatures must be reported explicitly.');
assertTrue(
    in_array('compiled artifact signatures are stale', $report->warnings, true),
    'Compile reports should warn when compiled service signatures are stale.'
);
assertTrue(! $reloaded->isCompiled(FreshnessConsumer::class), 'Stale compiled artifacts must not report compiled availability.');
assertSame('dynamic', $debug['compiledState']['decision'], 'Debug diagnostics should expose dynamic fallback for stale artifacts.');
assertSame(
    'compiled artifact is stale',
    $debug['explain']['fallback']['reason'],
    'Explain diagnostics should expose stale-artifact fallback reasons.'
);
assertSame('v2', $resolved->dependency->version(), 'Runtime should resolve the fresh dynamic graph after signature drift.');

echo basename(__FILE__) . " ok\n";
