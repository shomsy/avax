<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

$tool = dirname(__DIR__, 4) . '/tools/generate-runtime-artifacts.php';
$fixture = dirname(__DIR__, 3) . '/fixtures/generated_runtime_fixture.php';
$outputDir = sys_get_temp_dir() . '/generated-artifacts-smoke-' . uniqid();

mkdir($outputDir, 0777, true);

$result = shell_exec(
    'php ' . escapeshellarg($tool) . ' ' . escapeshellarg($fixture) . ' ' . escapeshellarg($outputDir)
);

assertTrue(is_string($result) && str_contains($result, 'compile-report.json'), 'Generated artifact tool should report emitted files.');
assertTrue(is_file($outputDir . '/compile-report.json'), 'Generated artifact tool should emit compile report JSON.');
assertTrue(is_file($outputDir . '/runtime-report.json'), 'Generated artifact tool should emit runtime report JSON.');
assertTrue(is_file($outputDir . '/governance.json'), 'Generated artifact tool should emit governance JSON.');
assertTrue(is_file($outputDir . '/architecture.json'), 'Generated artifact tool should emit architecture JSON.');
assertTrue(is_file($outputDir . '/dependency-graph.html'), 'Generated artifact tool should emit an HTML graph explorer.');

$compileReport = file_get_contents($outputDir . '/compile-report.json');
$runtimeReport = file_get_contents($outputDir . '/runtime-report.json');
$html = file_get_contents($outputDir . '/dependency-graph.html');

assertTrue(is_string($compileReport) && str_contains($compileReport, '"executionMode": "generated"'), 'Generated compile report should preserve generated execution mode.');
assertTrue(is_string($runtimeReport) && str_contains($runtimeReport, '"executionMode": "generated"'), 'Generated runtime report should preserve generated execution mode.');
assertTrue(is_string($html) && str_contains($html, 'Container Graph Explorer'), 'Generated artifact tool should emit a human-readable graph explorer.');

echo basename(__FILE__) . " ok\n";
