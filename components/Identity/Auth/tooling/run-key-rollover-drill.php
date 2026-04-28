<?php

declare(strict_types=1);

use Avax\Components\Identity\Auth\Integrations\Release\RunKeyRolloverDrill;

require dirname(path: __DIR__) . '/vendor/autoload.php';

$keyRingPath = $argv[1] ?? null;

if (! is_string(value: $keyRingPath) || $keyRingPath === '') {
    fwrite(stream: STDERR, data: "Usage: php tooling/run-key-rollover-drill.php <key-ring.json>\n");
    exit(1);
}

$drill = new RunKeyRolloverDrill();

echo json_encode(
        value: $drill->execute(keyRingPath: $keyRingPath),
        flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) . PHP_EOL;
