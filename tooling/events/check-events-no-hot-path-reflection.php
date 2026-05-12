#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Events No Hot-Path Reflection Gate
 *
 * Verifies that:
 * 1. Runtime dispatch path (EventEmitter::emit) has no ReflectionClass / ReflectionAttribute / getAttributes usage
 * 2. Compile/build path may use reflection if documented
 * 3. emit()/dispatch path must not scan attributes
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];

// --- Check 1: EventEmitter::emit has no reflection ---
$emitterPath = $root . '/components/Operations/Events/System/Foundation/EventEmitter.php';
$checks['EventEmitter_file_exists'] = file_exists($emitterPath);

if (file_exists($emitterPath)) {
    $emitterContent = file_get_contents($emitterPath);
    $emitBody = extractMethodBody($emitterContent, 'public function emit');

    // The emit() body must not contain reflection usage
    $checks['emit_no_ReflectionClass'] = ! str_contains($emitBody, 'ReflectionClass');
    $checks['emit_no_getAttributes'] = ! str_contains($emitBody, 'getAttributes');
    $checks['emit_no_ReflectionAttribute'] = ! str_contains($emitBody, 'ReflectionAttribute');

    // The attributeReflectionUsed flag is OK — it tracks whether attributes were used,
    // it doesn't perform reflection in the hot path.
    $checks['emit_tracks_reflection_flag_safely'] = str_contains($emitterContent, 'attributeReflectionUsed');

    // Overall: no reflection in emit body
    $hasReflectionInEmit = str_contains($emitBody, 'new \\ReflectionClass')
        || str_contains($emitBody, 'new ReflectionClass')
        || str_contains($emitBody, '->getAttributes(')
        || str_contains($emitBody, 'ReflectionAttribute');
    $checks['no_reflection_in_emit_body'] = ! $hasReflectionInEmit;
} else {
    $checks['emit_no_ReflectionClass'] = false;
    $checks['emit_no_getAttributes'] = false;
    $checks['emit_no_ReflectionAttribute'] = false;
    $checks['emit_tracks_reflection_flag_safely'] = false;
    $checks['no_reflection_in_emit_body'] = false;
}

// --- Check 2: Compile path may use reflection (documented) ---
$compilePath = $root . '/components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php';
$checks['CompileEventListeners_file_exists'] = file_exists($compilePath);

if (file_exists($compilePath)) {
    $compileContent = file_get_contents($compilePath);
    // Compile path is allowed to use reflection for attribute scanning
    $compileUsesReflection = str_contains($compileContent, 'ReflectionClass')
        && str_contains($compileContent, 'getAttributes');
    $checks['compile_path_uses_reflection_documented'] = $compileUsesReflection
        && str_contains($compileContent, 'attribute');

    // Verify compile path uses ListensTo attribute
    $checks['compile_path_scans_ListensTo'] = str_contains($compileContent, 'ListensTo::class')
        && str_contains($compileContent, '->newInstance()');
} else {
    $checks['compile_path_uses_reflection_documented'] = false;
    $checks['compile_path_scans_ListensTo'] = false;
}

// --- Check 3: No attribute scanning in PSR adapters hot path ---
$psrDispatcherAdapterPath = $root . '/components/Operations/Events/System/Capabilities/Psr14/Psr14EventDispatcherAdapter.php';
if (file_exists($psrDispatcherAdapterPath)) {
    $adapterContent = file_get_contents($psrDispatcherAdapterPath);
    $dispatchBody = extractMethodBody($adapterContent, 'public function dispatch');
    $checks['psr_adapter_dispatch_no_reflection'] = ! str_contains($dispatchBody, 'ReflectionClass')
        && ! str_contains($dispatchBody, 'getAttributes')
        && ! str_contains($dispatchBody, 'ReflectionAttribute');
} else {
    $checks['psr_adapter_dispatch_no_reflection'] = false;
}

$psrProviderAdapterPath = $root . '/components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php';
if (file_exists($psrProviderAdapterPath)) {
    $providerContent = file_get_contents($psrProviderAdapterPath);
    $providerBody = extractMethodBody($providerContent, 'public function getListenersForEvent');
    $checks['psr_provider_no_attribute_scanning'] = ! str_contains($providerBody, 'getAttributes');
    // PSR provider adapter uses CompiledListenerRegistry (pre-compiled), not raw reflection
    $checks['psr_provider_uses_compiled_registry'] = str_contains($providerContent, 'CompiledListenerRegistry')
        && str_contains($providerContent, 'getListenersFor');
} else {
    $checks['psr_provider_no_attribute_scanning'] = false;
    $checks['psr_provider_uses_compiled_registry'] = false;
}

// --- Check 4: functions.php emit() has no reflection ---
$functionsPath = $root . '/components/Operations/Events/System/PublicSurface/functions.php';
if (file_exists($functionsPath)) {
    $functionsContent = file_get_contents($functionsPath);
    $emitFuncBody = extractFunctionBody($functionsContent, 'function emit');
    $checks['surface_emit_no_reflection'] = ! str_contains($emitFuncBody, 'Reflection')
        && ! str_contains($emitFuncBody, 'getAttributes');
} else {
    $checks['surface_emit_no_reflection'] = false;
}

// --- Helpers ---
function extractMethodBody(string $content, string $signature): string
{
    $start = strpos($content, $signature);
    if ($start === false) {
        return '';
    }
    $braceStart = strpos($content, '{', $start);
    if ($braceStart === false) {
        return '';
    }
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

function extractFunctionBody(string $content, string $signature): string
{
    $start = strpos($content, $signature);
    if ($start === false) {
        return '';
    }
    $braceStart = strpos($content, '{', $start);
    if ($braceStart === false) {
        return '';
    }
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
echo "EVENTS NO HOT-PATH REFLECTION GATE\n";
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
    echo "Result: PASS — No hot-path reflection in events runtime.\n";
} else {
    echo "Result: FAIL — Events no hot-path reflection gate failed.\n";
}

exit($exitCode);
