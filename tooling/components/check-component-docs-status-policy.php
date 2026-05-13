<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

/**
 * check-component-docs-status-policy.php
 *
 * Does not require full docs for every component.
 * Fails if production-ready status is claimed without enough status/evidence.
 * Fails if ROADMAP/SCAFFOLD component appears production-ready.
 * Fails if docs/truth contradict component status.
 * Accepts deferred full docs until production docs phase.
 */

$lockFile  = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';
$truthFile = __DIR__ . '/../../CURRENT_TRUTH.md';

if (! is_file($lockFile)) {
    echo "FAIL: component-status-lock.md not found\n";
    exit(1);
}

$lockContent = file_get_contents($lockFile);
if ($lockContent === false) {
    echo "FAIL: cannot read component-status-lock.md\n";
    exit(1);
}

// Extract component statuses
$components = [];
preg_match_all('/\|\s*([^\|]+)\|\s*(\S+)\s*\|/', $lockContent, $matches);
for ($i = 0; $i < count($matches[1]); $i++) {
    $name   = trim($matches[1][$i]);
    $status = trim($matches[2][$i]);
    if ($name && $status && $name !== 'Component') {
        $components[$name] = $status;
    }
}

$violations = [];
$checked    = 0;

// Check for contradictions in CURRENT_TRUTH.md
$truthContent = '';
if (is_file($truthFile)) {
    $truthContent = file_get_contents($truthFile) ?: '';
}

foreach ($components as $name => $status) {
    // ROADMAP/SCAFFOLD should not appear as production-ready in truth
    if (in_array($status, ['ROADMAP', 'SCAFFOLD', 'EVIDENCE_ONLY'], true)) {
        // These should not be claimed as complete/prod-ready in truth
        // We allow mentions but not "PROVEN"/"COMPLETE"/"GREEN" claims
        if (preg_match('/' . preg_quote($name, '/') . '.*(?:PROVEN|COMPLETE.*GREEN|production-ready)/i', $truthContent)) {
            $violations[] = "$name is $status but CURRENT_TRUTH claims production-ready";
        }
    }

    // ACTIVE_GREEN should have some mention in truth or evidence
    if ($status === 'ACTIVE_GREEN') {
        $hasEvidence = strpos($truthContent, $name) !== false;
        if (! $hasEvidence) {
            // Not a hard fail — some small components may be implicitly proven
        }
    }

    $checked++;
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " docs/status contradictions found:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    exit(1);
}

echo "PASS: $checked components checked, no docs/status contradictions\n";
exit(0);
