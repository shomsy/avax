<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$ownershipFolders = [
    $root . '/docs/framework/System',
    $root . '/docs/framework/System/PublicSurface',
    $root . '/docs/framework/System/PublicSurface/Http',
    $root . '/docs/framework/System/PublicSurface/Console',
    $root . '/docs/framework/System/PublicSurface/Runtime',
    $root . '/docs/framework/System/Capabilities',
    $root . '/docs/framework/System/Capabilities/Runtime',
    $root . '/docs/framework/System/Capabilities/Runtime/Worker',
    $root . '/docs/framework/System/Flows',
    $root . '/docs/framework/System/Flows/BootApplication',
    $root . '/docs/framework/System/Flows/HandleIncomingHttp',
    $root . '/docs/framework/System/Flows/HandleWorkerRequest',
    $root . '/docs/framework/System/Flows/ResetApplicationState',
    $root . '/docs/framework/System/Flows/RunConsoleCommand',
    $root . '/docs/framework/System/Flows/ShutdownRuntime',
    $root . '/docs/framework/System/Configuration',
    $root . '/docs/framework/System/Configuration/BuildApplication',
];

$missing = [];

foreach ($ownershipFolders as $ownershipFolder) {
    if (! is_dir($ownershipFolder)) {
        $missing[] = 'Missing docs folder: ' . str_replace($root . '/', '', $ownershipFolder);

        continue;
    }

    $howThisWorks = $ownershipFolder . '/how-this-works.md';

    if (! is_file($howThisWorks)) {
        $missing[] = 'Missing how-this-works.md: ' . str_replace($root . '/', '', $howThisWorks);
    }
}

if ($missing !== []) {
    fwrite(STDERR, implode(PHP_EOL, $missing) . PHP_EOL);

    exit(1);
}

fwrite(STDOUT, "Documentation ownership checks passed.\n");
