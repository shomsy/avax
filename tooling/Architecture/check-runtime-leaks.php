<?php

declare(strict_types=1);

namespace Avax\Tooling\Architecture;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Check for runtime leaks.
 * Ensures Swoole, FrankenPHP, etc. do not leak outside Runtime adapters.
 */
$root = dirname(__DIR__, 2);
$forbiddenTokens = ['FrankenPhp', 'RoadRunner', 'Swoole', 'Workerman', 'ReactPHP', 'Amp'];
$allowedPaths = [
    '/framework/System/Capabilities/Runtime/Adapters/',
    '/components/Runtime/',
    '/components/RuntimeSafety/',
];
$scanRoots = [
    $root . '/framework/System',
    $root . '/components',
];

$violations = [];

foreach ($scanRoots as $scanRoot) {
    if (!is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo) {
            continue;
        }

        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = str_replace($root, '', $file->getPathname());
        $isAllowed = array_any($allowedPaths, fn($allowedPath): bool => str_contains($path, (string)$allowedPath));

        if ($isAllowed) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if (!is_string($contents)) {
            continue;
        }

        foreach ($forbiddenTokens as $forbiddenToken) {
            // Check for use statements or instantiation to avoid matching string literals in unrelated code
            // But for safety, simple str_contains is a good start.
            // We can add space before token to avoid matching parts of other words.
            if (str_contains($contents, ' ' . $forbiddenToken) || str_contains($contents, '\\' . $forbiddenToken)) {
                $violations[] = sprintf('%s leaks runtime-specific token "%s"', ltrim($path, '/'), $forbiddenToken);
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Runtime leak checks failed:\n" . implode(PHP_EOL, $violations) . PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Runtime leak checks passed.\n");
exit(0);
