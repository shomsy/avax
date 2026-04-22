<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\CreateReleaseProvenance;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root           = dirname(path: __DIR__);
$generator      = new CreateReleaseProvenance();
$buildDirectory = $root . '/build';

if (! is_dir(filename: $buildDirectory)) {
    mkdir(directory: $buildDirectory, permissions: 0777, recursive: true);
}

$provenance = $generator->execute(repositoryRoot: $root, validationCommands: [
    'php composer.phar test',
    'php composer.phar analyse',
    'php composer.phar analyse:strict',
]);
file_put_contents(filename: $buildDirectory . '/release-provenance.json', data: json_encode(value: $provenance, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(
        value: $provenance,
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
