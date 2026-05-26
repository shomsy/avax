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

SdlcRuntime::printRuntimeHeader('ADR / Trade-off Evidence Checker');
$files = GitChangedFiles::all($root);
$findings = [];
$warnings = [];

$evidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'adr-tradeoff-decision.md')));

// In strict mode, governance-sensitive changes without ADR evidence are flagged
if ($strict) {
    $governanceSensitive = [];
    foreach ($files as $file) {
        if (
            str_starts_with($file, '.agents/how-to/architecture/')
            || str_starts_with($file, '.agents/how-to/verification/')
            || str_starts_with($file, 'tooling/governance/')
            || str_starts_with($file, 'tooling/sdlc/')
            || $file === 'AGENTS.md'
            || $file === 'ARCHITECTURE.md'
            || $file === '.agents/GOVERNANCE_INDEX.md'
        ) {
            $governanceSensitive[] = $file;
        }
    }
    if ($governanceSensitive !== [] && $evidence === []) {
        $findings[] = 'Governance-sensitive architecture changes require adr-tradeoff-decision.md evidence in strict mode.';
    }
} else {
    // Non-strict: just warn if governance changes exist without ADR evidence
    $governanceSensitive = [];
    foreach ($files as $file) {
        if (str_starts_with($file, '.agents/how-to/architecture/') || str_starts_with($file, 'ARCHITECTURE.md')) {
            $governanceSensitive[] = $file;
        }
    }
    if ($governanceSensitive !== [] && $evidence === []) {
        $warnings[] = 'YELLOW: Architecture-sensitive changes without ADR/tradeoff evidence. Consider adding adr-tradeoff-decision.md.';
    }
}

// Validate evidence structure when present
$requiredHeadings = ['Task', 'Decision', 'Context', 'Forces', 'Options Considered', 'Comparison Matrix', 'Chosen Option', 'Consequences', 'Reversibility', 'Fitness Function', 'Coupling Impact', 'Data Impact', 'Security / Runtime Impact', 'Accepted Debt', 'Owner', 'Review Date'];
foreach ($evidence as $file) {
    $content = (string) file_get_contents($root.'/'.$file);
    foreach ($requiredHeadings as $heading) {
        if (! str_contains($content, '## '.$heading)) {
            $findings[] = "{$file} missing heading: {$heading}";
        }
    }
}

foreach ($warnings as $warning) {
    echo "{$warning}\n";
}

if ($findings === []) {
    echo "GREEN: ADR/trade-off evidence check passed. mode={$mode}; strict=".($strict ? 'yes' : 'no')."; evidence_files=".count($evidence)."\n";
    exit(0);
}

echo "RED: ADR/trade-off evidence check failed. mode={$mode}; strict=".($strict ? 'yes' : 'no')."\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
