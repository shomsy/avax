<?php

declare(strict_types=1);

require_once dirname(path: __DIR__, levels: 3) . '/bootstrap.php';

$tool      = dirname(path: __DIR__, levels: 4) . '/tools/generate-runtime-artifacts.php';
$fixture   = dirname(path: __DIR__, levels: 3) . '/fixtures/generated_runtime_fixture.php';
$outputDir = sys_get_temp_dir() . '/generated-artifacts-smoke-' . uniqid();

mkdir(directory: $outputDir, permissions: 0777, recursive: true);

$result = shell_exec(
    command: 'php ' . escapeshellarg(arg: $tool) . ' ' . escapeshellarg(arg: $fixture) . ' ' . escapeshellarg(arg: $outputDir)
);

assertTrue(condition: is_string(value: $result) && str_contains(haystack: $result, needle: 'compile-report.json'), message: 'Generated artifact tool should report emitted files.');
assertTrue(condition: is_file(filename: $outputDir . '/compile-report.json'), message: 'Generated artifact tool should emit compile report JSON.');
assertTrue(condition: is_file(filename: $outputDir . '/runtime-report.json'), message: 'Generated artifact tool should emit runtime report JSON.');
assertTrue(condition: is_file(filename: $outputDir . '/governance.json'), message: 'Generated artifact tool should emit governance JSON.');
assertTrue(condition: is_file(filename: $outputDir . '/architecture.json'), message: 'Generated artifact tool should emit architecture JSON.');
assertTrue(condition: is_file(filename: $outputDir . '/dependency-graph.html'), message: 'Generated artifact tool should emit an HTML graph explorer.');

$compileReport = file_get_contents(filename: $outputDir . '/compile-report.json');
$runtimeReport = file_get_contents(filename: $outputDir . '/runtime-report.json');
$html          = file_get_contents(filename: $outputDir . '/dependency-graph.html');

assertTrue(condition: is_string(value: $compileReport) && str_contains(haystack: $compileReport, needle: '"executionMode": "generated"'), message: 'Generated compile report should preserve generated execution mode.');
assertTrue(condition: is_string(value: $runtimeReport) && str_contains(haystack: $runtimeReport, needle: '"executionMode": "generated"'), message: 'Generated runtime report should preserve generated execution mode.');
assertTrue(condition: is_string(value: $html) && str_contains(haystack: $html, needle: 'Container Graph Explorer'), message: 'Generated artifact tool should emit a human-readable graph explorer.');

echo basename(path: __FILE__) . " ok\n";
