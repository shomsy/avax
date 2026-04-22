<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

$tool      = dirname(__DIR__, 4) . '/tools/generate-analysis-hints.php';
$fixture   = dirname(__DIR__, 3) . '/fixtures/analysis_hints_fixture.php';
$outputDir = sys_get_temp_dir() . '/container-analysis-hints-' . uniqid('', true);

$result = shell_exec(
    'php ' . escapeshellarg($tool)
    . ' ' . escapeshellarg($fixture)
    . ' ' . escapeshellarg($outputDir)
);

$jsonPath = $outputDir . '/container-static-hints.json';
$stubPath = $outputDir . '/container-static-hints.stub.php';

assertTrue(condition: is_string($result) && str_contains($result, 'container-static-hints.json'), message: 'Hint generator should report generated artifacts.');
assertTrue(condition: is_file($jsonPath), message: 'Hint generator should emit a JSON artifact.');
assertTrue(condition: is_file($stubPath), message: 'Hint generator should emit a PHP stub artifact.');

$payload = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
$stub    = (string) file_get_contents($stubPath);

assertSame(expected: 1, actual: $payload['schemaVersion'] ?? null, message: 'Hint payloads should expose a stable schema version.');
assertTrue(condition: in_array('HintIdentityService', $payload['serviceIds'] ?? [], true), message: 'Hint payloads should include service ids.');
assertSame(expected: ['capability.identity'], actual: $payload['sliceImports']['flow.hints'] ?? [], message: 'Hint payloads should include slice imports.');
assertTrue(
    condition: in_array(HintIdentityService::class, $payload['sliceExports']['capability.identity'] ?? [], true),
    message  : 'Hint payloads should include stable exported service ids for the owning slice.'
);
assertSame(expected: [HintPipelineStepA::class, HintPipelineStepB::class], actual: $payload['groups']['hint.pipeline'] ?? [], message: 'Hint payloads should include grouped bindings.');
assertSame(expected: ['token'], actual: $payload['runtimeInputs'][HintRuntimeInputConsumer::class] ?? [], message: 'Hint payloads should include runtime input requirements.');
assertTrue(condition: isset($payload['conditionals'][HintConditionalService::class]), message: 'Hint payloads should include conditional registrations.');
assertTrue(condition: str_contains($stub, '@phpstan-type ContainerServiceId'), message: 'Hint stubs should expose PHPStan aliases.');
assertTrue(condition: str_contains($stub, '@psalm-type ContainerServiceId'), message: 'Hint stubs should expose Psalm aliases.');

echo basename(__FILE__) . " ok\n";
