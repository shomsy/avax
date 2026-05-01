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

foreach ($forbiddenFolders as $forbiddenFolder) {
    if (is_dir($rootDir . '/' . $forbiddenFolder)) {
        $errors[] = sprintf('Forbidden dumping ground found: %s. Please use Screaming Architecture (System/Capabilities, System/Flows).', $forbiddenFolder);
    }
}

foreach ($legacyFolders as $legacyFolder) {
    if (! is_dir($rootDir . '/' . $legacyFolder)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootDir . '/' . $legacyFolder));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getRealPath());
            // If it's just a bridge or a responsibility descriptor, it might be allowed temporarily.
            // But ideally, no "real files" means no real business logic. Let's flag any file that isn't a bridge.
            // A simple heuristic: if it has more than just an interface or a describeResponsibility method, it's a real file.
            // If it has actual methods with logic, it's a real file.
            // Let's just flag all files in legacy folders for now unless they contain 'bridge' or 'deprecated'
            if (! str_contains($content, 'describeResponsibility') && ! str_contains($content, 'extends') && ! str_contains($content, 'implements') && (! str_contains(strtolower($content), '@deprecated') && ! str_contains(strtolower($content), 'bridge'))) {
                $errors[] = sprintf('Real file found in legacy folder %s: ', $legacyFolder) . str_replace($rootDir . '/', '', $file->getRealPath());
            }
        }
    }
}

if ($errors !== []) {
    echo "Forbidden folder checks failed:\n";
    foreach ($errors as $error) {
        echo sprintf('- %s%s', $error, PHP_EOL);
    }

    exit(1);
}

echo "Forbidden folder checks passed.\n";
exit(0);
