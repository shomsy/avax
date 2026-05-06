<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$sourceToDocs = [
    'framework/System' => 'docs/framework/System',
    'framework/System/PublicSurface' => 'docs/framework/System/PublicSurface',
    'framework/System/PublicSurface/Http' => 'docs/framework/System/PublicSurface/Http',
    'framework/System/PublicSurface/Console' => 'docs/framework/System/PublicSurface/Console',
    'framework/System/PublicSurface/Runtime' => 'docs/framework/System/PublicSurface/Runtime',
    'framework/System/Capabilities' => 'docs/framework/System/Capabilities',
    'framework/System/Capabilities/Runtime' => 'docs/framework/System/Capabilities/Runtime',
    'framework/System/Capabilities/Runtime/Worker' => 'docs/framework/System/Capabilities/Runtime/Worker',
    'framework/System/Flows' => 'docs/framework/System/Flows',
    'framework/System/Flows/BootApplication' => 'docs/framework/System/Flows/BootApplication',
    'framework/System/Flows/HandleIncomingHttp' => 'docs/framework/System/Flows/HandleIncomingHttp',
    'framework/System/Flows/HandleWorkerRequest' => 'docs/framework/System/Flows/HandleWorkerRequest',
    'framework/System/Flows/ResetApplicationState' => 'docs/framework/System/Flows/ResetApplicationState',
    'framework/System/Flows/RunConsoleCommand' => 'docs/framework/System/Flows/RunConsoleCommand',
    'framework/System/Flows/ShutdownRuntime' => 'docs/framework/System/Flows/ShutdownRuntime',
    'framework/System/Configuration' => 'docs/framework/System/Configuration',
    'framework/System/Configuration/BuildApplication' => 'docs/framework/System/Configuration/BuildApplication',
];

$missing = [];

foreach ($sourceToDocs as $source => $docs) {
    if (! is_dir($root.'/'.$source)) {
        continue;
    }

    if (! is_dir($root.'/'.$docs)) {
        $missing[] = sprintf('Missing docs mirror for %s -> %s', $source, $docs);
    }
}

if ($missing !== []) {
    fwrite(STDERR, implode(PHP_EOL, $missing).PHP_EOL);

    exit(1);
}

fwrite(STDOUT, "Documentation mirror checks passed.\n");
