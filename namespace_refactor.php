<?php

declare(strict_types=1);

/**
 * Namespace refactor script for Container DSL evolution
 * Maps old namespaces to new DSL-based namespaces
 */
$mapping = [
    // Sub-namespace refinements (longer first)
    'Avax\\Container\\Runtime\\Resolve'         => 'Avax\\Container\\Actions\\Resolve',
    'Avax\\Container\\Runtime\\Engine'          => 'Avax\\Container\\Actions\\Resolve',
    'Avax\\Container\\Runtime\\Build'           => 'Avax\\Container\\Actions\\Invoke',
    'Avax\\Container\\Planning\\MakePlan'       => 'Avax\\Container\\Think\\Prototype',
    'Avax\\Container\\Planning\\Cache'          => 'Avax\\Container\\Think\\Cache',
    'Avax\\Container\\Diagnostics\\Telemetry'   => 'Avax\\Container\\Observe\\Metrics',
    'Avax\\Container\\Diagnostics\\Debug'       => 'Avax\\Container\\Observe\\Inspect',
    'Avax\\Container\\Policy\\Security'         => 'Avax\\Container\\Guard\\Enforce',
    'Avax\\Container\\Behavior\\Lifecycle'      => 'Avax\\Container\\Operate\\Boot',
    'Avax\\Container\\Behavior\\Policy'         => 'Avax\\Container\\Guard\\Rules',
    'Avax\\Container\\Read\\DependencyInjector' => 'Avax\\Container\\Actions\\Inject',
    'Avax\\Container\\Read\\Resolver'           => 'Avax\\Container\\Actions\\Resolve',
    'Avax\\Container\\Read\\Traits'             => 'Avax\\Container\\Actions\\Lazy',
    'Avax\\Container\\Write\\KeepIn'            => 'Avax\\Container\\Define\\Store',
    'Avax\\Container\\Services\\Definition'     => 'Avax\\Container\\Define\\Store',
    'Avax\\Container\\Services\\Lifecycle'      => 'Avax\\Container\\Operate\\Boot',
    'Avax\\Container\\Validate\\Telemetry'      => 'Avax\\Container\\Observe\\Metrics',
    'Avax\\Container\\Execution\\Proxy'         => 'Avax\\Container\\Actions\\Lazy',
    'Avax\\Container\\Execution\\Invoker'       => 'Avax\\Container\\Actions\\Invoke',
    'Avax\\Container\\Analysis\\Dumper'         => 'Avax\\Container\\Think\\Prototype',
    'Avax\\Container\\Analysis\\Cache'          => 'Avax\\Container\\Think\\Cache',
    'Avax\\Container\\Application\\Provider'    => 'Avax\\Container\\Operate\\Boot',
    'Avax\\Container\\Kernel\\Bootstrap'        => 'Avax\\Container\\Operate\\Boot',

    // Top-level namespace changes
    'Avax\\Container\\Application'              => 'Avax\\Container\\Operate\\Boot',
    'Avax\\Container\\Analysis'                 => 'Avax\\Container\\Think',
    'Avax\\Container\\Attributes'               => 'Avax\\Container\\Core\\Attribute',
    'Avax\\Container\\Behavior'                 => 'Avax\\Container\\Operate',
    'Avax\\Container\\Builder'                  => 'Avax\\Container\\Operate\\Boot',
    'Avax\\Container\\Console'                  => 'Avax\\Container\\Tools\\Console',
    'Avax\\Container\\Diagnostics'              => 'Avax\\Container\\Observe',
    'Avax\\Container\\Execution'                => 'Avax\\Container\\Actions',
    'Avax\\Container\\Kernel'                   => 'Avax\\Container\\Operate\\Boot',
    'Avax\\Container\\Lifecycle'                => 'Avax\\Container\\Operate',
    'Avax\\Container\\Planning'                 => 'Avax\\Container\\Think',
    'Avax\\Container\\Policy'                   => 'Avax\\Container\\Guard',
    'Avax\\Container\\Read'                     => 'Avax\\Container\\Actions',
    'Avax\\Container\\Runtime'                  => 'Avax\\Container\\Actions',
    'Avax\\Container\\Security'                 => 'Avax\\Container\\Guard',
    'Avax\\Container\\Services'                 => 'Avax\\Container\\Define',
    'Avax\\Container\\Support'                  => 'Avax\\Container\\Core',
    'Avax\\Container\\Validate'                 => 'Avax\\Container\\Guard',
    'Avax\\Container\\Write'                    => 'Avax\\Container\\Define',
];

$directory = __DIR__ . '/Foundation/Container';

function refactorFile(string $file, array $mapping) : void
{
    $content  = file_get_contents(filename: $file);
    $original = $content;

    // Sort by length descending to handle longer namespaces first
    uksort(array: $mapping, callback: static fn ($a, $b) => strlen(string: $b) - strlen(string: $a));

    foreach ($mapping as $old => $new) {
        // Replace namespace declarations (exact or starting with)
        $content = preg_replace_callback(pattern: '/^namespace (' . preg_quote(str: $old, delimiter: '/') . '[^;]*);/m', callback: static function ($matches) use ($old, $new) {
            return 'namespace ' . str_replace(search: $old, replace: $new, subject: $matches[1]) . ';';
        },                               subject: $content);
        // Replace use statements (exact match)
        $content = preg_replace(pattern: '/^use ' . preg_quote(str: $old, delimiter: '/') . ';/m', replacement: 'use ' . $new . ';', subject: $content);
        $content = preg_replace(pattern: '/^use ' . preg_quote(str: $old, delimiter: '/') . ' as /m', replacement: 'use ' . $new . ' as ', subject: $content);
        // Replace any use that starts with the old namespace
        $content = preg_replace_callback(pattern: '/^use (' . preg_quote(str: $old, delimiter: '/') . '[^;]+);/m', callback: static function ($matches) use ($old, $new) {
            return 'use ' . str_replace(search: $old, replace: $new, subject: $matches[1]) . ';';
        },                               subject: $content);
        $content = preg_replace_callback(pattern: '/^use (' . preg_quote(str: $old, delimiter: '/') . '[^;]+) as /m', callback: static function ($matches) use ($old, $new) {
            return 'use ' . str_replace(search: $old, replace: $new, subject: $matches[1]) . ' as ';
        },                               subject: $content);
    }

    if ($content !== $original) {
        file_put_contents(filename: $file, data: $content);
        echo "Refactored: {$file}\n";
    }
}

function scanDirectory(string $dir, array $mapping) : void
{
    $iterator = new RecursiveIteratorIterator(iterator: new RecursiveDirectoryIterator(directory: $dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            refactorFile(file: $file->getPathname(), mapping: $mapping);
        }
    }
}

if ($argc > 1 && $argv[1] === '--dry-run') {
    echo "Dry run mode - showing what would be changed:\n";
    // For dry run, just show the mapping
    foreach ($mapping as $old => $new) {
        echo "{$old} -> {$new}\n";
    }
} else {
    echo "Starting namespace refactor...\n";
    scanDirectory(dir: $directory, mapping: $mapping);
    echo "Refactor complete!\n";
}
