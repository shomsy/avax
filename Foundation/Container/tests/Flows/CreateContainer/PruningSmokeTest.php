<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class PrunedDependency {}

final class PrunedFlowEntry
{
    public function __construct(public PrunedDependency $dependency) {}
}

final class DeadPrunableService {}

$cacheDir  = sys_get_temp_dir() . '/container-pruning-smoke-' . uniqid('', true);
$container = makeTestContainer(CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: 'pruning-smoke',
    pruneMode   : CreateContainerConfig::PRUNE_MODE_STRICT
));

$container->bind(DeadPrunableService::class, DeadPrunableService::class)
    ->asFlow('flow.dead')
    ->asPrivate();
$container->bind(PrunedFlowEntry::class, PrunedFlowEntry::class)
    ->asFlow('flow.used')
    ->asPrivate()
    ->entry();

$container->compileContainer([PrunedFlowEntry::class, PrunedDependency::class]);

$report        = $container->compileReport([PrunedFlowEntry::class]);
$deadSlice     = $container->forSlice('flow.dead');
$deadSliceView = $deadSlice->debugSlice();

assertTrue($report !== null, 'Strict pruning should still produce a compile report.');
assertSame(
    CreateContainerConfig::PRUNE_MODE_STRICT,
    $report->pruneMode,
    'Compile reports should expose strict pruning mode.'
);
assertSame(
    CreateContainerConfig::PRUNE_MODE_STRICT,
    $report->metadata?->pruneMode,
    'Compile metadata should expose strict pruning mode.'
);
assertSame(
    CreateContainerConfig::PRUNE_MODE_STRICT,
    $report->pruning['mode'] ?? null,
    'Pruning diagnostics should expose the active pruning mode.'
);
assertTrue(
    in_array(DeadPrunableService::class, $report->pruning['prunedServices'] ?? [], true),
    'Strict pruning should report services removed from the generated artifact.'
);
assertTrue(
    ! in_array(DeadPrunableService::class, $report->entries, true),
    'Pruned services should stay out of compiled entries.'
);
assertSame(false, $container->has(DeadPrunableService::class), 'Pruned private flow services should still stay outside the top-level surface.');
assertSame(true, $deadSlice->has(DeadPrunableService::class), 'Pruning must not mutate canonical authored slice visibility.');
assertTrue(
    in_array(DeadPrunableService::class, array_column($deadSliceView['visible'] ?? [], 'serviceId'), true),
    'Slice views should still expose authored services even when pruning omits them from the artifact.'
);

echo basename(__FILE__) . " ok\n";
