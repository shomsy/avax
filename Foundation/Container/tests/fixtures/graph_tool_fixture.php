<?php

declare(strict_types=1);

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

require_once dirname(__DIR__) . '/bootstrap.php';

final class GraphToolIdentityService {}

final class GraphToolIdentitySecret {}

final class GraphToolLoginEntry
{
    public function __construct(public GraphToolIdentityService $identity) {}
}

final class GraphToolStructureTarget {}

final class GraphToolStepOne {}

final class GraphToolStepTwo {}

$cacheDir  = sys_get_temp_dir() . '/container-graph-tool-' . uniqid('', true);
$container = makeTestContainer(CreateContainerConfig::create(cacheDir: $cacheDir));

$container->singleton(GraphToolIdentityService::class, GraphToolIdentityService::class)
    ->asCapability('capability.identity')
    ->asShared()
    ->export();
$container->bind(GraphToolIdentitySecret::class, GraphToolIdentitySecret::class)
    ->asCapability('capability.identity')
    ->asInternal();
$container->bind(GraphToolLoginEntry::class, GraphToolLoginEntry::class)
    ->asFlow('flow.login')
    ->asPrivate()
    ->entry()
    ->import('capability.identity');
$container->singleton(GraphToolStructureTarget::class, GraphToolStructureTarget::class)
    ->asCapability('capability.graph')
    ->asShared()
    ->export();
$container->singleton(GraphToolStepOne::class, GraphToolStepOne::class)
    ->asCapability('capability.graph')
    ->asShared()
    ->export()
    ->group('graph.steps', 20);
$container->singleton(GraphToolStepTwo::class, GraphToolStepTwo::class)
    ->asCapability('capability.graph')
    ->asShared()
    ->export()
    ->group('graph.steps', 10);

$container->compileContainer([
                                 GraphToolStructureTarget::class,
                                 GraphToolLoginEntry::class,
                                 GraphToolIdentityService::class,
                                 GraphToolStepOne::class,
                                 GraphToolStepTwo::class,
                             ]);
$container->singleton(GraphToolStructureTarget::class, GraphToolStructureTarget::class)
    ->asFoundation('foundation.graph')
    ->asPublic();

return $container;
