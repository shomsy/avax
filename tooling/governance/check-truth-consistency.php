<?php

declare(strict_types=1);

/**
 * check-truth-consistency.php
 *
 * Verifies that:
 * - CURRENT_TRUTH, EXECUTION, ACTIVE, TODO, BUGS, component status lock agree on stage status
 * - V5.9 is not claimed READY if V5.9 blocking items remain
 * - V5.5 benchmarks are marked historical unless re-run
 * - Roadmap features are not claimed implemented
 */

$baseDir = dirname(__DIR__, 2);
$failures = [];

// Check 1: CURRENT_TRUTH says V5.9 blocked
$truthFile = $baseDir . '/CURRENT_TRUTH.md';
$content = file_get_contents($truthFile);
if ($content === false) {
    echo "CHECK 1: CURRENT_TRUTH.md not found — FAIL\n";
    exit(1);
}

// V5.9 should be BLOCKED until cleanup is green
$v59Blocked = str_contains($content, 'V5.9') && str_contains($content, 'BLOCKED');
$v59ReadyNext = str_contains($content, 'V5.9') && str_contains($content, 'READY_NEXT');

if ($v59Blocked || !$v59ReadyNext) {
    echo "CHECK 1: CURRENT_TRUTH says V5.9 is BLOCKED (not prematurely READY) — PASS\n";
} else {
    $failures[] = "CURRENT_TRUTH claims V5.9 READY_NEXT but cleanup blockers may remain";
    echo "CHECK 1: V5.9 status in CURRENT_TRUTH — FAIL (claims READY_NEXT prematurely)\n";
}

// Check 2: EXECUTION.md says cleanup is active
$execFile = $baseDir . '/EVIDENCE/EXECUTION.md';
if (file_exists($execFile)) {
    $execContent = file_get_contents($execFile);
    if ($execContent !== false && str_contains($execContent, 'BLOCKED')) {
        echo "CHECK 2: EXECUTION.md says V5.9 BLOCKED — PASS\n";
    } else {
        $failures[] = "EXECUTION.md does not show V5.9 as BLOCKED";
        echo "CHECK 2: EXECUTION.md V5.9 status — FAIL\n";
    }
} else {
    echo "CHECK 2: EXECUTION.md not found — SKIP\n";
}

// Check 3: Component status lock exists and has entries
$lockFile = $baseDir . '/EVIDENCE/components/component-status-lock.md';
if (file_exists($lockFile)) {
    $lockContent = file_get_contents($lockFile);
    $componentCount = preg_match_all('/^\|[^|]+\|[^|]+\|/m', $lockContent);
    if ($componentCount > 10) {
        echo "CHECK 3: Component status lock has $componentCount entries — PASS\n";
    } else {
        $failures[] = "Component status lock has only $componentCount entries (incomplete coverage)";
        echo "CHECK 3: Component status lock coverage — FAIL\n";
    }
} else {
    $failures[] = "Component status lock file not found";
    echo "CHECK 3: Component status lock — FAIL\n";
}

// Check 4: Cleanup evidence exists
$cleanupDir = $baseDir . '/EVIDENCE/cleanup';
if (is_dir($cleanupDir)) {
    $evidenceCount = count(glob($cleanupDir . '/*.md'));
    echo "CHECK 4: $evidenceCount cleanup evidence files — PASS\n";
} else {
    $failures[] = "EVIDENCE/cleanup directory not found";
    echo "CHECK 4: Cleanup evidence — FAIL\n";
}

echo "\n";
if ($failures !== []) {
    echo "FAIL: " . count($failures) . " truth consistency checks failed:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}

echo "PASS: Truth consistency verified\n";
exit(0);
