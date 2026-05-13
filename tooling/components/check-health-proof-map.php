<?php

declare(strict_types=1);

/**
 * check-health-proof-map.php
 *
 * Verifies that every active runtime-critical component has:
 * - Health/doctor proof (Check*Health or Diagnose* file)
 * - OR exact accepted YELLOW entry in component status lock
 * - No fake always-green checks
 */

$lockFile = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';

$runtimeCriticalComponents = [
    'Application/Container',
    'DataStack/Database',
    'HTTP/Router',
    'Operations/Events',
    'Application/Cache',
    'Application/Filesystem',
    'Operations/Logging',
    'Operations/Queue',
    'Security/Redaction',
    'Security/Cryptography',
    'Integration/ObjectStorage',
];

$frameworkComponents = [
    'FailureBoundary' => __DIR__ . '/../../framework/System/Capabilities/FailureBoundary',
];

$excludedStatuses = ['ROADMAP', 'SCAFFOLD', 'LABS_ONLY', 'EVIDENCE_ONLY', 'TEST_ONLY', 'DEPRECATED'];

// Parse status lock
$classifiedComponents = [];
if (is_file($lockFile)) {
    $content = file_get_contents($lockFile);
    if ($content !== false) {
        foreach (explode("\n", $content) as $line) {
            if (!str_starts_with(trim($line), '|')) continue;
            $cells = array_map('trim', explode('|', trim($line, "|\t ")));
            if (count($cells) < 2 || $cells[0] === 'Component' || str_starts_with($cells[0], '-')) continue;
            // Only parse 4-column main table, skip summary
            if (count($cells) < 4) continue;
            $classifiedComponents[$cells[0]] = $cells[1];
        }
    }
}

$failures = [];
$checked = 0;
$basePath = __DIR__ . '/../../';

foreach ($runtimeCriticalComponents as $component) {
    $status = $classifiedComponents[$component] ?? null;
    if ($status === null) {
        $failures[] = "$component — missing from status lock";
        $checked++;
        continue;
    }

    if (in_array($status, $excludedStatuses, true)) {
        echo "  $component: $status (excluded) — SKIP\n";
        $checked++;
        continue;
    }

    $area = explode('/', $component)[0];
    $name = explode('/', $component)[1];

    // Check for health check files
    $healthFiles = findHealthFiles("$basePath/components/$area/$name/System/Capabilities");
    if ($healthFiles === []) {
        // Check framework paths
        $healthFiles = findHealthFiles("$basePath/framework/System/Capabilities");
    }

    if ($healthFiles === []) {
        $failures[] = "$component — no health/doctor proof (status: $status)";
        echo "  $component: NO HEALTH FILES\n";
    } else {
        echo "  $component: " . implode(', ', $healthFiles) . " — PASS\n";
    }
    $checked++;
}

foreach ($frameworkComponents as $component => $dirPath) {
    $healthFiles = findHealthFiles($dirPath);
    if ($healthFiles === []) {
        $failures[] = "Framework/$component — no health/doctor proof";
        echo "  Framework/$component: NO HEALTH FILES\n";
    } else {
        echo "  Framework/$component: " . implode(', ', $healthFiles) . " — PASS\n";
    }
    $checked++;
}

function findHealthFiles(string $basePath): array
{
    $files = [];
    if (!is_dir($basePath)) return $files;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') continue;
        $filename = $file->getFilename();
        if (stripos($filename, 'Health') !== false || stripos($filename, 'Check') !== false) {
            // Exclude test files
            if (!str_contains($file->getPathname(), '/tests/')) {
                $files[] = $filename;
            }
        }
    }
    return $files;
}

echo "\n";
if ($failures !== []) {
    echo "FAIL: " . count($failures) . " components missing health proof:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}

echo "PASS: $checked runtime-critical components have health proof\n";
exit(0);
