#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__.'/SdlcRuntime.php';

$root = SdlcRuntime::root();
$strict = in_array('--strict', array_slice($argv, 1), true);
$expectedRoot = '/home/shomsy/projects/avax-auth-rewrite-v2';
$git = escapeshellarg(SdlcRuntime::requireGit());
$findings = [];
$warnings = [];

$realRoot = realpath($root) ?: $root;
if ($realRoot !== $expectedRoot) {
    $findings[] = "Project root mismatch: expected {$expectedRoot}, got {$realRoot}";
}

$branchResult = SdlcRuntime::runCommand($git.' branch --show-current', $root);
$commitResult = SdlcRuntime::runCommand($git.' rev-parse --short HEAD', $root);
$statusResult = SdlcRuntime::runCommand($git.' status --short', $root);
$branch = $branchResult['output'];
$commit = $commitResult['output'];
$status = $statusResult['output'];

if ($branchResult['exit_code'] !== 0 || $branch === '') {
    $findings[] = 'Unable to determine git branch.';
}
if ($commitResult['exit_code'] !== 0 || $commit === '') {
    $findings[] = 'Unable to determine git commit.';
}
if ($statusResult['exit_code'] !== 0) {
    $findings[] = 'Unable to determine git status.';
}
if ($status !== '') {
    $message = 'Dirty status: '.str_replace("\n", ' | ', $status);
    if ($strict) {
        $findings[] = $message;
    } else {
        $warnings[] = $message;
    }
}

$required = [
    '.agents/knowledge/engineering-canon.md',
    '.agents/knowledge/book-to-rule-traceability.md',
    '.agents/how-to/verification/how-to-sdlc-automation.md',
    '.agents/how-to/verification/how-to-sdlc-runners.md',
    '.agents/skills/engineering-canon/SKILL.md',
    '.agents/skills/sdlc-automation/SKILL.md',
    '.agents/templates/evidence/scenario-input.md',
    '.agents/templates/evidence/domain-discovery.md',
    '.agents/templates/evidence/coupling-decision.md',
    '.agents/templates/evidence/architecture-fitness-functions.md',
    '.agents/templates/evidence/antipattern-review.md',
    '.agents/templates/evidence/enterprise-application-boundary.md',
    '.agents/templates/evidence/data-correctness.md',
    '.agents/templates/evidence/adr-tradeoff-decision.md',
    '.agents/templates/evidence/runtime-concurrency-safety.md',
    'tooling/sdlc/SdlcRuntime.php',
    'tooling/sdlc/GitChangedFiles.php',
    'tooling/sdlc/validate-changed.php',
    'tooling/sdlc/validate-governance.php',
    'tooling/sdlc/validate-agent-task.php',
];
foreach ($required as $path) {
    if (! is_file($root.'/'.$path)) {
        $findings[] = "Missing required SDLC file: {$path}";
    }
}

$shadowFiles = glob($root.'/.agents/how-to/how-to-*.md') ?: [];
foreach ($shadowFiles as $shadowFile) {
    $findings[] = 'Root how-to shadow file exists: '.substr($shadowFile, strlen($root) + 1);
}

SdlcRuntime::printRuntimeHeader('SDLC Preflight');
echo "root={$realRoot}\n";
echo "branch={$branch}\n";
echo "commit={$commit}\n";
foreach ($warnings as $warning) {
    echo "YELLOW: {$warning}\n";
}

if ($findings === []) {
    echo "GREEN: Preflight passed. strict=".($strict ? 'yes' : 'no')."\n";
    exit(0);
}

echo "RED: Preflight failed. strict=".($strict ? 'yes' : 'no')."\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);

