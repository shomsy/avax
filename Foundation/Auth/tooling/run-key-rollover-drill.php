<?php

declare(strict_types=1);

use Avax\Auth\Integrations\Release\RunKeyRolloverDrill;

require dirname(__DIR__) . '/vendor/autoload.php';

$keyRingPath = $argv[1] ?? null;

if (! is_string($keyRingPath) || $keyRingPath === '') {
    fwrite(STDERR, "Usage: php tooling/run-key-rollover-drill.php <key-ring.json>\n");
    exit(1);
}

$drill = new RunKeyRolloverDrill();

echo json_encode(
    $drill->execute($keyRingPath),
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
