#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__.'/SdlcRuntime.php';

$root = SdlcRuntime::root();
$git = escapeshellarg(SdlcRuntime::requireGit());

function runGovernanceCommand(string $label, string $command, string $cwd, bool $required): bool
{
    $result = SdlcRuntime::runCommand($command, $cwd);
    $status = $result['exit_code'] === 0 ? 'PASS' : ($required ? 'FAIL' : 'YELLOW');
    echo "[{$status}] {$label}: exit={$result['exit_code']}\n";
    if ($result['output'] !== '') {
        echo $result['output']."\n";
    }

    return $required && $result['exit_code'] !== 0;
}

SdlcRuntime::printRuntimeHeader('SDLC Governance Validation');
$php = escapeshellarg(PHP_BINARY);
$commands = [
    ['git diff --check', $git.' diff --check', true],
    ['canon traceability', $php.' tooling/governance/check-engineering-canon-traceability.php', true],
    ['canonical truth', $php.' tooling/governance/check-governance-canonical-truth.php', false],
    ['leakage', $php.' tooling/governance/check-governance-leakage.php', false],
    ['index current', $php.' tooling/governance/check-governance-index-current.php', false],
    ['stage lock', $php.' tooling/governance/check-stage-lock.php', false],
    ['scenario input', $php.' tooling/governance/check-scenario-input.php --mode=changed', true],
    ['coupling decisions', $php.' tooling/governance/check-coupling-decisions.php --mode=changed', true],
    ['architecture fitness functions', $php.' tooling/governance/check-architecture-fitness-functions.php --mode=changed --strict', true],
    ['anti-patterns', $php.' tooling/governance/check-antipatterns.php --mode=changed', true],
    ['data correctness', $php.' tooling/governance/check-data-correctness-evidence.php --mode=changed', true],
    ['enterprise application boundaries', $php.' tooling/governance/check-enterprise-application-boundaries.php --mode=changed', true],
    ['runtime concurrency safety', $php.' tooling/governance/check-runtime-concurrency-safety.php --mode=changed', true],
    ['refactoring safety', $php.' tooling/governance/check-refactoring-safety.php --mode=changed', true],
    ['construction checklist', $php.' tooling/governance/check-construction-checklist.php --mode=changed', false],
    ['adr tradeoff decisions', $php.' tooling/governance/check-adr-tradeoff-evidence.php --mode=changed', false], // Tradeoff starts as YELLOW warnings unless strict is required
];

$failed = false;
foreach ($commands as [$label, $command, $required]) {
    $failed = runGovernanceCommand($label, $command, $root, $required) || $failed;
}

if ($failed) {
    echo "RED_BLOCKED: Governance validation failed required commands.\n";
    exit(1);
}

echo "GREEN_CHANGED_SCOPE_READY: Governance validation passed required commands.\n";
exit(0);
