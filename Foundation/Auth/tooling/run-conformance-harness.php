<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\RunConformanceHarness;

require dirname(__DIR__) . '/vendor/autoload.php';

$root           = dirname(__DIR__);
$buildDirectory = $root . '/build';

if (! is_dir($buildDirectory)) {
    mkdir($buildDirectory, 0777, true);
}

$report     = (new RunConformanceHarness())->execute(repositoryRoot: $root);
$outputFile = $buildDirectory . '/conformance-report.json';
file_put_contents($outputFile, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($report['overall'] === 'PASSED' ? 0 : 1);
