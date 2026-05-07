<?php

declare(strict_types=1);

/**
 * Normalizes Avax components to the canonical shape: components/<Area>/<Component>/System/*
 * Removes the double "System/System" nesting and updates namespaces.
 */

$root          = __DIR__ . '/../../';
$componentsDir = $root . 'components';

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$doubleSystems = [];

foreach ($it as $file) {
    if ($file->isDir() && basename($file->getPathname()) === 'System') {
        $parent = dirname($file->getPathname());
        if (basename($parent) === 'System') {
            $doubleSystems[] = $file->getPathname();
        }
    }
}

// Filter to get unique deepest System/System paths to avoid nested issues
usort($doubleSystems, fn ($a, $b) => strlen($b) <=> strlen($a));

foreach ($doubleSystems as $doubleSystem) {
    $parentSystem = dirname($doubleSystem);
    echo "Processing $doubleSystem -> $parentSystem\n";

    $items = scandir($doubleSystem);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;

        $oldPath = $doubleSystem . '/' . $item;
        $newPath = $parentSystem . '/' . $item;

        if (file_exists($newPath)) {
            echo "  WARNING: Destination already exists: $newPath. Merging...\n";
            // If it's a directory, we should move its contents
            if (is_dir($oldPath)) {
                $subItems = scandir($oldPath);
                foreach ($subItems as $subItem) {
                    if ($subItem === '.' || $subItem === '..') continue;
                    rename($oldPath . '/' . $subItem, $newPath . '/' . $subItem);
                }
                rmdir($oldPath);
            } else {
                rename($oldPath, $newPath);
            }
        } else {
            rename($oldPath, $newPath);
        }
    }

    rmdir($doubleSystem);
}

// Now bulk update namespaces in all php files
echo "Updating namespaces...\n";
$allFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($allFiles as $file) {
    if ($file->getExtension() === 'php') {
        $content  = file_get_contents($file->getPathname());
        $original = $content;

        // Replace \System with \System
        $content = str_replace('System\\System', 'System', $content);

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            echo "  Updated: " . $file->getPathname() . "\n";
        }
    }
}

echo "Cleanup complete.\n";
