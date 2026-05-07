<?php

declare(strict_types=1);

/**
 * Audit all components against canonical component shape.
 *
 * Checks:
 * - System/ directory exists
 * - Canonical subfolders (PublicSurface, Flows, Capabilities, Configuration, Foundation)
 * - Forbidden folders inside System/
 * - Extra non-canonical folders
 */

$canonicalFolders = ['PublicSurface', 'Flows', 'Capabilities', 'Configuration', 'Foundation'];
$forbiddenFolders = [
    'Services', 'Helpers', 'Utils', 'Common', 'Shared', 'Managers', 'Core', 'Support',
    'Adapters', 'Contracts', 'Handlers', 'Processors', 'Commands', 'Queries', 'Domain',
    'Entities', 'ValueObjects', 'Aggregates', 'Repositories', 'Events', 'CQRS',
    'EventSourcing', 'Sagas', 'Policies', 'Specifications', 'Diagnostics', 'Tests',
    'Docs', 'InternalSystem', 'ExportedCapabilities',
];

$componentDir = dirname(__DIR__, 2) . '/components';
$issues       = [];
$summary      = [
    'total'              => 0,
    'has_system'         => 0,
    'missing_system'     => 0,
    'has_public_surface' => 0,
    'has_flows'          => 0,
    'has_capabilities'   => 0,
    'has_configuration'  => 0,
    'has_foundation'     => 0,
    'has_forbidden'      => 0,
    'has_extra'          => 0,
    'complete'           => 0,
];

// Iterate over components/Area/Component/
$areas = scandir($componentDir);
foreach ($areas as $area) {
    if ($area === '.' || $area === '..') {
        continue;
    }

    $areaPath = $componentDir . '/' . $area;
    if (! is_dir($areaPath)) {
        continue;
    }

    $components = scandir($areaPath);
    foreach ($components as $component) {
        if ($component === '.' || $component === '..') {
            continue;
        }

        $componentPath = $areaPath . '/' . $component;
        if (! is_dir($componentPath)) {
            continue;
        }

        $summary['total']++;
        $key        = "$area/$component";
        $systemPath = $componentPath . '/System';

        if (! is_dir($systemPath)) {
            $summary['missing_system']++;
            $issues[] = "[$key] MISSING System/ directory";
            continue;
        }

        $summary['has_system']++;

        // Check canonical folders
        $systemFolders  = scandir($systemPath);
        $presentFolders = [];
        $extraFolders   = [];
        $forbiddenFound = [];

        foreach ($systemFolders as $folder) {
            if ($folder === '.' || $folder === '..') {
                continue;
            }

            $folderPath = $systemPath . '/' . $folder;
            if (! is_dir($folderPath)) {
                continue;
            }

            if (in_array($folder, $canonicalFolders, true)) {
                $presentFolders[] = $folder;
            } elseif (in_array($folder, $forbiddenFolders, true)) {
                $forbiddenFound[] = $folder;
            } else {
                $extraFolders[] = $folder;
            }
        }

        // Track presence
        if (in_array('PublicSurface', $presentFolders, true)) $summary['has_public_surface']++;
        if (in_array('Flows', $presentFolders, true)) $summary['has_flows']++;
        if (in_array('Capabilities', $presentFolders, true)) $summary['has_capabilities']++;
        if (in_array('Configuration', $presentFolders, true)) $summary['has_configuration']++;
        if (in_array('Foundation', $presentFolders, true)) $summary['has_foundation']++;

        // Check forbidden
        if (! empty($forbiddenFound)) {
            $summary['has_forbidden']++;
            $issues[] = "[$key] FORBIDDEN folders in System/: " . implode(', ', $forbiddenFound);
        }

        // Check extra non-canonical
        if (! empty($extraFolders)) {
            $summary['has_extra']++;
            $issues[] = "[$key] EXTRA non-canonical folders in System/: " . implode(', ', $extraFolders);
        }

        // Check completeness (System + Capabilities required, others conditional)
        $isComplete = in_array('PublicSurface', $presentFolders, true)
            && in_array('Flows', $presentFolders, true)
            && in_array('Capabilities', $presentFolders, true)
            && in_array('Configuration', $presentFolders, true)
            && in_array('Foundation', $presentFolders, true)
            && empty($forbiddenFound);

        if ($isComplete) {
            $summary['complete']++;
        } else {
            $missing = array_diff($canonicalFolders, $presentFolders);
            if (! empty($missing)) {
                $issues[] = "[$key] MISSING canonical folders: " . implode(', ', $missing);
            }
        }

        // Count PHP files
        $phpCount = countPHPFiles($systemPath);
        if ($phpCount === 0) {
            $issues[] = "[$key] NO PHP files in System/";
        }
    }
}

echo "=== Component Shape Audit ===\n\n";
echo "Total components: {$summary['total']}\n";
echo "Has System/: {$summary['has_system']}\n";
echo "Missing System/: {$summary['missing_system']}\n";
echo "Has PublicSurface/: {$summary['has_public_surface']}\n";
echo "Has Flows/: {$summary['has_flows']}\n";
echo "Has Capabilities/: {$summary['has_capabilities']}\n";
echo "Has Configuration/: {$summary['has_configuration']}\n";
echo "Has Foundation/: {$summary['has_foundation']}\n";
echo "Has forbidden folders: {$summary['has_forbidden']}\n";
echo "Has extra non-canonical folders: {$summary['has_extra']}\n";
echo "Complete (all canonical + no forbidden): {$summary['complete']}\n\n";

if (! empty($issues)) {
    echo "=== Issues (" . count($issues) . ") ===\n\n";
    foreach ($issues as $issue) {
        echo "- $issue\n";
    }
} else {
    echo "No issues found.\n";
}

function countPHPFiles(string $dir) : int
{
    $count    = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $count++;
        }
    }

    return $count;
}
