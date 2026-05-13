<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-no-unclassified-scaffolding.php
 *
 * Scans components/**/
System/** for empty PublicSurface, Flows, Capabilities,
 * Configuration, Foundation folders. Fails if empty scaffold exists without
 * ROADMAP/SCAFFOLD/EVIDENCE_ONLY status.
 */

$componentsDir = __DIR__ . '/../../components';
$lockFile      = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';

$classifiedComponents = [];
if (is_file($lockFile)) {
    $content = file_get_contents($lockFile);
    if ($content !== false) {
        // Extract component names with SCAFFOLD/ROADMAP/EVIDENCE_ONLY status
        preg_match_all('/\|\s*([^\|]+)\|\s*(SCAFFOLD|ROADMAP|EVIDENCE_ONLY)\s*\|/', $content, $matches);
        foreach ($matches[1] as $name) {
            $classifiedComponents[trim($name)] = true;
        }
    }
}

$canonicalFolders = ['PublicSurface', 'Flows', 'Capabilities', 'Configuration', 'Foundation'];
$violations       = [];
$checked          = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if (! $file->isDir() || $file->getFilename() !== 'System') {
        continue;
    }

    $systemDir     = $file->getPathname();
    $componentDir  = dirname($systemDir);
    $componentName = basename($componentDir);
    $areaName      = basename(dirname($componentDir));
    $fullKey       = "$areaName/$componentName";

    foreach ($canonicalFolders as $folder) {
        $folderPath = $systemDir . '/' . $folder;
        if (! is_dir($folderPath)) {
            continue;
        }

        $phpFiles = glob($folderPath . '/*.php');
        if ($phpFiles === []) {
            // Empty folder
            if (! isset($classifiedComponents[$fullKey])) {
                $violations[] = "$fullKey/$folder is empty and not classified as SCAFFOLD/ROADMAP/EVIDENCE_ONLY";
            }
            $checked++;
        }
    }
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " unclassified scaffold folders found:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    exit(1);
}

echo "PASS: $checked empty folders checked, all classified or non-empty\n";
exit(0);
