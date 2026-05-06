#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$componentsDir = $root.'/components';
$frameworkDir = $root.'/framework';

$forbiddenPathPrefixes = [
    'Security/Services',
    'Security/Managers',
    'Security/Helpers',
    'Security/Utils',
    'Security/Support',
];

$violations = [];
$warnings = [];

$searchDirs = [$componentsDir, $frameworkDir];

foreach ($searchDirs as $searchDir) {
    if (! is_dir($searchDir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($searchDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        $relativePath = str_replace($root.'/', '', $path);

        foreach ($forbiddenPathPrefixes as $prefix) {
            if (strpos($path, $prefix) !== false || strpos($path, $prefix.'/') !== false) {
                $violations[] = [
                    'path' => $relativePath,
                    'issue' => "contains forbidden path prefix: $prefix",
                ];
            }
        }

        $content = file_get_contents($path);

        if (stripos($content, 'debug=true') !== false && stripos($content, 'if') === false && stripos($content, '?') === false) {
            $warnings[] = [
                'path' => $relativePath,
                'issue' => 'debug flag without conditional',
            ];
        }
    }
}

if (empty($violations) && empty($warnings)) {
    echo "GREEN: No security naming violations found.\n";
    exit(0);
}

echo "=== Security Governance Check ===\n\n";

if (! empty($violations)) {
    echo "BLOCKER VIOLATIONS:\n";
    foreach ($violations as $v) {
        echo "  - {$v['path']}: {$v['issue']}\n";
    }
    echo "\n";
}

if (! empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $w) {
        echo "  - {$w['path']}: {$w['issue']}\n";
    }
    echo "\n";
}

echo "Run: php tooling/security/check-security-naming.php\n";

exit(! empty($violations) ? 1 : 0);
