<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\GenerateEvidenceBundle;

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);
$buildDirectory = $root . '/build';

if (! is_dir($buildDirectory)) {
    mkdir($buildDirectory, 0777, true);
}

$bundle = (new GenerateEvidenceBundle())->execute(repositoryRoot: $root);
$outputFile = $buildDirectory . '/evidence-bundle.json';
file_put_contents($outputFile, json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
