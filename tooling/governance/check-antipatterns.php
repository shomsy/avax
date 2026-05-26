#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/sdlc/GitChangedFiles.php';

$root = SdlcRuntime::root();
$mode = 'changed';
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--mode=')) {
        $mode = substr($arg, 7);
    }
}

if (! in_array($mode, ['changed', 'baseline', 'full'], true)) {
    echo "RED: Unsupported mode '{$mode}'. Supported modes: changed, baseline, full.\n";
    exit(1);
}
if ($mode === 'baseline') {
    echo "RED: Baseline mode is not implemented yet for this checker.\n";
    exit(1);
}
if ($mode === 'full') {
    echo "RED: Full mode is not implemented yet for this checker.\n";
    exit(1);
}

SdlcRuntime::printRuntimeHeader('AntiPattern Checker');
$files = GitChangedFiles::all($root);
$findings = [];
$warnings = [];
$expectedDictionary = [
    'architecture-theater.md',
    'analysis-paralysis.md',
    'blob-god-object.md',
    'cut-and-paste-programming.md',
    'fake-abstraction.md',
    'generic-bucket.md',
    'golden-hammer.md',
    'service-locator.md',
    'shallow-tests.md',
    'spaghetti-code.md',
    'stovepipe-system.md',
];
$requiredHeadings = ['## What It Is', '## Symptoms', '## Why It Is Dangerous', '## Common AI Failure Mode', '## How to Fix', '## Allowed Exceptions', '## Severity'];
foreach ($expectedDictionary as $entry) {
    $path = $root.'/.agents/dictionary/antipatterns/'.$entry;
    if (! is_file($path)) {
        $findings[] = "Missing anti-pattern dictionary entry: {$entry}";
        continue;
    }
    $content = (string) file_get_contents($path);
    foreach ($requiredHeadings as $heading) {
        if (! str_contains($content, $heading)) {
            $findings[] = "{$entry} missing heading: {$heading}";
        }
    }
}

$forbiddenSuffixes = ['Manager', 'Helper', 'Util', 'Utils', 'Processor', 'Handler'];
$serviceLocatorPatterns = ['->get(', '->make(', 'ContainerInterface', 'ServiceLocator', 'static::resolve', 'self::resolve'];

foreach ($files as $file) {
    if (! str_ends_with($file, '.php')) {
        continue;
    }
    if (! preg_match('#^(components|framework|src|app|packages|tests)/#', $file)) {
        continue;
    }
    $basename = basename($file, '.php');
    $content = is_file($root.'/'.$file) ? (string) file_get_contents($root.'/'.$file) : '';
    $isTest = str_starts_with($file, 'tests/');
    if (! $isTest) {
        foreach ($forbiddenSuffixes as $suffix) {
            if (str_ends_with($basename, $suffix)) {
                $findings[] = "{$file}: forbidden vague suffix {$suffix}";
            }
        }
        if (str_ends_with($basename, 'Service') && ! str_ends_with($basename, 'ServiceProvider')) {
            $findings[] = "{$file}: forbidden Service suffix without documented exception";
        }
        foreach ($serviceLocatorPatterns as $pattern) {
            if (str_contains($content, $pattern)) {
                $findings[] = "{$file}: probable service locator pattern {$pattern}";
            }
        }
    }
    if (str_contains($content, 'TODO') && ! preg_match('/TODO.*(owner|ticket|review|expires|issue)/i', $content)) {
        $warnings[] = "{$file}: TODO without owner/ticket/review marker";
    }
    if (str_starts_with($file, 'tests/') && preg_match('/assertTrue\s*\(\s*true\s*\)|assertNotNull\s*\(/', $content)) {
        $warnings[] = "{$file}: possible shallow test signal";
    }
}

foreach ($warnings as $warning) {
    echo "YELLOW: {$warning}\n";
}
if ($findings === []) {
    echo "GREEN: Anti-pattern check passed. mode={$mode}; files_scanned=".count($files)."; dictionary_entries=".count($expectedDictionary)."\n";
    exit(0);
}

echo "RED: Anti-pattern check failed. mode={$mode}\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
