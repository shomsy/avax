<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class CompiledCacheDependency
{
    public function id() : string
    {
        return 'compiled';
    }
}

final class CompiledCacheTarget
{
    public function __construct(public CompiledCacheDependency $dependency) {}
}

$cacheDir = sys_get_temp_dir() . '/container-compiled-' . uniqid();
$version  = 'compiled-smoke';
$config   = CreateContainerConfig::create(cacheDir: $cacheDir, cacheVersion: $version);
$artifact = $cacheDir . '/container/' . rawurlencode($version) . '/blueprints/' . sha1(CompiledCacheTarget::class) . '.php';

$container = makeTestContainer($config);
$container->warmCompiled([CompiledCacheTarget::class, CompiledCacheDependency::class]);

assertTrue(is_file($artifact), 'Warmup should write compiled blueprints to disk.');
assertTrue(str_contains($container->exportMetrics(), 'container_compiled_warmups_total'), 'Warmup should be reported in metrics.');

$container->flushCompiled();
assertTrue(! is_file($artifact), 'Flush should remove compiled blueprints.');

$container->rebuildCompiled([CompiledCacheTarget::class, CompiledCacheDependency::class]);
assertTrue(is_file($artifact), 'Rebuild should repopulate compiled blueprints.');
assertTrue(str_contains($container->exportMetrics(), 'container_compiled_rebuilds_total'), 'Rebuild should be reported in metrics.');

$second   = makeTestContainer($config);
$resolved = $second->get(CompiledCacheTarget::class);
$metrics  = $second->exportMetrics();

assertInstanceOf(CompiledCacheTarget::class, $resolved, 'Compiled cache should still resolve services correctly.');
assertSame('compiled', $resolved->dependency->id(), 'Compiled cache should preserve dependency resolution behavior.');
assertTrue(str_contains($metrics, 'container_blueprint_cache_disk_hits_total'), 'Runtime should report compiled blueprint disk hits.');

echo basename(__FILE__) . " ok\n";
