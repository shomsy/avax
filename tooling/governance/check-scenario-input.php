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

function missingScenarioHeadings(string $path, array $headings): array
{
    $content = is_file($path) ? (string) file_get_contents($path) : '';
    $missing = [];
    foreach ($headings as $heading) {
        if (! preg_match('/^\#\#\s+'.preg_quote($heading, '/').'/m', $content)) {
            $missing[] = $heading;
        }
    }

    return $missing;
}

function checkScenarioQuality(string $path, string $relativeName, array &$findings): void
{
    $content = is_file($path) ? (string) file_get_contents($path) : '';
    if ($content === '') {
        $findings[] = "{$relativeName} is empty.";
        return;
    }

    // Extract sections based on ## headers
    $sections = [];
    $lines = explode("\n", $content);
    $currentHeader = null;
    $currentContent = [];

    foreach ($lines as $line) {
        if (preg_match('/^##\s+(.+)$/', trim($line), $matches)) {
            if ($currentHeader !== null) {
                $sections[$currentHeader] = trim(implode("\n", $currentContent));
            }
            $currentHeader = trim($matches[1]);
            $currentContent = [];
        } elseif ($currentHeader !== null) {
            $currentContent[] = $line;
        }
    }
    if ($currentHeader !== null) {
        $sections[$currentHeader] = trim(implode("\n", $currentContent));
    }

    // Help helper function to verify non-empty sections that aren't just comments/placeholders
    $isMeaningful = function (?string $text): bool {
        if ($text === null) {
            return false;
        }
        $clean = preg_replace('/<!--.*?-->/s', '', $text);
        $clean = trim(str_replace(['*', '-', '_'], '', $clean));
        return $clean !== '';
    };

    // 1. Primary Actor
    $actor = $sections['Primary Actor'] ?? null;
    if (! $isMeaningful($actor)) {
        $findings[] = "{$relativeName}: Primary Actor section is empty or contains only comments/placeholders.";
    }

    // 2. Actor Goal
    $goal = $sections['Actor Goal'] ?? null;
    if (! $isMeaningful($goal)) {
        $findings[] = "{$relativeName}: Actor Goal section is empty or contains only comments/placeholders.";
    }

    // 3. Main Success Scenario steps
    $mss = $sections['Main Success Scenario'] ?? '';
    $isMechanical = (bool) preg_match('/mechanical\s+exception/i', $mss);
    if (! $isMechanical) {
        preg_match_all('/^\d+\.\s/m', $mss, $stepMatches);
        $stepsCount = count($stepMatches[0]);
        if ($stepsCount < 3 || $stepsCount > 9) {
            $findings[] = "{$relativeName}: Main Success Scenario must have between 3 and 9 numbered steps (found {$stepsCount}), unless explicitly marked as mechanical exception.";
        }
    }

    // 4. Acceptance Criteria
    $ac = $sections['Acceptance Criteria'] ?? null;
    if (! $isMeaningful($ac)) {
        $findings[] = "{$relativeName}: Acceptance Criteria section is empty or contains only comments/placeholders.";
    }

    // 5. Sensitivity section verification
    $secSens = $sections['Security Sensitivity'] ?? '';
    $dataSens = $sections['Data / State Mutation Sensitivity'] ?? '';
    $runSens = $sections['Runtime / Concurrency Sensitivity'] ?? '';

    $classifyPattern = '/\b(YES|NO|N\/A)\b/i';
    if (! preg_match($classifyPattern, $secSens, $secMatches)) {
        $findings[] = "{$relativeName}: Security Sensitivity section must explicitly classify YES, NO, or N/A.";
    }
    if (! preg_match($classifyPattern, $dataSens, $dataMatches)) {
        $findings[] = "{$relativeName}: Data / State Mutation Sensitivity section must explicitly classify YES, NO, or N/A.";
    }
    if (! preg_match($classifyPattern, $runSens, $runMatches)) {
        $findings[] = "{$relativeName}: Runtime / Concurrency Sensitivity section must explicitly classify YES, NO, or N/A.";
    }

    $isYes = fn(string $val): bool => stripos($val, 'YES') !== false;

    // 6. Security Sensitivity check
    if ($isYes($secSens)) {
        $failPaths = $sections['Extension / Failure Paths'] ?? '';
        if (! $isMeaningful($failPaths)) {
            $findings[] = "{$relativeName}: Security-sensitive scenario requires a non-empty Extension / Failure Paths section.";
        } else {
            $securityKeywords = '/\b(denial|reject|rejection|fail-closed|unauthorized|forbidden|block|abort|verify|fail)\b/i';
            if (! preg_match($securityKeywords, $failPaths) && ! preg_match($securityKeywords, $content)) {
                $findings[] = "{$relativeName}: Security-sensitive scenario must mention denial/rejection/fail-closed/unauthorized/forbidden or equivalent in failure paths.";
            }
        }
    }

    // 7. Data Sensitivity check
    if ($isYes($dataSens)) {
        $dataKeywords = '/\b(transaction|idempotency|retry|failure|rollback|acid|isolation|lock|n\/a)\b/i';
        if (! preg_match($dataKeywords, $content)) {
            $findings[] = "{$relativeName}: Data-sensitive scenario must mention transaction/idempotency/retry/failure or explicitly N/A with reason.";
        }
    }

    // 8. Runtime Sensitivity check
    if ($isYes($runSens)) {
        $runKeywords = '/\b(scope|reset|runtime|concurrency|async|fiber|thread|worker|reactphp|swoole|lock|eventloop|n\/a)\b/i';
        if (! preg_match($runKeywords, $content)) {
            $findings[] = "{$relativeName}: Runtime-sensitive scenario must mention scope/reset/runtime/concurrency or explicitly N/A with reason.";
        }
    }
}

SdlcRuntime::printRuntimeHeader('Scenario Input Checker');
$files = GitChangedFiles::all($root);
$productionPrefixes = ['components/', 'framework/', 'src/', 'app/', 'packages/'];
$excludedPrefixes = ['.agents/', 'tooling/', 'tests/', 'docs/', 'vendor/', '_pack/', 'cache/', 'coverage/', 'tmp/'];
$productionBehavior = [];

foreach ($files as $file) {
    if (! str_ends_with($file, '.php')) {
        continue;
    }
    foreach ($excludedPrefixes as $prefix) {
        if (str_starts_with($file, $prefix)) {
            continue 2;
        }
    }
    foreach ($productionPrefixes as $prefix) {
        if (str_starts_with($file, $prefix)) {
            $productionBehavior[] = $file;
            break;
        }
    }
}

$scenarioEvidence = array_values(array_filter($files, static fn (string $file): bool => str_starts_with($file, '.agents/management/evidence/generated/') && str_ends_with($file, 'scenario-input.md')));
$findings = [];

if ($productionBehavior !== [] && $scenarioEvidence === []) {
    $findings[] = 'Changed production behavior requires changed scenario-input.md evidence.';
}

$requiredHeadings = [
    'Task',
    'Scope',
    'System Boundary',
    'Primary Actor',
    'Actor Goal',
    'Stakeholders and Interests',
    'Preconditions',
    'Success Guarantees',
    'Minimal Failure Guarantees',
    'Main Success Scenario',
    'Extension / Failure Paths',
    'Security Sensitivity',
    'Data / State Mutation Sensitivity',
    'Runtime / Concurrency Sensitivity',
    'Observability Requirement',
    'Acceptance Criteria',
    'Planned Tests',
    'Out of Scope',
];

foreach ($scenarioEvidence as $file) {
    $path = $root.'/'.$file;
    $missing = missingScenarioHeadings($path, $requiredHeadings);
    if ($missing !== []) {
        $findings[] = $file.' missing headings: '.implode(', ', $missing);
    } else {
        checkScenarioQuality($path, $file, $findings);
    }
}

if ($findings === []) {
    echo "GREEN: Scenario input check passed. mode={$mode}; production_behavior_changes=".count($productionBehavior)."; evidence_files=".count($scenarioEvidence)."\n";
    exit(0);
}

echo "RED: Scenario input check failed. mode={$mode}\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
