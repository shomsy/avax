#!/usr/bin/env php
<?php

declare(strict_types=1);

$root         = dirname(__DIR__, 2);
$currentTruth = $root . '/CURRENT_TRUTH.md';

if (! file_exists($currentTruth)) {
    echo "No CURRENT_TRUTH.md found. Stage check skipped.\n";
    exit(0);
}

$content = file_get_contents($currentTruth);
$status  = 'UNKNOWN';

foreach (['V1', 'V2', 'V3'] as $stage) {
    if (stripos($content, $stage) !== false && stripos($content, 'GREEN') !== false) {
        $status = $stage . ' GREEN';
        break;
    }
    if (stripos($content, $stage) !== false && stripos($content, 'YELLOW') !== false) {
        $status = $stage . ' YELLOW';
        break;
    }
    if (stripos($content, $stage) !== false && stripos($content, 'RED') !== false) {
        $status = $stage . ' RED';
        break;
    }
}

echo "Current Stage: $status\n\n";

$allowedActions = [
    'V1 GREEN'  => ['new V2 components', 'new V3 components', 'docs/planning', 'labs if not production path'],
    'V1 YELLOW' => ['repair V1', 'docs/planning', 'limited labs'],
    'V1 RED'    => ['repair V1 only', 'docs/planning'],
    'V2 GREEN'  => ['new V3 components', 'docs/planning'],
    'V2 YELLOW' => ['repair V2', 'docs/planning'],
    'V2 RED'    => ['repair V2', 'V1 repair'],
    'V3 GREEN'  => ['docs/planning'],
    'V3 any'    => ['V3 repair', 'docs/planning'],
];

$allowed   = $allowedActions[$status] ?? ['docs/planning only (unknown stage)'];
$forbidden = [
    'V1 RED' => 'new V2/V3 production components',
    'V2 RED' => 'new V3 components',
];

echo "Allowed: " . implode(', ', $allowed) . "\n";

if (isset($forbidden[$status])) {
    echo "FORBIDDEN: {$forbidden[$status]}\n";
}

echo "\nRun: php tooling/governance/check-stage-lock.php\n";

exit(0);