<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

interface CompileReportContract
{
    public function dependency(): CompileReportDependency;
}

final class CompileReportDependency
{
    public function value(): string
    {
        return 'dependency';
    }
}

final class CompileReportDeferredService
{
    public function value(): string
    {
        return 'deferred';
    }
}

final readonly class CompileReportService implements CompileReportContract
{
    public function __construct(private CompileReportDependency $compileReportDependency)
    {
    }

    #[Override]
    public function dependency(): CompileReportDependency
    {
        return $this->compileReportDependency;
    }
}

$cacheDir = sys_get_temp_dir() . '/container-compile-report-' . uniqid();
$config   = CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: 'compile-report-smoke',
    compileMode : CreateContainerConfig::COMPILE_MODE_WARMUP,
);

$container = makeTestContainer(config: $config);
$container->singleton(abstract: CompileReportContract::class, concrete: CompileReportService::class);
$container->alias(alias: 'compile.report', abstract: CompileReportContract::class);
$container->tag(abstracts: CompileReportContract::class, tags: 'reports');
$container->defer(abstract: CompileReportDeferredService::class, concrete: CompileReportDeferredService::class);
$container->compileContainer(serviceIds: [CompileReportContract::class, CompileReportDependency::class]);
$container->compileContainer(serviceIds: [CompileReportContract::class, CompileReportDependency::class]);
$container->warmCompiled(serviceIds: [CompileReportContract::class, CompileReportDependency::class]);

$report = $container->compileReport(serviceIds: [CompileReportContract::class]);

assertTrue(condition: $report !== null, message: 'Compile report should exist when compiler support is configured.');
assertTrue(condition: $report->available, message: 'Compile report should mark the artifact as available.');
assertTrue(condition: $report->compatible, message: 'Compile report should expose runtime compatibility for the attached artifact.');
assertSame(expected: 'fresh', actual: $report->freshnessState, message: 'Healthy compile reports should expose a fresh artifact state.');
assertSame(expected: [], actual: $report->compatibilityIssues, message: 'Healthy compile reports should have no compatibility issues.');
assertSame(expected: [], actual: $report->warnings, message: 'Healthy compile reports should avoid warnings.');
assertTrue(condition: in_array(needle: CompileReportContract::class, haystack: $report->entries, strict: true), message: 'Compile report should expose compiled service ids.');
assertSame(expected: 2, actual: $report->compiledServicesCount, message: 'Compile report should expose the compiled service count.');
assertSame(expected: 2, actual: $report->totalServices, message: 'Compile report should expose the total compiled service set.');
assertSame(expected: 1, actual: $report->deferredServicesCount, message: 'Compile report should expose deferred service counts from metadata statistics.');
assertSame(expected: 1, actual: $report->tagIndexSize, message: 'Compile report should expose tag index size.');
assertSame(expected: 1, actual: $report->aliasMapSize, message: 'Compile report should expose alias map size.');
assertSame(expected: ['shared' => 1, 'transient' => 2], actual: $report->lifetimePlanSummary, message: 'Compile report should expose lifetime plan summaries.');
assertSame(
    expected: 'shared',
    actual  : $report->metadata?->lifetimePlans()[CompileReportContract::class]->name,
    message : 'Compile metadata should expose first-class lifetime plans.',
);
assertSame(
    expected: CompileReportContract::class,
    actual  : $report->metadata?->aliases['compile.report'] ?? null,
    message : 'Compile metadata should expose flattened alias mappings.',
);
assertSame(
    expected: [CompileReportContract::class],
    actual  : $report->metadata?->tags['reports'] ?? [],
    message : 'Compile metadata should expose deterministic tag indexes.',
);
assertSame(
    expected: [CompileReportDependency::class],
    actual  : $report->metadata?->dependencies[CompileReportContract::class] ?? [],
    message : 'Compile metadata should expose compiled dependency graphs.',
);
assertSame(
    expected: CreateContainerConfig::COMPILE_MODE_WARMUP,
    actual  : $report->compileMode,
    message : 'Compile reports should expose the active compile mode.',
);
assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_COMPILED,
    actual  : $report->executionMode,
    message : 'Compile reports should expose the active execution mode.',
);
assertSame(
    expected: CreateContainerConfig::PRUNE_MODE_NONE,
    actual  : $report->pruneMode,
    message : 'Compile reports should expose the active prune mode.',
);
assertSame(
    expected: CreateContainerConfig::DIAGNOSTICS_MODE_MINIMAL,
    actual  : $report->metadata?->diagnosticsMode,
    message : 'Compile metadata should expose the diagnostics mode that produced the artifact.',
);
assertTrue(condition: $report->metadata?->warmed ?? false, message: 'Warm compilation should mark the artifact metadata as warmed.');
assertSame(expected: 8, actual: $report->metadata?->schemaVersion, message: 'Compile metadata should expose a stable schema version.');
assertTrue(condition: ($report->metadata?->dependencyGraphRevision ?? '') !== '', message: 'Compile metadata should expose dependency graph provenance.');
assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_COMPILED,
    actual  : $report->metadata?->executionMode,
    message : 'Compile metadata should expose the execution mode that produced the artifact.',
);
assertSame(
    expected: CreateContainerConfig::PRUNE_MODE_NONE,
    actual  : $report->metadata?->pruneMode,
    message : 'Compile metadata should expose the prune mode that produced the artifact.',
);
assertSame(
    expected: 'default',
    actual  : $report->metadata?->ownership[CompileReportContract::class]['ownerSlice'] ?? null,
    message : 'Compile metadata should expose derived ownership maps.',
);
assertTrue(
    condition: isset($report->metadata?->slices['default']),
    message  : 'Compile metadata should expose derived slice manifests.',
);
assertSame(
    expected: $report->path,
    actual  : $report->metadata?->artifactPaths['compiled'] ?? null,
    message : 'Compile metadata should expose the compiled artifact path.',
);
assertTrue(
    condition: array_key_exists(key: 'reusedServices', array: $report->statistics),
    message  : 'Compile reports should expose incremental compilation statistics.',
);
assertTrue(
    condition: ($report->statistics['reusedServices'] ?? 0) >= 1,
    message  : 'Repeated compilation should reuse stable compiled service sources when signatures stay unchanged.',
);
assertSame(
    expected: CreateContainerConfig::PRUNE_MODE_NONE,
    actual  : $report->pruning['mode'] ?? null,
    message : 'Compile reports should expose pruning posture even when pruning is disabled.',
);
assertSame(
    expected: $report->invalidatedServices,
    actual  : $report->metadata?->invalidatedServices ?? [],
    message : 'Compile reports and artifact metadata should agree on invalidated services.',
);

echo basename(path: __FILE__) . " ok\n";
