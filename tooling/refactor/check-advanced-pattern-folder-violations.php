#!/usr/bin/env php
<?php

declare(strict_types=1);

$root          = dirname(__DIR__, 2);
$componentsDir = $root . '/components';

if (! is_dir($componentsDir)) {
    echo "No components directory found. Skipping.\n";
    exit(0);
}

$forbiddenFolders = [
    'Commands',
    'Queries',
    'Handlers',
    'Adapters',
    'Sagas',
    'Policies',
    'Specifications',
    'Projectors',
    'Processors',
    'Services',
    'Events',
    'CQRS',
    'EventSourcing',
    'UseCases',
    'Strategies',
    'Factories',
    'Builders',
    'Decorators',
    'Composites',
    'Observers',
];

$allowlistFile = $root . '/.agents/governance-allowlist/advanced-pattern-folder-exceptions.json';
$allowlist     = [];

if (file_exists($allowlistFile)) {
    $json      = file_get_contents($allowlistFile);
    $data      = json_decode($json, true) ?? [];
    $allowlist = $data['exceptions'] ?? [];
}

$allowlistPaths = [];
foreach ($allowlist as $entry) {
    $allowlistPaths[$entry['path']] = true;
}

$violations = [];
$warnings   = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isDir()) {
        continue;
    }

    $path         = $file->getPathname();
    $relativePath = str_replace($componentsDir . '/', '', $path);

    foreach ($forbiddenFolders as $forbidden) {
        if (strpos($path, '/' . $forbidden) !== false || strpos($path, '/' . $forbidden . '/') !== false) {
            if (! isset($allowlistPaths[$relativePath])) {
                $violations[] = [
                    'path'   => $relativePath,
                    'folder' => $forbidden,
                    'reason' => 'forbidden pattern folder',
                ];
            }
        }
    }
}

if (empty($violations)) {
    echo "GREEN: No forbidden pattern folders found.\n";
    exit(0);
}

echo "=== Advanced Pattern Folder Violations ===\n\n";

echo "FORBIDDEN FOLDERS:\n";
foreach ($violations as $v) {
    echo "  - {$v['path']} ({$v['folder']})\n";
}
echo "\n";
echo "Run: php tooling/refactor/check-advanced-pattern-folder-violations.php\n";

exit(1);