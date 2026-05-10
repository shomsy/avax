<?php

declare(strict_types=1);

/**
 * Check Raw File Operations — Dogfooding Gate
 *
 * Scans for raw file operations (file_put_contents, file_get_contents, fopen, etc.)
 * outside the Filesystem component owner.
 *
 * Per how-to-dogfooding.md: Storage uses Filesystem. No raw file operations outside owner.
 *
 * Exit 0 = no violations
 * Exit 1 = violations found
 */

$rootDir       = dirname(__DIR__, 2);
$frameworkDir  = $rootDir . '/framework';
$componentsDir = $rootDir . '/components';

$violations = [];
$allowed    = [];

// Allowed contexts for raw file operations
$allowedPaths = [
    'components/Application/Filesystem/',  // Filesystem owner
    'components/Operations/Filesystem/',    // Filesystem adapters
    'tooling/',                             // Tooling scripts
    'tests/',                              // Test files
];

$rawFunctions = [
    'file_put_contents',
    'file_get_contents',
    'fopen(',
    'fclose(',
    'fread(',
    'fwrite(',
    'unlink(',
    'mkdir(',
    'chmod(',
    'rmdir(',
    'copy(',
    'rename(',
];

$directories = [$frameworkDir, $componentsDir];
$allFiles    = [];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $allFiles[] = $file->getRealPath();
    }
}

foreach ($allFiles as $path) {
    $relativePath = str_replace($rootDir . '/', '', $path);

    // Skip allowed paths
    $isAllowed = false;
    foreach ($allowedPaths as $allowedPath) {
        if (str_contains($relativePath, $allowedPath)) {
            $isAllowed = true;
            break;
        }
    }
    if ($isAllowed) {
        continue;
    }

    $content = file_get_contents($path);
    $lines   = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);

        // Skip comments
        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
            continue;
        }

        foreach ($rawFunctions as $func) {
            if (str_contains($line, $func)) {
                $violations[] = sprintf(
                    'RAW %s: %s:%d — %s',
                    $func,
                    $relativePath,
                    $lineNum + 1,
                    $trimmed
                );
                break; // Only report once per line
            }
        }
    }
}

// ---- Output ----

if ($violations === []) {
    echo "Raw file operations check passed — all file I/O goes through Filesystem component.\n";
    exit(0);
}

echo "RAW FILE OPERATIONS OUTSIDE FILESYSTEM OWNER:\n";
foreach ($violations as $violation) {
    echo "  - $violation\n";
}
echo "\n";
echo "Result: FAIL — " . count($violations) . " violation(s)\n";
echo "These should use Application/Filesystem component.\n";
exit(1);
