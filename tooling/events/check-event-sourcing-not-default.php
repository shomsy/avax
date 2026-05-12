#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * V5.7 — Event Sourcing Not Default Gate
 *
 * Verifies that:
 * 1. docs/events/fluent-events-dsl.md does not claim event sourcing as default persistence
 * 2. event-history proof is marked reference/proof only
 * 3. no production Event Sourcing GREEN claim without EventStore, stream versioning, etc.
 */

$root = dirname(__DIR__, 2);

$exitCode = 0;
$checks = [];
$findings = [];

// --- Check 1: Docs do not claim ES as default persistence ---
$docsPath = $root . '/docs/events/fluent-events-dsl.md';
$checks['events_docs_exist'] = file_exists($docsPath);

if (file_exists($docsPath)) {
    $docsContent = file_get_contents($docsPath);

    // Docs must NOT say events are the default persistence mechanism
    $claimsEsAsDefault = preg_match(
        '/event\s+sourcing.*default.*persist/i',
        $docsContent
    ) || preg_match(
        '/default.*persist.*event\s+sourcing/i',
        $docsContent
    );
    $checks['docs_do_not_claim_ES_as_default'] = ! $claimsEsAsDefault;

    // Docs should explicitly list ES-related items as ROADMAP or "not yet"
    $checks['docs_list_CQRS_as_not_yet'] = str_contains($docsContent, 'CQRS projection dogfooding')
        && str_contains($docsContent, 'not yet');
    $checks['docs_list_event_history_as_not_yet'] = str_contains($docsContent, 'Event-history proof')
        && str_contains($docsContent, 'not yet');
} else {
    $checks['docs_do_not_claim_ES_as_default'] = false;
    $checks['docs_list_CQRS_as_not_yet'] = false;
    $checks['docs_list_event_history_as_not_yet'] = false;
}

// --- Check 2: Event-history proof is marked reference/proof only ---
$historyStorePath = $root . '/examples/SecureRegistrationApi/ReferenceEventHistoryStore.php';
$checks['ReferenceEventHistoryStore_exists'] = file_exists($historyStorePath);

if (file_exists($historyStorePath)) {
    $historyContent = file_get_contents($historyStorePath);
    $checks['history_marked_reference_proof'] = str_contains($historyContent, 'reference/proof')
        || str_contains($historyContent, 'ReferenceEventHistoryStore');
    $checks['history_marked_not_production_ES'] = str_contains($historyContent, 'NOT production Event Sourcing')
        || str_contains($historyContent, 'NOT production');

    // Check that it lists missing ES components
    $checks['history_lists_missing_ES_components'] = str_contains($historyContent, 'EventStore')
        && str_contains($historyContent, 'stream versioning')
        && str_contains($historyContent, 'optimistic concurrency');
} else {
    $checks['history_marked_reference_proof'] = false;
    $checks['history_marked_not_production_ES'] = false;
    $checks['history_lists_missing_ES_components'] = false;
}

// --- Check 3: Replay proof is marked reference only ---
$replayPath = $root . '/examples/SecureRegistrationApi/ReplayEventHistory.php';
$checks['ReplayEventHistory_exists'] = file_exists($replayPath);

if (file_exists($replayPath)) {
    $replayContent = file_get_contents($replayPath);
    $checks['replay_marked_reference'] = str_contains($replayContent, 'Reference/proof')
        || str_contains($replayContent, 'reference/proof');
    $checks['replay_not_production'] = str_contains($replayContent, 'not a production replay engine')
        || str_contains($replayContent, 'not production');
    $checks['replay_lists_missing_components'] = str_contains($replayContent, 'event store')
        || str_contains($replayContent, 'stream versioning')
        || str_contains($replayContent, 'snapshots');
} else {
    $checks['replay_marked_reference'] = false;
    $checks['replay_not_production'] = false;
    $checks['replay_lists_missing_components'] = false;
}

// --- Check 4: No production ES GREEN claim without required components ---
// Scan for any file claiming "Event Sourcing" + "GREEN" / "production" + "ready"
$esGreenClaim = false;
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
);
foreach ($iterator as $file) {
    // Skip vendor, .git, node_modules
    $path = $file->getPathname();
    if (str_contains($path, '/vendor/') || str_contains($path, '/.git/') || str_contains($path, '/node_modules/')) {
        continue;
    }
    // Only check PHP and markdown files
    $ext = $file->getExtension();
    if ($ext !== 'php' && $ext !== 'md') {
        continue;
    }
    $content = @file_get_contents($path);
    if ($content === false) {
        continue;
    }
    // Look for claims of production ES with GREEN status
    if (preg_match('/Event\s+Sourcing.*(GREEN|production[-\s]*ready)/i', $content)) {
        // Only flag if it doesn't also say "not yet" or "ROADMAP" or "NOT production"
        if (! str_contains($content, 'NOT production') && ! str_contains($content, 'not yet') && ! str_contains($content, 'ROADMAP') && ! str_contains($content, 'reference/proof')) {
            $esGreenClaim = true;
            $findings[] = "Possible production ES claim in: {$path}";
        }
    }
}
$checks['no_premature_ES_GREEN_claim'] = ! $esGreenClaim;

// --- Check 5: Read model is explicitly in-memory only ---
$readModelPath = $root . '/examples/SecureRegistrationApi/RegisteredUserView.php';
if (file_exists($readModelPath)) {
    $viewModelContent = file_get_contents($readModelPath);
    $checks['read_model_in_memory_only'] = str_contains($viewModelContent, 'In-memory only')
        || str_contains($viewModelContent, 'reference/proof');
} else {
    $checks['read_model_in_memory_only'] = false;
}

// --- Report ---
echo "EVENT SOURCING NOT DEFAULT GATE\n";
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
    echo "Result: PASS — Event sourcing is not claimed as default.\n";
} else {
    echo "Result: FAIL — Event sourcing not default gate failed.\n";
}

exit($exitCode);
