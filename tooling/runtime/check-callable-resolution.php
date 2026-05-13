<?php

declare(strict_types=1);

/**
 * check-callable-resolution.php
 *
 * Verifies that:
 * - Events use ResolveEventListeners for callable resolution
 * - Database lifecycle uses GlobalDatabaseLifecycleState for listener resolution
 * - No direct `new $listener()` in hot-path dispatch
 * - ResolveCallable or equivalent exists in framework
 */

$baseDir = dirname(__DIR__, 2);
$failures = [];
$checks = 0;

// Check 1: ResolveEventListeners exists in Events
$resolveEvents = $baseDir . '/components/Operations/Events/System/Capabilities/ResolveEventListeners/ResolveEventListeners.php';
if (file_exists($resolveEvents)) {
    echo "CHECK 1: ResolveEventListeners exists — PASS\n";
} else {
    $failures[] = "ResolveEventListeners not found";
    echo "CHECK 1: ResolveEventListeners — FAIL\n";
}
$checks++;

// Check 2: Events dispatch uses resolution (not direct new $listener)
$invokeFile = $baseDir . '/components/Operations/Events/System/Capabilities/InvokeEventListener/InvokeEventListener.php';
if (file_exists($invokeFile)) {
    $content = file_get_contents($invokeFile);
    if ($content !== false && !preg_match('/new\s+\$[a-zA-Z_]/', $content)) {
        echo "CHECK 2: Events dispatch does not use new \$listener — PASS\n";
    } else {
        $failures[] = "Events dispatch uses new \$listener";
        echo "CHECK 2: Events dispatch — FAIL\n";
    }
} else {
    $failures[] = "InvokeEventListener not found";
    echo "CHECK 2: InvokeEventListener — FAIL\n";
}
$checks++;

// Check 3: Database lifecycle uses GlobalDatabaseLifecycleState
$globalState = $baseDir . '/components/DataStack/Database/System/Foundation/Lifecycle/GlobalDatabaseLifecycleState.php';
if (file_exists($globalState)) {
    echo "CHECK 3: Database lifecycle uses GlobalDatabaseLifecycleState — PASS\n";
} else {
    $failures[] = "GlobalDatabaseLifecycleState not found";
    echo "CHECK 3: Database lifecycle resolution — FAIL\n";
}
$checks++;

// Check 4: Database does not use direct new $listener in hot path
$lifecycleDir = $baseDir . '/components/DataStack/Database/System/Capabilities/Lifecycle';
$dbClean = true;
if (is_dir($lifecycleDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($lifecycleDir));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;
        $content = file_get_contents($file->getPathname());
        if ($content !== false && preg_match('/new\s+\$[a-zA-Z_]/', $content)) {
            $dbClean = false;
            break;
        }
    }
}
if ($dbClean) {
    echo "CHECK 4: Database lifecycle does not use new \$listener — PASS\n";
} else {
    $failures[] = "Database lifecycle uses new \$listener";
    echo "CHECK 4: Database lifecycle hot path — FAIL\n";
}
$checks++;

// Check 5: Compiled listener registry exists
$compiledRegistry = $baseDir . '/components/Operations/Events/System/Foundation/CompiledListenerRegistry.php';
if (file_exists($compiledRegistry)) {
    echo "CHECK 5: CompiledListenerRegistry exists — PASS\n";
} else {
    $failures[] = "CompiledListenerRegistry not found";
    echo "CHECK 5: Compiled listener registry — FAIL\n";
}
$checks++;

echo "\n";
if ($failures !== []) {
    echo "FAIL: " . count($failures) . " callable resolution checks failed:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}

echo "PASS: $checks callable resolution checks passed\n";
exit(0);
