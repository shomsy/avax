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

SdlcRuntime::printRuntimeHeader('Refactoring Safety Checker');
$files = GitChangedFiles::all($root);
$git = escapeshellarg(SdlcRuntime::requireGit());
$findings = [];

// Check existing refactoring-safety.md evidence for required headings
$evidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'refactoring-safety.md')));

$requiredHeadings = ['Task', 'Scope', 'Refactoring Type', 'Classes / Methods Affected', 'Behavioral Equivalence Proof', 'Tests Before', 'Tests After', 'Public API Impact', 'Coupling Impact', 'Runtime Impact', 'Rollback Plan', 'Review Date'];
foreach ($evidence as $file) {
    $content = (string) file_get_contents($root.'/'.$file);
    foreach ($requiredHeadings as $heading) {
        if (! str_contains($content, '## '.$heading)) {
            $findings[] = "{$file} missing heading: {$heading}";
        }
    }
}

// Detect modified production PHP files (not newly added) with movement-like changes
$productionModified = [];
foreach ($files as $file) {
    if (! str_ends_with($file, '.php')) {
        continue;
    }
    if (! preg_match('#^(components|framework|src|app|packages)/#', $file)) {
        continue;
    }
    // Check if file is modified (not new) via git diff
    $diffResult = SdlcRuntime::runCommand($git.' diff --name-only -- '.escapeshellarg($file), $root);
    if ($diffResult['exit_code'] === 0 && str_contains($diffResult['output'], $file)) {
        $productionModified[] = $file;
    }
}

// Heuristic: if production files are modified and no refactoring-safety or scenario evidence exists, warn
$scenarioEvidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'scenario-input.md')));
if ($productionModified !== [] && $evidence === [] && $scenarioEvidence === []) {
    // Conservative: only flag when evidence of method/class movement is detected
    foreach ($productionModified as $file) {
        $diffContent = SdlcRuntime::runCommand($git.' diff -- '.escapeshellarg($file), $root);
        if ($diffContent['exit_code'] === 0 && (
            preg_match('/^-\s*(class|interface|trait|enum)\s+/m', $diffContent['output'])
            || preg_match('/^-\s*(public|protected|private)\s+(static\s+)?function\s+/m', $diffContent['output'])
        )) {
            $findings[] = "{$file}: class/method movement detected without refactoring-safety.md or scenario-input.md evidence.";
        }
    }
}

if ($findings === []) {
    echo "GREEN: Refactoring safety check passed. mode={$mode}; modified_production=".count($productionModified)."; evidence_files=".count($evidence)."\n";
    exit(0);
}

echo "RED: Refactoring safety check failed. mode={$mode}\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
