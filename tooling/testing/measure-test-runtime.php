<?php

declare(strict_types=1);

/**
 * Measure test execution timing to identify slow test files and cases.
 *
 * Usage: php tooling/testing/measure-test-runtime.php [tests-directory]
 */

namespace Avax\Tooling\Testing;

$testsDir = $argv[1] ?? 'tests';

if (!is_dir($testsDir)) {
    echo "Tests directory not found: {$testsDir}\n";
    exit(1);
}

// Collect all test files
$iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($testsDir, \RecursiveDirectoryIterator::SKIP_DOTS)
);

$testFiles = [];
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Test.php')) {
        $testFiles[] = $file->getPathname();
}
}

echo "Found " . count($testFiles) . " test files.\n\n";
printf("%-80s | %8s | %10s | %s\n", 'FILE', 'TIME (s)', 'STATUS', 'ASSERTIONS');
echo str_repeat('-', 120) . "\n";

$results = [];
$totalTime = 0;
$totalTests = 0;
$totalAssertions = 0;

foreach ($testFiles as $file) {
    $start = microtime(true);
    $output = [];
    $returnCode = 0;
    exec(
        sprintf('vendor/bin/phpunit --no-coverage %s 2>&1', escapeshellarg($file)),
        $output,
        $returnCode
    );
    $duration = microtime(true) - $start;

    $outputText = implode("\n", $output);

    // Parse results
    $tests = 0;
    $assertions = 0;
    $status = 'PASS';

    if (preg_match('/\((\d+)\s+tests?,\s*(\d+)\s+assertions?\)/', $outputText, $m)) {
        $tests = (int) $m[1];
        $assertions = (int) $m[2];
    }
    if (preg_match('/Time:\s+[\d:]+,\s+Memory:.*?\n\n(OK|ERRORS|FAILURES|Incomplete|Skipped)/', $outputText, $m)) {
        $status = $m[1];
    } elseif (str_contains($outputText, 'OK')) {
        $status = 'PASS';
    } elseif (str_contains($outputText, 'ERRORS')) {
        $status = 'ERROR';
    } elseif (str_contains($outputText, 'FAILURES')) {
        $status = 'FAIL';
    }

    $relativePath = str_replace(__DIR__ . '/../../', '', $file);
    printf("%-80s | %8.3f | %10s | %d assertions\n", $relativePath, $duration, $status, $assertions);

    $results[] = [
        'file' => $relativePath,
        'duration' => $duration,
        'tests' => $tests,
        'assertions' => $assertions,
        'status' => $status,
    ];

    $totalTime += $duration;
    $totalTests += $tests;
    $totalAssertions += $assertions;
}

// Sort by duration descending
usort($results, static fn($a, $b) => $b['duration'] <=> $a['duration']);

echo "\n";
echo str_repeat('=', 120) . "\n";
printf("TOTAL SEQUENTIAL: %.3fs (%d tests, %d assertions)\n", $totalTime, $totalTests, $totalAssertions);
echo "\n";
echo "TOP 10 SLOWEST TEST FILES:\n";
foreach (array_slice($results, 0, 10) as $i => $result) {
    printf("  %2d. %8.3fs  %s\n", $i + 1, $result['duration'], $result['file']);
}
