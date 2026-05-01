<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class PrunedDependency {}

final class PrunedFlowEntry
{
    public PrunedDependency $dependency;

    public function __construct(PrunedDependency $dependency)
    {
        $this->dependency = $dependency;
    }
}

final class DeadPrunableService {}

$cacheDir = sys_get_temp_dir() . '/container-pruning-smoke-' . uniqid(prefix: '', more_entropy: true);
$container = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: 'pruning-smoke',
    pruneMode   : CreateContainerConfig::PRUNE_MODE_STRICT,
));

$container->bind(abstract: DeadPrunableService::class, concrete: DeadPrunableService::class)
    ->asFlow(ownerSlice: 'flow.dead')
    ->asPrivate();
$container->bind(abstract: PrunedFlowEntry::class, concrete: PrunedFlowEntry::class)
    ->asFlow(ownerSlice: 'flow.used')
    ->asPrivate()
    ->entry();

$container->compileContainer(serviceIds: [PrunedFlowEntry::class, PrunedDependency::class]);

$report    = $container->compileReport(serviceIds: [PrunedFlowEntry::class]);
$deadSlice = $container->forSlice(slice: 'flow.dead');
$deadSliceView = $deadSlice->debugSlice();

assertTrue(condition: $report !== null, message: 'Strict pruning should still produce a compile report.');
assertSame(
    expected: CreateContainerConfig::PRUNE_MODE_STRICT,
    actual  : $report->pruneMode,
    message : 'Compile reports should expose strict pruning mode.',
);
assertSame(
    expected: CreateContainerConfig::PRUNE_MODE_STRICT,
    actual  : $report->metadata?->pruneMode,
    message : 'Compile metadata should expose strict pruning mode.',
);
assertSame(
    expected: CreateContainerConfig::PRUNE_MODE_STRICT,
    actual  : $report->pruning['mode'] ?? null,
    message : 'Pruning diagnostics should expose the active pruning mode.',
);
assertTrue(
    condition: in_array(needle: DeadPrunableService::class, haystack: $report->pruning['prunedServices'] ?? [], strict: true),
    message  : 'Strict pruning should report services removed from the generated artifact.',
);
assertTrue(
    condition: ! in_array(needle: DeadPrunableService::class, haystack: $report->entries, strict: true),
    message  : 'Pruned services should stay out of compiled entries.',
);
assertSame(expected: false, actual: $container->has(id: DeadPrunableService::class), message: 'Pruned private flow services should still stay outside the top-level surface.');
assertSame(expected: true, actual: $deadSlice->has(id: DeadPrunableService::class), message: 'Pruning must not mutate canonical authored slice visibility.');
array_column(array: $deadSliceView['visible'] ?? [], column_key: 'serviceId')
    |> (static fn ($x) => in_array(needle: DeadPrunableService::class, haystack: $x, strict: true))
    |> (static fn ($x) => assertTrue(condition: $x, message: 'Slice views should still expose authored services even when pruning omits them from the artifact.'));

echo basename(path: __FILE__) . " ok\n";
