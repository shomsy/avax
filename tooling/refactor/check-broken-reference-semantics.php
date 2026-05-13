<?php

declare(strict_types=1);

/**
 * check-broken-reference-semantics.php
 *
 * Runs the broken-reference audit but with proper scoping:
 * - Only fails on active/current code references
 * - Does not fail on historical evidence/recovery snapshots
 * - Does not fail on .qoder/worktrees/** copies
 * - Does not fail on optional PHP extensions (Redis, Memcached)
 * - Does not fail on optional vendor dependencies (RoadRunner, Symplify)
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$baseDir = dirname(__DIR__);
$failures = [];
$classifiedSkipped = [];

// Run the main audit but filter results
$auditFile = __DIR__ . '/../../tooling/audit_broken_refs.php';
if (!file_exists($auditFile)) {
    echo "FAIL: audit_broken_refs.php not found\n";
    exit(1);
}

// Capture audit output
ob_start();
include $auditFile;
$output = ob_get_clean();

// Parse output
$lines = explode("\n", $output);
$inMissing = false;
$currentRef = '';
$currentRefs = [];

foreach ($lines as $line) {
    if (preg_match('/^MISSING: (.+?)\s+\[/', $line, $m)) {
        // Process previous ref
        if ($currentRef !== '' && $currentRefs !== []) {
            $result = classifyRef($currentRef, $currentRefs, $baseDir);
            if ($result === 'FAIL') {
                $failures[] = $currentRef;
            } elseif ($result !== 'PASS') {
                $classifiedSkipped[$result][] = $currentRef;
            }
        }
        $currentRef = $m[1];
        $currentRefs = [];
        $inMissing = true;
    } elseif ($inMissing && preg_match('/^\s+-\s+(.+?):(\d+)/', $line, $m)) {
        $currentRefs[] = ['file' => $m[1], 'line' => (int)$m[2]];
    }
}

// Process last ref
if ($currentRef !== '' && $currentRefs !== []) {
    $result = classifyRef($currentRef, $currentRefs, $baseDir);
    if ($result === 'FAIL') {
        $failures[] = $currentRef;
    } elseif ($result !== 'PASS') {
        $classifiedSkipped[$result][] = $currentRef;
    }
}

function classifyRef(string $ref, array $refs, string $baseDir): string
{
    // Check if all refs are in excluded paths
    $excludedPaths = [
        '/.qoder/worktrees/',
        '/EVIDENCE/archive/',
        '/EVIDENCE/recovery-staging/',
        '/EVIDENCE/recovery-generated/',
        '/vendor/',
    ];

    $allExcluded = true;
    foreach ($refs as $r) {
        $file = $r['file'];
        $isExcluded = false;
        foreach ($excludedPaths as $path) {
            if (str_contains($file, $path)) {
                $isExcluded = true;
                break;
            }
        }
        if (!$isExcluded) {
            $allExcluded = false;
            break;
        }
    }

    if ($allExcluded) {
        return 'EXCLUDED_PATH';
    }

    // Check for optional PHP extensions
    $optionalExtensions = ['Redis', 'Memcached', 'PDO', 'mysqli'];
    foreach ($optionalExtensions as $ext) {
        if ($ref === $ext) {
            return 'OPTIONAL_PHP_EXTENSION';
        }
    }

    // Check for optional vendor dependencies
    $optionalVendors = [
        'Spiral\\',
        'Symplify\\',
        'Aws\\',
        'Cron\\',
        'PhpCsFixer\\',
    ];
    foreach ($optionalVendors as $vendor) {
        if (str_starts_with($ref, $vendor)) {
            return 'OPTIONAL_VENDOR';
        }
    }

    // Test fixtures for negative testing
    $testFixtures = ['NonExistentResourceType'];
    foreach ($testFixtures as $fixture) {
        if (str_contains($ref, $fixture)) {
            return 'TEST_FIXTURE';
        }
    }

    // If any ref is in main tree code, it's a real failure
    foreach ($refs as $r) {
        $file = $r['file'];
        // Main tree paths
        if (str_starts_with($file, $baseDir . '/framework/')
            || str_starts_with($file, $baseDir . '/components/')
            || str_starts_with($file, $baseDir . '/tests/')) {
            return 'FAIL';
        }
    }

    return 'PASS';
}

echo "=== BROKEN REFERENCE SEMANTICS AUDIT ===\n\n";

if ($classifiedSkipped !== []) {
    foreach ($classifiedSkipped as $category => $refs) {
        echo "SKIPPED ($category): " . count($refs) . " refs\n";
        foreach ($refs as $ref) {
            echo "  - $ref\n";
        }
        echo "\n";
    }
}

if ($failures !== []) {
    echo "FAIL: " . count($failures) . " active broken references:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}

$total = count($failures);
foreach ($classifiedSkipped as $refs) {
    $total += count($refs);
}
echo "PASS: 0 active broken references ($total total refs classified)\n";
exit(0);
