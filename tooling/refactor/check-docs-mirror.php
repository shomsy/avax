<?php

declare(strict_types=1);

$root     = dirname(__DIR__, 2);
$docsRoot = $root . '/docs';

$forbiddenDocTokens = [
    'docs/Foundation/HTTP',
    'docs/Foundation/DataLayer',
    'Foundation/HTTP',
    'Foundation/DataLayer',
    'Avax\\DataFoundation',
    'Avax\\DataLayer',
    'namespace components\\',
];

$violations = [];

if (! is_dir($docsRoot)) {
    fwrite(STDOUT, "PASS\n");
    exit(0);
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($docsRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($it as $file) {
    if (! $file instanceof SplFileInfo || $file->isDir()) {
        continue;
    }

    $ext = strtolower($file->getExtension());
    if (! in_array($ext, ['md', 'markdown'], true)) {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    if (! is_string($contents)) {
        continue;
    }

    foreach ($forbiddenDocTokens as $token) {
        if (str_contains($contents, $token)) {
            $rel          = str_replace($root . '/', '', $file->getPathname());
            $violations[] = "{$rel} references forbidden token: {$token}";
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

