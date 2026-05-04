<?php

declare(strict_types=1);

namespace Avax\Tooling\Architecture;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Check for duplicate owners.
 * Ensures the same class name is not defined in multiple components as a real class.
 */
$rootDir = dirname(__DIR__, 2);
$componentsDir = $rootDir . '/components';
$errors = [];

if (!is_dir($componentsDir)) {
    exit(0);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($componentsDir));

$classMap = [];

foreach ($iterator as $file) {
    if (!$file->isFile()) {
        continue;
    }

    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getRealPath();
    $relativePath = str_replace($rootDir . '/', '', $path);
    // Ignore vendor, tests, tooling
    if (str_contains($relativePath, 'vendor/')) {
        continue;
    }

    if (str_contains($relativePath, 'tests/')) {
        continue;
    }

    $content = file_get_contents($path);

    if (preg_match('/(?:class|interface|trait|enum)\s+(\w+)/', $content, $matches)) {
        $className = $matches[1];

        // Exclude common names that are expected to be duplicated across components like Exception, Configuration
        $commonNames = ['Exception', 'Configuration', 'ServiceProvider', 'Factory', 'Manager', 'Builder'];
        $isCommon = array_any($commonNames, fn($common): bool => str_ends_with($className, (string)$common));

        if ($isCommon) {
            continue;
        }

        if (isset($classMap[$className])) {
            // Check if one is a bridge
            $isCurrentBridge = str_contains($content, '@deprecated') || str_contains($content, 'bridge');
            $isPreviousBridge = str_contains(file_get_contents($rootDir . '/' . $classMap[$className]), '@deprecated') || str_contains(file_get_contents($rootDir . '/' . $classMap[$className]), 'bridge');

            if (!$isCurrentBridge && !$isPreviousBridge) {
                $errors[] = sprintf('Duplicate class name owner found: %s. Claimed by both %s and %s', $className, $classMap[$className], $relativePath);
            }
        } else {
            $classMap[$className] = $relativePath;
        }
    }
}

if ($errors !== []) {
    echo "Duplicate owner checks failed:\n";
    foreach ($errors as $error) {
        echo sprintf('- %s%s', $error, PHP_EOL);
    }

    exit(1);
}

echo "Duplicate owner checks passed.\n";
exit(0);
