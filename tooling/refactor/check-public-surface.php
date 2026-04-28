<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$componentsRoot = $root . '/components';

$violations = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentsRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (! $file instanceof SplFileInfo || $file->getExtension() !== 'php') {
        continue;
    }

    $path = str_replace($root, '', $file->getPathname());

    if (! str_contains($path, '/System/PublicSurface/')) {
        continue;
    }

    $contents = file_get_contents($file->getPathname());
    if (! is_string($contents)) {
        continue;
    }

    // Lightweight heuristic: public surface should not contain heavy loops, SQL, or direct filesystem ops.
    // This is not a full parser. It is a guardrail.
    $suspects = [
        'SELECT '           => 'SQL should not live in PublicSurface',
        'INSERT '           => 'SQL should not live in PublicSurface',
        'UPDATE '           => 'SQL should not live in PublicSurface',
        'DELETE '           => 'SQL should not live in PublicSurface',
        'file_get_contents' => 'Filesystem IO should not live in PublicSurface',
        'file_put_contents' => 'Filesystem IO should not live in PublicSurface',
        'fopen('            => 'Filesystem IO should not live in PublicSurface',
        'curl_'             => 'Network/IO should not live in PublicSurface',
        'while ('           => 'Complex loops should not live in PublicSurface',
        'foreach ('         => 'Complex loops should not live in PublicSurface',
    ];

    foreach ($suspects as $needle => $message) {
        if (str_contains($contents, $needle)) {
            $violations[] = ltrim($path, '/') . " — {$message} ({$needle})";
            break;
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

