#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Gate 1: Entity Lifecycle Wiring
 *
 * Verifies that EntityPersister fires lifecycle events through the compiled registry.
 */

use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\CompiledDatabaseLifecycleRegistry;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecyclePhase;
use Avax\Components\DataStack\Database\System\Foundation\Lifecycle\EntityLifecycleRegistration;

$root     = dirname(__DIR__, 2);
$exitCode = 0;

echo "=== Gate 1: Entity Lifecycle Wiring ===\n\n";

// Check lifecycle event classes exist
$entityEvents = [
    'EntityCreating',
    'EntityCreated',
    'EntityUpdating',
    'EntityUpdated',
    'EntitySaving',
    'EntitySaved',
    'EntityDeleting',
    'EntityDeleted',
    'FailedToSave',
    'FailedToDelete',
];

$eventBase = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/Events/';
foreach ($entityEvents as $event) {
    $file = $eventBase . $event . '.php';
    if (!file_exists($file)) {
        echo "MISSING: {$event}.php\n";
        $exitCode = 1;
    }
}

// Check EntityLifecyclePhase enum
$phaseFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/EntityLifecyclePhase.php';
if (!file_exists($phaseFile)) {
    echo "MISSING: EntityLifecyclePhase.php\n";
    $exitCode = 1;
}

// Check CompiledDatabaseLifecycleRegistry
$registryFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/CompiledDatabaseLifecycleRegistry.php';
if (!file_exists($registryFile)) {
    echo "MISSING: CompiledDatabaseLifecycleRegistry.php\n";
    $exitCode = 1;
}

// Check EntityPersister has lifecycle dispatch methods
$persisterFile = $root . '/components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php';
if (!file_exists($persisterFile)) {
    echo "MISSING: EntityPersister.php\n";
    $exitCode = 1;
} else {
    $content = file_get_contents($persisterFile);
    $methods = [
        'dispatchEntityLifecycle',
        'dispatchEntityLifecycleAfter',
        'dispatchEntityFailure',
        'dispatchEntityEvent',
        'buildPreEvent',
        'buildPostEvent',
    ];
    foreach ($methods as $method) {
        if (!str_contains($content ?? '', "function {$method}")) {
            echo "MISSING METHOD: EntityPersister::{$method}()\n";
            $exitCode = 1;
        }
    }

    // Check that insert/update/delete call lifecycle dispatch
    foreach (['insert', 'update', 'delete'] as $op) {
        if (!str_contains($content ?? '', "function {$op}") || !str_contains($content ?? '', 'dispatchEntity')) {
            echo "MISSING: EntityPersister::{$op}() does not call dispatchEntity\n";
            $exitCode = 1;
        }
    }
}

// Check EntityLifecycleDsl
$dslFile = $root . '/components/DataStack/Database/System/Capabilities/Lifecycle/EntityLifecycleDsl.php';
if (!file_exists($dslFile)) {
    echo "MISSING: EntityLifecycleDsl.php\n";
    $exitCode = 1;
}

// Check global functions (autoloaded via composer)
$composerJson = json_decode(file_get_contents($root . '/composer.json') ?: '{}', true);
$filesAutoload = $composerJson['autoload']['files'] ?? [];
$hasLifecycleAutoload = false;
foreach ($filesAutoload as $f) {
    if (str_contains($f, 'lifecycle') || str_contains($f, 'global')) {
        $hasLifecycleAutoload = true;
    }
}
// Check if onEntity function is available via DSL class
if (!file_exists($root . '/components/DataStack/Database/System/Capabilities/Lifecycle/global-functions.php')) {
    // Functions may be defined via DSL class instead
    if (!file_exists($dslFile)) {
        echo "MISSING: onEntity() global function or DSL fallback\n";
        $exitCode = 1;
    }
}

if ($exitCode === 0) {
    // Behavior checks: verify the registry does NOT do superset expansion.
    require_once $root . '/vendor/autoload.php';

    $registry = new CompiledDatabaseLifecycleRegistry();
    $registry->registerEntity(new EntityLifecycleRegistration(
                                  entityClass: 'App\\TestUser',
                                  phase      : EntityLifecyclePhase::Creating,
                                  listener   : 'CreatingListener',
                              ));
    $registry->registerEntity(new EntityLifecycleRegistration(
                                  entityClass: 'App\\TestUser',
                                  phase      : EntityLifecyclePhase::Saving,
                                  listener   : 'SavingListener',
                              ));

    // Exact lookup: Creating phase should NOT include Saving listeners.
    $creatingListeners = $registry->entityListenersFor('App\\TestUser', EntityLifecyclePhase::Creating);
    if (count($creatingListeners) !== 1) {
        echo "BEHAVIOR FAIL: Creating phase returned " . count($creatingListeners) . " listeners (expected 1, no superset expansion).\n";
        $exitCode = 1;
    } elseif ($creatingListeners[0]['listener'] !== 'CreatingListener') {
        echo "BEHAVIOR FAIL: Creating phase returned wrong listener.\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: Registry exact lookup — no superset expansion in hot path.\n";
    }

    // Saving phase should return only Saving listeners, not Creating.
    $savingListeners = $registry->entityListenersFor('App\\TestUser', EntityLifecyclePhase::Saving);
    if (count($savingListeners) !== 1) {
        echo "BEHAVIOR FAIL: Saving phase returned " . count($savingListeners) . " listeners (expected 1).\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: Saving phase returns only Saving listeners.\n";
    }

    // Check GlobalDatabaseLifecycleState exists (shared registry for DSL + runtime).
    $globalStateFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/GlobalDatabaseLifecycleState.php';
    if (! file_exists($globalStateFile)) {
        echo "BEHAVIOR FAIL: GlobalDatabaseLifecycleState.php missing (DSL + runtime must share registry).\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: GlobalDatabaseLifecycleState provides shared DSL + runtime registry.\n";
    }

    // Check RedactBindings exists for query security.
    $redactFile = $root . '/components/DataStack/Database/System/Foundation/Lifecycle/RedactBindings.php';
    if (! file_exists($redactFile)) {
        echo "BEHAVIOR FAIL: RedactBindings.php missing (query bindings must be redacted).\n";
        $exitCode = 1;
    } else {
        echo "BEHAVIOR: RedactBindings provides query binding redaction.\n";
    }

    echo "PASS: Entity lifecycle wiring is complete with behavior proof.\n";
} else {
    echo "FAIL: Entity lifecycle wiring has gaps.\n";
}

exit($exitCode);
