<?php

declare(strict_types=1);

use Avax\Container\DI\ContainerInterface;

if ($argc < 3) {
    fwrite(stream: STDERR, data: "Usage: php tools/generate-runtime-artifacts.php <fixture> <output-dir>\n");
    exit(1);
}

require_once dirname(path: __DIR__) . '/tests/bootstrap.php';

$fixturePath = (string) ($argv[1] ?? '');
$outputDir   = (string) ($argv[2] ?? '');

if (! is_file(filename: $fixturePath)) {
    fwrite(stream: STDERR, data: "Fixture [{$fixturePath}] was not found.\n");
    exit(1);
}

if ($outputDir === '') {
    fwrite(stream: STDERR, data: "Output directory is required.\n");
    exit(1);
}

$loaded = require $fixturePath;
if (is_callable(value: $loaded)) {
    $loaded = $loaded();
}

if (! $loaded instanceof ContainerInterface) {
    fwrite(stream: STDERR, data: "Fixture [{$fixturePath}] must return a ContainerInterface instance.\n");
    exit(1);
}

if (! is_dir(filename: $outputDir) && ! mkdir(directory: $outputDir, permissions: 0777, recursive: true) && ! is_dir(filename: $outputDir)) {
    fwrite(stream: STDERR, data: "Output directory [{$outputDir}] could not be created.\n");
    exit(1);
}

$container = $loaded;
$container->warmCompiled();

/**
 * @throws JsonException
 */
$writeJson = static function (string $path, array $payload) : void {
    file_put_contents(
        filename: $path,
        data    : json_encode(value: $payload, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL
    );
};

$compileReport = $container->compileReport();
$runtimeReport = $container->runtimeReport();

$writeJson(path: $outputDir . '/compile-report.json', payload: $compileReport?->toArray() ?? []);
$writeJson(path: $outputDir . '/runtime-report.json', payload: $runtimeReport->toArray());
$writeJson(path: $outputDir . '/governance.json', payload: $container->debugGovernance());
$writeJson(path: $outputDir . '/architecture.json', payload: $container->debugArchitecture());
file_put_contents(filename: $outputDir . '/dependency-graph.html', data: $container->exportGraph(format: 'html', kind: 'dependency'));

echo json_encode(
        value: [
            'outputDir' => $outputDir,
            'files'     => [
                'compile-report.json',
                'runtime-report.json',
                'governance.json',
                'architecture.json',
                'dependency-graph.html',
            ],
        ],
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    ) . PHP_EOL;
