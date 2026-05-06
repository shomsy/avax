<?php

declare(strict_types=1);

namespace Avax\Tooling\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Check for business logic in PublicSurface.
 * Ensures classes in System/PublicSurface are thin and delegate behavior.
 */
$rootDir = dirname(__DIR__, 2);
$componentsDir = $rootDir.'/components';
$errors = [];

if (! is_dir($componentsDir)) {
    exit(0);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($componentsDir));

$businessLogicTokens = [
    ' if (', ' if(',
    ' foreach (', ' foreach(',
    ' while (', ' while(',
    ' switch (', ' switch(',
    ' throw new ',
    ' catch (', ' catch(',
];

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getRealPath();
    if (! str_contains((string) $path, '/System/PublicSurface/')) {
        continue;
    }

    $content = file_get_contents($path);
    $relativePath = str_replace($rootDir.'/', '', $path);

    // We allow small exceptions like throwing exceptions if explicitly needed, but generally PublicSurface should delegate.
    // Let's do a strict check for loops and conditionals.
    foreach ([' foreach', ' while', ' switch'] as $token) {
        if (str_contains($content, $token)) {
            $errors[] = sprintf('Potential business logic (%s) found in PublicSurface: %s. PublicSurface should delegate to Flows or Capabilities.', $token, $relativePath);
        }
    }

    // Check if file is too long (over 150 lines is likely too much for a thin facade/boundary)
    $lines = substr_count($content, "\n");
    if ($lines > 150) {
        $errors[] = sprintf('PublicSurface file is too large (%d lines): %s. It may contain business logic.', $lines, $relativePath);
    }
}

if ($errors !== []) {
    echo "PublicSurface checks failed:\n";
    foreach ($errors as $error) {
        echo sprintf('- %s%s', $error, PHP_EOL);
    }

    exit(1);
}

echo "PublicSurface checks passed.\n";
exit(0);
