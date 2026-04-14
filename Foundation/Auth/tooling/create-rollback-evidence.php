<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateRollbackEvidence;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$rollbackTarget = $argv[1] ?? 'HEAD^';
$buildDirectory = $root . '/build';

if (! is_dir($buildDirectory)) {
    mkdir($buildDirectory, 0777, true);
}

$artifacts = array_values(array_filter([
    $root . '/composer.json',
    $root . '/composer.lock',
    is_file($root . '/docs/STATUS.md') ? $root . '/docs/STATUS.md' : null,
    is_file($root . '/docs/product-boundary.md') ? $root . '/docs/product-boundary.md' : null,
    is_file($root . '/Auth.txt') ? $root . '/Auth.txt' : null,
]));

$evidence = (new GenerateRollbackEvidence())->execute(
    repositoryRoot     : $root,
    rollbackTarget     : is_string($rollbackTarget) && $rollbackTarget !== '' ? $rollbackTarget : 'HEAD^',
    artifacts          : $artifacts,
    validationCommands : [
        'php composer.phar test',
        'php composer.phar analyse',
        'php composer.phar analyse:strict',
    ]
);
file_put_contents($buildDirectory . '/rollback-evidence.json', json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(
    $evidence,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
