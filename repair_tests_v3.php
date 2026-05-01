<?php

declare(strict_types=1);

$baseDir = realpath(__DIR__);
$testDir = $baseDir . '/tests';

$mappings = [
    'Avax\HTTP\Response\ResponseFactory' => 'Avax\Components\HTTP\Response\System\PublicSurface\Responses',
    'Avax\HTTP\Response\Responses' => 'Avax\Components\HTTP\Response\System\PublicSurface\Responses',
    'Avax\Database\EntityManager'  => 'Avax\Components\DataStack\Persistence\System\PublicSurface\Persistence',
    'ResponseFactory'              => 'Responses',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path    = realpath($file->getPathname());
    $content = file_get_contents($path);
    $original = $content;

    // 1. Class and Namespace replacements
    foreach ($mappings as $old => $new) {
        $content = str_replace($old, $new, $content);
    }

    // 2. PSR-4 Namespace normalization
    $dir          = dirname($path);
    $relativePath = ltrim(str_replace($testDir, '', $dir), DIRECTORY_SEPARATOR);
    $expectedNamespace = 'Avax\\Tests';
    if ($relativePath !== '') {
        $expectedNamespace .= '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
    }

    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $currentNamespace = trim($matches[1]);
        if ($currentNamespace !== $expectedNamespace) {
            $content = str_replace("namespace $currentNamespace;", "namespace $expectedNamespace;", $content);
            echo '✅ Fixed Namespace: ' . basename($path) . "\n";
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
        echo '🔄 Updated References: ' . basename($path) . "\n";
    }
}

echo "\n✨ Global Test Reference Mapping Complete.\n";
