<?php

/**
 * check-serviceprovider-governance-consistency.php
 *
 * Scans .agents/how-to/*.md for ServiceProvider wording consistency.
 *
 * Usage: php tooling/governance/check-serviceprovider-governance-consistency.php
 */

$exitCode = 0;
$howToDir = __DIR__ . '/../../.agents/how-to';
$findings = [];
$scanned = 0;

$files = glob($howToDir . '/how-to-*.md');
sort($files);

// Broad/forbidden ServiceProvider patterns
$broadPatterns = [
    '/[Ee]very\s+ACTIVE\s+component\s+MUST\s+have\s+exactly\s+one\s+ServiceProvider(?!\s+with\s+runtime)/i',
    '/[Aa]ll\s+active\s+components\s+require\s+a\s+ServiceProvider/i',
    '/[Ee]very\s+component\s+(SHOULD|MUST)\s+have\s+a\s+ServiceProvider/i',
];

// Canonical pattern that should appear in DI doc
$canonicalPattern = '/Every ACTIVE production component with runtime behavior, public API, dependencies, replaceable services, state, I\/O, configuration, or lifecycle ownership MUST have exactly one real ServiceProvider\./';

$canonicalFound = false;

foreach ($files as $file) {
    $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file);
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }
    $scanned++;

    // Check for canonical rule
    if (preg_match($canonicalPattern, $content)) {
        if (str_contains($file, 'dependency-injection')) {
            $canonicalFound = true;
        }
    }

    // Check for broad patterns
    foreach ($broadPatterns as $pattern) {
        if (preg_match($pattern, $content, $m)) {
            $findings[] = [
                'file' => $relative,
                'type' => 'broad_serviceprovider_wording',
                'severity' => 'HIGH',
                'message' => "Broad ServiceProvider wording found (missing ACTIVE production qualification): {$m[0]}",
            ];
        }
    }
}

// Report
echo "ServiceProvider Governance Consistency Gate\n";
echo "============================================\n\n";
echo "Scanned files: {$scanned}\n";
echo "Canonical rule in DI doc: " . ($canonicalFound ? 'YES' : 'MISSING') . "\n";
echo "Violations: " . count($findings) . "\n\n";

if (!$canonicalFound) {
    echo "[BLOCKER] Canonical ServiceProvider rule not found in how-to-dependency-injection.md\n";
    $exitCode = 1;
}

if (count($findings) > 0) {
    foreach ($findings as $f) {
        echo "[{$f['severity']}] {$f['file']} — {$f['message']}\n";
    }
    $exitCode = 1;
} else {
    echo "PASS — all documents use correct ServiceProvider wording.\n";
}

echo "Exit code: {$exitCode}\n";
exit($exitCode);
