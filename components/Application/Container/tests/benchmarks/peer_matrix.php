<?php

declare(strict_types=1);

require_once dirname(path: __DIR__) . '/bootstrap.php';
require_once __DIR__ . '/adapters/ArtifactBenchmarkPeerAdapter.php';

/**
 * @param array<int, string> $arguments
 *
 * @return array<string, string>
 */
function peerTargets(array $arguments) : array
{
    $targets = [];

    foreach ($arguments as $argument) {
        if (str_starts_with(haystack: $argument, needle: '--')) {
            continue;
        }

        $parts = explode(separator: '=', string: $argument, limit: 2);
        if (count(value: $parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new RuntimeException(message: 'Peer benchmark targets must use the form name=/path/to/report.json.');
        }

        $targets[$parts[0]] = $parts[1];
    }

    if (! isset($targets['current']) || count(value: $targets) < 2) {
        throw new RuntimeException(message: 'Peer benchmark matrix requires current=/path/to/current.json plus at least one peer artifact.');
    }

    return $targets;
}

/**
 * @return list<string>
 */
function benchmarkScenariosForArtifact(array $artifact) : array
{
    $names = array_keys(array: $artifact['results']);
    sort(array: $names);

    return $names;
}

/**
 * @return list<string>
 */
function parityIssuesFor(string $peerName, array $current, array $peer) : array
{
    $issues = [];

    foreach (['dockerImage', 'php', 'sapi', 'suiteVersion'] as $key) {
        $currentValue = (string) ($current['meta'][$key] ?? '');
        $peerValue    = (string) ($peer['meta'][$key] ?? '');

        if ($currentValue !== $peerValue) {
            $issues[] = "peer [{$peerName}] mismatched {$key}: current={$currentValue}, peer={$peerValue}";
        }
    }

    $currentSettings = $current['meta']['phpSettings'] ?? [];
    $peerSettings = $peer['meta']['phpSettings'] ?? [];
    if ($currentSettings !== $peerSettings) {
        $issues[] = "peer [{$peerName}] PHP settings do not match the canonical benchmark settings";
    }

    if (($current['meta']['guard'] ?? null) !== ($peer['meta']['guard'] ?? null)) {
        $issues[] = "peer [{$peerName}] guard mode does not match the canonical median benchmark policy";
    }

    $currentScenarios = benchmarkScenariosForArtifact(artifact: $current);
    $peerScenarios    = benchmarkScenariosForArtifact(artifact: $peer);
    if ($currentScenarios !== $peerScenarios) {
        $issues[] = "peer [{$peerName}] scenario set does not match the canonical benchmark suite";
    }

    return $issues;
}

/**
 * @param array<string, array<string, float>> $thresholds
 *
 * @return array{max_time_ratio_vs_peer: float, max_peak_ratio_vs_peer: float}
 */
function peerThresholdFor(string $scenario, array $thresholds) : array
{
    /** @var array{max_time_ratio_vs_peer: float, max_peak_ratio_vs_peer: float} $default */
    $default = $thresholds['*'] ?? [
        'max_time_ratio_vs_peer' => 1.25,
        'max_peak_ratio_vs_peer' => 2.00,
    ];

    /** @var array{max_time_ratio_vs_peer: float, max_peak_ratio_vs_peer: float} $scenarioThreshold */
    $scenarioThreshold = $thresholds[$scenario] ?? $default;

    return $scenarioThreshold;
}

/**
 * @param array<string, array{meta: array<string, mixed>, results: array<string, array<string, mixed>>}> $artifacts
 * @param array<string, array<string, float>>                                                            $thresholds
 * @return array<string, mixed>
 */
function peerMatrixPayload(array $artifacts, array $thresholds) : array
{
    $current = $artifacts['current'];
    $peers   = $artifacts
            |> array_keys(...)
            |> (static fn ($x) => array_filter(array: $x, callback: static fn (string $name) : bool => $name !== 'current'))
            |> array_values(...);
    sort(array: $peers);

    $payload = [
        'schemaVersion' => 1,
        'meta'          => [
            'generatedAt'    => gmdate(format: 'c'),
            'subject'        => 'current',
            'peers'          => $peers,
            'dockerImage'    => (string) ($current['meta']['dockerImage'] ?? ''),
            'php'            => (string) ($current['meta']['php'] ?? ''),
            'sapi'           => (string) ($current['meta']['sapi'] ?? ''),
            'phpSettings'    => $current['meta']['phpSettings'] ?? [],
            'suiteVersion'   => (string) ($current['meta']['suiteVersion'] ?? ''),
            'thresholdsFile' => __DIR__ . '/peer-thresholds.php',
        ],
        'artifactMeta' => array_map(
            callback: static fn (array $artifact) : array => $artifact['meta'],
            array   : $artifacts,
        ),
        'parityIssues' => [],
        'regressions'   => [],
        'comparisons'   => [],
    ];

    foreach ($peers as $peerName) {
        $peer = $artifacts[$peerName];
        $payload['parityIssues'] = array_merge(
            $payload['parityIssues'],
            parityIssuesFor(peerName: $peerName, current: $current, peer: $peer),
        );

        $scenarios = benchmarkScenariosForArtifact(artifact: $current);
        $peerRows = [];

        foreach ($scenarios as $scenario) {
            $currentRow = $current['results'][$scenario];
            $peerRow    = $peer['results'][$scenario];
            $timeRatio  = ((float) ($peerRow['time_ms'] ?? 0.0)) > 0.0
                ? ((float) ($currentRow['time_ms'] ?? 0.0)) / (float) $peerRow['time_ms']
                : 0.0;
            $peakRatio = ((float) ($peerRow['peak_mb'] ?? 0.0)) > 0.0
                ? ((float) ($currentRow['peak_mb'] ?? 0.0)) / (float) $peerRow['peak_mb']
                : 0.0;
            $threshold = peerThresholdFor(scenario: $scenario, thresholds: $thresholds);
            $regressed = $timeRatio > $threshold['max_time_ratio_vs_peer']
                || $peakRatio > $threshold['max_peak_ratio_vs_peer'];

            $peerRows[$scenario] = [
                'current'         => $currentRow,
                'peer'            => $peerRow,
                'timeRatioVsPeer' => $timeRatio,
                'peakRatioVsPeer' => $peakRatio,
                'threshold'       => $threshold,
                'regressed'       => $regressed,
            ];

            if ($regressed) {
                $payload['regressions'][] = [
                    'peer'            => $peerName,
                    'scenario'        => $scenario,
                    'timeRatioVsPeer' => $timeRatio,
                    'peakRatioVsPeer' => $peakRatio,
                    'threshold'       => $threshold,
                ];
            }
        }

        $payload['comparisons'][$peerName] = [
            'scenarioCount' => count(value: $peerRows),
            'scenarios'     => $peerRows,
        ];
    }

    return $payload;
}

$jsonOutput       = in_array(needle: '--json', haystack: $argv, strict: true);
$failOnRegression = in_array(needle: '--fail-on-regression', haystack: $argv, strict: true);
$outputPath       = null;

foreach ($argv as $argument) {
    if (str_starts_with(haystack: $argument, needle: '--output=')) {
        $outputPath = substr(string: $argument, offset: strlen(string: '--output='));
    }
}

$targets = peerTargets(arguments: array_slice(array: $argv, offset: 1));
$artifacts = [];
foreach ($targets as $name => $path) {
    $adapter = new ArtifactBenchmarkPeerAdapter(peerName: $name, path: $path);
    $artifacts[$adapter->name()] = $adapter->load();
}

/** @var array<string, array<string, float>> $thresholds */
$thresholds = require __DIR__ . '/peer-thresholds.php';
$payload    = peerMatrixPayload(artifacts: $artifacts, thresholds: $thresholds);
$json       = json_encode(value: $payload, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;

if (is_string(value: $outputPath) && $outputPath !== '') {
    $directory = dirname(path: $outputPath);
    if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0o775, recursive: true) && ! is_dir(filename: $directory)) {
        throw new RuntimeException(message: "Cannot create peer benchmark artifact directory [{$directory}].");
    }

    if (file_put_contents(filename: $outputPath, data: $json, flags: LOCK_EX) === false) {
        throw new RuntimeException(message: "Cannot write peer benchmark artifact [{$outputPath}].");
    }
}

if ($failOnRegression && ($payload['parityIssues'] !== [] || $payload['regressions'] !== [])) {
    throw new RuntimeException(
        message: "Peer benchmark matrix failed:\n- "
                 . array_map(
                   callback: static fn (array $row) : string => 'regression vs peer [' . $row['peer'] . '] on [' . $row['scenario'] . ']',
                   array   : $payload['regressions'],
               )
                     |> (static fn ($x) => array_merge($payload['parityIssues'], $x))
                     |> (static fn ($x) => implode(separator: "\n- ", array: $x)),
    );
}

if ($jsonOutput) {
    echo $json;
    exit(0);
}

echo $json;
