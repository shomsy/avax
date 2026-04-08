<?php

declare(strict_types=1);

use Avax\Container\ContainerInterface;

if ($argc < 3) {
    fwrite(STDERR, "Usage: php tools/graph.php <command> <fixture> [args...]\n");
    exit(1);
}

require_once dirname(__DIR__) . '/tests/bootstrap.php';

$command = (string) ($argv[1] ?? '');
$fixturePath = (string) ($argv[2] ?? '');

if (! is_file($fixturePath)) {
    fwrite(STDERR, "Fixture [{$fixturePath}] was not found.\n");
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

$container = $loaded;

$print = static function (mixed $payload) : void {
    if (is_string($payload)) {
        echo $payload;

        return;
    }

    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
};

switch ($command) {
    case 'graph:export':
        $format = (string) ($argv[3] ?? 'json');
        $kind = (string) ($argv[4] ?? 'dependency');
        $id = (string) ($argv[5] ?? '');
        $print($container->exportGraph(format: $format, kind: $kind, id: $id));
        exit(0);

    case 'graph:explore':
        $kind = (string) ($argv[3] ?? 'dependency');
        $id = (string) ($argv[4] ?? '');
        $print($container->exportGraph(format: 'html', kind: $kind, id: $id));
        exit(0);

    case 'graph:diff':
        $format = (string) ($argv[3] ?? 'json');
        $id = (string) ($argv[4] ?? '');
        $print($container->diffGraph(format: $format, id: $id));
        exit(0);

    case 'graph:slice':
        $slice = (string) ($argv[3] ?? '');
        $print($container->showSlice(slice: $slice));
        exit(0);

    case 'graph:policy':
        $format = (string) ($argv[3] ?? 'json');
        $id = (string) ($argv[4] ?? '');
        $print($container->exportGraph(format: $format, kind: 'policy', id: $id));
        exit(0);

    case 'graph:architecture':
        $format = (string) ($argv[3] ?? 'json');
        $id = (string) ($argv[4] ?? '');
        $print($container->exportGraph(format: $format, kind: 'architecture', id: $id));
        exit(0);

    case 'graph:governance':
        $print($container->debugGovernance(id: (string) ($argv[3] ?? '')));
        exit(0);

    case 'why':
        $print($container->why(id: (string) ($argv[3] ?? '')));
        exit(0);

    case 'who-uses':
        $print($container->whoUses(id: (string) ($argv[3] ?? '')));
        exit(0);

    case 'what-breaks-if':
        $print($container->whatBreaksIf(id: (string) ($argv[3] ?? '')));
        exit(0);

    case 'show-owner':
        $print($container->showOwner(id: (string) ($argv[3] ?? '')));
        exit(0);

    case 'show-slice':
        $print($container->showSlice(slice: (string) ($argv[3] ?? '')));
        exit(0);

    case 'group':
        $print($container->debugGroup(group: (string) ($argv[3] ?? '')));
        exit(0);

    case 'selection':
        $print($container->debugSelection(id: (string) ($argv[3] ?? '')));
        exit(0);

    case 'architecture:debug':
        $print($container->debugArchitecture(id: (string) ($argv[3] ?? '')));
        exit(0);

    default:
        fwrite(STDERR, "Unknown command [{$command}].\n");
        exit(1);
}
