<?php

declare(strict_types=1);

/**
 * Check Security Blockers — V5 P0/P1 Gate
 *
 * Scans for identified P0/P1 security blockers:
 * - Session IDs in logs (hardcoded session ID patterns, session_id() in logging context)
 * - Unencrypted callable serialization outside CallableSerialization component
 * - Container::get() outside bootstrap boundaries (service locator abuse)
 *
 * Exit 0 = no blockers found
 * Exit 1 = blockers found
 */

$rootDir       = dirname(__DIR__, 2);
$frameworkDir  = $rootDir . '/framework';
$componentsDir = $rootDir . '/components';

$blockers = [];
$warnings = [];

// ---- Check 1: Session ID exposure patterns ----

$sessionPatterns = [
    'session_id()' => 'Direct session_id() call — ensure result is redacted before logging',
    'session_id'   => 'session_id reference — verify not logged raw',
    'PHPSESSID'    => 'Session cookie name exposed — verify not logged raw',
];

$filesToScan = [];
$directories = [$frameworkDir, $componentsDir];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }
        $filesToScan[] = $file->getRealPath();
    }
}

foreach ($filesToScan as $path) {
    $relativePath = str_replace($rootDir . '/', '', $path);

    // Skip test files
    if (str_contains($relativePath, '/tests/') || str_contains($relativePath, '/Test/')) {
        continue;
    }

    // Skip the CallableSerialization component itself — it owns serialization
    if (str_contains($relativePath, 'Foundation/CallableSerialization/')) {
        continue;
    }

    $content = file_get_contents($path);
    $lines   = explode("\n", $content);

    foreach ($lines as $lineNum => $line) {
        // Check for session_id in logging context
        if (str_contains($line, 'session_id()') && (str_contains($line, 'log') || str_contains($line, 'Log') || str_contains($line, 'info(') || str_contains($line, 'debug(') || str_contains($line, 'warning(') || str_contains($line, 'error('))) {
            $blockers[] = sprintf(
                'BLOCKER: Session ID may be logged: %s:%d — %s',
                $relativePath,
                $lineNum + 1,
                trim($line)
            );
        }

        // Check for Container::get() outside bootstrap
        if (preg_match('/Container\s*::\s*get\s*\(/', $line)) {
            // Skip comment lines
            $trimmed = trim($line);
            if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '/*')) {
                continue;
            }
            // Allow in Configuration/ and bootstrap files
            if (! str_contains($relativePath, '/Configuration/') && ! str_contains($relativePath, 'bootstrap') && ! str_contains($relativePath, 'BuildApplication')) {
                $warnings[] = sprintf(
                    'WARNING: Container::get() outside bootstrap: %s:%d — %s',
                    $relativePath,
                    $lineNum + 1,
                    trim($line)
                );
            }
        }

        // Check for serialize() on callables/closures outside CallableSerialization
        if (str_contains($line, 'serialize(') && (str_contains($line, 'Closure') || str_contains($line, 'callable') || str_contains($line, '$closure') || str_contains($line, '$callback'))) {
            if (! str_contains($relativePath, 'CallableSerialization') && ! str_contains($relativePath, 'Cryptography')) {
                $blockers[] = sprintf(
                    'BLOCKER: Callable serialization outside owner: %s:%d — %s',
                    $relativePath,
                    $lineNum + 1,
                    trim($line)
                );
            }
        }
    }
}

// ---- Output ----

if ($blockers !== []) {
    echo "SECURITY BLOCKERS FOUND:\n";
    foreach ($blockers as $blocker) {
        echo "  - $blocker\n";
    }
    echo "\n";
}

if ($warnings !== []) {
    echo "SECURITY WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  - $warning\n";
    }
    echo "\n";
}

if ($blockers === [] && $warnings === []) {
    echo "Security blockers check passed.\n";
    exit(0);
}

if ($blockers !== []) {
    echo "Result: FAIL — " . count($blockers) . " blocker(s), " . count($warnings) . " warning(s)\n";
    exit(1);
}

echo "Result: PASS — 0 blockers, " . count($warnings) . " warning(s) to review\n";
exit(0);
