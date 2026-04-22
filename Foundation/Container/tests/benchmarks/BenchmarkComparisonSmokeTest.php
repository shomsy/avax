<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$artifactDir = sys_get_temp_dir() . '/container-benchmark-compare-' . uniqid();
if (! mkdir($artifactDir, 0775, true) && ! is_dir($artifactDir)) {
    throw new RuntimeException(message: "Cannot create benchmark comparison artifact directory [{$artifactDir}].");
}

$left  = $artifactDir . '/left.json';
$right = $artifactDir . '/right.json';

$command = 'php tests/benchmarks/run.php --json --output=' . escapeshellarg($left) . ' >/dev/null';
exec($command, $output, $status);
assertSame(expected: 0, actual: $status, message: 'Benchmark runner should write a machine-readable artifact.');
assertTrue(condition: is_file($left), message: 'Benchmark runner should create the requested output artifact.');

copy($left, $right);

$compareCommand = 'php tests/benchmarks/compare.php --json current='
    . escapeshellarg($left)
    . ' baseline='
    . escapeshellarg($right);

$compareOutput = [];
exec($compareCommand, $compareOutput, $compareStatus);

assertSame(expected: 0, actual: $compareStatus, message: 'Benchmark comparison runner should compare benchmark artifacts successfully.');
$json = implode(PHP_EOL, $compareOutput);
assertTrue(condition: str_contains($json, '"scenarioCount"'), message: 'Benchmark comparison output should expose the scenario count.');
assertTrue(condition: str_contains($json, '"baseline": "current"'), message: 'Benchmark comparison output should expose the baseline target.');
assertTrue(condition: str_contains($json, '"targetMeta"'), message: 'Benchmark comparison output should expose benchmark artifact metadata for each target.');

echo basename(__FILE__) . " ok\n";
