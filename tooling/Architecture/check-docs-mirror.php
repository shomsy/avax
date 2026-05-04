<?php

declare(strict_types=1);

namespace Avax\Tooling\Architecture;
/**
 * Check for docs mirror.
 * Ensures that components/ structure is mirrored in docs/components/ without obsolete namespaces.
 */
$rootDir = dirname(__DIR__, 2);
$componentsDir = $rootDir . '/components';
$docsDir = $rootDir . '/docs/components';
$errors = [];

if (!is_dir($componentsDir) || !is_dir($docsDir)) {
    exit(0);
}

// Find all components
$components = [];
$iterator = new DirectoryIterator($componentsDir);
foreach ($iterator as $fileinfo) {
    if ($fileinfo->isDir() && !$fileinfo->isDot()) {
        $components[] = $fileinfo->getFilename();
    }
}

// Check if docs/Foundation/DataLayer is still there (obsolete)
if (is_dir($rootDir . '/docs/Foundation/DataLayer')) {
    $errors[] = 'Obsolete docs folder found: docs/Foundation/DataLayer. Please move to docs/components/Persistence.';
}

// Check if docs/Foundation/DataHandling is still there
if (is_dir($rootDir . '/docs/Foundation/DataHandling')) {
    $errors[] = 'Obsolete docs folder found: docs/Foundation/DataHandling. Please move to docs/components/Data.';
}

if ($errors !== []) {
    echo "Docs mirror checks failed:\n";
    foreach ($errors as $error) {
        echo sprintf('- %s%s', $error, PHP_EOL);
    }

    exit(1);
}

echo "Docs mirror checks passed.\n";
exit(0);
