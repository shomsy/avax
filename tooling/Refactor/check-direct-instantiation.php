<?php

declare(strict_types=1);

/**
 * check-direct-instantiation.php
 *
 * Governance gate: scans production PHP files for direct class instantiation
 * outside allowed boundaries (constructors, ServiceProviders, factories, tests).
 *
 * Violations reported:
 * - new Class() in methods (not constructors)
 * - ?? new Fallback() patterns
 * - Container::get() outside bootstrap
 *
 * Exit code 0 = GREEN, exit code 1 = violations found.
 *
 * No external dependencies — pure PHP.
 */

// ──────────────────────────────────────────────
// Configuration
// ──────────────────────────────────────────────

$projectRoot = dirname(__DIR__, 2);

$scanDirs = [
    $projectRoot . '/framework',
    $projectRoot . '/components',
    $projectRoot . '/labs',
];

// Patterns that are allowed to use new Class() (relative path regex)
$allowedPatterns = [
    '/System\/Configuration\/.*ServiceProvider\.php$/',
    '/System\/Configuration\/.*Factory\.php$/',
    '/System\/Foundation\/.*Factory\.php$/',
    '/Capabilities\/.*Factory/',
];

// ──────────────────────────────────────────────
// File finder (pure PHP, no Symfony Finder)
// ──────────────────────────────────────────────

function findPhpFiles(array $dirs, array $excludeDirs) : iterable
{
    foreach ($dirs as $dir) {
        if (! is_dir($dir)) {
            continue;
        }
        $iterator  = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $recursive = new RecursiveIteratorIterator($iterator);

        foreach ($recursive as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $path = $file->getPathname();
            $skip = false;
            foreach ($excludeDirs as $exclude) {
                if (str_contains($path, '/' . $exclude . '/')) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) {
                continue;
            }

            yield $file;
        }
    }
}

$excludeDirs = ['vendor', 'node_modules', 'tests', 'EVIDENCE', 'docs', 'examples', '.git'];

// ──────────────────────────────────────────────
// Scan
// ──────────────────────────────────────────────

$violations = [];
$totalFiles = 0;

foreach (findPhpFiles($scanDirs, $excludeDirs) as $file) {
    $totalFiles++;
    $filePath     = $file->getPathname();
    $relativePath = str_replace($projectRoot . '/', '', $filePath);
    $content      = file_get_contents($filePath);
    $lines        = explode("\n", $content);

    // Check if this file is in an allowed pattern
    $isAllowed = false;
    foreach ($allowedPatterns as $pattern) {
        if (preg_match($pattern, $relativePath)) {
            $isAllowed = true;
            break;
        }
    }

    // Track if we're inside a constructor
    $inConstructor    = false;
    $braceDepth       = 0;
    $constructorDepth = 0;

    foreach ($lines as $lineNumber => $line) {
        $lineNum = $lineNumber + 1;

        // Track constructor entry
        if (preg_match('/public\s+function\s+__construct\s*\(/', $line)) {
            $inConstructor    = true;
            $constructorDepth = $braceDepth;
        }

        // Track brace depth
        $braceDepth += substr_count($line, '{') - substr_count($line, '}');

        // Exit constructor when we return to its depth
        if ($inConstructor && $braceDepth <= $constructorDepth) {
            $inConstructor = false;
        }

        // Skip comments and blank lines
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '/*')) {
            continue;
        }

        // Skip allowed files entirely for certain checks
        if ($isAllowed) {
            continue;
        }

        // Check: new Class() outside constructor
        if (preg_match('/\bnew\s+[A-Z][a-zA-Z0-9_\\\\]*\s*\(/', $line) && ! $inConstructor) {
            if (preg_match('/^\s*use\s+/', $line)) {
                continue;
            }
            if (preg_match('/^\s*\*\s*@/', $line)) {
                continue;
            }

            $violations[] = [
                'file'     => $relativePath,
                'line'     => $lineNum,
                'type'     => 'DIRECT_INSTANTIATION',
                'severity' => 'BLOCKER',
                'code'     => trim($line),
                'message'  => 'Direct class instantiation outside constructor. Use DI or ResolveCallable.',
            ];
        }

        // Check: ?? new Fallback() pattern
        if (preg_match('/\?\?\s*new\s+[A-Z]/', $line)) {
            $violations[] = [
                'file'     => $relativePath,
                'line'     => $lineNum,
                'type'     => 'NULL_COALESCE_FALLBACK',
                'severity' => 'BLOCKER',
                'code'     => trim($line),
                'message'  => '?? new Fallback() pattern indicates missing DI configuration. Register fallback in ServiceProvider.',
            ];
        }

        // Check: Container::get() outside bootstrap
        if (preg_match('/\bContainer\s*::\s*get\s*\(/', $line) || preg_match('/\bapp\s*\(\s*[\'"]/', $line)) {
            if (! preg_match('/(bootstrap|AppKernel|ServiceProvider|Configuration)/i', $relativePath)) {
                $violations[] = [
                    'file'     => $relativePath,
                    'line'     => $lineNum,
                    'type'     => 'SERVICE_LOCATOR',
                    'severity' => 'HIGH',
                    'code'     => trim($line),
                    'message'  => 'Container::get() outside bootstrap boundary. Use constructor injection.',
                ];
            }
        }
    }
}

// ──────────────────────────────────────────────
// Report
// ──────────────────────────────────────────────

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  Direct Instantiation Gate\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  Files scanned: {$totalFiles}\n";

if (empty($violations)) {
    echo "  Status: GREEN — No violations found.\n";
    echo "═══════════════════════════════════════════════════════════\n\n";
    exit(0);
}

$blockers = array_filter($violations, fn ($v) => $v['severity'] === 'BLOCKER');
$high     = array_filter($violations, fn ($v) => $v['severity'] === 'HIGH');

echo "  Violations: " . count($violations) . "\n";
echo "  BLOCKER: " . count($blockers) . "\n";
echo "  HIGH: " . count($high) . "\n";
echo "═══════════════════════════════════════════════════════════\n\n";

foreach ($violations as $i => $v) {
    $num = $i + 1;
    echo "  [{$v['severity']}] #{$num}\n";
    echo "    File: {$v['file']}:{$v['line']}\n";
    echo "    Type: {$v['type']}\n";
    echo "    {$v['message']}\n";
    echo "    Code: " . substr($v['code'], 0, 100) . "\n";
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "  Status: RED — " . count($violations) . " violation(s) found.\n";
echo "  Fix: Move dependencies to constructor injection or ServiceProvider.\n";
echo "═══════════════════════════════════════════════════════════\n\n";

exit(1);
