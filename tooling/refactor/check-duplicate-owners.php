<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$pathsThatMustNotExist = [
    $root . '/components/Session',
    $root . '/components/Middleware',
    $root . '/components/DataLayer',
];

$pathsThatMustBeBridgeOnlyOrDeleted = [
    $root . '/components/DataFoundation',
];

$violations = [];

foreach ($pathsThatMustNotExist as $path) {
    if (file_exists($path)) {
        $rel          = str_replace($root . '/', '', $path);
        $violations[] = "duplicate/forbidden owner exists: {$rel}";
    }
}

foreach ($pathsThatMustBeBridgeOnlyOrDeleted as $path) {
    if (! file_exists($path)) {
        continue;
    }

    // Very simple bridge-only heuristic: no System/ folder and every PHP file is marked @deprecated.
    if (is_dir($path . '/System')) {
        $rel          = str_replace($root . '/', '', $path);
        $violations[] = "legacy owner still has System/: {$rel}/System";
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if (! is_string($contents)) {
            continue;
        }

        if (! str_contains($contents, '@deprecated')) {
            $rel          = str_replace($root . '/', '', $file->getPathname());
            $violations[] = "bridge file missing @deprecated: {$rel}";
        }
    }
}

if ($violations !== []) {
    fwrite(STDOUT, "FAIL\n");
    foreach ($violations as $v) {
        fwrite(STDOUT, "- {$v}\n");
    }
    exit(1);
}

fwrite(STDOUT, "PASS\n");
exit(0);

