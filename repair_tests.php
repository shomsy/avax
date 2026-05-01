<?php

declare(strict_types=1);

$testDir = __DIR__ . '/tests';

$replacements = [
    // 1. Classes
    'ResponseFactory' => 'Responses',
    'EntityManager'                      => 'Persistence',

    // 2. Namespaces & Imports
    'Avax\HTTP\Response\ResponseFactory' => 'Avax\Components\HTTP\Response\System\PublicSurface\Responses',
    'Avax\Components\HTTP\Response\ResponseFactory' => 'Avax\Components\HTTP\Response\System\PublicSurface\Responses',
    'Avax\Database\EntityManager'        => 'Avax\Components\DataStack\Persistence\System\PublicSurface\Persistence',
    'Avax\DataLayer'                     => 'Avax\Components\DataStack\Database',
    'Avax\DataFoundation'                => 'Avax\Components\DataStack\Database',
];

if (! is_dir($testDir)) {
    exit("❌ Test directory not found!\n");
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path    = $file->getPathname();
    $content = file_get_contents($path);
    $original = $content;

    // Execute string replacements
    foreach ($replacements as $old => $new) {
        $content = str_replace($old, $new, $content);
    }

    // Fix PSR-4 Namespace Drift
    // Logic: namespace should match the directory structure relative to 'tests/'
    $relativePath      = str_replace(__DIR__ . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR, '', dirname($path));
    $expectedNamespace = 'Avax\\Tests\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $currentNamespace = trim($matches[1]);
        if ($currentNamespace !== $expectedNamespace) {
            $content = str_replace("namespace $currentNamespace;", "namespace $expectedNamespace;", $content);
            echo "📍 Fixed Namespace: $path ($currentNamespace -> $expectedNamespace)\n";
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "✅ Repaired: $path\n";
    }
}

echo "\n✨ Test suite repair complete! PSR-4 drift resolved and legacy references mapped.\n";
