<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * NamespaceReconstructor - The Nuclear Option V2 (Enhanced Auto-Fix).
 */

$root = dirname(__DIR__, 2);
require_once $root . '/vendor/autoload.php';

echo "=== Namespace Reconstructor V2: Enhanced Auto-Fix ===\n\n";

// 1. Build Class Map with Normalization
echo "Step 1: Building global class map (with normalization)...\n";
$classMap = [];
$fqcnToPath = [];

$scanDirs = ['framework', 'components'];
foreach ($scanDirs as $dir) {
    $dirPath = "$root/$dir";
    if (!is_dir($dirPath)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;

        $content = file_get_contents($file->getPathname());
        if (preg_match('/namespace\s+([^;]+);/', $content, $nsMatches)) {
            $namespace = trim($nsMatches[1]);

            // Normalize namespace if it starts with components\ or framework\
            if (str_starts_with(strtolower($namespace), 'components\\')) {
                $namespace = 'Avax\\Components\\' . substr($namespace, 11);
            } elseif (str_starts_with(strtolower($namespace), 'framework\\')) {
                $namespace = 'Avax\\Framework\\' . substr($namespace, 10);
            }

            if (preg_match('/(?:class|interface|trait|enum)\s+([A-Za-z0-9_]+)/', $content, $classMatches)) {
                $shortName = $classMatches[1];
                $fqcn = $namespace . '\\' . $shortName;

                $classMap[$shortName][] = $fqcn;
                $fqcnToPath[$fqcn] = $file->getPathname();
            }
        }
    }
}

echo "Found " . count($classMap) . " unique short names across " . count($fqcnToPath) . " classes.\n\n";

// 2. Fix Imports Globally (not just in tests)
echo "Step 2: Fixing imports globally...\n";
$targetDirs = ['framework', 'components', 'tests'];
$fixedFiles = 0;

foreach ($targetDirs as $targetDir) {
    $dirPath = "$root/$targetDir";
    if (!is_dir($dirPath)) continue;

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dirPath));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        if (str_contains($file->getPathname(), '/vendor/')) continue;

        $content = file_get_contents($file->getPathname());
        $originalContent = $content;

        $content = preg_replace_callback(
            '/^use\s+([A-Za-z0-9_\\\\]+)(?:\s+as\s+[A-Za-z0-9_]+)?;/m',
            function ($matches) use ($classMap, $root, $file) {
                $fullUse = $matches[0];
                $fqcn = $matches[1];

                // If it starts with components\ or framework\, it's definitely broken
                $isBrokenPrefix = str_starts_with(strtolower($fqcn), 'components\\') || str_starts_with(strtolower($fqcn), 'framework\\');

                if (!$isBrokenPrefix && classExists($fqcn, $root)) {
                    return $fullUse;
                }

                // Extract short name
                $parts = explode('\\', $fqcn);
                $shortName = end($parts);

                if (isset($classMap[$shortName])) {
                    if (count($classMap[$shortName]) === 1) {
                        $newFqcn = $classMap[$shortName][0];
                        return str_replace($fqcn, $newFqcn, $fullUse);
                    } else {
                        // Heuristic: pick the one that matches the original FQCN suffix if possible
                        foreach ($classMap[$shortName] as $potential) {
                            if (str_ends_with(strtolower($potential), strtolower($fqcn))) {
                                return str_replace($fqcn, $potential, $fullUse);
                            }
                        }

                        // Heuristic: pick the one in the same suite if we are in components/
                        if (str_contains($file->getPathname(), 'components/')) {
                            $suite = explode('/', str_replace($root . '/components/', '', $file->getPathname()))[0];
                            foreach ($classMap[$shortName] as $potential) {
                                if (str_contains($potential, 'Components\\' . $suite)) {
                                    return str_replace($fqcn, $potential, $fullUse);
                                }
                            }
                        }
                    }
                }
                if (isset($classMap[$shortName])) {
                    echo "  [DEBUG] Found match for $shortName in " . count($classMap[$shortName]) . " locations.\n";
                } else {
                    echo "  [DEBUG] No match found for $shortName in class map.\n";
                }

                return $fullUse;
            },
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($file->getPathname(), $content);
            $fixedFiles++;
            echo "  [FIXED] " . str_replace($root . '/', '', $file->getPathname()) . "\n";
        }
    }
}

function classExists(string $fqcn, string $root): bool
{
    $prefixes = [
        'Avax\\Framework\\' => 'framework/',
        'Avax\\Components\\' => 'components/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (str_starts_with($fqcn, $prefix)) {
            $relative = str_replace($prefix, '', $fqcn);
            $path = $root . '/' . $dir . str_replace('\\', '/', $relative) . '.php';
            if (file_exists($path)) return true;
        }
    }
    return false;
}

echo "\nDone! Fixed imports in $fixedFiles files.\n";
