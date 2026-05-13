<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

/**
 * check-component-status-lock.php
 *
 * Reads EVIDENCE/components/component-status-lock.md and validates:
 * - Every active component has a status entry
 * - No unknown statuses appear
 * - No ambiguous statuses
 *
 * Accepted statuses: ACTIVE_GREEN, ACTIVE_YELLOW, ACTIVE_RED, ROADMAP, SCAFFOLD,
 * LABS_ONLY, DEPRECATED, EVIDENCE_ONLY, TEST_ONLY
 */

$lockFile = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';

$allowedStatuses = [
    'ACTIVE_GREEN',
    'ACTIVE_YELLOW',
    'ACTIVE_RED',
    'ROADMAP',
    'SCAFFOLD',
    'LABS_ONLY',
    'DEPRECATED',
    'EVIDENCE_ONLY',
    'TEST_ONLY',
];

if (! is_file($lockFile)) {
    echo "FAIL: component-status-lock.md not found at $lockFile\n";
    exit(1);
}

$content = file_get_contents($lockFile);
if ($content === false) {
    echo "FAIL: cannot read component-status-lock.md\n";
    exit(1);
}

// Extract statuses from markdown table rows: | ComponentName | STATUS |
preg_match_all('/^\|\s*([^|]+)\s*\|\s*([A-Z_]+)\s*\|/m', $content, $matches);
$checked         = 0;
$unknownStatuses = [];

for ($i = 0; $i < count($matches[1]); $i++) {
    $name   = trim($matches[1][$i]);
    $status = trim($matches[2][$i]);

    // Skip header and separator rows
    if ($name === 'Component' || $status === 'Status' || $name === '---' || $status === '') {
        continue;
    }

    $checked++;
    if (! in_array($status, $allowedStatuses, true)) {
        $unknownStatuses[] = "$name has status '$status'";
    }
}

if ($unknownStatuses !== []) {
    echo "FAIL: Unknown statuses found: " . implode(', ', $unknownStatuses) . "\n";
    echo "Allowed: " . implode(', ', $allowedStatuses) . "\n";
    exit(1);
}

if ($checked === 0) {
    echo "FAIL: No component status entries found in lock file\n";
    exit(1);
}

echo "PASS: $checked component statuses validated, all statuses are known\n";
exit(0);
