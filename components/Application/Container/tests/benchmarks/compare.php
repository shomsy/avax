<?php

declare(strict_types=1);

require_once dirname(path: __DIR__) . '/bootstrap.php';

/**
 * @return array{meta: array<string, mixed>, results: array<string, array<string, mixed>>}
 *
 * @throws JsonException
 */
function readBenchmarkArtifact(string $path) : array
{
    if (! is_file(filename: $path)) {
        throw new RuntimeException(message: sprintf('Benchmark artifact [%s] does not exist.', $path));
    }

    $json = file_get_contents(filename: $path);
    if (! is_string(value: $json) || $json === '') {
        throw new RuntimeException(message: sprintf('Benchmark artifact [%s] could not be read.', $path));
    }

    $decoded = json_decode(json: $json, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
    if (! is_array(value: $decoded)) {
        throw new RuntimeException(message: sprintf('Benchmark artifact [%s] is invalid.', $path));
    }

    $meta = $decoded['meta'] ?? [];
    if (! is_array(value: $meta)) {
        $meta = [];
    }

    $results = $decoded['results'] ?? $decoded;
    if (! is_array(value: $results)) {
        throw new RuntimeException(message: sprintf('Benchmark artifact [%s] does not contain benchmark results.', $path));
    }

    return [
        'meta' => $meta,
        'results' => $results,
    ];
}

/**
 * @param array<int, string> $arguments
 *
 * @return array<string, string>
 */
function benchmarkTargets(array $arguments) : array
{
    $targets = [];

    foreach ($arguments as $argument) {
        if (str_starts_with(haystack: $argument, needle: '--')) {
            continue;
        }

        $parts = explode(separator: '=', string: $argument, limit: 2);
        if (count(value: $parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new RuntimeException(
                message: 'Comparison targets must use the form name=/absolute/or/relative/path/to/report.json.',
            );
        }

        $targets[$parts[0]] = $parts[1];
    }

    if (count(value: $targets) < 2) {
        throw new RuntimeException(message: 'Benchmark comparison requires at least two benchmark artifacts.');
    }

    return $targets;
}

/**
 * @param array<string, array<string, array<string, mixed>>> $reports
 *
 * @return list<string>
 */
function commonScenarios(array $reports) : array
{
    $scenarioSets = array_map(
        callback: static fn (array $results) : array => array_keys(array: $results),
        array   : $reports,
    );

    $common = array_shift(array: $scenarioSets) ?? [];
    foreach ($scenarioSets as $scenarioSet) {
        $common = array_values(array: array_intersect($common, $scenarioSet));
    }

    sort(array: $common);

    return $common;
}

/**
 * @param array<string, array{meta: array<string, mixed>, results: array<string, array<string, mixed>>}> $artifacts
 * @return array<string, mixed>
 */
function comparisonPayload(array $artifacts, string $baselineName) : array
{
    $reports = array_map(
        callback: static fn (array $artifact) : array => $artifact['results'],
        array   : $artifacts,
    );
    $common  = commonScenarios(reports: $reports);
    $baseline = $reports[$baselineName] ?? null;
    if (! is_array(value: $baseline)) {
        throw new RuntimeException(message: sprintf('Baseline report [%s] is missing.', $baselineName));
    }

    $scenarios = [];
    foreach ($common as $scenario) {
        $baselineTime = (float) ($baseline[$scenario]['time_ms'] ?? 0.0);
        $baselineOps  = (float) ($baseline[$scenario]['ops_per_s'] ?? 0.0);
        $rows = [];

        foreach ($reports as $name => $results) {
            $time = (float) ($results[$scenario]['time_ms'] ?? 0.0);
            $ops  = (float) ($results[$scenario]['ops_per_s'] ?? 0.0);
            $peak = (float) ($results[$scenario]['peak_mb'] ?? 0.0);

            $rows[$name] = [
                'time_ms'   => $time,
                'ops_per_s' => $ops,
                'peak_mb'   => $peak,
                'time_ratio_vs_baseline' => $baselineTime > 0 ? $time / $baselineTime : 0.0,
                'ops_ratio_vs_baseline' => $baselineOps > 0 ? $ops / $baselineOps : 0.0,
            ];
        }

        uasort(
            array   : $rows,
            callback: static fn (array $left, array $right) : int => $left['time_ms'] <=> $right['time_ms'],
        );

        $fastest = array_key_first(array: $rows);
        $scenarios[$scenario] = [
            'fastest' => $fastest,
            'results' => $rows,
        ];
    }

    return [
        'baseline'        => $baselineName,
        'targets'         => array_keys(array: $reports),
        'targetMeta' => array_map(
            callback: static fn (array $artifact) : array => $artifact['meta'],
            array   : $artifacts,
        ),
        'commonScenarios' => $common,
        'scenarioCount'   => count(value: $common),
        'scenarios'       => $scenarios,
    ];
}

$jsonOutput = in_array(needle: '--json', haystack: $argv, strict: true);
$baselineName = 'current';

foreach ($argv as $argument) {
    if (str_starts_with(haystack: $argument, needle: '--baseline=')) {
        $baselineName = substr(string: $argument, offset: strlen(string: '--baseline='));
    }
}

$targets = benchmarkTargets(arguments: array_slice(array: $argv, offset: 1));
$artifacts = [];

foreach ($targets as $name => $path) {
    $artifacts[$name] = readBenchmarkArtifact(path: $path);
}

$comparison = comparisonPayload(artifacts: $artifacts, baselineName: $baselineName);

if ($jsonOutput) {
    echo json_encode(value: $comparison, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    exit(0);
}

fwrite(stream: STDOUT, data: 'baseline: ' . $comparison['baseline'] . PHP_EOL);
fwrite(stream: STDOUT, data: 'common scenarios: ' . $comparison['scenarioCount'] . PHP_EOL);

foreach ($comparison['scenarios'] as $scenario => $row) {
    fwrite(stream: STDOUT, data: PHP_EOL . '[' . $scenario . ']' . PHP_EOL);
    fwrite(stream: STDOUT, data: 'fastest: ' . $row['fastest'] . PHP_EOL);

    foreach ($row['results'] as $name => $result) {
        fwrite(
            stream: STDOUT,
            data  : str_pad(string: (string) $name, length: 18)
                    . str_pad(string: number_format(num: (float) $result['time_ms'], decimals: 2) . ' ms', length: 14)
                    . str_pad(string: number_format(num: (float) $result['ops_per_s'], decimals: 2) . ' ops/s', length: 18)
                    . 'ratio '
                    . number_format(num: (float) $result['time_ratio_vs_baseline'], decimals: 2)
                    . PHP_EOL,
        );
    }
}

echo PHP_EOL . "comparison ok\n";
