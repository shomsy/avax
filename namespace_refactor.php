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
    $content  = file_get_contents($file);
    $original = $content;

    // Sort by length descending to handle longer namespaces first
    uksort($mapping, static fn($a, $b) => strlen($b) - strlen($a));

    foreach ($mapping as $old => $new) {
        // Replace namespace declarations (exact or starting with)
        $content = preg_replace_callback('/^namespace (' . preg_quote($old, '/') . '[^;]*);/m', static function ($matches) use ($old, $new) {
            return 'namespace ' . str_replace($old, $new, $matches[1]) . ';';
        }, $content);
        // Replace use statements (exact match)
        $content = preg_replace('/^use ' . preg_quote($old, '/') . ';/m', 'use ' . $new . ';', $content);
        $content = preg_replace('/^use ' . preg_quote($old, '/') . ' as /m', 'use ' . $new . ' as ', $content);
        // Replace any use that starts with the old namespace
        $content = preg_replace_callback('/^use (' . preg_quote($old, '/') . '[^;]+);/m', static function ($matches) use ($old, $new) {
            return 'use ' . str_replace($old, $new, $matches[1]) . ';';
        }, $content);
        $content = preg_replace_callback('/^use (' . preg_quote($old, '/') . '[^;]+) as /m', static function ($matches) use ($old, $new) {
            return 'use ' . str_replace($old, $new, $matches[1]) . ' as ';
        }, $content);
    }

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "Refactored: $file\n";
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
        echo "$old -> $new\n";
    }
} else {
    echo "Starting namespace refactor...\n";
    scanDirectory(dir: $directory, mapping: $mapping);
    echo "Refactor complete!\n";
}
