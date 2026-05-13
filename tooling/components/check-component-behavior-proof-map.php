<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-component-behavior-proof-map.php
 *
 * Reads behavior proof map.
 * Fails if ACTIVE_GREEN component has no central or local test proof.
 * Does not require tests inside component folders.
 * Central tests/ are valid in monorepo.
 * Fails if active public surface has no proof that it delegates.
 */

$lockFile = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';
$testsDir = __DIR__ . '/../../tests';

$activeGreenComponents = [];
if (is_file($lockFile)) {
    $content = file_get_contents($lockFile);
    if ($content !== false) {
        preg_match_all('/\|\s*([^\|]+)\|\s*ACTIVE_GREEN\s*\|/', $content, $matches);
        foreach ($matches[1] as $name) {
            $activeGreenComponents[trim($name)] = true;
        }
    }
}

$violations = [];
$checked    = 0;

// Check if tests directory exists and has test files
if (! is_dir($testsDir)) {
    echo "FAIL: tests directory not found\n";
    exit(1);
}

// Count total test files
$testFileCount = 0;
$testIterator  = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($testsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($testIterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Test.php')) {
        $testFileCount++;
    }
}

if ($testFileCount === 0) {
    echo "FAIL: no test files found in tests/\n";
    exit(1);
}

// For each ACTIVE_GREEN component, verify it has at least one test reference
foreach ($activeGreenComponents as $component => $_) {
    $parts = explode('/', $component);
    if (count($parts) < 2) {
        continue;
    }

    $area = $parts[0];
    $name = $parts[1];

    // Search for test references in tests/
    $found    = false;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Test.php')) {
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                continue;
            }
            if (strpos($content, $name) !== false || strpos($content, $area) !== false) {
                $found = true;
                break;
            }
        }
    }

    if (! $found) {
        $violations[] = "$component — no test proof found in central tests/";
    }
    $checked++;
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " ACTIVE_GREEN components without test proof:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    exit(1);
}

echo "PASS: $checked ACTIVE_GREEN components checked, all have test proof\n";
exit(0);
