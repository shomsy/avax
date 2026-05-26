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

SdlcRuntime::printRuntimeHeader('Construction Checklist Checker');
$files = GitChangedFiles::all($root);
$findings = [];
$warnings = [];

// Validate existing construction checklist evidence
$constructionEvidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'construction-checklist.md')));

$requiredHeadings = ['Task', 'Scope', 'Naming Discipline', 'Minimal Viable Construction', 'Encapsulation', 'Method Length and Complexity', 'Guard Clauses', 'Error Handling', 'Comments vs Self-Explaining Code', 'Tests', 'Review Date'];
foreach ($constructionEvidence as $file) {
    $content = (string) file_get_contents($root.'/'.$file);
    foreach ($requiredHeadings as $heading) {
        if (! str_contains($content, '## '.$heading)) {
            $findings[] = "{$file} missing heading: {$heading}";
        }
    }
}

// For changed production PHP files, require either scenario-input.md, construction-checklist.md, or accepted exception
$productionChanges = [];
foreach ($files as $file) {
    if (! str_ends_with($file, '.php')) {
        continue;
    }
    if (! preg_match('#^(components|framework|src|app|packages)/#', $file)) {
        continue;
    }
    $productionChanges[] = $file;
}

$scenarioEvidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'scenario-input.md')));
$exceptionEvidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && (str_ends_with($file, 'construction-exception.md') || str_ends_with($file, 'accepted-exception.md'))));

if ($productionChanges !== [] && $constructionEvidence === [] && $scenarioEvidence === [] && $exceptionEvidence === []) {
    $message = 'Changed production PHP files require scenario-input.md, construction-checklist.md, or accepted exception evidence.';
    if ($strict) {
        $findings[] = $message;
    } else {
        $warnings[] = $message;
    }
}

foreach ($warnings as $warning) {
    echo "YELLOW: {$warning}\n";
}

if ($findings === []) {
    echo "GREEN: Construction checklist check passed. mode={$mode}; strict=".($strict ? 'yes' : 'no')."; production_changes=".count($productionChanges)."; checklist_evidence=".count($constructionEvidence)."\n";
    exit(0);
}

echo "RED: Construction checklist check failed. mode={$mode}; strict=".($strict ? 'yes' : 'no')."\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
