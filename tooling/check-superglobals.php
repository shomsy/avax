<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$root         = dirname(path: __DIR__);
$patterns     = ['$_SERVER', '$_GET', '$_POST', '$_COOKIE', '$_FILES', '$_SESSION', '$_REQUEST'];
$allowedFiles = [
    'Foundation/HTTP/Context/PhpGlobalsProvider.php',
    'Foundation/HTTP/ServerRequest/ServerRequest.php',
    'Foundation/HTTP/ServerRequest/AbsoluteServerRequest.php',
    'Foundation/HTTP/Session/Session.php',
    'Foundation/Container/tests/Integration/RequestFromGlobalsTest.php',
];

$violations = [];
$iterator   = new RecursiveIteratorIterator(
    iterator: new RecursiveDirectoryIterator(directory: $root . '/Foundation', flags: FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    if (strtolower(string: $file->getExtension()) !== 'php') {
        continue;
    }

    $relativePath = str_replace(search: '\\', replace: '/', subject: substr(string: $file->getRealPath(), offset: strlen(string: $root) + 1));
    if (in_array(needle: $relativePath, haystack: $allowedFiles, strict: true)) {
        continue;
    }

    $lines = file(filename: $file->getRealPath(), flags: FILE_IGNORE_NEW_LINES);
    foreach ($lines as $index => $line) {
        foreach ($patterns as $pattern) {
            if (str_contains(haystack: $line, needle: $pattern)) {
                $violations[] = sprintf('%s:%d uses %s', $relativePath, $index + 1, $pattern);
            }
        }
    }
}

if ($violations !== []) {
    echo "Superglobal usage detected outside HTTP boundary:\n";
    foreach (array_unique(array: $violations) as $violation) {
        echo "  - {$violation}\n";
    }
    exit(1);
}

echo "No unauthorized superglobal usage detected.\n";
