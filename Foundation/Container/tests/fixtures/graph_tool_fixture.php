<?php

declare(strict_types=1);

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

require_once dirname(__DIR__) . '/bootstrap.php';

final class GraphToolIdentityService {}

final class GraphToolIdentitySecret {}

final class GraphToolLoginEntry
{
    public GraphToolIdentityService $identity;

    public function __construct(GraphToolIdentityService $identity) { $this->identity = $identity; }
}

final class GraphToolStructureTarget {}

final class GraphToolStepOne {}

final class GraphToolStepTwo {}

$cacheDir  = sys_get_temp_dir() . '/container-graph-tool-' . uniqid('', true);
$container = makeTestContainer(config: CreateContainerConfig::create(cacheDir: $cacheDir));

$container->singleton(abstract: GraphToolIdentityService::class, concrete: GraphToolIdentityService::class)
    ->asCapability(ownerSlice: 'capability.identity')
    ->asShared()
    ->export();
$container->bind(abstract: GraphToolIdentitySecret::class, concrete: GraphToolIdentitySecret::class)
    ->asCapability(ownerSlice: 'capability.identity')
    ->asInternal();
$container->bind(abstract: GraphToolLoginEntry::class, concrete: GraphToolLoginEntry::class)
    ->asFlow(ownerSlice: 'flow.login')
    ->asPrivate()
    ->entry()
    ->import(slices: 'capability.identity');
$container->singleton(abstract: GraphToolStructureTarget::class, concrete: GraphToolStructureTarget::class)
    ->asCapability(ownerSlice: 'capability.graph')
    ->asShared()
    ->export();
$container->singleton(abstract: GraphToolStepOne::class, concrete: GraphToolStepOne::class)
    ->asCapability(ownerSlice: 'capability.graph')
    ->asShared()
    ->export()
    ->group(group: 'graph.steps', order: 20);
$container->singleton(abstract: GraphToolStepTwo::class, concrete: GraphToolStepTwo::class)
    ->asCapability(ownerSlice: 'capability.graph')
    ->asShared()
    ->export()
    ->group(group: 'graph.steps', order: 10);

$container->compileContainer(serviceIds: [
                                             GraphToolStructureTarget::class,
                                             GraphToolLoginEntry::class,
                                             GraphToolIdentityService::class,
                                             GraphToolStepOne::class,
                                             GraphToolStepTwo::class,
                                         ]);
$container->singleton(abstract: GraphToolStructureTarget::class, concrete: GraphToolStructureTarget::class)
    ->asFoundation(ownerSlice: 'foundation.graph')
    ->asPublic();

return $container;
