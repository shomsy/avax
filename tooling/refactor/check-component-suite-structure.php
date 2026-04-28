<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$componentsRoot = $root . '/components';

$allowedSuites = [
    'Application',
    'HTTP',
    'CLI',
    'DataStack',
    'Identity',
    'Operations',
    'Presentation',
    'DeveloperTools',
];

$allowedRootFiles = [
    'compat.php',
    'new-component.md',
];

$allowedRootDirs = array_merge($allowedSuites, [
    '.idea',
    'storage',
    'tests',
    // Bridge-only legacy roots:
    'DataFoundation',
]);

if (! is_dir($componentsRoot)) {
    fwrite(STDERR, "FAIL\ncomponents root not found: {$componentsRoot}\n");
    exit(1);
}

$entries = array_values(array_diff(scandir($componentsRoot) ?: [], ['.', '..']));

$violations = [];

foreach ($entries as $entry) {
    $path = $componentsRoot . '/' . $entry;

    if (is_dir($path)) {
        if (! in_array($entry, $allowedRootDirs, true)) {
            $violations[] = "forbidden components root dir: components/{$entry}";
        }
        continue;
    }

    if (is_file($path) && ! in_array($entry, $allowedRootFiles, true)) {
        $violations[] = "forbidden components root file: components/{$entry}";
    }
}

foreach ($allowedSuites as $suite) {
    $suitePath = $componentsRoot . '/' . $suite;
    if (! is_dir($suitePath)) {
        $violations[] = "missing suite folder: components/{$suite}";
        continue;
    }
}

if ($violations !== []) {
    fwrite(STDOUT, "FAIL\n");
    foreach ($violations as $v) {
        fwrite(STDOUT, "- {$v}\n");
    }
    exit(1);
}

fwrite(STDOUT, "PASS\n");
exit(0);

