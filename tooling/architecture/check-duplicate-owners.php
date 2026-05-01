<?php

declare(strict_types=1);

/**
 * Check for duplicate owners.
 * Ensures the same class name is not defined in multiple components as a real class.
 */
$rootDir       = dirname(__DIR__, 2);
$componentsDir = $rootDir . '/components';
$errors        = [];

if (! is_dir($componentsDir)) {
    exit(0);
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($componentsDir));

$classMap = [];

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path         = $file->getRealPath();
    $relativePath = str_replace($rootDir . '/', '', $path);

    // Ignore vendor, tests, tooling
    if (str_contains($relativePath, 'vendor/') || str_contains($relativePath, 'tests/')) {
        continue;
    }

    $content = file_get_contents($path);

    if (preg_match('/(?:class|interface|trait|enum)\s+([a-zA-Z0-9_]+)/', $content, $matches)) {
        $className = $matches[1];

        // Exclude common names that are expected to be duplicated across components like Exception, Configuration
        $commonNames = ['Exception', 'Configuration', 'ServiceProvider', 'Factory', 'Manager', 'Builder'];
        $isCommon    = false;
        foreach ($commonNames as $common) {
            if (str_ends_with($className, $common)) {
                $isCommon = true;

                break;
            }
        }

        if ($isCommon) {
            continue;
        }

        if (isset($classMap[$className])) {
            // Check if one is a bridge
            $isCurrentBridge  = str_contains($content, '@deprecated')                                                  || str_contains($content, 'bridge');
            $isPreviousBridge = str_contains(file_get_contents($rootDir . '/' . $classMap[$className]), '@deprecated') || str_contains(file_get_contents($rootDir . '/' . $classMap[$className]), 'bridge');

            if (! $isCurrentBridge && ! $isPreviousBridge) {
                $errors[] = "Duplicate class name owner found: {$className}. Claimed by both {$classMap[$className]} and {$relativePath}";
            }
        } else {
            $classMap[$className] = $relativePath;
        }
    }
}

if (! empty($errors)) {
    echo "Duplicate owner checks failed:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "Duplicate owner checks passed.\n";
exit(0);
