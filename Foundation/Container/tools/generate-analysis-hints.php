<?php

declare(strict_types=1);

use Avax\Container\DI\ContainerInterface;

if ($argc < 3) {
    fwrite(stream: STDERR, data: "Usage: php tools/generate-analysis-hints.php <fixture> <output-dir>\n");
    exit(1);
}

require_once dirname(path: __DIR__) . '/tests/bootstrap.php';

$fixturePath = (string) ($argv[1] ?? '');
$outputDir   = rtrim(string: (string) ($argv[2] ?? ''), characters: '/');

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

$container  = $loaded;
$graph      = $container->debugGraph();
$serviceIds = array_keys(array: $graph['graph'] ?? []);
sort(array: $serviceIds);

$runtimeInputs = [];
$conditionals  = [];
foreach ($serviceIds as $serviceId) {
    $plan                      = $container->debugPlan(id: $serviceId);
    $runtimeInputs[$serviceId] = array_filter(
            array   : $plan['constructor'] ?? [],
            callback: static fn (array $parameter) : bool => ($parameter['source'] ?? '') === 'runtime'
        )
            |> array_values(...)
            |> (static fn ($x) => array_map(callback: static fn (array $parameter) : string => (string) ($parameter['inputName'] ?? $parameter['name'] ?? ''), array: $x))
            |> array_values(...);

    foreach ($plan['methods'] ?? [] as $method) {
        foreach ($method['plan']->parameters ?? [] as $parameter) {
            if (($parameter['source'] ?? '') !== 'runtime') {
                continue;
            }

            $runtimeInputs[$serviceId][] = (string) ($parameter['inputName'] ?? $parameter['name'] ?? '');
        }
    }

    $runtimeInputs[$serviceId] = array_filter(
            array   : $runtimeInputs[$serviceId],
            callback: static fn (string $name) : bool => $name !== ''
        )
            |> array_unique(...)
            |> array_values(...);

    $description = $container->describeService(id: $serviceId);
    $conditions  = $description['ownership'] ?? [];
    if (($description['conditions']['active'] ?? true) === false || ($conditions['profiles'] ?? []) !== [] || ($conditions['flags'] ?? []) !== [] || ($conditions['tenants'] ?? []) !== [] || ($conditions['regions'] ?? []) !== [] || ($conditions['modes'] ?? []) !== []) {
        $conditionals[$serviceId] = [
            'profiles' => $conditions['profiles'] ?? [],
            'flags'    => $conditions['flags'] ?? [],
            'tenants'  => $conditions['tenants'] ?? [],
            'regions'  => $conditions['regions'] ?? [],
            'modes'    => $conditions['modes'] ?? [],
            'fallback' => (bool) ($conditions['fallback'] ?? false),
        ];
    }
}

$payload = [
    'schemaVersion' => 1,
    'serviceIds'    => $serviceIds,
    'sliceExports'  => array_map(
        callback: static fn (array $slice) : array => $slice['exports'] ?? [],
        array   : $graph['slices'] ?? []
    ),
    'sliceImports'  => array_map(
        callback: static fn (array $slice) : array => $slice['imports'] ?? [],
        array   : $graph['slices'] ?? []
    ),
    'groups'        => array_map(
        callback: static fn (array $items) : array => array_map(
            callback: static fn (array $item) : string => (string) ($item['serviceId'] ?? ''),
            array   : $items
        ),
        array   : $graph['groups'] ?? []
    ),
    'runtimeInputs' => $runtimeInputs,
    'conditionals'  => $conditionals,
];

if (! is_dir(filename: $outputDir) && ! mkdir(directory: $outputDir, permissions: 0775, recursive: true) && ! is_dir(filename: $outputDir)) {
    fwrite(stream: STDERR, data: "Cannot create output directory [{$outputDir}].\n");
    exit(1);
}

$jsonPath = $outputDir . '/container-static-hints.json';
$stubPath = $outputDir . '/container-static-hints.stub.php';

$serviceIdUnion = implode(separator: '|', array: array_map(
    callback: static fn (string $serviceId) : string => "'" . str_replace(search: "'", replace: "\\'", subject: $serviceId) . "'",
    array   : $serviceIds
));
$groupUnion     = $payload['groups']
        |> array_keys(...)
        |> (static fn ($x) => array_map(callback: static fn (string $group) : string => "'" . str_replace(search: "'", replace: "\\'", subject: $group) . "'", array: $x))
        |> (static fn ($x) => implode(separator: '|', array: $x));
$sliceUnion     = $payload['sliceExports']
        |> array_keys(...)
        |> (static fn ($x) => array_map(callback: static fn (string $slice) : string => "'" . str_replace(search: "'", replace: "\\'", subject: $slice) . "'", array: $x))
        |> (static fn ($x) => implode(separator: '|', array: $x));

$stub = <<<PHP
    <?php
    
    declare(strict_types=1);
    
    /**
     * Generated static analysis hints from canonical authored truth.
     *
     * @phpstan-type ContainerServiceId {$serviceIdUnion}
     * @phpstan-type ContainerGroup {$groupUnion}
     * @phpstan-type ContainerSlice {$sliceUnion}
     * @psalm-type ContainerServiceId = {$serviceIdUnion}
     * @psalm-type ContainerGroup = {$groupUnion}
     * @psalm-type ContainerSlice = {$sliceUnion}
     */
    return %s;
    PHP;

$json = json_encode(value: $payload, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
$stub = sprintf($stub, var_export(value: $payload, return: true)) . PHP_EOL;

file_put_contents(filename: $jsonPath, data: $json, flags: LOCK_EX);
file_put_contents(filename: $stubPath, data: $stub, flags: LOCK_EX);

echo $jsonPath . PHP_EOL;
echo $stubPath . PHP_EOL;
