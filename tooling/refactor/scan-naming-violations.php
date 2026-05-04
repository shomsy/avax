<?php

declare(strict_types=1);

/**
 * Naming convention scanner strictly following AGENTS.md and how-to-coding-standards.md
 */
$baseDir = dirname(dirname(__DIR__));

// Section 21: Forbidden Generic Names (mostly for folders/buckets)
$forbiddenBuckets = [
    'Services', 'Helpers', 'Utils', 'Common', 'Misc', 'Managers', 'Stuff', 
    'Shared', 'Base', 'Core', 'SharedThings', 'General', 'InternalHelpers'
];

// Section 22.2: Bad examples (mostly for files/classes)
$badExamples = [
    'ServiceManager', 'CommonUtils', 'SharedService', 'CoreStuff', 
    'DataHelpers', 'BaseHandler'
];

// User-defined rules from previous interactions
$userForbidden = [
    'Adapter', 'Proxy'
];

$violations = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($it as $info) {
    $path = $info->getPathname();
    $relPath = str_replace($baseDir . '/', '', $path);
    
    // Ignore internal/third-party
    if (
        str_contains($path, '/vendor/') || 
        str_contains($path, '/tests/') || 
        str_contains($path, '/.git/') || 
        str_contains($path, '/.kilo/') ||
        str_contains($path, '/.agents/') ||
        str_contains($path, '/tooling/')
    ) {
        continue;
    }

    $name = $info->getBasename();
    $nameNoExt = $info->isFile() ? $info->getBasename('.php') : $name;

    // Check buckets (folders)
    if ($info->isDir()) {
        foreach ($forbiddenBuckets as $b) {
            if (strcasecmp($name, $b) === 0) {
                $violations[] = ['path' => $relPath, 'type' => 'Folder', 'violation' => "Forbidden Bucket Name: $b"];
            }
        }
    }

    // Check bad examples and user forbidden suffixes
    foreach ($badExamples as $bad) {
        if (stripos($nameNoExt, $bad) !== false) {
            $violations[] = ['path' => $relPath, 'type' => $info->isDir() ? 'Folder' : 'File', 'violation' => "Bad Example Match: $bad"];
        }
    }

    foreach ($userForbidden as $uf) {
        if (stripos($nameNoExt, $uf) !== false) {
            // Special case: ignore if it's part of a valid noun that isn't just a suffix
            // But usually Adapter/Proxy are suffixes here.
            $violations[] = ['path' => $relPath, 'type' => $info->isDir() ? 'Folder' : 'File', 'violation' => "User Prohibited: $uf"];
        }
    }
    
    // Rule: Folder says flow or capability (nouns/verbs)
    // This is hard to automate, but we can flag very generic ones.
}

echo "=== STRICT NAMING CONVENTION VIOLATIONS ===\n\n";
if (empty($violations)) {
    echo "No strict violations found.\n";
} else {
    foreach ($violations as $v) {
        echo "{$v['type']}: {$v['path']} -> {$v['violation']}\n";
    }
    echo "\nTotal: " . count($violations) . "\n";
}
