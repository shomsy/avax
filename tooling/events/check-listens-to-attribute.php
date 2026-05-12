#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — ListensTo Attribute Gate
 *
 * Verifies that:
 * 1. #[ListensTo] attribute exists
 * 2. attribute is declaration only
 * 3. no queued/async/afterCommit production claim
 * 4. no EventInterface/ListenerInterface requirement
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];

// --- Check 1: ListensTo attribute file exists ---
$listensToPath = $root . '/components/Operations/Events/System/Foundation/ListensTo.php';
$checks['ListensTo_file_exists'] = file_exists($listensToPath);

if (file_exists($listensToPath)) {
    $listensToContent = file_get_contents($listensToPath);

    // --- Check 2: Is a proper PHP attribute ---
    $checks['ListensTo_is_attribute'] = str_contains($listensToContent, '#[Attribute(');
    $checks['ListensTo_targets_class'] = str_contains($listensToContent, 'Attribute::TARGET_CLASS');

    // --- Check 3: Declaration only — no dispatch/emit logic in code (ignore docblock comments) ---
    // Strip docblock comments before checking for dispatch/emit logic
    $codeOnly = preg_replace('/\/\*\*.*?\*\//s', '', $listensToContent);
    $codeOnly = preg_replace('/\/\/.*$/m', '', $codeOnly);
    $checks['ListensTo_declaration_only'] = ! str_contains($codeOnly, 'dispatch(')
        && ! str_contains($codeOnly, 'emit(')
        && ! str_contains($codeOnly, 'invoke(')
        && ! str_contains($codeOnly, 'subscribe(');

    // --- Check 4: No queued/async/afterCommit production claim ---
    $checks['no_queued_claim'] = ! str_contains($listensToContent, 'queued')
        && ! str_contains($listensToContent, 'async')
        && ! str_contains($listensToContent, 'afterCommit');

    // Verify ListenerExecutionMode only has Sync active
    $modePath = $root . '/components/Operations/Events/System/Foundation/ListenerExecutionMode.php';
    if (file_exists($modePath)) {
        $modeContent = file_get_contents($modePath);
        $checks['only_Sync_execution_mode_active'] = str_contains($modeContent, "case Sync = 'sync'")
            && ! str_contains($modeContent, "case Async")
            && ! str_contains($modeContent, "case AfterCommit");
    } else {
        $checks['only_Sync_execution_mode_active'] = false;
    }

    // --- Check 5: No EventInterface/ListenerInterface requirement ---
    $checks['no_EventInterface_in_attribute'] = ! preg_match('/implements.*EventInterface/', $listensToContent);
    $checks['no_ListenerInterface_in_attribute'] = ! preg_match('/implements.*ListenerInterface/', $listensToContent);
    $checks['no_interface_requirement_comment'] = ! (
        str_contains($listensToContent, 'EventInterface required')
        && ! str_contains($listensToContent, 'No EventInterface required')
    ) && ! (
        str_contains($listensToContent, 'ListenerInterface required')
        && ! str_contains($listensToContent, 'No ListenerInterface required')
    );
} else {
    $checks['ListensTo_is_attribute'] = false;
    $checks['ListensTo_targets_class'] = false;
    $checks['ListensTo_declaration_only'] = false;
    $checks['no_queued_claim'] = false;
    $checks['only_Sync_execution_mode_active'] = false;
    $checks['no_EventInterface_in_attribute'] = false;
    $checks['no_ListenerInterface_in_attribute'] = false;
    $checks['no_interface_requirement_comment'] = false;
}

// --- Check 6: ListensTo has eventClass and priority params ---
if (file_exists($listensToPath)) {
    $listensToContent = file_get_contents($listensToPath);
    $checks['ListensTo_has_eventClass_param'] = str_contains($listensToContent, 'public string $eventClass');
    $checks['ListensTo_has_priority_param'] = str_contains($listensToContent, 'public int $priority');
} else {
    $checks['ListensTo_has_eventClass_param'] = false;
    $checks['ListensTo_has_priority_param'] = false;
}

// --- Report ---
echo "EVENT LISTENS-TO ATTRIBUTE GATE\n";
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
    echo "Result: PASS — ListensTo attribute verified.\n";
} else {
    echo "Result: FAIL — ListensTo attribute gate failed.\n";
}

exit($exitCode);
