<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\RunKeyCompromiseDrill;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$beforePath = $argv[1] ?? null;
$afterPath  = $argv[2] ?? null;

if (! is_string(value: $beforePath) || $beforePath === '' || ! is_string(value: $afterPath) || $afterPath === '') {
    fwrite(stream: STDERR, data: "Usage: php tooling/run-key-compromise-drill.php <before.json> <after.json>\n");
    exit(1);
}

$drill = new RunKeyCompromiseDrill();

echo json_encode(
        value: $drill->execute(preRotationKeyRingPath: $beforePath, postCompromiseKeyRingPath: $afterPath),
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
