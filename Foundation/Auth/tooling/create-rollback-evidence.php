<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateRollbackEvidence;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$rollbackTarget = $argv[1] ?? 'HEAD^';
$artifacts = array_values(array_filter([
    $root . '/composer.json',
    $root . '/composer.lock',
    is_file($root . '/Auth.txt') ? $root . '/Auth.txt' : null,
]));

echo json_encode(
    (new GenerateRollbackEvidence())->execute(
        repositoryRoot     : $root,
        rollbackTarget     : is_string($rollbackTarget) && $rollbackTarget !== '' ? $rollbackTarget : 'HEAD^',
        artifacts          : $artifacts,
        validationCommands : [
            'php composer.phar test',
            'php composer.phar analyse',
            'php composer.phar analyse:strict',
        ]
    ),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
