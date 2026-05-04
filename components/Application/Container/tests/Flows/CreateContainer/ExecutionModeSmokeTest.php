<?php

declare(strict_types=1);

require_once dirname(2, path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class ExecutionModeSmokeTest
{
}

final class ExecutionModeService
{
    public function __construct(public ExecutionModeDependency $executionModeDependency)
    {
    }
}

$generatedCacheDir = sys_get_temp_dir() . '/container-generated-mode-' . uniqid(prefix: '', more_entropy: true);
$generated         = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir     : $generatedCacheDir,
    cacheVersion : 'generated-mode-smoke',
    executionMode: CreateContainerConfig::EXECUTION_MODE_GENERATED,
));
$generated->compileContainer(serviceIds: [ExecutionModeService::class, ExecutionModeDependency::class]);
$generated->get(id: ExecutionModeService::class);

$generatedCompileReport = $generated->compileReport(serviceIds: [ExecutionModeService::class]);
$generatedRuntimeReport = $generated->runtimeReport();
$generatedDescription   = $generated->describeService(id: ExecutionModeService::class);

assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_GENERATED,
    actual  : $generatedCompileReport?->executionMode,
    message : 'Generated mode should be preserved in compile reports.',
);
assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_GENERATED,
    actual  : $generatedRuntimeReport->executionMode,
    message : 'Generated mode should be preserved in runtime reports.',
);
assertSame(
    expected: 'generated',
    actual  : $generatedDescription['compiledState']['decision'] ?? null,
    message : 'Generated mode should resolve through the generated execution lane.',
);
assertSame(
    expected: 'generated execution path is attached and usable',
    actual  : $generatedDescription['compiledState']['reason'] ?? null,
    message : 'Generated mode should remain explainable.',
);

$dynamicCacheDir = sys_get_temp_dir() . '/container-dynamic-mode-' . uniqid(prefix: '', more_entropy: true);
$dynamic         = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir     : $dynamicCacheDir,
    cacheVersion : 'dynamic-mode-smoke',
    executionMode: CreateContainerConfig::EXECUTION_MODE_DYNAMIC,
));
$dynamic->compileContainer(serviceIds: [ExecutionModeService::class, ExecutionModeDependency::class]);
$dynamic->get(id: ExecutionModeService::class);

$dynamicCompileReport = $dynamic->compileReport(serviceIds: [ExecutionModeService::class]);
$dynamicRuntimeReport = $dynamic->runtimeReport();
$dynamicDescription   = $dynamic->describeService(id: ExecutionModeService::class);

assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_DYNAMIC,
    actual  : $dynamicCompileReport?->executionMode,
    message : 'Dynamic mode should be preserved in compile reports.',
);
assertSame(
    expected: CreateContainerConfig::EXECUTION_MODE_DYNAMIC,
    actual  : $dynamicRuntimeReport->executionMode,
    message : 'Dynamic mode should be preserved in runtime reports.',
);
assertSame(
    expected: 'dynamic',
    actual  : $dynamicDescription['compiledState']['decision'] ?? null,
    message : 'Dynamic mode should disable the attached compiled hot path deterministically.',
);
assertSame(
    expected: 'execution mode is dynamic',
    actual  : $dynamicDescription['compiledState']['reason'] ?? null,
    message : 'Dynamic mode fallback should stay explainable.',
);
assertSame(
    expected: false,
    actual  : $dynamicDescription['compiledState']['attached'] ?? true,
    message : 'Dynamic mode should keep compiled hot-path attachment disabled.',
);
assertSame(
    expected: true,
    actual  : $dynamicDescription['compiledState']['artifactAvailable'] ?? false,
    message : 'Dynamic mode should still preserve compiled artifacts as derived outputs.',
);

echo basename(path: __FILE__) . " ok\n";
