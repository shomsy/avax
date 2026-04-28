<?php

declare(strict_types=1);

require_once dirname(path: __DIR__) . '/bootstrap.php';

use Avax\Components\Application\Container\DI\Capabilities\Composition\Compilation\CompileReport;
use Avax\Components\Application\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Components\Application\Container\DI\Capabilities\Diagnostics\Observability\RuntimeReport;

final class DiagnosticsSchemaDependency
{
    public function value() : string
    {
        return 'diagnostics-schema';
    }
}

final class DiagnosticsSchemaConsumer
{
    public DiagnosticsSchemaDependency $dependency;

    public function __construct(DiagnosticsSchemaDependency $dependency) { $this->dependency = $dependency; }
}

/**
 * @param array<string, mixed> $payload
 * @param list<string>         $requiredKeys
 */
function assertSchemaKeys(array $payload, array $requiredKeys, string $label) : void
{
    foreach ($requiredKeys as $key) {
        assertTrue(condition: array_key_exists(key: $key, array: $payload), message: "{$label} should expose required key [{$key}].");
    }
}

$cacheDir  = sys_get_temp_dir() . '/container-diagnostics-schema-' . uniqid();
$container = makeTestContainer(config: CreateContainerConfig::create(
    cacheDir       : $cacheDir,
    cacheVersion   : 'diagnostics-schema',
    compileMode    : CreateContainerConfig::COMPILE_MODE_CI,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI
));

$container->singleton(abstract: DiagnosticsSchemaDependency::class, concrete: DiagnosticsSchemaDependency::class);
$container->compileContainer(serviceIds: [DiagnosticsSchemaConsumer::class, DiagnosticsSchemaDependency::class]);
$container->get(id: DiagnosticsSchemaConsumer::class);

$compileReport = $container->compileReport(serviceIds: [DiagnosticsSchemaConsumer::class]);
$runtimeReport = $container->runtimeReport();

assertTrue(condition: $compileReport instanceof CompileReport, message: 'Compile report should be available for diagnostics schema validation.');
assertInstanceOf(expectedClass: RuntimeReport::class, value: $runtimeReport, message: 'Runtime report should be available for diagnostics schema validation.');

$compilePayload = $compileReport->toArray();
$runtimePayload = $runtimeReport->toArray();

assertSame(expected: CompileReport::SCHEMA_VERSION, actual: $compilePayload['schemaVersion'], message: 'Compile report schema version should stay explicit.');
assertSame(expected: RuntimeReport::SCHEMA_VERSION, actual: $runtimePayload['schemaVersion'], message: 'Runtime report schema version should stay explicit.');

assertSchemaKeys(
    payload     : $compilePayload,
    requiredKeys: [
                      'schemaVersion',
                      'available',
                      'compatible',
                      'freshnessState',
                      'warnings',
                      'compileMode',
                      'executionMode',
                      'pruneMode',
                      'environment',
                      'fingerprint',
                      'entries',
                      'pruning',
                      'compatibilityIssues',
                      'statistics',
                      'metadata',
                  ],
    label       : 'Compile report'
);
assertSchemaKeys(
    payload     : $runtimePayload,
    requiredKeys: [
                      'schemaVersion',
                      'registrationRevision',
                      'compiledRevision',
                      'compiledAttached',
                      'warmedUp',
                      'diagnosticsMode',
                      'executionMode',
                      'asyncTarget',
                      'sliceBoundaryMode',
                      'timelineEnabled',
                      'sharedServiceCount',
                      'scopedServiceCount',
                      'metrics',
                      'timeline',
                      'scopes',
                      'hotPath',
                      'compiled',
                  ],
    label       : 'Runtime report'
);

$compileJson = json_decode(json: $compileReport->toJson(), associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
$runtimeJson = json_decode(json: $runtimeReport->toJson(), associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);

assertSame(
    expected: CompileReport::SCHEMA_VERSION,
    actual  : $compileJson['schemaVersion'] ?? null,
    message : 'Compile report JSON should preserve its schema version.'
);
assertSame(
    expected: RuntimeReport::SCHEMA_VERSION,
    actual  : $runtimeJson['schemaVersion'] ?? null,
    message : 'Runtime report JSON should preserve its schema version.'
);

echo basename(path: __FILE__) . " ok\n";
