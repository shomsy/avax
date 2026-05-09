<?php

declare(strict_types=1);

/**
 * Split test files into balanced groups for CI parallel jobs.
 *
 * Uses previous timing report to distribute tests evenly across N groups.
 *
 * Usage: php tooling/testing/split-tests-by-runtime.php [num-groups]
 * Output: JSON array of groups, each containing file paths.
 */

namespace Avax\Tooling\Testing;

$numGroups = (int) ($argv[1] ?? 4);

if ($numGroups < 2 || $numGroups > 16) {
    echo "Usage: php tooling/testing/split-tests-by-runtime.php [num-groups (2-16)]\n";
    exit(1);
}

$timingFile = __DIR__ . '/../../var/test-reports/timing.json';

if (!is_file($timingFile)) {
    echo "No timing data found. Run measure-test-runtime.php first to generate timing baseline.\n";
    echo "Falling back to file-count-based splitting.\n";

    // Fallback: collect test files and split by count
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/../../tests', RecursiveDirectoryIterator::SKIP_DOTS)
    );

    $testFiles = [];
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Test.php')) {
            $testFiles[] = $file->getPathname();
        }
    }

    sort($testFiles);
    $groups = array_chunk($testFiles, (int) ceil(count($testFiles) / $numGroups));

    foreach ($groups as $i => $group) {
        printf("GROUP %d: %d files\n", $i + 1, count($group));
        foreach ($group as $file) {
            echo "  {$file}\n";
        }
    }
    exit(0);
}

$timings = json_decode(file_get_contents($timingFile), true, 512, JSON_THROW_ON_ERROR);

// Sort by duration descending (longest first for better balancing)
usort($timings, static fn($a, $b) => $b['duration'] <=> $a['duration']);

// Greedy bin-packing: assign each file to the group with least total time
$groups = array_fill(0, $numGroups, ['files' => [], 'total_time' => 0.0]);

foreach ($timings as $timing) {
    // Find group with minimum total time
    $minIndex = 0;
    $minTime = $groups[0]['total_time'];
    foreach ($groups as $i => $group) {
        if ($group['total_time'] < $minTime) {
            $minIndex = $i;
            $minTime = $group['total_time'];
        }
    }

    $groups[$minIndex]['files'][] = $timing['file'];
    $groups[$minIndex]['total_time'] += $timing['duration'];
}

// Output results
foreach ($groups as $i => $group) {
    printf("GROUP %d: %d files, %.3fs estimated\n", $i + 1, count($group['files']), $group['total_time']);
    foreach ($group['files'] as $file) {
        echo "  {$file}\n";
    }
    echo "\n";
}
