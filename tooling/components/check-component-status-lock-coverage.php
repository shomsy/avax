<?php

declare(strict_types=1);

/**
 * check-component-status-lock-coverage.php
 *
 * Verifies that every leaf component under components/ has a status entry.
 * Includes Application/Cache and all discovered components.
 */

$lockFile = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';
$componentsDir = __DIR__ . '/../../components';

// Discover all leaf components
$discovered = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iterator as $file) {
    if (!$file->isDir() || $file->getFilename() !== 'System') continue;

    $path = $file->getPath();
    $relative = str_replace($componentsDir . '/', '', $path);

    // Only leaf components (Area/Name pattern)
    $parts = explode('/', $relative);
    if (count($parts) === 2) {
        $discovered[] = $relative;
    }
}

sort($discovered);

// Parse status lock
$locked = [];
if (is_file($lockFile)) {
    $content = file_get_contents($lockFile);
    if ($content !== false) {
        foreach (explode("\n", $content) as $line) {
            if (!str_starts_with(trim($line), '|')) continue;
            $cells = array_map('trim', explode('|', trim($line, "|\t ")));
            if (count($cells) < 2 || $cells[0] === 'Component' || str_starts_with($cells[0], '-')) continue;
            // Only main table (4 columns)
            if (count($cells) < 4) continue;
            $locked[$cells[0]] = $cells[1];
        }
    }
}

$missing = [];
foreach ($discovered as $component) {
    if (!isset($locked[$component])) {
        $missing[] = $component;
    }
}

echo "COMPONENT STATUS LOCK COVERAGE\n";
echo "==============================\n\n";
echo "Discovered components: " . count($discovered) . "\n";
echo "Locked components: " . count($locked) . "\n";
echo "Missing from lock: " . count($missing) . "\n\n";

if ($missing !== []) {
    echo "MISSING:\n";
    foreach ($missing as $m) {
        echo "  - $m\n";
    }
    echo "\n";
    echo "FAIL: " . count($missing) . " components missing from status lock\n";
    exit(1);
}

echo "PASS: All " . count($discovered) . " discovered components have status lock entries\n";
exit(0);
