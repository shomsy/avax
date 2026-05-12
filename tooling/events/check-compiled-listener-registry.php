#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Compiled Listener Registry Gate
 *
 * Verifies that:
 * 1. DSL registrations compile into registry
 * 2. #[ListensTo] attributes compile into same registry
 * 3. registry is canonical
 * 4. priority/order metadata exists
 * 5. source tracking exists
 * 6. registry freezes
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];

// --- Check 1: CompiledListenerRegistry exists ---
$compiledRegistryPath = $root . '/components/Operations/Events/System/Foundation/CompiledListenerRegistry.php';
$checks['CompiledListenerRegistry_file_exists'] = file_exists($compiledRegistryPath);

if (file_exists($compiledRegistryPath)) {
    $registryContent = file_get_contents($compiledRegistryPath);

    // --- Check 2: Has add, getListenersFor, freeze methods ---
    $checks['CompiledListenerRegistry_has_add'] = str_contains($registryContent, 'public function add(');
    $checks['CompiledListenerRegistry_has_getListenersFor'] = str_contains($registryContent, 'public function getListenersFor(');
    $checks['CompiledListenerRegistry_has_freeze'] = str_contains($registryContent, 'public function freeze()');

    // --- Check 3: Registry freezes ---
    $checks['CompiledListenerRegistry_frozen_state'] = str_contains($registryContent, 'private bool $frozen')
        && str_contains($registryContent, 'Cannot register listener after CompiledListenerRegistry is frozen');

    // --- Check 4: Priority/sort exists ---
    $checks['CompiledListenerRegistry_priority_sort'] = str_contains($registryContent, 'usort')
        && str_contains($registryContent, 'priority');
} else {
    $checks['CompiledListenerRegistry_has_add'] = false;
    $checks['CompiledListenerRegistry_has_getListenersFor'] = false;
    $checks['CompiledListenerRegistry_has_freeze'] = false;
    $checks['CompiledListenerRegistry_frozen_state'] = false;
    $checks['CompiledListenerRegistry_priority_sort'] = false;
}

// --- Check 5: CompileEventListeners flow compiles both DSL and attribute sources ---
$compilePath = $root . '/components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php';
$checks['CompileEventListeners_file_exists'] = file_exists($compilePath);

if (file_exists($compilePath)) {
    $compileContent = file_get_contents($compilePath);
    $checks['CompileEventListeners_compiles_DSL'] = str_contains($compileContent, 'compileDslRegistrations');
    $checks['CompileEventListeners_compiles_attributes'] = str_contains($compileContent, 'compileAttributeDeclarations');
    $checks['CompileEventListeners_freezes_result'] = str_contains($compileContent, '->freeze()');
    $checks['CompileEventListeners_uses_single_registry'] = str_contains($compileContent, 'CompiledListenerRegistry')
        && str_contains($compileContent, 'new CompiledListenerRegistry()');
} else {
    $checks['CompileEventListeners_compiles_DSL'] = false;
    $checks['CompileEventListeners_compiles_attributes'] = false;
    $checks['CompileEventListeners_freezes_result'] = false;
    $checks['CompileEventListeners_uses_single_registry'] = false;
}

// --- Check 6: CompiledListener has priority, source, order metadata ---
$compiledListenerPath = $root . '/components/Operations/Events/System/Foundation/CompiledListener.php';
$checks['CompiledListener_file_exists'] = file_exists($compiledListenerPath);

if (file_exists($compiledListenerPath)) {
    $listenerContent = file_get_contents($compiledListenerPath);
    $checks['CompiledListener_has_priority'] = str_contains($listenerContent, 'public int $priority');
    $checks['CompiledListener_has_source'] = str_contains($listenerContent, 'public ListenerSource $source');
    $checks['CompiledListener_has_order'] = str_contains($listenerContent, 'public int $order');
} else {
    $checks['CompiledListener_has_priority'] = false;
    $checks['CompiledListener_has_source'] = false;
    $checks['CompiledListener_has_order'] = false;
}

// --- Check 7: ListenerSource enum exists with Dsl/Attribute/Configuration ---
$listenerSourcePath = $root . '/components/Operations/Events/System/Foundation/ListenerSource.php';
$checks['ListenerSource_file_exists'] = file_exists($listenerSourcePath);

if (file_exists($listenerSourcePath)) {
    $sourceContent = file_get_contents($listenerSourcePath);
    $checks['ListenerSource_has_Dsl'] = str_contains($sourceContent, 'case Dsl');
    $checks['ListenerSource_has_Attribute'] = str_contains($sourceContent, 'case Attribute');
    $checks['ListenerSource_has_Configuration'] = str_contains($sourceContent, 'case Configuration');
} else {
    $checks['ListenerSource_has_Dsl'] = false;
    $checks['ListenerSource_has_Attribute'] = false;
    $checks['ListenerSource_has_Configuration'] = false;
}

// --- Check 8: Registry is canonical — no duplicate CompiledListenerRegistry outside Operations/Events ---
$duplicateFound = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/components', RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    if ($file->getFilename() === 'CompiledListenerRegistry.php') {
        $path = $file->getPathname();
        if (! str_contains($path, 'Operations/Events')) {
            $duplicateFound[] = $path;
        }
    }
}
$checks['no_duplicate_CompiledListenerRegistry'] = $duplicateFound === [];

// --- Report ---
echo "EVENT COMPILED LISTENER REGISTRY GATE\n";
echo str_repeat('=', 60) . "\n\n";

$allPass = true;
foreach ($checks as $name => $pass) {
    $status = $pass ? 'PASS' : 'FAIL';
    if (! $pass) {
        $allPass = false;
        $exitCode = 1;
    }
    printf("  [%s] %s\n", $status, $name);
}

echo "\n";
echo str_repeat('=', 60) . "\n";

if ($allPass) {
    echo "Result: PASS — Compiled listener registry verified.\n";
} else {
    echo "Result: FAIL — Compiled listener registry gate failed.\n";
    if ($duplicateFound !== []) {
        echo "  Duplicate CompiledListenerRegistry found:\n";
        foreach ($duplicateFound as $f) {
            echo "    - {$f}\n";
        }
    }
}

exit($exitCode);
