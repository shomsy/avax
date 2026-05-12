#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Fluent DSL Registration Gate
 *
 * Verifies that:
 * 1. onEvent() function exists
 * 2. onEvent() returns EventListenerDsl with do() method
 * 3. do() creates listener registration/declaration
 * 4. DSL does not dispatch
 * 5. DSL uses canonical registry/path
 * 6. no EventInterface required
 * 7. no ListenerInterface required
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];
$findings = [];

// --- Check 1: onEvent() function exists in canonical public surface ---
$functionsPath = $root . '/components/Operations/Events/System/PublicSurface/functions.php';
$checks['onEvent_function_file_exists'] = file_exists($functionsPath);

if (file_exists($functionsPath)) {
    $functionsContent = file_get_contents($functionsPath);
    $checks['onEvent_function_exists'] = str_contains($functionsContent, 'function onEvent');
    $checks['onEvent_returns_EventListenerDsl'] = str_contains($functionsContent, 'EventListenerDsl');
} else {
    $checks['onEvent_function_exists'] = false;
    $checks['onEvent_returns_EventListenerDsl'] = false;
}

// --- Check 2: EventListenerDsl has do() method ---
$dslPath = $root . '/components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php';
$checks['EventListenerDsl_file_exists'] = file_exists($dslPath);

if (file_exists($dslPath)) {
    $dslContent = file_get_contents($dslPath);
    $checks['EventListenerDsl_has_do_method'] = str_contains($dslContent, 'public function do(');
    $checks['EventListenerDsl_do_registers_not_dispatches'] = str_contains($dslContent, 'subscribe')
        && ! str_contains($dslContent, 'dispatch')
        && ! str_contains($dslContent, 'emit');
    $checks['EventListenerDsl_uses_canonical_registry'] = str_contains($dslContent, 'ListenerRegistry');
} else {
    $checks['EventListenerDsl_has_do_method'] = false;
    $checks['EventListenerDsl_do_registers_not_dispatches'] = false;
    $checks['EventListenerDsl_uses_canonical_registry'] = false;
}

// --- Check 3: No EventInterface or ListenerInterface required ---
$noEventInterface = true;
$noListenerInterface = true;

// Check functions.php
if (file_exists($functionsPath)) {
    $content = file_get_contents($functionsPath);
    if (str_contains($content, 'EventInterface') && ! str_contains($content, 'No EventInterface required')) {
        $noEventInterface = false;
    }
    if (str_contains($content, 'ListenerInterface') && ! str_contains($content, 'No ListenerInterface required')) {
        $noListenerInterface = false;
    }
}

// Check EventListenerDsl
if (file_exists($dslPath)) {
    $content = file_get_contents($dslPath);
    if (str_contains($content, 'implements') && (str_contains($content, 'EventInterface') || str_contains($content, 'ListenerInterface'))) {
        $noEventInterface = false;
        $noListenerInterface = false;
    }
    if (str_contains($content, 'EventInterface required') || str_contains($content, 'ListenerInterface required')) {
        // Only fail if it says "required" as a constraint, not "No X required" as a comment
        if (! str_contains($content, 'No EventInterface required')) {
            $noEventInterface = false;
        }
        if (! str_contains($content, 'No ListenerInterface required')) {
            $noListenerInterface = false;
        }
    }
}

$checks['no_EventInterface_required'] = $noEventInterface;
$checks['no_ListenerInterface_required'] = $noListenerInterface;

// --- Check 4: DSL do() returns self for chaining ---
if (file_exists($dslPath)) {
    $dslContent = file_get_contents($dslPath);
    $checks['EventListenerDsl_do_returns_self'] = str_contains($dslContent, '): self')
        && str_contains($dslContent, 'return $this');
} else {
    $checks['EventListenerDsl_do_returns_self'] = false;
}

// --- Report ---
echo "EVENT FLUENT DSL REGISTRATION GATE\n";
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

if (! empty($findings)) {
    echo "\nFindings:\n";
    foreach ($findings as $finding) {
        echo "  - {$finding}\n";
    }
}

echo "\n";
echo str_repeat('=', 60) . "\n";

if ($allPass) {
    echo "Result: PASS — Fluent DSL registration verified.\n";
} else {
    echo "Result: FAIL — Fluent DSL registration gate failed.\n";
}

exit($exitCode);
