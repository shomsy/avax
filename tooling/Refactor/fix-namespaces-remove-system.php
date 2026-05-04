<?php


namespace Avax\Tooling\Refactor;
$dirs = [
    __DIR__ . '/../../tooling/dependency-map',
    __DIR__ . '/../../benchmarks/performance',
    __DIR__ . '/../../docs/components/System',
];

$fixed = 0;

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $content = file_get_contents($file->getPathname());
        $original = $content;

        $content = preg_replace('/\\\System\\\/', '\\', $content);

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            $fixed++;
            echo "Fixed: " . $file->getPathname() . "\n";
        }
    }
}

echo "\nTotal fixed: {$fixed} files\n";