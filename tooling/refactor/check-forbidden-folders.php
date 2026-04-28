<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$forbiddenAtRepoRoot = [
    'System',
    'DI',
    'ServerRequest',
    'Auth',
    'Providers',
    'Traits',
    'Writers',
    'Config',
    'Presentation',
    'bootstrap',
    'scripts',
    'tools',
    'public',
];

$violations = [];

foreach ($forbiddenAtRepoRoot as $name) {
    $path = $root . '/' . $name;
    if (is_dir($path)) {
        $violations[] = "forbidden repo-root source folder exists: {$name}/";
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

