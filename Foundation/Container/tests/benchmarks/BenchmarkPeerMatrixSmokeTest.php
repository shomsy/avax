<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

$artifactDir = sys_get_temp_dir() . '/container-benchmark-peer-' . uniqid();
if (! mkdir($artifactDir, 0775, true) && ! is_dir($artifactDir)) {
    throw new RuntimeException("Cannot create benchmark peer artifact directory [{$artifactDir}].");
}

$current = $artifactDir . '/current.json';
$peer = $artifactDir . '/peer.json';
$output = $artifactDir . '/peer-matrix.json';

$baselineArtifact = [
    'meta' => [
        'php' => '8.3.0',
        'sapi' => 'cli',
        'timestamp' => gmdate('c'),
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

file_put_contents($current, json_encode($baselineArtifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);
file_put_contents($peer, json_encode($peerArtifact, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL);

$command = 'php tests/benchmarks/peer_matrix.php --json --output='
    . escapeshellarg($output)
    . ' current=' . escapeshellarg($current)
    . ' peer=' . escapeshellarg($peer);

$peerOutput = [];
exec($command, $peerOutput, $status);

assertSame(0, $status, 'Peer benchmark matrix runner should emit a JSON artifact.');
assertTrue(is_file($output), 'Peer benchmark matrix runner should write the requested JSON artifact.');

$json = implode(PHP_EOL, $peerOutput);
assertTrue(str_contains($json, '"schemaVersion"'), 'Peer benchmark matrix output should expose a schema version.');
assertTrue(str_contains($json, '"comparisons"'), 'Peer benchmark matrix output should expose peer comparison rows.');
assertTrue(str_contains($json, '"regressions"'), 'Peer benchmark matrix output should expose regression rows.');

echo basename(__FILE__) . " ok\n";
