<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$artifactDir = sys_get_temp_dir().'/container-benchmark-compare-'.uniqid();
if (! mkdir(directory: $artifactDir, permissions: 0o775, recursive: true) && ! is_dir(filename: $artifactDir)) {
    throw new RuntimeException(message: sprintf('Cannot create benchmark comparison artifact directory [%s].', $artifactDir));
}

$left = $artifactDir.'/left.json';
$right = $artifactDir.'/right.json';

$command = 'php tests/benchmarks/run.php --json --output='.escapeshellarg(arg: $left).' >/dev/null';
exec(command: $command, output: $output, result_code: $status);
assertSame(expected: 0, actual: $status, message: 'Benchmark runner should write a machine-readable artifact.');
assertTrue(condition: is_file(filename: $left), message: 'Benchmark runner should create the requested output artifact.');

copy(from: $left, to: $right);

$compareCommand = 'php tests/benchmarks/compare.php --json current='
    .escapeshellarg(arg: $left)
    .' baseline='
    .escapeshellarg(arg: $right);

$compareOutput = [];
exec(command: $compareCommand, output: $compareOutput, result_code: $compareStatus);

assertSame(expected: 0, actual: $compareStatus, message: 'Benchmark comparison runner should compare benchmark artifacts successfully.');
$json = implode(separator: PHP_EOL, array: $compareOutput);
assertTrue(condition: str_contains(haystack: $json, needle: '"scenarioCount"'), message: 'Benchmark comparison output should expose the scenario count.');
assertTrue(condition: str_contains(haystack: $json, needle: '"baseline": "current"'), message: 'Benchmark comparison output should expose the baseline target.');
assertTrue(condition: str_contains(haystack: $json, needle: '"targetMeta"'), message: 'Benchmark comparison output should expose benchmark artifact metadata for each target.');

echo basename(path: __FILE__)." ok\n";
