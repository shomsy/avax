<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;

final class CompileReportDependency
{
    public function value() : string
    {
        return 'dependency';
    }
}

final class CompileReportDeferredService
{
    public function value() : string
    {
        return 'deferred';
    }
}

interface CompileReportContract
{
    public function dependency() : CompileReportDependency;
}

final class CompileReportService implements CompileReportContract
{
    public function __construct(private CompileReportDependency $dependency)
    {
    }

    public function dependency() : CompileReportDependency
    {
        return $this->dependency;
    }
}

$cacheDir = sys_get_temp_dir() . '/container-compile-report-' . uniqid();
$config = CreateContainerConfig::create(
    cacheDir: $cacheDir,
    cacheVersion: 'compile-report-smoke',
    compileMode: CreateContainerConfig::COMPILE_MODE_WARMUP
);

$container = makeTestContainer($config);
$container->singleton(CompileReportContract::class, CompileReportService::class);
$container->alias('compile.report', CompileReportContract::class);
$container->tag(CompileReportContract::class, 'reports');
$container->defer(CompileReportDeferredService::class, CompileReportDeferredService::class);
$container->compileContainer([CompileReportContract::class, CompileReportDependency::class]);
$container->compileContainer([CompileReportContract::class, CompileReportDependency::class]);
$container->warmCompiled([CompileReportContract::class, CompileReportDependency::class]);

$report = $container->compileReport([CompileReportContract::class]);

assertTrue($report !== null, 'Compile report should exist when compiler support is configured.');
assertTrue($report->available, 'Compile report should mark the artifact as available.');
assertTrue($report->compatible, 'Compile report should expose runtime compatibility for the attached artifact.');
assertSame('fresh', $report->freshnessState, 'Healthy compile reports should expose a fresh artifact state.');
assertSame([], $report->compatibilityIssues, 'Healthy compile reports should have no compatibility issues.');
assertSame([], $report->warnings, 'Healthy compile reports should avoid warnings.');
assertTrue(in_array(CompileReportContract::class, $report->entries, true), 'Compile report should expose compiled service ids.');
assertSame(2, $report->compiledServicesCount, 'Compile report should expose the compiled service count.');
assertSame(2, $report->totalServices, 'Compile report should expose the total compiled service set.');
assertSame(1, $report->deferredServicesCount, 'Compile report should expose deferred service counts from metadata statistics.');
assertSame(1, $report->tagIndexSize, 'Compile report should expose tag index size.');
assertSame(1, $report->aliasMapSize, 'Compile report should expose alias map size.');
assertSame(['shared' => 1, 'transient' => 2], $report->lifetimePlanSummary, 'Compile report should expose lifetime plan summaries.');
assertSame(
    'shared',
    $report->metadata?->lifetimePlans()[CompileReportContract::class]->name,
    'Compile metadata should expose first-class lifetime plans.'
);
assertSame(
    CompileReportContract::class,
    $report->metadata?->aliases['compile.report'] ?? null,
    'Compile metadata should expose flattened alias mappings.'
);
assertSame(
    [CompileReportContract::class],
    $report->metadata?->tags['reports'] ?? [],
    'Compile metadata should expose deterministic tag indexes.'
);
assertSame(
    [CompileReportDependency::class],
    $report->metadata?->dependencies[CompileReportContract::class] ?? [],
    'Compile metadata should expose compiled dependency graphs.'
);
assertSame(
    CreateContainerConfig::COMPILE_MODE_WARMUP,
    $report->compileMode,
    'Compile reports should expose the active compile mode.'
);
assertSame(
    CreateContainerConfig::EXECUTION_MODE_COMPILED,
    $report->executionMode,
    'Compile reports should expose the active execution mode.'
);
assertSame(
    CreateContainerConfig::PRUNE_MODE_NONE,
    $report->pruneMode,
    'Compile reports should expose the active prune mode.'
);
assertSame(
    CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL,
    $report->metadata?->diagnosticsMode,
    'Compile metadata should expose the diagnostics mode that produced the artifact.'
);
assertTrue($report->metadata?->warmed ?? false, 'Warm compilation should mark the artifact metadata as warmed.');
assertSame(8, $report->metadata?->schemaVersion, 'Compile metadata should expose a stable schema version.');
assertTrue(($report->metadata?->dependencyGraphRevision ?? '') !== '', 'Compile metadata should expose dependency graph provenance.');
assertSame(
    CreateContainerConfig::EXECUTION_MODE_COMPILED,
    $report->metadata?->executionMode,
    'Compile metadata should expose the execution mode that produced the artifact.'
);
assertSame(
    CreateContainerConfig::PRUNE_MODE_NONE,
    $report->metadata?->pruneMode,
    'Compile metadata should expose the prune mode that produced the artifact.'
);
assertSame(
    'default',
    $report->metadata?->ownership[CompileReportContract::class]['ownerSlice'] ?? null,
    'Compile metadata should expose derived ownership maps.'
);
assertTrue(
    isset($report->metadata?->slices['default']),
    'Compile metadata should expose derived slice manifests.'
);
assertSame(
    $report->path,
    $report->metadata?->artifactPaths['compiled'] ?? null,
    'Compile metadata should expose the compiled artifact path.'
);
assertTrue(
    array_key_exists('reusedServices', $report->statistics),
    'Compile reports should expose incremental compilation statistics.'
);
assertTrue(
    ($report->statistics['reusedServices'] ?? 0) >= 1,
    'Repeated compilation should reuse stable compiled service sources when signatures stay unchanged.'
);
assertSame(
    CreateContainerConfig::PRUNE_MODE_NONE,
    $report->pruning['mode'] ?? null,
    'Compile reports should expose pruning posture even when pruning is disabled.'
);
assertSame(
    $report->invalidatedServices,
    $report->metadata?->invalidatedServices ?? [],
    'Compile reports and artifact metadata should agree on invalidated services.'
);

echo basename(__FILE__) . " ok\n";
