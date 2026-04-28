<?php

declare(strict_types=1);

use Avax\Components\Identity\Auth\Integrations\Release\GenerateRollbackEvidence;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root           = dirname(path: __DIR__);
$rollbackTarget = $argv[1] ?? 'HEAD^';
$buildDirectory = $root . '/build';

if (! is_dir(filename: $buildDirectory)) {
    mkdir(directory: $buildDirectory, permissions: 0777, recursive: true);
}

$artifacts = array_values(array: array_filter(array: [
                                                         $root . '/composer.json',
                                                         $root . '/composer.lock',
                                                         is_file(filename: $root . '/docs/STATUS.md') ? $root . '/docs/STATUS.md' : null,
                                                         is_file(filename: $root . '/docs/product-boundary.md') ? $root . '/docs/product-boundary.md' : null,
                                                         is_file(filename: $root . '/Auth.txt') ? $root . '/Auth.txt' : null,
                                                     ]));

$evidence = new GenerateRollbackEvidence()->execute(
    repositoryRoot    : $root,
    rollbackTarget    : is_string(value: $rollbackTarget) && $rollbackTarget !== '' ? $rollbackTarget : 'HEAD^',
    artifacts         : $artifacts,
    validationCommands: [
                            'php composer.phar test',
                            'php composer.phar analyse',
                            'php composer.phar analyse:strict',
                        ]
);
file_put_contents(filename: $buildDirectory . '/rollback-evidence.json', data: json_encode(value: $evidence, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(
        value: $evidence,
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
