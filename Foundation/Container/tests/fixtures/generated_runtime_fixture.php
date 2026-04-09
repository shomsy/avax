<?php

declare(strict_types=1);

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class GeneratedFixtureDependency
{
    public function label() : string
    {
        return 'generated';
    }
}

final class GeneratedFixtureEntry
{
    public function __construct(public GeneratedFixtureDependency $dependency)
    {
    }
}

return static function () {
    $cacheDir = sys_get_temp_dir() . '/container-generated-fixture-' . uniqid();
    $container = makeTestContainer(CreateContainerConfig::create(
        cacheDir: $cacheDir,
        cacheVersion: 'generated-fixture',
        executionMode: CreateContainerConfig::EXECUTION_MODE_GENERATED,
        pruneMode: CreateContainerConfig::PRUNE_MODE_STRICT
    ));

    $container->singleton(GeneratedFixtureDependency::class, GeneratedFixtureDependency::class)
        ->asCapability('capability.generated')
        ->asShared()
        ->export();
    $container->bind(GeneratedFixtureEntry::class, GeneratedFixtureEntry::class)
        ->asFlow('flow.generated')
        ->asPrivate()
        ->entry()
        ->import('capability.generated');

    return $container;
};
