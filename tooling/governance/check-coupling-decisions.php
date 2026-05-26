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

SdlcRuntime::printRuntimeHeader('Coupling Decision Checker');
$files = GitChangedFiles::all($root);
$signals = ['PublicSurface', 'Adapter', 'Provider', 'Factory', 'Builder', 'Runtime', 'Event', 'Queue', 'Cache', 'Http', 'Database', 'Identity', 'Security', 'Auth', 'interface ', 'implements ', 'ServiceProvider', 'ContainerInterface'];
$sensitive = [];
foreach ($files as $file) {
    if (! str_ends_with($file, '.php') || ! preg_match('#^(components|framework|src|app|packages)/#', $file)) {
        continue;
    }
    $content = is_file($root.'/'.$file) ? (string) file_get_contents($root.'/'.$file) : '';
    foreach ($signals as $signal) {
        if (str_contains($file, $signal) || str_contains($content, $signal)) {
            $sensitive[] = $file;
            break;
        }
    }
}

$evidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'coupling-decision.md')));
$findings = [];
if ($sensitive !== [] && $evidence === []) {
    $findings[] = 'Coupling-sensitive production changes require changed coupling-decision.md evidence.';
}

$required = ['Scope', 'Boundary', 'Coupling Types', 'Accepted Coupling', 'Rejected Coupling', 'Dependency Direction', 'Runtime Impact', 'Test / Fitness Proof', 'Risk', 'Review Date'];
foreach ($evidence as $file) {
    $content = (string) file_get_contents($root.'/'.$file);
    foreach ($required as $heading) {
        if (! str_contains($content, '## '.$heading)) {
            $findings[] = "{$file} missing heading: {$heading}";
        }
    }
}

if ($findings === []) {
    echo "GREEN: Coupling decision check passed. mode={$mode}; sensitive_changes=".count($sensitive)."; evidence_files=".count($evidence)."\n";
    exit(0);
}

echo "RED: Coupling decision check failed. mode={$mode}\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
