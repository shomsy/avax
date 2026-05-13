#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 6: Lifecycle Integration Tests
 *
 * Verifies that lifecycle integration tests exist and cover all three domains.
 */

$root = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 6: Lifecycle Integration Tests ===\n\n";

$testFiles = [
    'DatabaseLifecycleIntegrationTest.php',
];

$testDir = $root . '/tests/Unit/Components/DataStack/Database/Lifecycle/';
foreach ($testFiles as $file) {
    $path = $testDir . $file;
    if (!file_exists($path)) {
        echo "MISSING: {$file}\n";
        $exitCode = 1;
    } else {
        $content = file_get_contents($path);

        // Check for query lifecycle tests
        if (!str_contains($content ?? '', 'query') || !str_contains($content ?? '', 'Executing')) {
            echo "WARNING: No query lifecycle test evidence.\n";
        }

        // Check for transaction lifecycle tests
        if (!str_contains($content ?? '', 'Transaction') || !str_contains($content ?? '', 'Committed')) {
            echo "WARNING: No transaction lifecycle test evidence.\n";
        }

        // Check for entity lifecycle tests
        if (!str_contains($content ?? '', 'Entity') || !str_contains($content ?? '', 'Created')) {
            echo "WARNING: No entity lifecycle test evidence.\n";
        }

        // Count test methods (uses #[Test] attribute)
        $testCount = preg_match_all('/#\[Test\]/', $content ?? '');
        echo "Found {$testCount} test methods in {$file}.\n";

        if ($testCount < 10) {
            echo "WARNING: Fewer than 10 lifecycle integration tests.\n";
        }
    }
}

// Check that PHPUnit can find the tests
echo "\nRunning quick PHPUnit discovery...\n";
$phpunit = $root . '/vendor/bin/phpunit';
if (file_exists($phpunit)) {
    exec("{$phpunit} --list-tests --filter=DatabaseLifecycle 2>&1", $output, $code);
    $testCount = 0;
    foreach ($output as $line) {
        if (str_contains($line, '::test')) {
            $testCount++;
        }
    }
    echo "PHPUnit found {$testCount} lifecycle tests.\n";
    if ($testCount === 0) {
        $exitCode = 1;
    }
}

if ($exitCode === 0) {
    echo "\nPASS: Lifecycle integration tests are present.\n";
} else {
    echo "\nFAIL: Lifecycle integration tests have gaps.\n";
}

exit($exitCode);
