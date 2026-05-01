<?php

declare(strict_types=1);

$baseDir = realpath(__DIR__);
$testDir = $baseDir . '/tests';

if (! is_dir($testDir)) {
    die("❌ Test directory not found!\n");
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path     = realpath($file->getPathname());
    $content  = file_get_contents($path);
    $original = $content;

    // 1. Precise Relative Path calculation
    $dir          = dirname($path);
    $relativePath = ltrim(str_replace($testDir, '', $dir), DIRECTORY_SEPARATOR);

    $expectedNamespace = 'Avax\\Tests';
    if ($relativePath !== '') {
        $expectedNamespace .= '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
    }

    // 2. Fix corrupted namespaces from previous run (handling the absolute path mess)
    $content = preg_replace('/namespace Avax\\\\Tests\\\\home\\\\shomsy[^;]+;/', "namespace $expectedNamespace;", $content);

    // 3. Normal namespace update
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $currentNamespace = trim($matches[1]);
        if ($currentNamespace !== $expectedNamespace) {
            $content = str_replace("namespace $currentNamespace;", "namespace $expectedNamespace;", $content);
            echo '✅ Fixed: ' . basename($path) . " ($currentNamespace -> $expectedNamespace)\n";
        }
    }

    if ($content !== $original) {
        file_put_contents($path, $content);
    }
}

echo "\n✨ PSR-4 Test Namespaces normalized correctly.\n";
