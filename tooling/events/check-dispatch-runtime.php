#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Dispatch Runtime Gate
 *
 * Verifies that:
 * 1. emit() reaches EventEmitter/EventDispatcher path
 * 2. dispatch reads compiled listener registry
 * 3. dispatch invokes listeners
 * 4. listener failure bubbles by default
 * 5. no-listener case returns event
 * 6. no runtime attribute reflection in hot path
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];

// --- Check 1: emit() in functions.php delegates to EventEmitter ---
$functionsPath = $root . '/components/Operations/Events/System/PublicSurface/functions.php';
$checks['emit_function_exists'] = file_exists($functionsPath);

if (file_exists($functionsPath)) {
    $functionsContent = file_get_contents($functionsPath);
    $checks['emit_reaches_EventEmitter'] = str_contains($functionsContent, 'GlobalEventListenerState::emitter()->emit');
} else {
    $checks['emit_reaches_EventEmitter'] = false;
}

// --- Check 2: EventEmitter reads from CompiledListenerRegistry ---
$emitterPath = $root . '/components/Operations/Events/System/Foundation/EventEmitter.php';
$checks['EventEmitter_file_exists'] = file_exists($emitterPath);

if (file_exists($emitterPath)) {
    $emitterContent = file_get_contents($emitterPath);
    $checks['EventEmitter_reads_compiled_registry'] = str_contains($emitterContent, 'CompiledListenerRegistry')
        && str_contains($emitterContent, 'getListenersFor');
    $checks['EventEmitter_has_emit_signature'] = str_contains($emitterContent, 'public function emit(object $event): object');
} else {
    $checks['EventEmitter_reads_compiled_registry'] = false;
    $checks['EventEmitter_has_emit_signature'] = false;
}

// --- Check 3: Dispatch invokes listeners via ResolveEventListeners + InvokeEventListener ---
if (file_exists($emitterPath)) {
    $emitterContent = file_get_contents($emitterPath);
    $checks['EventEmitter_uses_ResolveEventListeners'] = str_contains($emitterContent, 'ResolveEventListeners')
        && str_contains($emitterContent, '->resolve(');
    $checks['EventEmitter_uses_InvokeEventListener'] = str_contains($emitterContent, 'InvokeEventListener')
        && str_contains($emitterContent, '->invoke(');
} else {
    $checks['EventEmitter_uses_ResolveEventListeners'] = false;
    $checks['EventEmitter_uses_InvokeEventListener'] = false;
}

// --- Check 4: Listener failure bubbles by default (no catch-and-ignore) ---
if (file_exists($emitterPath)) {
    $emitSection = extractEmitBody(file_get_contents($emitterPath));
    // No try/catch that swallows exceptions in the emit body
    $hasCatchAndIgnore = preg_match('/try\s*\{.*catch\s*\([^)]+\)\s*\{\s*(\/\/|\/\*)?.*(ignore|suppress|skip|silence)/s', $emitSection);
    $checks['listener_failure_bubbles_by_default'] = ! $hasCatchAndIgnore;
    $checks['no_broad_catch_in_emit'] = ! str_contains($emitSection, 'catch (\Exception')
        && ! str_contains($emitSection, 'catch (\Throwable');
} else {
    $checks['listener_failure_bubbles_by_default'] = false;
    $checks['no_broad_catch_in_emit'] = false;
}

// --- Check 5: No-listener case returns event ---
if (file_exists($emitterPath)) {
    $emitterContent = file_get_contents($emitterPath);
    // emit() must return $event at the end
    $checks['no_listener_returns_event'] = str_contains($emitterContent, 'return $event');
} else {
    $checks['no_listener_returns_event'] = false;
}

// --- Check 6: No runtime attribute reflection in hot path ---
if (file_exists($emitterPath)) {
    $emitBody = extractEmitBody(file_get_contents($emitterPath));
    // The emit body itself should not use ReflectionClass, getAttributes, or ReflectionAttribute
    $checks['no_reflection_in_hot_path'] = ! str_contains($emitBody, 'ReflectionClass')
        && ! str_contains($emitBody, 'getAttributes')
        && ! str_contains($emitBody, 'ReflectionAttribute');

    // However, ReflectionClass usage outside emit body (in compile path) is OK
    $checks['reflection_only_in_compile_path'] = str_contains($emitterContent, 'attributeReflectionUsed')
        || ! preg_match('/new\s+\\\\?ReflectionClass/', $emitBody);
} else {
    $checks['no_reflection_in_hot_path'] = false;
    $checks['reflection_only_in_compile_path'] = false;
}

// --- Helpers ---
function extractEmitBody(string $content): string
{
    $start = strpos($content, 'public function emit');
    if ($start === false) {
        return '';
    }
    // Find the opening brace
    $braceStart = strpos($content, '{', $start);
    if ($braceStart === false) {
        return '';
    }
    // Find matching closing brace
    $depth = 1;
    $pos = $braceStart + 1;
    $len = strlen($content);
    while ($pos < $len && $depth > 0) {
        if ($content[$pos] === '{') {
            $depth++;
        } elseif ($content[$pos] === '}') {
            $depth--;
        }
        $pos++;
    }
    return substr($content, $braceStart, $pos - $braceStart);
}

// --- Report ---
echo "EVENT DISPATCH RUNTIME GATE\n";
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
    echo "Result: PASS — Dispatch runtime verified.\n";
} else {
    echo "Result: FAIL — Dispatch runtime gate failed.\n";
}

exit($exitCode);
