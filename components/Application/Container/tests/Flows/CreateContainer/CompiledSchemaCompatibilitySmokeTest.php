<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, 2) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;

final class SchemaCompatibilityDependency {}

final class SchemaCompatibilityService
{
    public function __construct(public SchemaCompatibilityDependency $schemaCompatibilityDependency) {}
}

$cacheDir     = sys_get_temp_dir() . '/container-schema-compatibility-' . uniqid();
$version      = 'compiled-schema-compatibility';
$metadataPath = $cacheDir . '/container/' . rawurlencode(string: $version) . '/compiled/container.json';

$compiled = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
));
$compiled->singleton(abstract: SchemaCompatibilityDependency::class, concrete: SchemaCompatibilityDependency::class);
$compiled->compileContainer(serviceIds: [SchemaCompatibilityService::class, SchemaCompatibilityDependency::class]);

$metadata = json_decode(json: (string) file_get_contents(filename: $metadataPath), associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
$metadata['schemaVersion'] = 999;
file_put_contents(filename: $metadataPath, data: json_encode(value: $metadata, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

$reloaded = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir    : $cacheDir,
    cacheVersion: $version,
    compileMode : CreateContainerConfig::COMPILE_MODE_PRODUCTION,
));
$reloaded->singleton(abstract: SchemaCompatibilityDependency::class, concrete: SchemaCompatibilityDependency::class);

$report = $reloaded->compileReport(serviceIds: [SchemaCompatibilityService::class]);
$resolved = $reloaded->get(id: SchemaCompatibilityService::class);

assertTrue(condition: $report !== null, message: 'Schema compatibility checks should still expose compile reports.');
assertTrue(condition: ! $report->available, message: 'Schema-incompatible artifacts must not be available.');
assertTrue(condition: ! $report->compatible, message: 'Schema-incompatible artifacts must fail compatibility checks.');
assertSame(expected: 'incompatible', actual: $report->freshnessState, message: 'Schema-incompatible artifacts must report incompatible freshness.');
assertTrue(
    condition: in_array(needle: 'schema version mismatch', haystack: $report->compatibilityIssues, strict: true),
    message  : 'Compile reports should expose schema version compatibility mismatches.',
);
assertTrue(condition: ! $reloaded->isCompiled(id: SchemaCompatibilityService::class), message: 'Schema-incompatible artifacts must not report compiled service availability.');
assertInstanceOf(
    expectedClass: SchemaCompatibilityService::class,
    value        : $resolved,
    message      : 'Schema-incompatible artifacts should fall back to dynamic resolution.',
);

echo basename(path: __FILE__) . " ok\n";
