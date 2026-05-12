<?php

declare(strict_types=1);

/**
 * V5.7-01 — Canonical Event Owner Gate
 *
 * Verifies that:
 * 1. components/Operations/Events/ exists as the canonical event owner
 * 2. No other component claims generic canonical event dispatcher ownership
 * 3. Domain-specific event buses are classified correctly
 * 4. No duplicate ListenerRegistry/EventDispatcher exists outside Operations/Events
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];
$findings = [];

// --- Check 1: Canonical owner exists ---
$canonicalPath = $root . '/components/Operations/Events/System/PublicSurface/Events.php';
$canonicalDispatcher = $root . '/components/Operations/Events/System/Capabilities/Dispatcher/EventDispatcher.php';
$canonicalRegistry = $root . '/components/Operations/Events/System/Capabilities/Registry/ListenerRegistry.php';

$checks['canonical_owner_exists'] = file_exists($canonicalPath)
    && file_exists($canonicalDispatcher)
    && file_exists($canonicalRegistry);

// --- Check 2: No duplicate ListenerRegistry outside Operations/Events ---
$listenerRegistryFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/components', RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if ($file->getFilename() === 'ListenerRegistry.php') {
        $path = $file->getPathname();
        if (!str_contains($path, 'Operations/Events')) {
            $listenerRegistryFiles[] = $path;
        }
    }
}
$checks['no_duplicate_listener_registry'] = $listenerRegistryFiles === [];

// --- Check 3: No duplicate EventDispatcher outside Operations/Events ---
$eventDispatcherFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/components', RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if ($file->getFilename() === 'EventDispatcher.php') {
        $path = $file->getPathname();
        if (!str_contains($path, 'Operations/Events')) {
            $eventDispatcherFiles[] = $path;
        }
    }
}
$checks['no_duplicate_event_dispatcher'] = $eventDispatcherFiles === [];

// --- Check 4: Domain-specific event buses exist in expected locations ---
$domainBuses = [
    'messagebus_eventbus' => $root . '/components/Operations/MessageBus/System/Capabilities/Bus/EventBus.php',
    'database_eventbus' => $root . '/components/DataStack/Database/System/Capabilities/Telemetry/Events/EventBus.php',
    'session_eventbus' => $root . '/components/HTTP/Session/System/Capabilities/Events/SessionEventBus.php',
];

foreach ($domainBuses as $name => $path) {
    $checks["domain_bus_{$name}_exists"] = file_exists($path);
}

// --- Check 5: Domain buses do NOT import canonical EventDispatcher ---
$domainBusFiles = array_values($domainBuses);
foreach ($domainBusFiles as $path) {
    if (!file_exists($path)) {
        continue;
    }
    $content = file_get_contents($path);
    $name = basename(dirname($path));
    if (str_contains($content, 'Operations\\Events\\System\\Capabilities\\Dispatcher\\EventDispatcher')) {
        $findings[] = "WARNING: {$name} imports canonical EventDispatcher — should use its own internal dispatch";
    }
}
$checks['no_domain_bus_imports_canonical_dispatcher'] = empty($findings);

// --- Report ---
echo "EVENT CANONICAL OWNER GATE\n";
echo str_repeat('=', 60) . "\n\n";

$allPass = true;
foreach ($checks as $name => $pass) {
    $status = $pass ? 'PASS' : 'FAIL';
    if (!$pass) {
        $allPass = false;
        $exitCode = 1;
    }
    printf("  [%s] %s\n", $status, $name);
}

if (!empty($findings)) {
    echo "\nFindings:\n";
    foreach ($findings as $finding) {
        echo "  - {$finding}\n";
    }
}

echo "\n";
echo str_repeat('=', 60) . "\n";

if ($allPass) {
    echo "Result: PASS — Canonical event owner verified.\n";
} else {
    echo "Result: FAIL — Canonical event owner gate failed.\n";
    if (!empty($listenerRegistryFiles)) {
        echo "  Duplicate ListenerRegistry found:\n";
        foreach ($listenerRegistryFiles as $f) {
            echo "    - {$f}\n";
        }
    }
    if (!empty($eventDispatcherFiles)) {
        echo "  Duplicate EventDispatcher found:\n";
        foreach ($eventDispatcherFiles as $f) {
            echo "    - {$f}\n";
        }
    }
}

exit($exitCode);
