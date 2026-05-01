<?php

declare(strict_types=1);

/**
 * Check for forbidden folders (dumping grounds).
 * Ensures no Core/, Shared/, Helpers/ folders exist, and DataLayer/DataFoundation are empty of real code.
 */
$rootDir = dirname(__DIR__, 2);

$forbiddenFolders = [
    'components/Core',
    'components/Shared',
    'components/Helpers',
    'framework/Core',
    'framework/Shared',
    'framework/Helpers',
];

$legacyFolders = [
    'components/DataFoundation',
    'components/DataLayer',
];

$errors = [];

foreach ($forbiddenFolders as $folder) {
    if (is_dir($rootDir . '/' . $folder)) {
        $errors[] = "Forbidden dumping ground found: {$folder}. Please use Screaming Architecture (System/Capabilities, System/Flows).";
    }
}

foreach ($legacyFolders as $folder) {
    if (! is_dir($rootDir . '/' . $folder)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootDir . '/' . $folder));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getRealPath());
            // If it's just a bridge or a responsibility descriptor, it might be allowed temporarily.
            // But ideally, no "real files" means no real business logic. Let's flag any file that isn't a bridge.
            // A simple heuristic: if it has more than just an interface or a describeResponsibility method, it's a real file.
            if (! str_contains($content, 'describeResponsibility') && ! str_contains($content, 'extends') && ! str_contains($content, 'implements')) {
                // If it has actual methods with logic, it's a real file.
                // Let's just flag all files in legacy folders for now unless they contain 'bridge' or 'deprecated'
                if (! str_contains(strtolower($content), '@deprecated') && ! str_contains(strtolower($content), 'bridge')) {
                    $errors[] = "Real file found in legacy folder {$folder}: " . str_replace($rootDir . '/', '', $file->getRealPath());
                }
            }
        }
    }
}

if (! empty($errors)) {
    echo "Forbidden folder checks failed:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}

echo "Forbidden folder checks passed.\n";
exit(0);
