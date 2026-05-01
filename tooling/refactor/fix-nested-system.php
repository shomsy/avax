<?php

declare(strict_types=1);

$root = getcwd();

$nestedMoves = [
    'components/Identity/Access/System/Capabilities/Policy/System/Capabilities'                        => 'components/Identity/Access/System/Capabilities/Policy/Capabilities',
    'components/Identity/Access/System/Capabilities/Policy/System/PublicSurface'                       => 'components/Identity/Access/System/Capabilities/Policy/PublicSurface',
    'components/Identity/Tokens/System/Capabilities/JwtAuth/System/Capabilities'                       => 'components/Identity/Tokens/System/Capabilities/JwtAuth/Capabilities',
    'components/Identity/Tokens/System/Capabilities/JwtAuth/System/PublicSurface'                      => 'components/Identity/Tokens/System/Capabilities/JwtAuth/PublicSurface',
    'components/DataStack/Database/System/Capabilities/QueryGovernance/System/Capabilities'            => 'components/DataStack/Database/System/Capabilities/QueryGovernance/Capabilities',
    'components/DataStack/Database/System/Capabilities/QueryGovernance/System/PublicSurface'           => 'components/DataStack/Database/System/Capabilities/QueryGovernance/PublicSurface',
    'components/DeveloperTools/Testing/System/Capabilities/ContractTesting/System/Capabilities'        => 'components/DeveloperTools/Testing/System/Capabilities/ContractTesting/Capabilities',
    'components/DeveloperTools/Testing/System/Capabilities/ContractTesting/System/PublicSurface'       => 'components/DeveloperTools/Testing/System/Capabilities/ContractTesting/PublicSurface',
    'components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/System/Capabilities'   => 'components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/Capabilities',
    'components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/System/PublicSurface'  => 'components/DeveloperTools/Diagnostics/System/Capabilities/ScalingReadiness/PublicSurface',
    'components/Application/Config/System/Capabilities/EnvironmentAwareness/System/Capabilities'       => 'components/Application/Config/System/Capabilities/EnvironmentAwareness/Capabilities',
    'components/Application/Config/System/Capabilities/EnvironmentAwareness/System/PublicSurface'      => 'components/Application/Config/System/Capabilities/EnvironmentAwareness/PublicSurface',
    'components/Operations/Resilience/System/Capabilities/Fallback/System/PublicSurface'               => 'components/Operations/Resilience/System/Capabilities/Fallback/PublicSurface',
    'components/Operations/Resilience/System/Capabilities/Idempotency/System/Capabilities'             => 'components/Operations/Resilience/System/Capabilities/Idempotency/Capabilities',
    'components/Operations/Resilience/System/Capabilities/Idempotency/System/PublicSurface'            => 'components/Operations/Resilience/System/Capabilities/Idempotency/PublicSurface',
    'components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/System/Capabilities'  => 'components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/Capabilities',
    'components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/System/PublicSurface' => 'components/Operations/ApplicationWorkflow/System/Capabilities/Orchestration/PublicSurface',
    'components/Operations/Queue/System/Capabilities/TaskDispatch/System/Capabilities'                 => 'components/Operations/Queue/System/Capabilities/TaskDispatch/Capabilities',
    'components/Operations/Queue/System/Capabilities/TaskDispatch/System/PublicSurface'                => 'components/Operations/Queue/System/Capabilities/TaskDispatch/PublicSurface',
];

$dryRun = ! in_array('--apply', $argv, true);

echo "Phase 1: Fix Nested System Folders\n";
echo "Mode: " . ($dryRun ? "DRY-RUN" : "APPLY") . "\n\n";

foreach ($nestedMoves as $from => $to) {
    $fromPath = $root . '/' . $from;
    $toPath   = $root . '/' . $to;

    if (! is_dir($fromPath)) {
        continue;
    }

    $op = "MOVE {$from} -> {$to}";
    echo ($dryRun ? "[dry-run] " : "") . $op . "\n";

    if (! $dryRun) {
        if (! is_dir(dirname($toPath))) {
            mkdir(dirname($toPath), 0777, true);
        }

        $items = scandir($fromPath);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $src = $fromPath . '/' . $item;
            $dst = $toPath . '/' . $item;

            if (is_dir($src)) {
                rename($src, $dst);
            } elseif (is_file($src)) {
                rename($src, $dst);
            }
        }

        @rmdir($fromPath);
    }
}

echo "\n";
echo $dryRun
    ? "Dry-run complete. Run with --apply to apply.\n"
    : "Phase 1 complete.\n";
