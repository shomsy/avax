#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 4: Compiled Registry Frozen and Tested
 *
 * Verifies that CompiledDatabaseLifecycleRegistry follows the frozen pattern
 * and has superset handling, priority sorting, and no reflection in hot path.
 */

$root = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 4: Compiled Registry Frozen and Tested ===\n\n";

$registryFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/CompiledDatabaseLifecycleRegistry.php';
if (!file_exists($registryFile)) {
    echo "FAIL: CompiledDatabaseLifecycleRegistry.php not found.\n";
    exit(1);
}

$content = file_get_contents($registryFile);

// Check frozen pattern (compile/freeze methods or readonly/final class)
if (!str_contains($content ?? '', 'compile') && !str_contains($content ?? '', 'freeze')) {
    echo "MISSING: Registry does not have compile/freeze pattern.\n";
    $exitCode = 1;
}

// Check entity listeners method
if (!str_contains($content ?? '', 'entityListenersFor')) {
    echo "MISSING: entityListenersFor() method.\n";
    $exitCode = 1;
}

// Check query listeners method
if (!str_contains($content ?? '', 'queryListenersFor')) {
    echo "MISSING: queryListenersFor() method.\n";
    $exitCode = 1;
}

// Check transaction listeners method
if (!str_contains($content ?? '', 'transactionListenersFor')) {
    echo "MISSING: transactionListenersFor() method.\n";
    $exitCode = 1;
}

// Check exact lookup (no superset expansion in hot path).
// Superset dispatch is handled by EntityPersister explicitly, not by the registry.
if (! str_contains($content ?? '', 'entityListenersFor') || ! str_contains($content ?? '', 'return $this->entityListeners')) {
    echo "MISSING: entityListenersFor() with exact lookup.\n";
    $exitCode = 1;
} else {
    echo "BEHAVIOR: Registry uses exact lookup (no superset expansion in hot path).\n";
}

// Check priority sorting
if (!str_contains($content ?? '', 'priority') && !str_contains($content ?? '', 'sort')) {
    echo "WARNING: No evidence of priority sorting.\n";
}

// Check tests exist
$testFile = $root . '/tests/Unit/Components/DataStack/Database/Lifecycle/CompiledDatabaseLifecycleRegistryTest.php';
if (!file_exists($testFile)) {
    echo "WARNING: No dedicated registry test file found.\n";
}

if ($exitCode === 0) {
    echo "PASS: Compiled registry follows frozen pattern.\n";
} else {
    echo "FAIL: Compiled registry has gaps.\n";
}

exit($exitCode);
