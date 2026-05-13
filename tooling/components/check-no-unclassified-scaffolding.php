<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-no-unclassified-scaffolding.php
 *
 * Scans components/.../System/... for empty PublicSurface, Flows, Capabilities,
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

$systems       = glob($componentsDir . '/*/System', GLOB_ONLYDIR);
$nestedSystems = glob($componentsDir . '/*/*/System', GLOB_ONLYDIR);

if ($systems === false) {
    $systems = [];
}

if ($nestedSystems === false) {
    $nestedSystems = [];
}

foreach ([...$systems, ...$nestedSystems] as $systemDir) {
    $relativeSystem = str_replace($componentsDir . '/', '', $systemDir);

    if (str_contains($relativeSystem, '/docs/') || str_contains($relativeSystem, '/tests/')) {
        continue;
    }

    $fullKey = str_replace('/System', '', $relativeSystem);

    foreach ($canonicalFolders as $folder) {
        $folderPath = $systemDir . '/' . $folder;
        if (! is_dir($folderPath)) {
            continue;
        }

        $phpFiles = phpFilesUnder($folderPath);
        if ($phpFiles === []) {
            // Empty folder
            if (! isset($classifiedComponents[$fullKey])) {
                $violations[] = "$fullKey/$folder is empty and not classified as SCAFFOLD/ROADMAP/EVIDENCE_ONLY";
            }
            $checked++;
        }
    }
}

/**
 * @return list<string>
 */
function phpFilesUnder(string $folderPath) : array
{
    $phpFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $phpFiles[] = $file->getPathname();
        }
    }

    return $phpFiles;
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
