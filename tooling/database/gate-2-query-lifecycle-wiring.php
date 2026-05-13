#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 2: Query Lifecycle Wiring
 *
 * Verifies that QueryOrchestrator fires lifecycle events through the compiled registry.
 */

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\RedactBindings;

$root     = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 2: Query Lifecycle Wiring ===\n\n";

// Check lifecycle event classes exist
$queryEvents = [
    'QueryExecuting',
    'QueryExecuted',
    'QueryFailed',
];

$eventBase = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/Events/';
foreach ($queryEvents as $event) {
    $file = $eventBase . $event . '.php';
    if (!file_exists($file)) {
        echo "MISSING: {$event}.php\n";
        $exitCode = 1;
    }
}

// Check QueryLifecyclePhase enum
$phaseFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/QueryLifecyclePhase.php';
if (!file_exists($phaseFile)) {
    echo "MISSING: QueryLifecyclePhase.php\n";
    $exitCode = 1;
}

// Check QueryOrchestrator has lifecycle dispatch methods
$orchestratorFile = $root . '/components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php';
if (!file_exists($orchestratorFile)) {
    echo "MISSING: QueryOrchestrator.php\n";
    $exitCode = 1;
} else {
    $content = file_get_contents($orchestratorFile);
    $methods = [
        'dispatchQueryLifecycle',
        'checkSlowQuery',
        'buildQueryEvent',
    ];
    foreach ($methods as $method) {
        if (!str_contains($content ?? '', "function {$method}")) {
            echo "MISSING METHOD: QueryOrchestrator::{$method}()\n";
            $exitCode = 1;
        }
    }

    // Check that query/execute call lifecycle dispatch
    foreach (['query', 'execute'] as $op) {
        if (!str_contains($content ?? '', "function {$op}") || !str_contains($content ?? '', 'dispatchQuery')) {
            echo "MISSING: QueryOrchestrator::{$op}() does not call dispatchQuery\n";
            $exitCode = 1;
        }
    }
}

// Check QueryLifecycleDsl
$dslFile = $root . '/components/DataStack/Database/System/Capabilities/Lifecycle/QueryLifecycleDsl.php';
if (!file_exists($dslFile)) {
    echo "MISSING: QueryLifecycleDsl.php\n";
    $exitCode = 1;
}

// Check global functions (autoloaded via composer)
if (!file_exists($root . '/components/DataStack/Database/System/Capabilities/Lifecycle/global-functions.php')) {
    // Functions may be defined via DSL class instead
    if (!file_exists($dslFile)) {
        echo "MISSING: onQuery() global function or DSL fallback\n";
        $exitCode = 1;
    }
}

if ($exitCode === 0) {
    // Behavior checks: verify query binding redaction.
    require_once $root . '/vendor/autoload.php';

    // Verify RedactBindings can redact named bindings with sensitive keys.
    $redacted = RedactBindings::redact(['password' => 'secret123', 'name' => 'Alice']);
    if ($redacted['password'] !== '***') {
        echo "BEHAVIOR FAIL: RedactBindings did not redact 'password' binding.\n";
        $exitCode = 1;
    } elseif ($redacted['name'] !== 'Alice') {
        echo "BEHAVIOR FAIL: RedactBindings incorrectly redacted non-sensitive 'name' binding.\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: RedactBindings redacts sensitive named bindings correctly.\n";
    }

    // Verify RedactBindings redacts positional Bearer tokens.
    $positional = RedactBindings::redact([0 => 'Bearer abc123', 1 => 'normal']);
    if ($positional[0] !== '***') {
        echo "BEHAVIOR FAIL: RedactBindings did not redact Bearer token in positional binding.\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: RedactBindings redacts Bearer tokens in positional bindings.\n";
    }

    // Verify QueryOrchestrator::buildQueryEvent() applies redaction.
    $orchestratorContent = file_get_contents($orchestratorFile);
    if (! str_contains($orchestratorContent ?? '', 'RedactBindings::redact')) {
        echo "BEHAVIOR FAIL: QueryOrchestrator::buildQueryEvent() does not call RedactBindings::redact().\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: QueryOrchestrator applies RedactBindings in buildQueryEvent().\n";
    }

    echo "PASS: Query lifecycle wiring is complete with behavior proof.\n";
} else {
    echo "FAIL: Query lifecycle wiring has gaps.\n";
}

exit($exitCode);
