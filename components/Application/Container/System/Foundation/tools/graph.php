<?php

declare(strict_types=1);

use Avax\Components\Application\Container\System\ContainerInterface;

if ($argc < 3) {
    fwrite(stream: STDERR, data: "Usage: php tools/graph.php <command> <fixture> [args...]\n");
    exit(1);
}

require_once dirname(path: __DIR__).'/tests/bootstrap.php';

$command = $argv[1] ?? '';
$fixturePath = $argv[2] ?? '';

if (! is_file(filename: $fixturePath)) {
    fwrite(stream: STDERR, data: "Fixture [{$fixturePath}] was not found.\n");
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

$container = $loaded;

/**
 * @throws JsonException
 */
$print = static function (mixed $payload): void {
    if (is_string(value: $payload)) {
        echo $payload;

        return;
    }

    echo json_encode(value: $payload, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;
};

switch ($command) {
    case 'graph:export':
        $format = $argv[3] ?? 'json';
        $kind = $argv[4] ?? 'dependency';
        $id = $argv[5] ?? '';
        $print(payload: $container->exportGraph(format: $format, kind: $kind, id: $id));
        exit(0);

    case 'graph:explore':
        $kind = $argv[3] ?? 'dependency';
        $id = $argv[4] ?? '';
        $print(payload: $container->exportGraph(format: 'html', kind: $kind, id: $id));
        exit(0);

    case 'graph:diff':
        $format = $argv[3] ?? 'json';
        $id = $argv[4] ?? '';
        $print(payload: $container->diffGraph(format: $format, id: $id));
        exit(0);

    case 'graph:slice':
        $slice = $argv[3] ?? '';
        $print(payload: $container->showSlice(slice: $slice));
        exit(0);

    case 'graph:policy':
        $format = $argv[3] ?? 'json';
        $id = $argv[4] ?? '';
        $print(payload: $container->exportGraph(format: $format, kind: 'policy', id: $id));
        exit(0);

    case 'graph:architecture':
        $format = $argv[3] ?? 'json';
        $id = $argv[4] ?? '';
        $print(payload: $container->exportGraph(format: $format, kind: 'architecture', id: $id));
        exit(0);

    case 'graph:governance':
        $print(payload: $container->debugGovernance(id: $argv[3] ?? ''));
        exit(0);

    case 'why':
        $print(payload: $container->why(id: $argv[3] ?? ''));
        exit(0);

    case 'who-uses':
        $print(payload: $container->whoUses(id: $argv[3] ?? ''));
        exit(0);

    case 'what-breaks-if':
        $print(payload: $container->whatBreaksIf(id: $argv[3] ?? ''));
        exit(0);

    case 'show-owner':
        $print(payload: $container->showOwner(id: $argv[3] ?? ''));
        exit(0);

    case 'show-slice':
        $print(payload: $container->showSlice(slice: $argv[3] ?? ''));
        exit(0);

    case 'group':
        $print(payload: $container->debugGroup(group: $argv[3] ?? ''));
        exit(0);

    case 'selection':
        $print(payload: $container->debugSelection(id: $argv[3] ?? ''));
        exit(0);

    case 'architecture:debug':
        $print(payload: $container->debugArchitecture(id: $argv[3] ?? ''));
        exit(0);

    default:
        fwrite(stream: STDERR, data: "Unknown command [{$command}].\n");
        exit(1);
}
