<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class ExecutionModeDependency {}

final class ExecutionModeService
{
    public function __construct(public ExecutionModeDependency $dependency) {}
}

$generatedCacheDir = sys_get_temp_dir() . '/container-generated-mode-' . uniqid('', true);
$generated         = makeTestContainer(CreateContainerConfig::create(
    cacheDir     : $generatedCacheDir,
    cacheVersion : 'generated-mode-smoke',
    executionMode: CreateContainerConfig::EXECUTION_MODE_GENERATED
));
$generated->compileContainer([ExecutionModeService::class, ExecutionModeDependency::class]);
$generated->get(ExecutionModeService::class);

$generatedCompileReport = $generated->compileReport([ExecutionModeService::class]);
$generatedRuntimeReport = $generated->runtimeReport();
$generatedDescription   = $generated->describeService(ExecutionModeService::class);

assertSame(
    CreateContainerConfig::EXECUTION_MODE_GENERATED,
    $generatedCompileReport?->executionMode,
    'Generated mode should be preserved in compile reports.'
);
assertSame(
    CreateContainerConfig::EXECUTION_MODE_GENERATED,
    $generatedRuntimeReport->executionMode,
    'Generated mode should be preserved in runtime reports.'
);
assertSame(
    'generated',
    $generatedDescription['compiledState']['decision'] ?? null,
    'Generated mode should resolve through the generated execution lane.'
);
assertSame(
    'generated execution path is attached and usable',
    $generatedDescription['compiledState']['reason'] ?? null,
    'Generated mode should remain explainable.'
);

$dynamicCacheDir = sys_get_temp_dir() . '/container-dynamic-mode-' . uniqid('', true);
$dynamic         = makeTestContainer(CreateContainerConfig::create(
    cacheDir     : $dynamicCacheDir,
    cacheVersion : 'dynamic-mode-smoke',
    executionMode: CreateContainerConfig::EXECUTION_MODE_DYNAMIC
));
$dynamic->compileContainer([ExecutionModeService::class, ExecutionModeDependency::class]);
$dynamic->get(ExecutionModeService::class);

$dynamicCompileReport = $dynamic->compileReport([ExecutionModeService::class]);
$dynamicRuntimeReport = $dynamic->runtimeReport();
$dynamicDescription   = $dynamic->describeService(ExecutionModeService::class);

assertSame(
    CreateContainerConfig::EXECUTION_MODE_DYNAMIC,
    $dynamicCompileReport?->executionMode,
    'Dynamic mode should be preserved in compile reports.'
);
assertSame(
    CreateContainerConfig::EXECUTION_MODE_DYNAMIC,
    $dynamicRuntimeReport->executionMode,
    'Dynamic mode should be preserved in runtime reports.'
);
assertSame(
    'dynamic',
    $dynamicDescription['compiledState']['decision'] ?? null,
    'Dynamic mode should disable the attached compiled hot path deterministically.'
);
assertSame(
    'execution mode is dynamic',
    $dynamicDescription['compiledState']['reason'] ?? null,
    'Dynamic mode fallback should stay explainable.'
);
assertSame(
    false,
    $dynamicDescription['compiledState']['attached'] ?? true,
    'Dynamic mode should keep compiled hot-path attachment disabled.'
);
assertSame(
    true,
    $dynamicDescription['compiledState']['artifactAvailable'] ?? false,
    'Dynamic mode should still preserve compiled artifacts as derived outputs.'
);

echo basename(__FILE__) . " ok\n";
