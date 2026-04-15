<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CreateReleaseProvenance;

require dirname(__DIR__) . '/vendor/autoload.php';

$root           = dirname(__DIR__);
$generator      = new CreateReleaseProvenance();
$buildDirectory = $root . '/build';

if (! is_dir($buildDirectory)) {
    mkdir($buildDirectory, 0777, true);
}

$provenance = $generator->execute(repositoryRoot: $root, validationCommands: [
    'php composer.phar test',
    'php composer.phar analyse',
    'php composer.phar analyse:strict',
]);
file_put_contents($buildDirectory . '/release-provenance.json', json_encode($provenance, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(
        $provenance,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
