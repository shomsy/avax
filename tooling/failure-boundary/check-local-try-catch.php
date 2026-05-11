#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * check-local-try-catch.php — Gate: Warns when controller/job/command has framework-level try/catch blocks.
 *
 * Exit code 0: No suspicious try/catch blocks found.
 * Exit code 1: Suspicious try/catch blocks detected (warning).
 *
 * This gate does not forbid local try/catch — it only warns about patterns that
 * look like framework-level failure handling that should be delegated to FailureBoundary.
 *
 * Usage:
 * php tooling/failure-boundary/check-local-try-catch.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$warnings = [];

$directories = [
    __DIR__ . '/../../components',
    __DIR__ . '/../../examples',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());

        // Look for try/catch patterns that map exceptions to HTTP responses
        // These should use FailureBoundary instead
        if (preg_match_all('/try\s*\{[^}]*Response::(json|text|html|error|redirect)/s', $content, $matches)) {
            $relativePath = str_replace(__DIR__ . '/../../', '', $file->getPathname());
            $warnings[] = $relativePath;
        }
    }
}

if (empty($warnings)) {
    echo "GREEN: No suspicious framework-level try/catch blocks found.\n";
    exit(0);
}

echo "YELLOW: Files with potential framework-level try/catch that should use FailureBoundary:\n";
foreach ($warnings as $warning) {
    echo "  - {$warning}\n";
}

exit(0); // Warning only, not a hard fail
