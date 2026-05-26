#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-root-evidence-hygiene.php
 *
 * Enforces the root EVIDENCE/ dashboard boundary:
 * - No subdirectories allowed
 * - Max 10 files
 * - Max 10MB total size
 * - No archive-like folders
 * - No .install-archive (runtime noise)
 * - Root files must be known legacy anchors or timestamp-prefixed
 *
 * Exit 0 = GREEN, Exit 1 = FAIL
 */

$root = dirname(__DIR__, 2);
$evidenceDir = $root . '/EVIDENCE';

if (! is_dir($evidenceDir)) {
    echo "RED: EVIDENCE/ directory does not exist.\n";
    exit(1);
}

$failures = [];
$warnings = [];

// --- Rule 1: No subdirectories ---
$items = scandir($evidenceDir);
if ($items === false) {
    echo "RED: Cannot scan EVIDENCE/ directory.\n";
    exit(1);
}

$dirs = [];
$files = [];
$totalSize = 0;

foreach ($items as $item) {
    if ($item === '.' || $item === '..') {
        continue;
    }

    $path = $evidenceDir . '/' . $item;

    if (is_dir($path)) {
        $dirs[] = $item;
    }

    if (is_file($path)) {
        $files[] = $item;
        $totalSize += filesize($path) ?: 0;
    }
}

if ($dirs !== []) {
    $failures[] = 'Subdirectories found (not allowed): ' . implode(', ', $dirs);
}

// --- Rule 2: Max 10 files ---
$fileCount = count($files);
if ($fileCount > 10) {
    $failures[] = "Too many files: {$fileCount} (max 10)";
}

// --- Rule 3: Max 10MB total ---
$maxBytes = 10 * 1024 * 1024; // 10MB
if ($totalSize > $maxBytes) {
    $mb = round($totalSize / 1024 / 1024, 2);
    $failures[] = "Total size exceeds 10MB: {$mb}MB";
}

// --- Rule 4: No archive-like folder names ---
$archivePatterns = [
    'v1', 'v2', 'v3', 'v4', 'v5',
    'v5.', 'recovery', 'archive', 'backup',
    'fix-this', 'cleanup', 'reviews', 'templates',
    'master-plan', 'truth-reconciliation',
];

foreach ($dirs as $dir) {
    $lower = strtolower($dir);
    foreach ($archivePatterns as $pattern) {
        if (str_starts_with($lower, $pattern) || str_contains($lower, $pattern)) {
            $failures[] = "Archive-like directory found: {$dir}";
            break;
        }
    }
}

// --- Rule 5: New root files must be timestamp-prefixed ---
$legacyRootFiles = [
    'README.md',
    'EXECUTION.md',
    'route-cache-plan.md',
];

foreach ($files as $file) {
    if (in_array($file, $legacyRootFiles, true)) {
        continue;
    }

    if (preg_match('/^\d{4}-\d{2}-\d{2}-\d{2}-\d{2}-\d{2}-[a-z0-9][a-z0-9-]*\.md$/', $file) === 1) {
        continue;
    }

    $failures[] = "Root evidence file is not a legacy anchor or timestamp-prefixed: {$file}";
}

// Also check files for obvious runtime noise
$noisePatterns = [
    'EVIDENCE.txt',     // raw dump
    '.install-archive', // runtime noise
    'snapshot',
    'cache',
];

// Known legitimate files that match noise patterns but are valid evidence
$allowedFiles = [
    'route-cache-plan.md', // V4-04 DX route cache architecture plan
];

foreach (array_merge($dirs, $files) as $name) {
    if (in_array($name, $allowedFiles, true)) {
        continue;
    }
    foreach ($noisePatterns as $pattern) {
        if (str_contains(strtolower($name), strtolower($pattern))) {
            $failures[] = "Runtime noise detected: {$name}";
            break;
        }
    }
}

// --- Rule 6: .install-archive specifically forbidden ---
if (is_dir($evidenceDir . '/.install-archive') || is_dir($evidenceDir . '/install-archive')) {
    $failures[] = '.install-archive found (runtime noise, must be deleted or moved to archive)';
}

// --- Report ---
echo "Root Evidence Hygiene Check\n";
echo "===========================\n\n";

echo "Files: {$fileCount}\n";
echo "Directories: " . count($dirs) . "\n";
echo "Total size: " . round($totalSize / 1024, 1) . "KB\n\n";

if ($files !== []) {
    echo "Files present:\n";
    foreach ($files as $f) {
        $size = filesize($evidenceDir . '/' . $f) ?: 0;
        echo "  - {$f} (" . round($size / 1024, 1) . "KB)\n";
    }
    echo "\n";
}

if ($failures !== []) {
    echo "FAILURES:\n";
    foreach ($failures as $i => $msg) {
        echo "  " . ($i + 1) . ". {$msg}\n";
    }
    echo "\nRED: Root evidence hygiene FAILED.\n";
    exit(1);
}

if ($warnings !== []) {
    echo "WARNINGS:\n";
    foreach ($warnings as $msg) {
        echo "  - {$msg}\n";
    }
    echo "\n";
}

echo "GREEN: Root evidence hygiene PASSED.\n";
exit(0);
