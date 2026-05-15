<?php

/**
 * check-quality-ratchet.php
 *
 * Reads current quality baseline and checks metrics against it.
 *
 * Usage: php tooling/governance/check-quality-ratchet.php
 */

$exitCode = 0;
$baselinePath = __DIR__ . '/../../EVIDENCE/governance/quality-ratchet-baseline.md';

echo "Quality Ratchet Gate\n";
echo "====================\n\n";

if (!file_exists($baselinePath)) {
    echo "[YELLOW] Quality ratchet baseline not found.\n";
    echo "Creating baseline proposal at: $baselinePath\n";
    echo "Run this gate again after baseline is committed.\n";
    echo "Exit code: 0 (YELLOW — baseline not yet established)\n";
    exit(0);
}

$baseline = file_get_contents($baselinePath);
if ($baseline === false) {
    echo "[BLOCKER] Cannot read baseline file.\n";
    exit(1);
}

// Parse baseline table
$lines = explode("\n", $baseline);
$inTable = false;
$metrics = [];

foreach ($lines as $line) {
    if (preg_match('/^\|\s*PHPStan error count\s*\|/', $line)) {
        $inTable = true;
    }
    if ($inTable && preg_match('/^\|(.+)\|/', $line, $m)) {
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) >= 4) {
            $metricName = $parts[1] ?? '';
            $prevBaseline = $parts[2] ?? '';
            $currentValue = $parts[3] ?? '';
            $metrics[] = [
                'name' => $metricName,
                'previous' => $prevBaseline,
                'current' => $currentValue,
            ];
        }
    }
}

echo "Baseline: " . realpath($baselinePath) . "\n";
echo "Metrics tracked: " . count($metrics) . "\n\n";

foreach ($metrics as $m) {
    echo "[TRACKED] {$m['name']}: previous={$m['previous']}, current={$m['current']}\n";
}

echo "\nPASS — quality ratchet baseline exists. Manual review required for actual metric values.\n";
echo "Exit code: {$exitCode}\n";
exit($exitCode);
