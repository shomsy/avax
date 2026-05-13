#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 8: PHPStan Clean on Database Lifecycle Code
 *
 * Runs PHPStan on the database lifecycle code and verifies zero errors.
 */

$root = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 8: PHPStan Clean on Database Lifecycle Code ===\n\n";

$phpstan = $root . '/vendor/bin/phpstan';
if (!file_exists($phpstan)) {
    echo "SKIP: PHPStan not installed. Run 'composer require --dev phpstan/phpstan'.\n";
    exit(0);
}

$lifecyclePaths = [
    'components/DataStack/Database/System/Foundation/Lifecycle',
    'components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php',
    'components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php',
    'components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php',
];

$checkPaths = [];
foreach ($lifecyclePaths as $path) {
    $fullPath = $root . '/' . $path;
    if (file_exists($fullPath)) {
        $checkPaths[] = $path;
    } else {
        echo "MISSING: {$path}\n";
        $exitCode = 1;
    }
}

if ($checkPaths === []) {
    echo "FAIL: No lifecycle paths found to check.\n";
    exit(1);
}

$pathsStr = implode(' ', $checkPaths);
exec("{$phpstan} analyse {$pathsStr} --memory-limit=1G --error-format=raw --no-progress 2>&1", $output, $code);

foreach ($output as $line) {
    echo $line . "\n";
    if (str_contains($line, 'error') || str_contains($line, 'Error')) {
        $exitCode = 1;
    }
}

if ($exitCode === 0) {
    echo "\nPASS: PHPStan is clean on database lifecycle code.\n";
} else {
    echo "\nFAIL: PHPStan found errors in database lifecycle code.\n";
}

exit($exitCode);
