<?php

declare(strict_types=1);

$testDir = 'tests';
$legacyNamespaces = [
    'Avax\DataLayer',
    'Avax\DataFoundation',
    'Avax\Foundation\Auth',
    'ServiceProvider',
    'ServiceMap',
    'EntityManager',
    'ResponseFactory',
];

$results = [
    'legacy_usage' => [],
    'invalid_imports' => [],
    'psr4_drift' => [],
];

if (! is_dir($testDir)) {
    exit('❌ Test directory not found!');
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($testDir));

foreach ($iterator as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $content = file_get_contents($path);

    // Check for legacy namespace usage
    foreach ($legacyNamespaces as $ns) {
        if (str_contains($content, $ns)) {
            $results['legacy_usage'][$path][] = $ns;
        }
    }

    // Check if the file's own namespace matches its path (PSR-4 Drift)
    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
        $namespace = $matches[1];
        $expectedSubPath = str_replace('\\', '/', preg_replace('/^Avax\\\\Tests\\\\?/', '', $namespace));
        if (! str_contains($path, $expectedSubPath) && ! str_contains($path, 'Integration')) {
            $results['psr4_drift'][$path] = $namespace;
        }
    }
}

echo "=== TEST INTEGRITY AUDIT ===\n\n";

echo '🚩 FILES WITH LEGACY REFERENCES: '.count($results['legacy_usage'])."\n";
foreach (array_slice($results['legacy_usage'], 0, 10) as $file => $hints) {
    echo "  - $file (Uses: ".implode(', ', array_unique($hints)).")\n";
}
if (count($results['legacy_usage']) > 10) {
    echo '  ... and '.(count($results['legacy_usage']) - 10)." more.\n";
}

echo "\n🚩 PSR-4 DRIFT IN TESTS: ".count($results['psr4_drift'])."\n";
foreach (array_slice($results['psr4_drift'], 0, 10) as $file => $ns) {
    echo "  - $file (Declared NS: $ns)\n";
}

echo "\n🚀 Plan: Map all legacy references to the new 'Screaming' structure.\n";
