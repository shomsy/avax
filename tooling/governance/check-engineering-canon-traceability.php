#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once dirname(__DIR__).'/sdlc/SdlcRuntime.php';

$root = SdlcRuntime::root();
$mode = 'changed';
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--mode=')) {
        $mode = substr($arg, 7);
    }
}

$requiredFiles = [
    '.agents/knowledge/README.md',
    '.agents/knowledge/engineering-canon.md',
    '.agents/knowledge/book-to-rule-traceability.md',
    '.agents/knowledge/source-principles/README.md',
    '.agents/knowledge/source-principles/software-architecture-hard-parts.md',
    '.agents/knowledge/source-principles/designing-data-intensive-applications.md',
    '.agents/knowledge/source-principles/clean-code-code-complete.md',
    '.agents/knowledge/source-principles/pragmatic-programmer.md',
    '.agents/knowledge/source-principles/domain-driven-design.md',
    '.agents/knowledge/source-principles/use-cases-domain-storytelling.md',
    '.agents/knowledge/source-principles/antipatterns-refactoring-patterns.md',
    '.agents/how-to/modeling/how-to-scenario-input.md',
    '.agents/how-to/modeling/how-to-domain-discovery.md',
    '.agents/how-to/architecture/how-to-coupling-governance.md',
    '.agents/how-to/architecture/how-to-architecture-fitness-functions.md',
    '.agents/how-to/architecture/how-to-enterprise-application-patterns.md',
    '.agents/how-to/architecture/how-to-data-correctness.md',
    '.agents/how-to/architecture/how-to-adr-tradeoff-governance.md',
    '.agents/how-to/implementation/how-to-software-construction.md',
    '.agents/how-to/implementation/how-to-refactoring.md',
    '.agents/how-to/implementation/how-to-design-patterns.md',
    '.agents/how-to/runtime/how-to-concurrency-runtime-safety.md',
    '.agents/how-to/verification/how-to-antipattern-detection.md',
    '.agents/how-to/verification/how-to-sdlc-automation.md',
    '.agents/how-to/verification/how-to-sdlc-runners.md',
    '.agents/skills/engineering-canon/SKILL.md',
    '.agents/skills/sdlc-automation/SKILL.md',
    '.agents/templates/evidence/scenario-input.md',
    '.agents/templates/evidence/domain-discovery.md',
    '.agents/templates/evidence/coupling-decision.md',
    '.agents/templates/evidence/architecture-fitness-functions.md',
    '.agents/templates/evidence/antipattern-review.md',
    '.agents/templates/evidence/construction-checklist.md',
    '.agents/templates/evidence/refactoring-safety.md',
    '.agents/templates/evidence/pattern-decision.md',
    '.agents/templates/evidence/enterprise-application-boundary.md',
    '.agents/templates/evidence/data-correctness.md',
    '.agents/templates/evidence/adr-tradeoff-decision.md',
    '.agents/templates/evidence/runtime-concurrency-safety.md',
    'tooling/governance/check-engineering-canon-traceability.php',
    'tooling/governance/check-scenario-input.php',
    'tooling/governance/check-coupling-decisions.php',
    'tooling/governance/check-architecture-fitness-functions.php',
    'tooling/governance/check-antipatterns.php',
    'tooling/governance/check-data-correctness-evidence.php',
    'tooling/governance/check-enterprise-application-boundaries.php',
    'tooling/governance/check-adr-tradeoff-evidence.php',
    'tooling/governance/check-runtime-concurrency-safety.php',
    'tooling/governance/check-refactoring-safety.php',
    'tooling/governance/check-construction-checklist.php',
    'tooling/governance/create-actual-changes-review-pack.php',
    'tooling/sdlc/SdlcRuntime.php',
    'tooling/sdlc/GitChangedFiles.php',
    'tooling/sdlc/validate-changed.php',
    'tooling/sdlc/preflight.php',
    'tooling/sdlc/validate-governance.php',
    'tooling/sdlc/validate-agent-task.php',
];

$markers = [
    'ENGINEERING_CANON_CONVERGENCE',
    'ENGINEERING_CANON_OPERATIONAL_HOWTO',
    'ENGINEERING_CANON_CONSTRUCTION_HOWTO',
    'ENGINEERING_CANON_CHECKERS',
    'ENGINEERING_CANON_SDLC_AUTOMATION',
    'ENGINEERING_CANON_SDLC_RUNNERS',
];

$dictionaryHeadings = [
    '## What It Is',
    '## Symptoms',
    '## Why It Is Dangerous',
    '## Common AI Failure Mode',
    '## How to Fix',
    '## Allowed Exceptions',
    '## Severity',
];

$expectedDictionary = [
    'analysis-paralysis.md',
    'architecture-theater.md',
    'blob-god-object.md',
    'cut-and-paste-programming.md',
    'fake-abstraction.md',
    'generic-bucket.md',
    'golden-hammer.md',
    'service-locator.md',
    'shallow-tests.md',
    'spaghetti-code.md',
    'stovepipe-system.md',
];

$findings = [];

foreach ($requiredFiles as $path) {
    if (! is_file($root.'/'.$path)) {
        $findings[] = "MISSING file: {$path}";
    }
}

foreach (['.agents/GOVERNANCE_INDEX.md', '.agents/how-to/00-how-to-reading-order.md'] as $path) {
    $content = is_file($root.'/'.$path) ? (string) file_get_contents($root.'/'.$path) : '';
    foreach ($markers as $marker) {
        $start = substr_count($content, "<!-- {$marker}_START -->");
        $end = substr_count($content, "<!-- {$marker}_END -->");
        if ($start !== 1 || $end !== 1) {
            $findings[] = "MARKER {$marker} in {$path}: expected once, found start={$start}, end={$end}";
        }
    }
}

$dictionaryFiles = glob($root.'/.agents/dictionary/antipatterns/*.md') ?: [];
$dictionaryNames = array_map(static fn (string $file): string => basename($file), $dictionaryFiles);
sort($dictionaryNames);
if ($dictionaryNames !== $expectedDictionary) {
    $findings[] = 'DICTIONARY entries mismatch. expected='.implode(',', $expectedDictionary).' actual='.implode(',', $dictionaryNames);
}
foreach ($dictionaryFiles as $file) {
    $content = (string) file_get_contents($file);
    if (! preg_match('/^# AntiPattern: .+/m', $content)) {
        $findings[] = 'DICTIONARY missing title format # AntiPattern: <Name> in '.substr($file, strlen($root) + 1);
    }
    foreach ($dictionaryHeadings as $heading) {
        if (! str_contains($content, $heading)) {
            $findings[] = 'DICTIONARY missing heading '.$heading.' in '.substr($file, strlen($root) + 1);
        }
    }
}

if ($findings === []) {
    echo "GREEN: Engineering canon traceability is complete. mode={$mode}\n";
    exit(0);
}

echo "RED: Engineering canon traceability failed. mode={$mode}\n";
foreach ($findings as $finding) {
    echo "- {$finding}\n";
}
exit(1);
