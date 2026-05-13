#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 7: Database Lifecycle Component Shape
 *
 * Verifies that the V5.8 database lifecycle code follows canonical AvaX shape
 * and does not introduce new forbidden folder names in the lifecycle scope.
 */

$root = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 7: Database Lifecycle Component Shape ===\n\n";

$forbiddenFolders = [
    'Services',
    'Helpers',
    'Utils',
    'Common',
    'Shared',
    'Managers',
    'Core',
    'Support',
    'Adapters',
    'Handlers',
    'Processors',
    'Commands',
    'Queries',
    'UseCases',
    'InternalSystem',
    'ExportedCapabilities',
];

// Check V5.8 lifecycle directories specifically
$lifecycleDirs = [
    $root . '/components/DataStack/Database/System/Foundation/Lifecycle/',
    $root . '/components/DataStack/Database/System/Capabilities/Lifecycle/',
];

foreach ($lifecycleDirs as $baseDir) {
    if (!is_dir($baseDir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isDir()) {
            $dirName = $file->getFilename();
            if (in_array($dirName, $forbiddenFolders, true)) {
                echo "FORBIDDEN: {$dirName} found at {$file->getPathname()}\n";
                $exitCode = 1;
            }
        }
    }
}

// Check expected V5.8 lifecycle structure
$expected = [
    'Foundation/Lifecycle/EntityLifecyclePhase.php',
    'Foundation/Lifecycle/QueryLifecyclePhase.php',
    'Foundation/Lifecycle/TransactionLifecyclePhase.php',
    'Foundation/Lifecycle/CompiledDatabaseLifecycleRegistry.php',
    'Foundation/Lifecycle/LifecycleExecutionMode.php',
    'Foundation/Lifecycle/LifecycleSource.php',
    'Capabilities/Lifecycle/EntityLifecycleDsl.php',
    'Capabilities/Lifecycle/QueryLifecycleDsl.php',
    'Capabilities/Lifecycle/TransactionLifecycleDsl.php',
];

foreach ($expected as $file) {
    $path = $root . '/components/DataStack/Database/System/' . $file;
    if (!file_exists($path)) {
        echo "MISSING: {$file}\n";
        $exitCode = 1;
    }
}

// Check Events are in Foundation (not a forbidden top-level bucket)
$eventsDir = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/Events/';
if (!is_dir($eventsDir)) {
    echo "MISSING: Foundation/Lifecycle/Events/ directory\n";
    $exitCode = 1;
} else {
    $eventCount = count(glob($eventsDir . '*.php'));
    echo "Found {$eventCount} lifecycle event classes in Foundation/Lifecycle/Events/.\n";
    if ($eventCount < 15) {
        echo "WARNING: Expected at least 15 lifecycle event classes.\n";
    }
}

if ($exitCode === 0) {
    echo "PASS: Database lifecycle code follows canonical shape with no forbidden folders.\n";
} else {
    echo "FAIL: Database lifecycle code has shape or forbidden folder violations.\n";
}

exit($exitCode);
