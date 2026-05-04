<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

$psr4Map = [
    'Avax\Framework\\' => 'framework/',
    'Avax\Components\\' => 'components/',
    'Avax\Labs\\' => 'labs/',
    'Avax\Benchmarks\\' => 'benchmarks/',
    'Avax\Docs\\' => 'docs/',
    'Avax\Tooling\\' => 'tooling/',
    'Avax\Tests\\' => 'tests/',
];

$basePath = dirname(__DIR__, 2);
$fixed = 0;
$checked = 0;

function getExpectedNamespace(string $filePath, array $psr4Map, string $basePath): ?string
{
    $relativePath = str_replace($basePath . '/', '', $filePath);

    foreach ($psr4Map as $prefix => $dir) {
        if (str_starts_with($relativePath, (string)$dir)) {
            $remainingPath = substr($relativePath, strlen((string)$dir));
            $parts = explode('/', $remainingPath);
            array_pop($parts);
            $parts = array_filter($parts, fn(string $p): bool => $p !== '.' && $p !== '..');

            return $prefix . implode('\\', $parts);
        }
    }

    return null;
}

function normalizePathSegments(array $parts): array
{
    $result = [];
    $last = null;

    foreach ($parts as $part) {
        if ($part === $last) {
            continue;
        }

        $result[] = $part;
        $last = $part;
    }

    return $result;
}

function fixFile(string $filePath, string $expectedNamespace, array $psr4Map): bool
{
    $content = file_get_contents($filePath);
    $modified = false;

    if (preg_match('/^namespace\s+([A-Za-z\\\\]+);/m', $content, $matches)) {
        $currentNamespace = $matches[1];
        $parts = explode('\\', $currentNamespace);
        $parts = normalizePathSegments($parts);
        $cleanNamespace = implode('\\', $parts);

        if ($cleanNamespace !== $expectedNamespace) {
            $content = preg_replace(
                '/^namespace\s+[A-Za-z\\\\]+;/m',
                'namespace ' . $expectedNamespace . ';',
                $content
            );
            $modified = true;
        }
    }

    foreach (array_keys($psr4Map) as $prefix) {
        if (preg_match_all('/use\s+(' . preg_quote((string)$prefix, '/') . '[A-Za-z\\\\]+);/m', $content, $matches)) {
            foreach ($matches[1] as $oldUse) {
                $parts = explode('\\', $oldUse);
                $parts = normalizePathSegments($parts);
                $newUse = implode('\\', $parts);

                if ($oldUse !== $newUse) {
                    $content = str_replace('use ' . $oldUse . ';', 'use ' . $newUse . ';', $content);
                    $modified = true;
                }
            }
        }
    }

    if ($modified) {
        file_put_contents($filePath, $content);

        return true;
    }

    return false;
}

$dirs = ['framework', 'components', 'labs', 'benchmarks', 'docs', 'tooling', 'tests'];

echo "=== Namespace Fixer v2 ===\n\n";

foreach ($dirs as $dir) {
    $fullPath = $basePath . '/' . $dir;
    if (!is_dir($fullPath)) {
        continue;
    }

    echo "Processing: {$dir}/\n";

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($fullPath),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $filePath = $file->getPathname();
        $checked++;

        $expectedNs = getExpectedNamespace($filePath, $psr4Map, $basePath);
        if ($expectedNs === null) {
            continue;
        }

        if (fixFile($filePath, $expectedNs, $psr4Map)) {
            $fixed++;
            $relPath = str_replace($basePath . '/', '', $filePath);
            echo sprintf('  [FIXED] %s%s', $relPath, PHP_EOL);
        }
    }
}

echo "\n=== Summary ===\n";
echo "Checked: {$checked} files\n";
echo "Fixed: {$fixed} files\n";
echo ($fixed === 0) ? "Status: ALL OK\n" : "Status: SOME FIXED\n";