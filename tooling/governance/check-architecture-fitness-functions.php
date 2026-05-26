#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/sdlc/GitChangedFiles.php';

$root = SdlcRuntime::root();
$mode = 'changed';
$strict = false;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--mode=')) {
        $mode = substr($arg, 7);
    }
    if ($arg === '--strict') {
        $strict = true;
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

SdlcRuntime::printRuntimeHeader('Architecture Fitness Function Checker');
$changed = GitChangedFiles::all($root);
$sensitivePrefixes = [
    '.agents/how-to/',
    '.agents/knowledge/',
    'tooling/sdlc/',
];
$sensitiveExact = ['.agents/GOVERNANCE_INDEX.md', '.agents/how-to/00-how-to-reading-order.md', 'AGENTS.md', 'ARCHITECTURE.md'];
$sensitive = [];
foreach ($changed as $file) {
    foreach ($sensitivePrefixes as $prefix) {
        if (str_starts_with($file, $prefix)) {
            $sensitive[] = $file;
            continue 2;
        }
    }
    if (in_array($file, $sensitiveExact, true) || preg_match('#^tooling/governance/check-.*\.php$#', $file)) {
        $sensitive[] = $file;
    }
}

$evidence = array_values(array_filter($changed, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'architecture-fitness-functions.md')));
$exceptions = array_values(array_filter($changed, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'architecture-fitness-exception.md')));
$findings = [];
if ($sensitive !== [] && $evidence === [] && $exceptions === []) {
    $message = 'Governance/architecture-sensitive changes require changed architecture-fitness-functions.md evidence or architecture-fitness-exception.md.';
    $findings[] = $message;
}

$required = ['Architecture Rule', 'Why This Rule Exists', 'Failure Mode Prevented', 'Fitness Function Type', 'Command / Review Procedure', 'Scope', 'Baseline Mode', 'Changed-Scope Mode', 'Full Mode', 'Expected Pass Signal', 'Expected Fail Signal', 'Evidence Path', 'Owner', 'Review Date'];
foreach ($evidence as $file) {
    $content = (string) file_get_contents($root.'/'.$file);
    foreach ($required as $heading) {
        if (! str_contains($content, '## '.$heading)) {
            $findings[] = "{$file} missing heading: {$heading}";
        }
    }
}

if ($findings === []) {
    echo "GREEN: Architecture fitness function check passed. mode={$mode}; strict=".($strict ? 'yes' : 'no')."; sensitive_changes=".count($sensitive)."; evidence_files=".count($evidence)."\n";
    exit(0);
}

echo "RED: Architecture fitness function check failed. mode={$mode}; strict=".($strict ? 'yes' : 'no')."\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
