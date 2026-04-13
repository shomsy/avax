<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CreateReleaseProvenance;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$generator = new CreateReleaseProvenance();

echo json_encode(
    $generator->execute(repositoryRoot: $root, validationCommands: [
        'php composer.phar test',
        'php composer.phar analyse',
        'php composer.phar analyse:strict',
    ]),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
