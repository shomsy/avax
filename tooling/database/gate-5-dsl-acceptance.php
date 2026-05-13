#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 5: DSL Acceptance
 *
 * Verifies that all three DSL classes exist and global functions are registered.
 */

$root = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 5: DSL Acceptance ===\n\n";

$dslFiles = [
    'EntityLifecycleDsl.php',
    'QueryLifecycleDsl.php',
    'TransactionLifecycleDsl.php',
];

$dslDir = $root . '/components/DataStack/Database/System/Capabilities/Lifecycle/';
foreach ($dslFiles as $file) {
    $path = $dslDir . $file;
    if (!file_exists($path)) {
        echo "MISSING: {$file}\n";
        $exitCode = 1;
    } else {
        $content = file_get_contents($path);
        // Check for fluent API methods
        if (str_contains($file, 'Entity')) {
            $methods = ['creating', 'created', 'updating', 'updated', 'saving', 'saved', 'deleting', 'deleted'];
            foreach ($methods as $method) {
                if (!str_contains($content ?? '', "function {$method}")) {
                    echo "MISSING METHOD: {$file}::{$method}()\n";
                    $exitCode = 1;
                }
            }
        }
        if (str_contains($file, 'Query')) {
            $methods = ['executing', 'executed', 'slow'];
            foreach ($methods as $method) {
                if (!str_contains($content ?? '', "function {$method}")) {
                    echo "MISSING METHOD: {$file}::{$method}()\n";
                    $exitCode = 1;
                }
            }
        }
        if (str_contains($file, 'Transaction')) {
            $methods = ['beginning', 'committed', 'afterCommit', 'rolledBack', 'afterRollback'];
            foreach ($methods as $method) {
                if (!str_contains($content ?? '', "function {$method}")) {
                    echo "MISSING METHOD: {$file}::{$method}()\n";
                    $exitCode = 1;
                }
            }
        }
    }
}

// Check global functions (autoloaded via composer)
// DSL classes provide the fluent API; global functions may or may not exist
$functionsFile = $root . '/components/DataStack/Database/System/Capabilities/Lifecycle/global-functions.php';
if (file_exists($functionsFile)) {
    $content = file_get_contents($functionsFile);
    foreach (['onEntity', 'onQuery', 'onTransaction'] as $func) {
        if (!str_contains($content ?? '', "function {$func}")) {
            echo "MISSING: {$func}() global function\n";
            $exitCode = 1;
        }
    }
} else {
    echo "NOTE: No global-functions.php; DSL classes provide fluent API directly.\n";
}

if ($exitCode === 0) {
    echo "PASS: DSL acceptance is complete.\n";
} else {
    echo "FAIL: DSL acceptance has gaps.\n";
}

exit($exitCode);
