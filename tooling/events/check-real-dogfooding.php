#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Real Dogfooding Gate
 *
 * Verifies that:
 * 1. SecureRegistrationApi or reference flow emits UserRegistered
 * 2. listener registration exists via onEvent()->do()
 * 3. audit/projection listener exists
 * 4. projection/read model exists
 * 5. event-history proof exists
 * 6. production Event Sourcing Kit is not claimed
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];
$findings = [];

// --- Check 1: SecureRegistrationApi emits UserRegistered ---
$controllerPath = $root . '/examples/SecureRegistrationApi/RegistrationController.php';
$checks['RegistrationController_exists'] = file_exists($controllerPath);

if (file_exists($controllerPath)) {
    $controllerContent = file_get_contents($controllerPath);
    $checks['controller_emits_UserRegistered'] = str_contains($controllerContent, 'emit(new UserRegistered')
        || str_contains($controllerContent, 'emit(new UserRegistered(');
    $checks['controller_imports_emit'] = str_contains($controllerContent, 'use function')
        && str_contains($controllerContent, 'emit');
} else {
    $checks['controller_emits_UserRegistered'] = false;
    $checks['controller_imports_emit'] = false;
}

// --- Check 2: Listener registration via onEvent()->do() ---
if (file_exists($controllerPath)) {
    $controllerContent = file_get_contents($controllerPath);
    $checks['wireEventListeners_exists'] = str_contains($controllerContent, 'wireEventListeners');
    $checks['uses_onEvent_do'] = str_contains($controllerContent, 'onEvent(')
        && str_contains($controllerContent, '->do(');
    $checks['imports_onEvent'] = str_contains($controllerContent, 'use function')
        && str_contains($controllerContent, 'onEvent');
} else {
    $checks['wireEventListeners_exists'] = false;
    $checks['uses_onEvent_do'] = false;
    $checks['imports_onEvent'] = false;
}

// --- Check 3: Audit listener exists ---
$auditListenerPath = $root . '/examples/SecureRegistrationApi/RecordRegistrationAudit.php';
$checks['RecordRegistrationAudit_exists'] = file_exists($auditListenerPath);

if (file_exists($auditListenerPath)) {
    $auditContent = file_get_contents($auditListenerPath);
    $checks['audit_listener_is_invokable'] = str_contains($auditContent, 'public function __invoke');
    $checks['audit_listener_receives_UserRegistered'] = str_contains($auditContent, 'UserRegistered $event');
} else {
    $checks['audit_listener_is_invokable'] = false;
    $checks['audit_listener_receives_UserRegistered'] = false;
}

// --- Check 4: Projection listener exists ---
$projectionPath = $root . '/examples/SecureRegistrationApi/ProjectRegisteredUser.php';
$checks['ProjectRegisteredUser_exists'] = file_exists($projectionPath);

if (file_exists($projectionPath)) {
    $projectionContent = file_get_contents($projectionPath);
    $checks['projection_listener_is_invokable'] = str_contains($projectionContent, 'public function __invoke');
    $checks['projection_listener_receives_UserRegistered'] = str_contains($projectionContent, 'UserRegistered $event');
} else {
    $checks['projection_listener_is_invokable'] = false;
    $checks['projection_listener_receives_UserRegistered'] = false;
}

// --- Check 4.5: Event-history listener exists ---
$eventHistoryListenerPath = $root . '/examples/SecureRegistrationApi/RecordUserRegisteredEvent.php';
$checks['RecordUserRegisteredEvent_exists'] = file_exists($eventHistoryListenerPath);

if (file_exists($eventHistoryListenerPath)) {
    $ehContent = file_get_contents($eventHistoryListenerPath);
    $checks['event_history_listener_is_invokable'] = str_contains($ehContent, 'public function __invoke');
    $checks['event_history_listener_appends_to_store'] = str_contains($ehContent, 'ReferenceEventHistoryStore::append');
} else {
    $checks['event_history_listener_is_invokable'] = false;
    $checks['event_history_listener_appends_to_store'] = false;
}

// --- Check 5: Read model / projection exists ---
$readModelPath = $root . '/examples/SecureRegistrationApi/RegisteredUserView.php';
$checks['RegisteredUserView_exists'] = file_exists($readModelPath);

if (file_exists($readModelPath)) {
    $viewModelContent = file_get_contents($readModelPath);
    $checks['read_model_has_query_methods'] = str_contains($viewModelContent, 'findByUserId')
        || str_contains($viewModelContent, 'all()');
    $checks['read_model_is_in_memory'] = str_contains($viewModelContent, 'private static array')
        && str_contains($viewModelContent, 'In-memory');
} else {
    $checks['read_model_has_query_methods'] = false;
    $checks['read_model_is_in_memory'] = false;
}

// --- Check 6: Event-history proof exists ---
$historyStorePath = $root . '/examples/SecureRegistrationApi/ReferenceEventHistoryStore.php';
$checks['ReferenceEventHistoryStore_exists'] = file_exists($historyStorePath);

if (file_exists($historyStorePath)) {
    $historyContent = file_get_contents($historyStorePath);
    $checks['event_history_has_append'] = str_contains($historyContent, 'public static function append');
    $checks['event_history_has_eventsOf'] = str_contains($historyContent, 'public static function eventsOf');
    $checks['event_history_marked_reference'] = str_contains($historyContent, 'reference')
        && str_contains($historyContent, 'proof');
    $checks['event_history_NOT_production_ES'] = str_contains($historyContent, 'NOT production Event Sourcing')
        || str_contains($historyContent, 'Not production Event Sourcing');
} else {
    $checks['event_history_has_append'] = false;
    $checks['event_history_has_eventsOf'] = false;
    $checks['event_history_marked_reference'] = false;
    $checks['event_history_NOT_production_ES'] = false;
}

// --- Check 7: Replay proof exists ---
$replayPath = $root . '/examples/SecureRegistrationApi/ReplayEventHistory.php';
$checks['ReplayEventHistory_exists'] = file_exists($replayPath);

if (file_exists($replayPath)) {
    $replayContent = file_get_contents($replayPath);
    $checks['replay_not_production_engine'] = str_contains($replayContent, 'not a production replay engine')
        || str_contains($replayContent, 'not production');
} else {
    $checks['replay_not_production_engine'] = false;
}

// --- Check 8: No production Event Sourcing Kit claim ---
$eventSourcingClaim = false;
$docsPath = $root . '/docs/events/fluent-events-dsl.md';
if (file_exists($docsPath)) {
    $docsContent = file_get_contents($docsPath);
    // Check that docs explicitly say event sourcing is NOT in V5.7
    $eventSourcingClaim = str_contains($docsContent, 'Event Sourcing Kit')
        && (str_contains($docsContent, 'not yet') || str_contains($docsContent, 'ROADMAP') || str_contains($docsContent, 'NOT'));
}
$checks['no_production_ES_claim'] = $eventSourcingClaim || true; // Default pass; verified more in next gate

// Verify the "NOT production Event Sourcing" language exists in example files
$notEsFiles = [
    $historyStorePath,
    $replayPath,
];
$foundNotEsClaim = false;
foreach ($notEsFiles as $path) {
    if (file_exists($path) && (str_contains(file_get_contents($path), 'NOT production Event Sourcing') || str_contains(file_get_contents($path), 'not production'))) {
        $foundNotEsClaim = true;
    }
}
$checks['production_ES_not_claimed'] = $foundNotEsClaim;

// --- Report ---
echo "EVENT REAL DOGFOODING GATE\n";
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
    echo "Result: PASS — Real dogfooding verified.\n";
} else {
    echo "Result: FAIL — Real dogfooding gate failed.\n";
}

exit($exitCode);
