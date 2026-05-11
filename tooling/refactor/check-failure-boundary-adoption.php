#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-failure-boundary-adoption.php — Gate: Verifies FailureBoundary has real adoption.
 *
 * Exit code 0: Adoption checks pass.
 * Exit code 1: One or more adoption checks fail.
 *
 * Usage:
 * php tooling/refactor/check-failure-boundary-adoption.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Avax\Framework\System\Capabilities\FailureBoundary\Foundation\CompiledPolicyCache;

$failures = [];
$passes = [];

// Check 1: FailureBoundary owner directory exists
$fbDir = __DIR__ . '/../../framework/System/Capabilities/FailureBoundary';
if (is_dir($fbDir)) {
    $passes[] = 'FailureBoundary owner directory exists';
} else {
    $failures[] = 'FailureBoundary owner directory missing';
}

// Check 2: At least one PHP file outside tests/definitions uses a FailureBoundary attribute
$attributePatterns = [
    '#[OnFailure',
    '#[ReportFailure',
    '#[Retry',
    '#[Fallback',
    '#[DeadLetter',
    '#[Rethrow',
    '#[RecoverWith',
    '#[Timeout',
];

$scanDirs = [
    __DIR__ . '/../../framework',
    __DIR__ . '/../../components',
    __DIR__ . '/../../examples',
];

$foundRealUsage = false;
foreach ($scanDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $path = $file->getPathname();
        // Skip attribute definitions themselves and tests
        if (str_contains($path, 'Foundation/Attributes/')) {
            continue;
        }
        if (str_contains($path, 'tests/')) {
            continue;
        }
        $content = file_get_contents($path);
        foreach ($attributePatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                $foundRealUsage = true;
                $passes[] = "Real attribute usage found: {$path}";
                break 2;
            }
        }
    }
}
if (!$foundRealUsage) {
    $failures[] = 'No real attribute usage found outside tests/definitions';
}

// Check 3: No HOT_PATH_VIOLATION reflection in FailureBoundary runtime path
$runtimeFiles = [
    'Flows/RunProtectedAction/RunProtectedAction.php',
    'Flows/ResolveFailurePolicy/ResolveFailurePolicy.php',
    'Capabilities/RunFailurePipeline/RunFailurePipeline.php',
    'Capabilities/ClassifyFailure/ClassifyFailure.php',
    'Capabilities/ResolveFailurePolicy/ResolveFailurePolicy.php',
    'Capabilities/ReportFailure/ReportFailure.php',
    'Capabilities/RetryFailedAction/RetryFailedAction.php',
    'Capabilities/RunFallbackAction/RunFallbackAction.php',
    'Capabilities/MapFailureToResult/MapFailureToResult.php',
    'Capabilities/SendFailureToDeadLetter/SendFailureToDeadLetter.php',
    'Capabilities/CleanupAfterFailure/CleanupAfterFailure.php',
    'Foundation/CompiledPolicyCache.php',
];

$hotPathReflection = false;
foreach ($runtimeFiles as $relPath) {
    $fullPath = $fbDir . '/' . $relPath;
    if (!file_exists($fullPath)) {
        continue;
    }
    $content = file_get_contents($fullPath);
    if (preg_match('/new\s+\\\\?Reflection(Class|Method|Attribute)/', $content)) {
        $hotPathReflection = true;
        $failures[] = "HOT_PATH_VIOLATION: Reflection found in {$relPath}";
    }
}
if (!$hotPathReflection) {
    $passes[] = 'No HOT_PATH_VIOLATION reflection in runtime path';
}

// Check 4: Evidence directory exists with proof files
$evidenceDir = __DIR__ . '/../../EVIDENCE/failure-boundary';
$requiredEvidence = [
    'failure-boundary-implementation-inventory.md',
    'ownership-decision.md',
    'try-catch-inventory.md',
    'http-integration-proof.md',
    'compiled-failure-policy-proof.md',
    'dogfooding-proof.md',
    'adoption-scan.md',
];

foreach ($requiredEvidence as $file) {
    if (file_exists($evidenceDir . '/' . $file)) {
        $passes[] = "Evidence exists: {$file}";
    } else {
        $failures[] = "Missing evidence: {$file}";
    }
}

// Report results
echo "=== FailureBoundary Adoption Gate ===\n\n";

if (!empty($passes)) {
    foreach ($passes as $pass) {
        echo "  GREEN: {$pass}\n";
    }
}

if (!empty($failures)) {
    foreach ($failures as $failure) {
        echo "  RED: {$failure}\n";
    }
}

echo "\n";
echo count($passes) . " passed, " . count($failures) . " failed\n";

if (!empty($failures)) {
    exit(1);
}

echo "\nGREEN: FailureBoundary adoption checks pass.\n";
exit(0);
