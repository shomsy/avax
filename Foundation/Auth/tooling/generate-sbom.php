<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateReleaseSbom;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$generator = new GenerateReleaseSbom();

echo json_encode(
    $generator->execute($root . '/composer.json', $root . '/composer.lock'),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
