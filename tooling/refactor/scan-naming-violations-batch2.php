<?php

declare(strict_types=1);

/**
 * Strict naming convention scanner — Batch 2: Checking for Shared/Base/Support suffixes.
 */
$baseDir = dirname(dirname(__DIR__));
$prohibitedSuffixes = ['Adapter', 'Proxy', 'Base', 'Shared', 'Support', 'Common', 'Utils', 'Helper', 'Manager'];

$violations = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($it as $info) {
    $path = $info->getPathname();
    $relPath = str_replace($baseDir . '/', '', $path);
    
    if (
        str_contains($path, '/vendor/') || 
        str_contains($path, '/tests/') || 
        str_contains($path, '/.git/') || 
        str_contains($path, '/.kilo/') ||
        str_contains($path, '/.agents/') ||
        str_contains($path, '/tooling/')
    ) {
        continue;
    }

    $name = $info->getBasename();
    $nameNoExt = $info->isFile() ? $info->getBasename('.php') : $name;

    foreach ($prohibitedSuffixes as $s) {
        if (str_ends_with($nameNoExt, $s) || str_starts_with($nameNoExt, $s)) {
            // Flag it
            $violations[] = ['path' => $relPath, 'type' => $info->isDir() ? 'Folder' : 'File', 'violation' => $s];
        }
    }
}

echo "=== NAMING VIOLATIONS BATCH 2 ===\n\n";
if (empty($violations)) {
    echo "No violations found.\n";
} else {
    foreach ($violations as $v) {
        echo "{$v['type']}: {$v['path']} -> {$v['violation']}\n";
    }
    echo "\nTotal: " . count($violations) . "\n";
}
