#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$componentsDir = $root.'/components';

if (! is_dir($componentsDir)) {
    echo "No components directory found. Skipping.\n";
    exit(0);
}

$allowedDefaultFolders = ['PublicSurface', 'Flows', 'Capabilities', 'Configuration', 'Foundation'];

$forbiddenTopLevelFolders = [
    'InternalSystem',
    'ExportedCapabilities',
    'Adapters',
    'Contracts',
    'Services',
    'Managers',
    'Helpers',
    'Utils',
    'Support',
    'Common',
    'Shared',
    'Domain',
    'Entities',
    'ValueObjects',
    'Aggregates',
    'Repositories',
    'Events',
    'Handlers',
    'Processors',
    'Commands',
    'Queries',
    'CQRS',
    'EventSourcing',
    'Sagas',
    'Policies',
    'Specifications',
    'Diagnostics',
    'Tests',
    'Docs',
    'UseCases',
];

$violations = [];
$warnings = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isDir()) {
        continue;
    }

    $path = $file->getPathname();
    $parts = explode('/', str_replace($componentsDir.'/', '', $path));

    if (count($parts) < 2) {
        continue;
    }

    $componentPath = $path.'/System';

    if (! is_dir($componentPath)) {
        continue;
    }

    $systemContents = scandir($componentPath);
    if ($systemContents === false) {
        continue;
    }

    $systemFolders = array_filter($systemContents, function ($item) use ($componentPath) {
        return $item !== '.' && $item !== '..' && is_dir($componentPath.'/'.$item);
    });

    foreach ($systemFolders as $folder) {
        if (! in_array($folder, $allowedDefaultFolders, true)) {
            $forbiddenTopLevelFolders[] = $folder;
            $violations[] = [
                'component' => implode('/', array_slice($parts, 0, 2)),
                'folder' => 'System/'.$folder,
                'type' => 'forbidden_system_folder',
            ];
        }
    }

    $hasCapabilities = in_array('Capabilities', $systemFolders, true);
    $hasFlows = in_array('Flows', $systemFolders, true);
    $hasPublicSurface = in_array('PublicSurface', $systemFolders, true);

    if (! $hasCapabilities && ! $hasFlows && count($systemFolders) > 0) {
        $warnings[] = [
            'component' => implode('/', array_slice($parts, 0, 2)),
            'issue' => 'no Capabilities folder - may not be a real component',
        ];
    }

    if ($hasPublicSurface && ! $hasCapabilities && ! $hasFlows) {
        $warnings[] = [
            'component' => implode('/', array_slice($parts, 0, 2)),
            'issue' => 'PublicSurface without internal behavior - may be a facade without an engine',
        ];
    }
}

if (empty($violations) && empty($warnings)) {
    echo "GREEN: All components follow canonical shape.\n";
    exit(0);
}

echo "=== Component Canonical Shape Check ===\n\n";

if (! empty($violations)) {
    echo "BLOCKER VIOLATIONS:\n";
    foreach ($violations as $v) {
        echo "  - {$v['component']}: {$v['folder']} ({$v['type']})\n";
    }
    echo "\n";
}

if (! empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $w) {
        echo "  - {$w['component']}: {$w['issue']}\n";
    }
    echo "\n";
}

echo "Command: php tooling/refactor/check-component-canonical-shape.php\n";

exit(! empty($violations) ? 1 : 0);
