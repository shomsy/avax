<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateReleaseSbom;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root           = dirname(path: __DIR__);
$generator      = new GenerateReleaseSbom();
$buildDirectory = $root . '/build';

if (! is_dir(filename: $buildDirectory)) {
    mkdir(directory: $buildDirectory, permissions: 0777, recursive: true);
}

$sbom = $generator->execute(composerJsonPath: $root . '/composer.json', composerLockPath: $root . '/composer.lock');
file_put_contents(filename: $buildDirectory . '/sbom.json', data: json_encode(value: $sbom, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(
        value: $sbom,
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
