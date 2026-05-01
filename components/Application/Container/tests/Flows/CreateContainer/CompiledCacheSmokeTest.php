<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class CompiledCacheDependency
{
    public function id(): string
    {
        return 'compiled';
    }
}

final class CompiledCacheTarget
{
    public function __construct(public CompiledCacheDependency $compiledCacheDependency)
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-compiled-' . uniqid();
$version  = 'compiled-smoke';
$config   = CreateContainerConfig::create(cacheDir: $cacheDir, cacheVersion: $version);
$artifact = $cacheDir . '/container/' . rawurlencode(string: $version) . '/blueprints/' . sha1(string: CompiledCacheTarget::class) . '.php';

$container = makeTestContainer(config: $config);
$container->warmCompiled(serviceIds: [CompiledCacheTarget::class, CompiledCacheDependency::class]);

assertTrue(condition: is_file(filename: $artifact), message: 'Warmup should write compiled blueprints to disk.');
assertTrue(condition: str_contains(haystack: (string) $container->exportMetrics(), needle: 'container_compiled_warmups_total'), message: 'Warmup should be reported in metrics.');

$container->flushCompiled();
assertTrue(condition: ! is_file(filename: $artifact), message: 'Flush should remove compiled blueprints.');

$container->rebuildCompiled(serviceIds: [CompiledCacheTarget::class, CompiledCacheDependency::class]);
assertTrue(condition: is_file(filename: $artifact), message: 'Rebuild should repopulate compiled blueprints.');
assertTrue(condition: str_contains(haystack: (string) $container->exportMetrics(), needle: 'container_compiled_rebuilds_total'), message: 'Rebuild should be reported in metrics.');

$second   = makeTestContainer(config: $config);
$resolved = $second->get(id: CompiledCacheTarget::class);
$metrics  = $second->exportMetrics();

assertInstanceOf(expectedClass: CompiledCacheTarget::class, value: $resolved, message: 'Compiled cache should still resolve services correctly.');
assertSame(expected: 'compiled', actual: $resolved->dependency->id(), message: 'Compiled cache should preserve dependency resolution behavior.');
assertTrue(condition: str_contains(haystack: (string) $metrics, needle: 'container_blueprint_cache_disk_hits_total'), message: 'Runtime should report compiled blueprint disk hits.');

echo basename(path: __FILE__) . " ok\n";
