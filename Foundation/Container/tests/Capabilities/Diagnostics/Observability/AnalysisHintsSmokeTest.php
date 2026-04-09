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

assertTrue(is_string($result) && str_contains($result, 'container-static-hints.json'), 'Hint generator should report generated artifacts.');
assertTrue(is_file($jsonPath), 'Hint generator should emit a JSON artifact.');
assertTrue(is_file($stubPath), 'Hint generator should emit a PHP stub artifact.');

$payload = json_decode((string) file_get_contents($jsonPath), true, 512, JSON_THROW_ON_ERROR);
$stub    = (string) file_get_contents($stubPath);

assertSame(1, $payload['schemaVersion'] ?? null, 'Hint payloads should expose a stable schema version.');
assertTrue(in_array('HintIdentityService', $payload['serviceIds'] ?? [], true), 'Hint payloads should include service ids.');
assertSame(['capability.identity'], $payload['sliceImports']['flow.hints'] ?? [], 'Hint payloads should include slice imports.');
assertTrue(
    in_array(HintIdentityService::class, $payload['sliceExports']['capability.identity'] ?? [], true),
    'Hint payloads should include stable exported service ids for the owning slice.'
);
assertSame([HintPipelineStepA::class, HintPipelineStepB::class], $payload['groups']['hint.pipeline'] ?? [], 'Hint payloads should include grouped bindings.');
assertSame(['token'], $payload['runtimeInputs'][HintRuntimeInputConsumer::class] ?? [], 'Hint payloads should include runtime input requirements.');
assertTrue(isset($payload['conditionals'][HintConditionalService::class]), 'Hint payloads should include conditional registrations.');
assertTrue(str_contains($stub, '@phpstan-type ContainerServiceId'), 'Hint stubs should expose PHPStan aliases.');
assertTrue(str_contains($stub, '@psalm-type ContainerServiceId'), 'Hint stubs should expose Psalm aliases.');

echo basename(__FILE__) . " ok\n";
