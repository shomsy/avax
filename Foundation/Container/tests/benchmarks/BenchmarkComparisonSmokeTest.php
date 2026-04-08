<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$artifactDir = sys_get_temp_dir() . '/container-benchmark-compare-' . uniqid();
if (! mkdir($artifactDir, 0775, true) && ! is_dir($artifactDir)) {
    throw new RuntimeException("Cannot create benchmark comparison artifact directory [{$artifactDir}].");
}

$left = $artifactDir . '/left.json';
$right = $artifactDir . '/right.json';

$command = 'php tests/benchmarks/run.php --json --output=' . escapeshellarg($left) . ' >/dev/null';
exec($command, $output, $status);
assertSame(0, $status, 'Benchmark runner should write a machine-readable artifact.');
assertTrue(is_file($left), 'Benchmark runner should create the requested output artifact.');

copy($left, $right);

$compareCommand = 'php tests/benchmarks/compare.php --json current='
    . escapeshellarg($left)
    . ' baseline='
    . escapeshellarg($right);

$compareOutput = [];
exec($compareCommand, $compareOutput, $compareStatus);

assertSame(0, $compareStatus, 'Benchmark comparison runner should compare benchmark artifacts successfully.');
$json = implode(PHP_EOL, $compareOutput);
assertTrue(str_contains($json, '"scenarioCount"'), 'Benchmark comparison output should expose the scenario count.');
assertTrue(str_contains($json, '"baseline": "current"'), 'Benchmark comparison output should expose the baseline target.');

echo basename(__FILE__) . " ok\n";
