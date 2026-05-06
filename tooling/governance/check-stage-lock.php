#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$currentTruth = $root.'/CURRENT_TRUTH.md';

if (! file_exists($currentTruth)) {
    echo "No CURRENT_TRUTH.md found. Stage check skipped.\n";
    exit(0);
}

$content = file_get_contents($currentTruth);

if (! is_string($content)) {
    echo "Could not read CURRENT_TRUTH.md. Stage check failed.\n";
    exit(1);
}

$v1Line      = findLine($content, 'V1 Kernel Green:');
$v2Line      = findLine($content, 'V2 Implementation:') ?? findLine($content, 'V2 implementation:');
$v3Line      = findLine($content, 'V3 Implementation:') ?? findLine($content, 'V3 implementation:');
$activeStage = findActiveStage($content);

$v1Green     = is_string($v1Line) && str_contains($v1Line, 'GREEN') && ! str_contains($v1Line, 'NOT PROVEN');
$v1NotProven = is_string($v1Line) && str_contains($v1Line, 'NOT PROVEN');
$v2Locked    = is_string($v2Line) && str_contains($v2Line, 'LOCKED');
$v3Locked    = is_string($v3Line) && str_contains($v3Line, 'LOCKED');

echo ($v1Line ?? 'V1 Kernel Green: UNKNOWN') . "\n";
echo ($v2Line ?? 'V2 Implementation: UNKNOWN') . "\n";
echo ($v3Line ?? 'V3 Implementation: UNKNOWN') . "\n";
echo 'Active Stage: ' . $activeStage . "\n\n";

if ($v1NotProven) {
    echo "Allowed: active V1/stage work, docs/planning, evidence updates\n";
    echo "FORBIDDEN: V2/V3/V4 production implementation\n";

    if (! $v2Locked || ! $v3Locked) {
        echo "\nStage lock violation: V1 is not proven but V2 or V3 is not locked.\n";
        exit(1);
    }

    echo "\nRun: php tooling/governance/check-stage-lock.php\n";
    exit(0);
}

if ($v1Green) {
    echo "Allowed: next stage work according to EVIDENCE/EXECUTION.md; V2 only when explicitly unlocked there\n";
    echo "FORBIDDEN: V3/V4 production implementation until their locks are lifted\n";
    echo "\nRun: php tooling/governance/check-stage-lock.php\n";
    exit(0);
}

echo "Allowed: docs/planning only until CURRENT_TRUTH.md is clarified\n";
echo "FORBIDDEN: production implementation beyond the active stage\n";
echo "\nStage lock status is UNKNOWN. Clarify CURRENT_TRUTH.md.\n";
exit(1);

function findLine(string $content, string $prefix) : string|null
{
    foreach (explode("\n", $content) as $line) {
        if (str_starts_with(trim($line), $prefix)) {
            return trim($line);
        }
    }

    return null;
}

function findActiveStage(string $content) : string
{
    foreach (explode("\n", $content) as $line) {
        $line = trim($line);

        if (str_contains($line, 'ACTIVE')) {
            return $line;
        }
    }

    return 'UNKNOWN';
}
