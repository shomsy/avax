<?php

/**
 * check-canonical-terms.php
 *
 * Verifies the canonical terms registry exists and has required content.
 *
 * Usage: php tooling/governance/check-canonical-terms.php
 */

$exitCode = 0;
$registryPath = __DIR__ . '/../../docs/governance/canonical-terms.md';

echo "Canonical Terms Gate\n";
echo "====================\n\n";

// Check registry exists
if (!file_exists($registryPath)) {
    echo "[BLOCKER] Canonical terms registry not found at docs/governance/canonical-terms.md\n";
    exit(1);
}

$content = file_get_contents($registryPath);

// Check required columns
$requiredColumns = ['canonical term', 'meaning', 'allowed aliases', 'forbidden aliases'];
foreach ($requiredColumns as $col) {
    if (!preg_match('/' . preg_quote($col, '/') . '/i', $content)) {
        $findings[] = "Missing required column: '$col'";
    }
}

// Check initial critical terms
$criticalTerms = ['Response', 'CreateHttpResponse', 'ServiceProvider', 'Runtime', 'EventEmitter', 'FailureBoundary'];
foreach ($criticalTerms as $term) {
    if (!preg_match('/\|\s*' . preg_quote($term, '/') . '\s*\|/i', $content)) {
        $findings[] = "Missing critical term: '$term'";
    }
}

echo "Registry: " . realpath($registryPath) . "\n";
echo "File exists: YES\n";
echo "File size: " . filesize($registryPath) . " bytes\n";

if (!empty($findings)) {
    echo "\nIssues found:\n";
    foreach ($findings as $f) {
        echo "- $f\n";
    }
    $exitCode = 1;
} else {
    echo "\nPASS — all required columns and critical terms present.\n";
}

echo "Exit code: {$exitCode}\n";
exit($exitCode);
