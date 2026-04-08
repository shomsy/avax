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
$container->compileContainer([CompileReportContract::class, CompileReportDependency::class]);
$container->compileContainer([CompileReportContract::class, CompileReportDependency::class]);

$report = $container->compileReport([CompileReportContract::class]);

assertTrue($report !== null, 'Compile report should exist when compiler support is configured.');
assertTrue($report->available, 'Compile report should mark the artifact as available.');
assertTrue($report->compatible, 'Compile report should expose runtime compatibility for the attached artifact.');
assertSame([], $report->compatibilityIssues, 'Healthy compile reports should have no compatibility issues.');
assertTrue(in_array(CompileReportContract::class, $report->entries, true), 'Compile report should expose compiled service ids.');
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
assertTrue(
    array_key_exists('reusedServices', $report->statistics),
    'Compile reports should expose incremental compilation statistics.'
);
assertTrue(
    ($report->statistics['reusedServices'] ?? 0) >= 1,
    'Repeated compilation should reuse stable compiled service sources when signatures stay unchanged.'
);
assertSame(
    $report->invalidatedServices,
    $report->metadata?->invalidatedServices ?? [],
    'Compile reports and artifact metadata should agree on invalidated services.'
);

echo basename(__FILE__) . " ok\n";
