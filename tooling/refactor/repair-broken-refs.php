<?php

declare(strict_types=1);

$baseDir = dirname(__DIR__, 2);

// Most impactful remaining mappings
$mappings = [
    // Filesystem flat → System/Flows
    'Avax\\Components\\Application\\Filesystem\\Files\\' => 'Avax\\Components\\Application\\Filesystem\\System\\Flows\\Files\\',
    'Avax\\Components\\Application\\Filesystem\\Directories\\' => 'Avax\\Components\\Application\\Filesystem\\System\\Flows\\Directories\\',
    'Avax\\Components\\Application\\Filesystem\\Disks\\' => 'Avax\\Components\\Application\\Filesystem\\System\\Capabilities\\Disks\\',
    'Avax\\Components\\Application\\Filesystem\\Paths\\' => 'Avax\\Components\\Application\\Filesystem\\System\\Capabilities\\Paths\\',
    'Avax\\Components\\Application\\Filesystem\\Filesystem' => 'Avax\\Components\\Application\\Filesystem\\System\\PublicSurface\\FilesystemInterface',

    // Operations Filesystem flat → System/Flows
    'Avax\\Components\\Operations\\Filesystem\\System\\Flows\\Files\\' => 'Avax\\Components\\Application\\Filesystem\\System\\Flows\\Files\\',
    'Avax\\Components\\Operations\\Filesystem\\System\\Flows\\Directories\\' => 'Avax\\Components\\Application\\Filesystem\\System\\Flows\\Directories\\',

    // Container legacy DI namespace → System/Capabilities
    'Avax\\Components\\Application\\Container\\DependencyInjection\\Capability\\Invocation' => 'Avax\\Components\\Application\\Container\\System\\Capabilities\\Execution\\Injection\\Invocation',
    'Avax\\Components\\Application\\Container\\DependencyInjection\\Capability\\Prototypes' => 'Avax\\Components\\Application\\Container\\System\\Capabilities\\Declaration\\Blueprints',
    'Avax\\Components\\Application\\Container\\DependencyInjection\\Capability\\Scopes' => 'Avax\\Components\\Application\\Container\\System\\Capabilities\\Runtime\\Scopes',
    'Avax\\Components\\Application\\Container\\DependencyInjection\\Configuration\\ContainerBuilder' => 'Avax\\Components\\Application\\Container\\System\\Configuration\\ContainerBuilder',
    'Avax\\Components\\Application\\Container\\DependencyInjection\\Configuration\\ContainerConfig' => 'Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\ContainerSettings',
    'Avax\\Components\\Application\\Container\\DependencyInjection\\Configuration\\KernelConfig' => 'Avax\\Components\\Application\\Container\\System\\Capabilities\\Composition\\ContainerSettings',

    // Container legacy flat → System/
    'Avax\\Components\\Container\\DependencyInjection\\' => 'Avax\\Components\\Application\\Container\\System\\',

    // Response flat → HTTP/Response/System
    'Avax\\Components\\HTTP\\Response\\Capabilities\\Streams\\' => 'Avax\\Components\\HTTP\\Response\\System\\Capabilities\\Streaming\\',
    'Avax\\Components\\HTTP\\Response\\Capabilities\\' => 'Avax\\Components\\HTTP\\Response\\System\\Capabilities\\',
    'Avax\\Components\\HTTP\\Response\\Flows\\' => 'Avax\\Components\\HTTP\\Response\\System\\Flows\\',

    // ServerRequest planned-but-never-created subnamespace cleanup
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\Configuration\\' => 'Avax\\Components\\HTTP\\Request\\System\\Configuration\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ProtocolVersion\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestAttributes\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\Body\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestBody\\Parsers\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\Body\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestCookies\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestedInputs\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\RequestData\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestedInputs\\Mapping\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\RequestData\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestedInputs\\Sanitization\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\RequestData\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestHeaders\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\Headers\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\UploadedFiles\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\Files\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestTarget\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\RequestInit' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\RequestInit',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\IncomingRequest\\ServerInit' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\ServerInit',
    'Avax\\Components\\HTTP\\Request\\ServerRequest\\Network\\' => 'Avax\\Components\\HTTP\\Request\\System\\Capabilities\\',

    // DataStack/Database legacy flat
    'Avax\\Components\\DataStack\\Database\\Structures\\' => 'Avax\\Components\\DataStack\\Database\\System\\Capabilities\\',
    'Avax\\Components\\DataStack\\Database\\Composites\\' => 'Avax\\Components\\DataStack\\Database\\System\\Capabilities\\',

    // Text flat
    'Avax\\Components\\Text\\System\\Capabilities\\PatternMatching\\' => 'Avax\\Components\\Application\\Text\\System\\Capabilities\\PatternMatching\\',
    'Avax\\Components\\Text\\System\\PublicSurface\\' => 'Avax\\Components\\Application\\Text\\System\\PublicSurface\\',

    // Framework PublicSurface
    'Avax\\Framework\\System\\PublicSurface\\AppKernel' => 'Avax\\Components\\HTTP\\System\\Capabilities\\Kernel\\AppKernel',
    'Avax\\Framework\\System\\PublicSurface\\HttpKernel' => 'Avax\\Components\\HTTP\\System\\Capabilities\\Kernel\\HttpKernel',
    'Avax\\HTTP\\ResolveRouteFromHttpRequest' => 'Avax\\Components\\HTTP\\System\\Flows\\Routing\\ResolveRouteFromHttpRequest',

    // Identity/Sessions legacy
    'Avax\\Components\\Identity\\Sessions\\System\\' => 'Avax\\Components\\HTTP\\Session\\System\\',

    // Presentation/Views legacy (plural)
    'Avax\\Components\\Presentation\\Views\\System\\' => 'Avax\\Components\\Presentation\\View\\System\\',
];

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
$updated = 0;

foreach ($files as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    if (str_contains($path, '/vendor/')) {
        continue;
    }

    $content = file_get_contents($path);
    $original = $content;

    // Sort mappings by key length (longest first) to avoid partial matches
    uksort($mappings, fn ($a, $b) => strlen($b) - strlen($a));

    foreach ($mappings as $old => $new) {
        $content = str_replace($old, $new, $content);
    }

    if ($content !== $original) {
        echo "Updating $path\n";
        file_put_contents($path, $content);
        $updated++;
    }
}

echo "Bulk repair v4 complete. Updated $updated files.\n";
