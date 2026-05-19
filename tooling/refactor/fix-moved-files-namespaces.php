<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

$replacements = [
    'Avax\Components\API' => 'Avax\Labs\API',
    'Avax\Components\Integration' => 'Avax\Labs\Integration',
];

$dirs = [
    __DIR__.'/../../labs',
    __DIR__.'/../../benchmarks/performance',
    __DIR__.'/../../docs/components/System',
    __DIR__.'/../../tooling/dependency-map',
];

$fixed = 0;

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
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

        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content);
        }

        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            $fixed++;
            echo 'Fixed: '.$file->getPathname()."\n";
        }
    }
}

echo "\nTotal fixed: {$fixed} files\n";
