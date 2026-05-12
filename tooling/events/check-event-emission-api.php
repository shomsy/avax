#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Event Emission API Gate
 *
 * Verifies that:
 * 1. emit(object $event): object exists as primary public API
 * 2. emit() delegates to canonical emitter/dispatcher
 * 3. emit() does not register listeners
 * 4. emit() does not expose class-string/array/DTO payload emission
 * 5. emit() does not require EventInterface
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];

// --- Check 1: emit() function exists with correct signature ---
$functionsPath = $root . '/components/Operations/Events/System/PublicSurface/functions.php';
$checks['emit_function_file_exists'] = file_exists($functionsPath);

if (file_exists($functionsPath)) {
    $functionsContent = file_get_contents($functionsPath);
    $checks['emit_function_exists'] = str_contains($functionsContent, 'function emit(object $event): object');
    $checks['emit_delegates_to_emitter'] = str_contains($functionsContent, 'emitter()->emit($event)')
        || str_contains($functionsContent, '->emit($event)');
} else {
    $checks['emit_function_exists'] = false;
    $checks['emit_delegates_to_emitter'] = false;
}

// --- Check 2: emit() does not register listeners ---
if (file_exists($functionsPath)) {
    $emitSection = extractEmitSection(file_get_contents($functionsPath));
    $checks['emit_does_not_register_listeners'] = ! str_contains($emitSection, 'subscribe')
        && ! str_contains($emitSection, 'register')
        && ! str_contains($emitSection, 'onEvent');
} else {
    $checks['emit_does_not_register_listeners'] = false;
}

// --- Check 3: emit() does not accept class-string/array/DTO ---
if (file_exists($functionsPath)) {
    $functionsContent = file_get_contents($functionsPath);
    // emit must accept object $event, not class-string or array
    $checks['emit_accepts_object_not_class_string'] = str_contains($functionsContent, 'function emit(object $event): object');
    $checks['emit_does_not_accept_array'] = ! preg_match('/function\s+emit\s*\(\s*array\s/', $functionsContent);
} else {
    $checks['emit_accepts_object_not_class_string'] = false;
    $checks['emit_does_not_accept_array'] = false;
}

// --- Check 4: EventEmitter::emit matches expected behavior ---
$emitterPath = $root . '/components/Operations/Events/System/Foundation/EventEmitter.php';
$checks['EventEmitter_file_exists'] = file_exists($emitterPath);

if (file_exists($emitterPath)) {
    $emitterContent = file_get_contents($emitterPath);
    $checks['EventEmitter_has_emit_method'] = str_contains($emitterContent, 'public function emit(object $event): object');
    $checks['EventEmitter_uses_compiled_registry'] = str_contains($emitterContent, 'CompiledListenerRegistry')
        && str_contains($emitterContent, 'getListenersFor');
} else {
    $checks['EventEmitter_has_emit_method'] = false;
    $checks['EventEmitter_uses_compiled_registry'] = false;
}

// --- Check 5: No EventInterface requirement ---
$noEventInterface = true;
foreach ([$functionsPath, $emitterPath] as $path) {
    if (! file_exists($path)) {
        continue;
    }
    $content = file_get_contents($path);
    // Allow "No EventInterface required" as a comment but not as an implements clause
    if (preg_match('/implements.*EventInterface/', $content)) {
        $noEventInterface = false;
    }
}
$checks['no_EventInterface_required'] = $noEventInterface;

// --- Helper: extract the emit() function body ---
function extractEmitSection(string $content): string
{
    $start = strpos($content, 'function emit');
    if ($start === false) {
        return '';
    }
    // Get roughly the next 500 chars to cover the function body
    return substr($content, $start, 500);
}

// --- Report ---
echo "EVENT EMISSION API GATE\n";
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
    echo "Result: PASS — Event emission API verified.\n";
} else {
    echo "Result: FAIL — Event emission API gate failed.\n";
}

exit($exitCode);
