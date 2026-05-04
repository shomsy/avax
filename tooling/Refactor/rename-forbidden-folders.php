<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;
$basePath = dirname(__DIR__, 2);

$renames = [
    'components/Application/Cache/tests/Support' => 'components/Application/Cache/tests/Fakes',
    'components/Identity/Credentials/System/Capabilities/Passkey/Support' => 'components/Identity/Credentials/System/Capabilities/Passkey/TestSupport',
];

foreach ($renames as $from => $to) {
    $fromPath = $basePath . '/' . $from;
    $toPath = $basePath . '/' . $to;

    if (is_dir($fromPath)) {
        rename($fromPath, $toPath);
        echo sprintf('Renamed: %s -> %s%s', $from, $to, PHP_EOL);
    } else {
        echo sprintf('Not found: %s%s', $from, PHP_EOL);
    }
}
