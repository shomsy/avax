<?php

declare(strict_types=1);

$root            = dirname(__DIR__, 2);
$forbiddenTokens = ['FrankenPhp', 'RoadRunner', 'Swoole', 'Workerman', 'ReactPHP', 'Amp'];
$allowedPaths    = [
    '/framework/System/Capabilities/Runtime/Adapters/',
];
$scanRoots = [
    $root . '/framework/System',
];

$violations = [];

foreach ($scanRoots as $scanRoot) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($scanRoot, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $path = str_replace($root, '', $file->getPathname());

        $isAllowed = false;

        foreach ($allowedPaths as $allowedPath) {
            if (str_contains($path, $allowedPath)) {
                $isAllowed = true;

                break;
            }
        }

        if ($isAllowed) {
            continue;
        }

        $contents = file_get_contents($file->getPathname());

        if (! is_string($contents)) {
            continue;
        }

        foreach ($forbiddenTokens as $token) {
            if (str_contains($contents, $token)) {
                $violations[] = sprintf('%s leaks runtime-specific token "%s"', ltrim($path, '/'), $token);
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, implode(PHP_EOL, $violations) . PHP_EOL);

    exit(1);
}

fwrite(STDOUT, "Runtime leak checks passed.\n");
