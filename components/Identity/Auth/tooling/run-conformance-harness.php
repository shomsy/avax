<?php

declare(strict_types=1);

use Avax\Components\Identity\Auth\Integrations\Release\RunConformanceHarness;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root           = dirname(path: __DIR__);
$buildDirectory = $root . '/build';

if (! is_dir(filename: $buildDirectory)) {
    mkdir(directory: $buildDirectory, permissions: 0777, recursive: true);
}

$report     = new RunConformanceHarness()->execute(repositoryRoot: $root);
$outputFile = $buildDirectory . '/conformance-report.json';
file_put_contents(filename: $outputFile, data: json_encode(value: $report, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(value: $report, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($report['overall'] === 'PASSED' ? 0 : 1);
