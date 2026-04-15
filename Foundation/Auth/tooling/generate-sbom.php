<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateReleaseSbom;

require dirname(__DIR__) . '/vendor/autoload.php';

$root           = dirname(__DIR__);
$generator      = new GenerateReleaseSbom();
$buildDirectory = $root . '/build';

if (! is_dir($buildDirectory)) {
    mkdir($buildDirectory, 0777, true);
}

$sbom = $generator->execute(composerJsonPath: $root . '/composer.json', composerLockPath: $root . '/composer.lock');
file_put_contents($buildDirectory . '/sbom.json', json_encode($sbom, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(
        $sbom,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
