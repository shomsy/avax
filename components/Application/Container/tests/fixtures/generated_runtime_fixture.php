<?php

declare(strict_types=1);

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class GeneratedFixtureDependency
{
    public function label() : string
    {
        return 'generated';
    }
}

final class GeneratedFixtureEntry
{
    public GeneratedFixtureDependency $dependency;

    public function __construct(GeneratedFixtureDependency $dependency) { $this->dependency = $dependency; }
}

return static function () {
    $cacheDir  = sys_get_temp_dir() . '/container-generated-fixture-' . uniqid();
    $container = makeTestContainer(config: CreateContainerConfig::create(
        cacheDir     : $cacheDir,
        cacheVersion : 'generated-fixture',
        executionMode: CreateContainerConfig::EXECUTION_MODE_GENERATED,
        pruneMode    : CreateContainerConfig::PRUNE_MODE_STRICT
    ));

    $container->singleton(abstract: GeneratedFixtureDependency::class, concrete: GeneratedFixtureDependency::class)
        ->asCapability(ownerSlice: 'capability.generated')
        ->asShared()
        ->export();
    $container->bind(abstract: GeneratedFixtureEntry::class, concrete: GeneratedFixtureEntry::class)
        ->asFlow(ownerSlice: 'flow.generated')
        ->asPrivate()
        ->entry()
        ->import(slices: 'capability.generated');

    return $container;
};
