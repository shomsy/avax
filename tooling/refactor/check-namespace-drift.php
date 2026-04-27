<?php

declare(strict_types=1);

/**
 * Check for namespace drift.
 * Ensures all files in components/ follow the Avax\Components\<Component>\System pattern
 * or are explicitly whitelisted (like compat.php).
 */

$componentsDir = __DIR__ . '/../../components';
$errors        = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($componentsDir));

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path         = $file->getRealPath();
    $relativePath = str_replace(realpath($componentsDir) . '/', '', $path);

    // Skip root files in components/
    if (! str_contains($relativePath, '/')) {
        continue;
    }

    $content = file_get_contents($path);
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $namespace = trim($matches[1]);

        // Whitelist legacy components for now (to be migrated)
        if (str_starts_with($relativePath, 'DataFoundation/') ||
            str_starts_with($relativePath, 'DataLayer/') ||
            str_starts_with($relativePath, 'ApplicationWorkflow/Saga/')) {
            continue;
        }

        // Check for lowercase 'components\'
        if (str_starts_with($namespace, 'components\\')) {
            $errors[] = "Legacy lowercase namespace in {$relativePath}: {$namespace}";
        }

        // Check for missing 'Components' in Avax namespace for components
        if (str_starts_with($namespace, 'Avax\\') &&
            ! str_starts_with($namespace, 'Avax\\Components\\') &&
            ! str_starts_with($namespace, 'Avax\\Database\\') && // Database is special
            ! str_starts_with($namespace, 'Avax\\Framework\\')) {
            $errors[] = "Missing 'Components' sub-namespace in {$relativePath}: {$namespace}";
        }
    }
}

if (! empty($errors)) {
    echo "Namespace drift detected:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "No namespace drift detected.\n";
exit(0);
