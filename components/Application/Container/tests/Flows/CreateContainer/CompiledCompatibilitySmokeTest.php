<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class CompatibilityDependency
{
    public function id() : string
    {
        return 'compatibility';
    }
}

final class CompatibilityTarget
{
    public CompatibilityDependency $dependency;

    public function __construct(CompatibilityDependency $dependency) { $this->dependency = $dependency; }
}

$cacheDir = sys_get_temp_dir() . '/container-compatibility-' . uniqid();
$version  = 'compiled-compatibility-smoke';

$compiled = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    settings    : ['app_env' => 'prod'],
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
));
$compiled->singleton(abstract: CompatibilityDependency::class, concrete: CompatibilityDependency::class);
$compiled->compileContainer(serviceIds: [CompatibilityTarget::class, CompatibilityDependency::class]);

$reloaded = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir       : $cacheDir,
    cacheVersion   : $version,
    debug          : true,
    settings       : ['app_env' => 'dev'],
    compileMode    : CreateContainerConfig::COMPILE_MODE_DEV,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_DETAILED,
));
$reloaded->singleton(abstract: CompatibilityDependency::class, concrete: CompatibilityDependency::class);

$report   = $reloaded->compileReport(serviceIds: [CompatibilityTarget::class]);
$resolved = $reloaded->get(id: CompatibilityTarget::class);

assertTrue(condition: $report !== null, message: 'Compile reports should still exist for incompatible artifacts.');
assertTrue(condition: ! $report->available, message: 'Incompatible compiled artifacts must not be reported as available.');
assertTrue(condition: ! $report->compatible, message: 'Incompatible compiled artifacts must report compatibility failure.');
assertSame(expected: 'incompatible', actual: $report->freshnessState, message: 'Incompatible compiled artifacts must expose an incompatible freshness state.');
assertTrue(
    condition: in_array(needle: 'config hash mismatch', haystack: $report->compatibilityIssues, strict: true),
    message  : 'Compile reports should expose config compatibility mismatches.'
);
assertTrue(
    condition: in_array(needle: 'compile mode mismatch', haystack: $report->compatibilityIssues, strict: true),
    message  : 'Compile reports should expose compile mode compatibility mismatches.'
);
assertTrue(
    condition: in_array(needle: 'diagnostics mode mismatch', haystack: $report->compatibilityIssues, strict: true),
    message  : 'Compile reports should expose diagnostics mode compatibility mismatches.'
);
assertTrue(condition: ! $reloaded->isCompiled(id: CompatibilityTarget::class), message: 'Incompatible artifacts must not report compiled service availability.');
assertSame(expected: 'compatibility', actual: $resolved->dependency->id(), message: 'Runtime should fall back to dynamic resolution for incompatible artifacts.');

echo basename(path: __FILE__) . " ok\n";
