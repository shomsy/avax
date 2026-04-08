<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\Configuration\CreateContainerConfig;

final class SchemaCompatibilityDependency
{
}

final class SchemaCompatibilityService
{
    public function __construct(public SchemaCompatibilityDependency $dependency)
    {
    }
}

$cacheDir = sys_get_temp_dir() . '/container-schema-compatibility-' . uniqid();
$version = 'compiled-schema-compatibility';
$metadataPath = $cacheDir . '/container/' . rawurlencode($version) . '/compiled/container.json';

$compiled = makeTestContainer(CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION
));
$compiled->singleton(SchemaCompatibilityDependency::class, SchemaCompatibilityDependency::class);
$compiled->compileContainer([SchemaCompatibilityService::class, SchemaCompatibilityDependency::class]);

$metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
$metadata['schemaVersion'] = 999;
file_put_contents($metadataPath, json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

$reloaded = makeTestContainer(CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION
));
$reloaded->singleton(SchemaCompatibilityDependency::class, SchemaCompatibilityDependency::class);

$report = $reloaded->compileReport([SchemaCompatibilityService::class]);
$resolved = $reloaded->get(SchemaCompatibilityService::class);

assertTrue($report !== null, 'Schema compatibility checks should still expose compile reports.');
assertTrue(! $report->available, 'Schema-incompatible artifacts must not be available.');
assertTrue(! $report->compatible, 'Schema-incompatible artifacts must fail compatibility checks.');
assertSame('incompatible', $report->freshnessState, 'Schema-incompatible artifacts must report incompatible freshness.');
assertTrue(
    in_array('schema version mismatch', $report->compatibilityIssues, true),
    'Compile reports should expose schema version compatibility mismatches.'
);
assertTrue(! $reloaded->isCompiled(SchemaCompatibilityService::class), 'Schema-incompatible artifacts must not report compiled service availability.');
assertInstanceOf(
    SchemaCompatibilityService::class,
    $resolved,
    'Schema-incompatible artifacts should fall back to dynamic resolution.'
);

echo basename(__FILE__) . " ok\n";
