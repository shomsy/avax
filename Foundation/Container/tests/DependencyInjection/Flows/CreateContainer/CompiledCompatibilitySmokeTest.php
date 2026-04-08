<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;

final class CompatibilityDependency
{
    public function id() : string
    {
        return 'compatibility';
    }
}

final class CompatibilityTarget
{
    public function __construct(public CompatibilityDependency $dependency)
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-compatibility-' . uniqid();
$version = 'compiled-compatibility-smoke';

$compiled = makeTestContainer(CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
    settings    : ['app_env' => 'prod'],
));
$compiled->singleton(CompatibilityDependency::class, CompatibilityDependency::class);
$compiled->compileContainer([CompatibilityTarget::class, CompatibilityDependency::class]);

$reloaded = makeTestContainer(CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_DEV,
    settings    : ['app_env' => 'dev'],
    debug       : true,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED,
));
$reloaded->singleton(CompatibilityDependency::class, CompatibilityDependency::class);

$report = $reloaded->compileReport([CompatibilityTarget::class]);
$resolved = $reloaded->get(CompatibilityTarget::class);

assertTrue($report !== null, 'Compile reports should still exist for incompatible artifacts.');
assertTrue(! $report->available, 'Incompatible compiled artifacts must not be reported as available.');
assertTrue(! $report->compatible, 'Incompatible compiled artifacts must report compatibility failure.');
assertSame('incompatible', $report->freshnessState, 'Incompatible compiled artifacts must expose an incompatible freshness state.');
assertTrue(
    in_array('config hash mismatch', $report->compatibilityIssues, true),
    'Compile reports should expose config compatibility mismatches.'
);
assertTrue(
    in_array('compile mode mismatch', $report->compatibilityIssues, true),
    'Compile reports should expose compile mode compatibility mismatches.'
);
assertTrue(
    in_array('diagnostics mode mismatch', $report->compatibilityIssues, true),
    'Compile reports should expose diagnostics mode compatibility mismatches.'
);
assertTrue(! $reloaded->isCompiled(CompatibilityTarget::class), 'Incompatible artifacts must not report compiled service availability.');
assertSame('compatibility', $resolved->dependency->id(), 'Runtime should fall back to dynamic resolution for incompatible artifacts.');

echo basename(__FILE__) . " ok\n";
