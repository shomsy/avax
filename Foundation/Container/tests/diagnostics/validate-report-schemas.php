<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Composition\Compilation\CompileReport;
use Avax\Container\DI\Capabilities\Composition\CreateContainerConfig;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\RuntimeReport;

final class DiagnosticsSchemaDependency
{
    public function value() : string
    {
        return 'diagnostics-schema';
    }
}

final class DiagnosticsSchemaConsumer
{
    public function __construct(public DiagnosticsSchemaDependency $dependency) {}
}

/**
 * @param array<string, mixed> $payload
 * @param list<string>         $requiredKeys
 */
function assertSchemaKeys(array $payload, array $requiredKeys, string $label) : void
{
    foreach ($requiredKeys as $key) {
        assertTrue(array_key_exists($key, $payload), "{$label} should expose required key [{$key}].");
    }
}

$cacheDir  = sys_get_temp_dir() . '/container-diagnostics-schema-' . uniqid();
$container = makeTestContainer(CreateContainerConfig::create(
    cacheDir       : $cacheDir,
    cacheVersion   : 'diagnostics-schema',
    compileMode    : CreateContainerConfig::COMPILE_MODE_CI,
    diagnosticsMode: CreateContainerConfig::DIAGNOSTICS_MODE_CI
));

$container->singleton(DiagnosticsSchemaDependency::class, DiagnosticsSchemaDependency::class);
$container->compileContainer([DiagnosticsSchemaConsumer::class, DiagnosticsSchemaDependency::class]);
$container->get(DiagnosticsSchemaConsumer::class);

$compileReport = $container->compileReport([DiagnosticsSchemaConsumer::class]);
$runtimeReport = $container->runtimeReport();

assertTrue($compileReport instanceof CompileReport, 'Compile report should be available for diagnostics schema validation.');
assertInstanceOf(RuntimeReport::class, $runtimeReport, 'Runtime report should be available for diagnostics schema validation.');

$compilePayload = $compileReport->toArray();
$runtimePayload = $runtimeReport->toArray();

assertSame(CompileReport::SCHEMA_VERSION, $compilePayload['schemaVersion'], 'Compile report schema version should stay explicit.');
assertSame(RuntimeReport::SCHEMA_VERSION, $runtimePayload['schemaVersion'], 'Runtime report schema version should stay explicit.');

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

$compileJson = json_decode($compileReport->toJson(), true, 512, JSON_THROW_ON_ERROR);
$runtimeJson = json_decode($runtimeReport->toJson(), true, 512, JSON_THROW_ON_ERROR);

assertSame(
    CompileReport::SCHEMA_VERSION,
    $compileJson['schemaVersion'] ?? null,
    'Compile report JSON should preserve its schema version.'
);
assertSame(
    RuntimeReport::SCHEMA_VERSION,
    $runtimeJson['schemaVersion'] ?? null,
    'Runtime report JSON should preserve its schema version.'
);

echo basename(__FILE__) . " ok\n";
