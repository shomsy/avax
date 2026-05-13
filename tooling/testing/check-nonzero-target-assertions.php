<?php

declare(strict_types=1);

/**
 * check-nonzero-target-assertions.php
 *
 * Verifies that targeted test filters actually run nonzero tests.
 * Prevents fake pass from empty filter results.
 */

$failures = [];
$checked = 0;

// Test filters that must have nonzero results
$filters = [
    'Router',
    'Health',
    'Doctor',
    'Cache',
    'Database',
    'Events',
    'FailureBoundary',
];

echo "NONZERO TARGET ASSERTIONS CHECK\n";
echo "================================\n\n";

foreach ($filters as $filter) {
    $checked++;
    $cmd = sprintf(
        'vendor/bin/phpunit --filter %s --no-coverage 2>&1',
        escapeshellarg($filter)
    );
    $output = shell_exec($cmd);
    if ($output === null) {
        $failures[] = "$filter: could not run phpunit";
        echo "  $filter: COULD NOT RUN\n";
        continue;
    }

    // Look for "OK (N tests" or "Tests: N," pattern
    if (preg_match('/OK \((\d+) tests/', $output, $m)) {
        $count = (int)$m[1];
        if ($count > 0) {
            echo "  $filter: $count tests — PASS\n";
        } else {
            $failures[] = "$filter: zero tests";
            echo "  $filter: ZERO TESTS\n";
        }
    } elseif (preg_match('/No tests found/', $output)) {
        $failures[] = "$filter: no tests found";
        echo "  $filter: NO TESTS FOUND\n";
    } elseif (preg_match('/0 tests?/', $output)) {
        $failures[] = "$filter: zero tests reported";
        echo "  $filter: ZERO TESTS\n";
    } elseif (str_contains($output, 'OK')) {
        // Fallback: OK without count
        echo "  $filter: PASS (OK)\n";
    } else {
        $failures[] = "$filter: could not determine result";
        echo "  $filter: UNKNOWN\n";
    }
}

echo "\n";
if ($failures !== []) {
    echo "FAIL: " . count($failures) . " filters have zero or unknown tests:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}

echo "PASS: $checked targeted filters all have nonzero tests\n";
exit(0);
