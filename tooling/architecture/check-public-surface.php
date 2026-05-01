<?php

declare(strict_types=1);

/**
 * Check for business logic in PublicSurface.
 * Ensures classes in System/PublicSurface are thin and delegate behavior.
 */
$rootDir       = dirname(__DIR__, 2);
$componentsDir = $rootDir . '/components';
$errors        = [];

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
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getRealPath();
    if (! str_contains($path, '/System/PublicSurface/')) {
        continue;
    }

    $content      = file_get_contents($path);
    $relativePath = str_replace($rootDir . '/', '', $path);

    // We allow small exceptions like throwing exceptions if explicitly needed, but generally PublicSurface should delegate.
    // Let's do a strict check for loops and conditionals.
    foreach ([' foreach', ' while', ' switch'] as $token) {
        if (str_contains($content, $token)) {
            $errors[] = "Potential business logic ({$token}) found in PublicSurface: {$relativePath}. PublicSurface should delegate to Flows or Capabilities.";
        }
    }

    // Check if file is too long (over 150 lines is likely too much for a thin facade/boundary)
    $lines = substr_count($content, "\n");
    if ($lines > 150) {
        $errors[] = "PublicSurface file is too large ({$lines} lines): {$relativePath}. It may contain business logic.";
    }
}

if (! empty($errors)) {
    echo "PublicSurface checks failed:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "PublicSurface checks passed.\n";
exit(0);
