<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateEvidenceBundle;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root           = dirname(path: __DIR__);
$buildDirectory = $root . '/build';

if (! is_dir(filename: $buildDirectory)) {
    mkdir(directory: $buildDirectory, permissions: 0777, recursive: true);
}

$bundle = new GenerateEvidenceBundle()->execute(repositoryRoot: $root);
$outputFile = $buildDirectory . '/evidence-bundle.json';
file_put_contents(filename: $outputFile, data: json_encode(value: $bundle, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(value: $bundle, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
