<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

/**
 * @return array<string, array<string, mixed>>
 */
function readBenchmarkResults(string $path) : array
{
    if (! is_file($path)) {
        throw new RuntimeException("Benchmark artifact [{$path}] does not exist.");
    }

    $json = file_get_contents($path);
    if (! is_string($json) || $json === '') {
        throw new RuntimeException("Benchmark artifact [{$path}] could not be read.");
    }

    $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (! is_array($decoded)) {
        throw new RuntimeException("Benchmark artifact [{$path}] is invalid.");
    }

    $results = $decoded['results'] ?? $decoded;
    if (! is_array($results)) {
        throw new RuntimeException("Benchmark artifact [{$path}] does not contain benchmark results.");
    }

    return $results;
}

/**
 * @param array<int, string> $arguments
 * @return array<string, string>
 */
function benchmarkTargets(array $arguments) : array
{
    $targets = [];

    foreach ($arguments as $argument) {
        if (str_starts_with($argument, '--')) {
            continue;
        }

        $parts = explode('=', $argument, 2);
        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new RuntimeException(
                'Comparison targets must use the form name=/absolute/or/relative/path/to/report.json.'
            );
        }

        $targets[$parts[0]] = $parts[1];
    }

    if (count($targets) < 2) {
        throw new RuntimeException('Benchmark comparison requires at least two benchmark artifacts.');
    }

    return $targets;
}

/**
 * @param array<string, array<string, array<string, mixed>>> $reports
 * @return list<string>
 */
function commonScenarios(array $reports) : array
{
    $scenarioSets = array_map(
        static fn(array $results) : array => array_keys($results),
        $reports
    );

    $common = array_shift($scenarioSets) ?? [];
    foreach ($scenarioSets as $set) {
        $common = array_values(array_intersect($common, $set));
    }

    sort($common);

    return $common;
}

/**
 * @param array<string, array<string, array<string, mixed>>> $reports
 * @return array<string, mixed>
 */
function comparisonPayload(array $reports, string $baselineName) : array
{
    $common = commonScenarios(reports: $reports);
    $baseline = $reports[$baselineName] ?? null;
    if (! is_array($baseline)) {
        throw new RuntimeException("Baseline report [{$baselineName}] is missing.");
    }

    $scenarios = [];
    foreach ($common as $scenario) {
        $baselineTime = (float) ($baseline[$scenario]['time_ms'] ?? 0.0);
        $baselineOps = (float) ($baseline[$scenario]['ops_per_s'] ?? 0.0);
        $rows = [];

        foreach ($reports as $name => $results) {
            $time = (float) ($results[$scenario]['time_ms'] ?? 0.0);
            $ops = (float) ($results[$scenario]['ops_per_s'] ?? 0.0);
            $peak = (float) ($results[$scenario]['peak_mb'] ?? 0.0);

            $rows[$name] = [
                'time_ms' => $time,
                'ops_per_s' => $ops,
                'peak_mb' => $peak,
                'time_ratio_vs_baseline' => $baselineTime > 0 ? $time / $baselineTime : 0.0,
                'ops_ratio_vs_baseline' => $baselineOps > 0 ? $ops / $baselineOps : 0.0,
            ];
        }

        uasort(
            $rows,
            static fn(array $left, array $right) : int => $left['time_ms'] <=> $right['time_ms']
        );

        $fastest = array_key_first($rows);
        $scenarios[$scenario] = [
            'fastest' => $fastest,
            'results' => $rows,
        ];
    }

    return [
        'baseline' => $baselineName,
        'targets' => array_keys($reports),
        'commonScenarios' => $common,
        'scenarioCount' => count($common),
        'scenarios' => $scenarios,
    ];
}

$jsonOutput = in_array('--json', $argv, true);
$baselineName = 'current';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--baseline=')) {
        $baselineName = substr($argument, strlen('--baseline='));
    }
}

$targets = benchmarkTargets(arguments: array_slice($argv, 1));
$reports = [];

foreach ($targets as $name => $path) {
    $reports[$name] = readBenchmarkResults(path: $path);
}

$comparison = comparisonPayload(reports: $reports, baselineName: $baselineName);

if ($jsonOutput) {
    echo json_encode($comparison, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
    exit(0);
}

fwrite(STDOUT, 'baseline: ' . $comparison['baseline'] . PHP_EOL);
fwrite(STDOUT, 'common scenarios: ' . $comparison['scenarioCount'] . PHP_EOL);

foreach ($comparison['scenarios'] as $scenario => $row) {
    fwrite(STDOUT, PHP_EOL . '[' . $scenario . ']' . PHP_EOL);
    fwrite(STDOUT, 'fastest: ' . $row['fastest'] . PHP_EOL);

    foreach ($row['results'] as $name => $result) {
        fwrite(
            STDOUT,
            str_pad($name, 18)
            . str_pad(number_format((float) $result['time_ms'], 2) . ' ms', 14)
            . str_pad(number_format((float) $result['ops_per_s'], 2) . ' ops/s', 18)
            . 'ratio '
            . number_format((float) $result['time_ratio_vs_baseline'], 2)
            . PHP_EOL
        );
    }
}

echo PHP_EOL . "comparison ok\n";
