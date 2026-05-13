<?php

declare(strict_types=1);

namespace Avax\Tooling\Components;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * check-hollow-public-surfaces.php
 *
 * Fails on active production PublicSurface methods that only:
 * - return new self()
 * - return 0
 * - return null
 * - do nothing
 * - have TODO-only behavior
 *
 * Allows scaffold/roadmap components if classified.
 */

$componentsDir = __DIR__ . '/../../components';
$lockFile      = __DIR__ . '/../../EVIDENCE/components/component-status-lock.md';

$classifiedComponents = [];
if (is_file($lockFile)) {
    $content = file_get_contents($lockFile);
    if ($content !== false) {
        preg_match_all('/\|\s*([^\|]+)\|\s*(SCAFFOLD|ROADMAP|EVIDENCE_ONLY|TEST_ONLY|LABS_ONLY)\s*\|/', $content, $matches);
        foreach ($matches[1] as $name) {
            $classifiedComponents[trim($name)] = true;
        }
    }
}

$hollowPatterns = [
    '/return\s+new\s+self\s*\(/',
    '/return\s+0\s*;/',
    '/return\s+null\s*;/',
    '/return\s+\[\s*\]\s*;/',
    '/return\s+""\s*;/',
    "/return\s+''\s*;/",
    '/\/\/\s*TODO\b/',
];

$violations = [];
$checked    = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (strpos($path, '/System/PublicSurface/') === false) {
        continue;
    }

    $content = file_get_contents($path);
    if ($content === false) {
        continue;
    }

    // Check if file has real behavior (not just hollow)
    $hasRealBehavior = false;
    foreach (['return', 'throw', 'call', 'dispatch', 'execute', 'resolve', 'create', 'build', 'emit', 'register'] as $keyword) {
        if (strpos($content, $keyword) !== false) {
            $hasRealBehavior = true;
            break;
        }
    }

    if ($hasRealBehavior) {
        $checked++;
        continue;
    }

    // Check for hollow patterns
    foreach ($hollowPatterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $areaName      = '';
            $componentName = '';
            $parts         = explode('/', $path);
            foreach ($parts as $i => $part) {
                if ($part === 'components' && isset($parts[$i + 1], $parts[$i + 2])) {
                    $areaName      = $parts[$i + 1];
                    $componentName = $parts[$i + 2];
                }
            }
            $key = "$areaName/$componentName";
            if (! isset($classifiedComponents[$key])) {
                $violations[] = "$path — hollow public surface (no real behavior)";
            }
            break;
        }
    }
    $checked++;
}

if ($violations !== []) {
    echo "FAIL: " . count($violations) . " hollow public surfaces found:\n";
    foreach ($violations as $v) {
        echo "  - $v\n";
    }
    exit(1);
}

echo "PASS: $checked public surface files checked, no hollow active surfaces\n";
exit(0);
