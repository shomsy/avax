<?php

declare(strict_types=1);

use Avax\Container\DI\ContainerInterface;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php tools/generate-analysis-hints.php <fixture> <output-dir>\n");
    exit(1);
}

require_once dirname(__DIR__) . '/tests/bootstrap.php';

$fixturePath = (string) ($argv[1] ?? '');
$outputDir   = rtrim((string) ($argv[2] ?? ''), '/');

if (! is_file($fixturePath)) {
    fwrite(STDERR, "Fixture [{$fixturePath}] was not found.\n");
    exit(1);
}

if ($outputDir === '') {
    fwrite(STDERR, "Output directory is required.\n");
    exit(1);
}

$loaded = require $fixturePath;
if (is_callable($loaded)) {
    $loaded = $loaded();
}

if (! $loaded instanceof ContainerInterface) {
    fwrite(STDERR, "Fixture [{$fixturePath}] must return a ContainerInterface instance.\n");
    exit(1);
}

$container  = $loaded;
$graph      = $container->debugGraph();
$serviceIds = array_keys($graph['graph'] ?? []);
sort($serviceIds);

$runtimeInputs = [];
$conditionals  = [];
foreach ($serviceIds as $serviceId) {
    $plan                      = $container->debugPlan($serviceId);
    $runtimeInputs[$serviceId] = array_values(array_map(
                                                  static fn (array $parameter) : string => (string) ($parameter['inputName'] ?? $parameter['name'] ?? ''),
                                                  array_values(array_filter(
                                                                   $plan['constructor'] ?? [],
                                                                   static fn (array $parameter) : bool => ($parameter['source'] ?? '') === 'runtime'
                                                               ))
                                              ));

    foreach ($plan['methods'] ?? [] as $method) {
        foreach ($method['plan']->parameters ?? [] as $parameter) {
            if (($parameter['source'] ?? '') !== 'runtime') {
                continue;
            }

            $runtimeInputs[$serviceId][] = (string) ($parameter['inputName'] ?? $parameter['name'] ?? '');
        }
    }

    $runtimeInputs[$serviceId] = array_values(array_unique(array_filter(
                                                               $runtimeInputs[$serviceId],
                                                               static fn (string $name) : bool => $name !== ''
                                                           )));

    $description = $container->describeService($serviceId);
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
        static fn (array $slice) : array => $slice['exports'] ?? [],
        $graph['slices'] ?? []
    ),
    'sliceImports'  => array_map(
        static fn (array $slice) : array => $slice['imports'] ?? [],
        $graph['slices'] ?? []
    ),
    'groups'        => array_map(
        static fn (array $items) : array => array_map(
            static fn (array $item) : string => (string) ($item['serviceId'] ?? ''),
            $items
        ),
        $graph['groups'] ?? []
    ),
    'runtimeInputs' => $runtimeInputs,
    'conditionals'  => $conditionals,
];

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, "Cannot create output directory [{$outputDir}].\n");
    exit(1);
}

$jsonPath = $outputDir . '/container-static-hints.json';
$stubPath = $outputDir . '/container-static-hints.stub.php';

$serviceIdUnion = implode('|', array_map(
    static fn (string $serviceId) : string => "'" . str_replace("'", "\\'", $serviceId) . "'",
    $serviceIds
));
$groupUnion     = implode('|', array_map(
    static fn (string $group) : string => "'" . str_replace("'", "\\'", $group) . "'",
    array_keys($payload['groups'])
));
$sliceUnion     = implode('|', array_map(
    static fn (string $slice) : string => "'" . str_replace("'", "\\'", $slice) . "'",
    array_keys($payload['sliceExports'])
));

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

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
$stub = sprintf($stub, var_export($payload, true)) . PHP_EOL;

file_put_contents($jsonPath, $json, LOCK_EX);
file_put_contents($stubPath, $stub, LOCK_EX);

echo $jsonPath . PHP_EOL;
echo $stubPath . PHP_EOL;
