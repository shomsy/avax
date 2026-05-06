<?php

declare(strict_types=1);

require_once dirname(path: __DIR__).'/bootstrap.php';

$artifactDir = sys_get_temp_dir().'/container-benchmark-peer-'.uniqid();
if (! mkdir(directory: $artifactDir, permissions: 0o775, recursive: true) && ! is_dir(filename: $artifactDir)) {
    throw new RuntimeException(message: sprintf('Cannot create benchmark peer artifact directory [%s].', $artifactDir));
}

$current = $artifactDir.'/current.json';
$peer = $artifactDir.'/peer.json';
$output = $artifactDir.'/peer-matrix.json';

$baselineArtifact = [
    'meta' => [
        'php' => '8.3.0',
        'sapi' => 'cli',
        'timestamp' => gmdate(format: 'c'),
        'dockerImage' => 'php:8.3-cli',
        'phpSettings' => [
            'memory_limit' => '-1',
            'opcache.enable_cli' => '0',
            'zend.assertions' => '1',
        ],
        'suiteVersion' => '2026-04-08',
        'buildMarker' => '',
        'guard' => true,
        'scenarioCount' => 2,
        'scenarios' => ['cached_get', 'warm_boot'],
    ],
    'results' => [
        'cached_get' => ['time_ms' => 10.0, 'ops_per_s' => 1000.0, 'peak_mb' => 1.0, 'memory_per_op_kb' => 0.1],
        'warm_boot' => ['time_ms' => 5.0, 'ops_per_s' => 200.0, 'peak_mb' => 1.0, 'memory_per_op_kb' => 0.2],
    ],
];

$peerArtifact = $baselineArtifact;
$peerArtifact['results']['cached_get']['time_ms'] = 12.0;
$peerArtifact['results']['cached_get']['ops_per_s'] = 833.3;

file_put_contents(filename: $current, data: json_encode(value: $baselineArtifact, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
file_put_contents(filename: $peer, data: json_encode(value: $peerArtifact, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);

$command = 'php tests/benchmarks/peer_matrix.php --json --output='
    .escapeshellarg(arg: $output)
    .' current='.escapeshellarg(arg: $current)
    .' peer='.escapeshellarg(arg: $peer);

$peerOutput = [];
exec(command: $command, output: $peerOutput, result_code: $status);

assertSame(expected: 0, actual: $status, message: 'Peer benchmark matrix runner should emit a JSON artifact.');
assertTrue(condition: is_file(filename: $output), message: 'Peer benchmark matrix runner should write the requested JSON artifact.');

$json = implode(separator: PHP_EOL, array: $peerOutput);
assertTrue(condition: str_contains(haystack: $json, needle: '"schemaVersion"'), message: 'Peer benchmark matrix output should expose a schema version.');
assertTrue(condition: str_contains(haystack: $json, needle: '"comparisons"'), message: 'Peer benchmark matrix output should expose peer comparison rows.');
assertTrue(condition: str_contains(haystack: $json, needle: '"regressions"'), message: 'Peer benchmark matrix output should expose regression rows.');

echo basename(path: __FILE__)." ok\n";
