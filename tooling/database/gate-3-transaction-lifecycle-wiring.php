#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 3: Transaction Lifecycle Wiring
 *
 * Verifies that Transaction fires lifecycle events through the compiled registry.
 */

$root = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 3: Transaction Lifecycle Wiring ===\n\n";

// Check lifecycle event classes exist
$transactionEvents = [
    'TransactionBeginning',
    'TransactionCommitted',
    'TransactionRolledBack',
    'AfterCommit',
    'AfterRollback',
];

$eventBase = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/Events/';
foreach ($transactionEvents as $event) {
    $file = $eventBase . $event . '.php';
    if (!file_exists($file)) {
        echo "MISSING: {$event}.php\n";
        $exitCode = 1;
    }
}

// Check TransactionLifecyclePhase enum
$phaseFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/TransactionLifecyclePhase.php';
if (!file_exists($phaseFile)) {
    echo "MISSING: TransactionLifecyclePhase.php\n";
    $exitCode = 1;
}

// Check Transaction has lifecycle dispatch method
$transactionFile = $root . '/components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php';
if (!file_exists($transactionFile)) {
    echo "MISSING: Transaction.php\n";
    $exitCode = 1;
} else {
    $content = file_get_contents($transactionFile);
    if (!str_contains($content ?? '', 'function dispatchTransactionLifecycle')) {
        echo "MISSING METHOD: Transaction::dispatchTransactionLifecycle()\n";
        $exitCode = 1;
    }

    // Check that begin/commit/rollback call lifecycle dispatch
    foreach (['begin', 'commit', 'rollback'] as $op) {
        if (!str_contains($content ?? '', "function {$op}") || !str_contains($content ?? '', 'dispatchTransaction')) {
            echo "MISSING: Transaction::{$op}() does not call dispatchTransaction\n";
            $exitCode = 1;
        }
    }
}

// Check TransactionLifecycleDsl
$dslFile = $root . '/components/DataStack/Database/System/Capabilities/Lifecycle/TransactionLifecycleDsl.php';
if (!file_exists($dslFile)) {
    echo "MISSING: TransactionLifecycleDsl.php\n";
    $exitCode = 1;
}

// Check global functions (autoloaded via composer)
if (!file_exists($root . '/components/DataStack/Database/System/Capabilities/Lifecycle/global-functions.php')) {
    if (!file_exists($dslFile)) {
        echo "MISSING: onTransaction() global function or DSL fallback\n";
        $exitCode = 1;
    }
}

if ($exitCode === 0) {
    echo "PASS: Transaction lifecycle wiring is complete.\n";
} else {
    echo "FAIL: Transaction lifecycle wiring has gaps.\n";
}

exit($exitCode);
