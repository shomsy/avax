<?php

declare(strict_types=1);

/**
 * Governance Canonical Truth Checker
 *
 * Detects:
 * - root-level how-to shadow files
 * - duplicate canonical governance filenames
 * - stale reading-order references
 * - missing canonical governance files
 * - canonical files not listed in reading order
 * - project-specific docs outside .agents/how-to/project/
 * - evidence files used as mandatory canonical governance
 * - planned docs treated as required docs
 * - duplicate numbering in reading order
 */

$root = dirname(__DIR__, 2);
$howToDir = $root . '/.agents/how-to';
$readingOrderFile = $howToDir . '/00-how-to-reading-order.md';
$indexFile = $root . '/.agents/GOVERNANCE_INDEX.md';

$findings = [];

function add_finding(array &$findings, string $severity, string $finding, string $where, string $why, string $action): void
{
    $findings[] = [
        'severity' => $severity,
        'finding' => $finding,
        'where' => $where,
        'why' => $why,
        'action' => $action,
    ];
}

function relative_path(string $root, string $path): string
{
    return str_replace($root . '/', '', $path);
}

function markdown_files(string $root, string $dir): array
{
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $files[] = relative_path($root, $file->getPathname());
        }
    }

    sort($files);

    return $files;
}

function extract_section(string $content, string $heading): string
{
    $pattern = '/^## ' . preg_quote($heading, '/') . '\R(?P<body>.*?)(?=^## |\z)/ms';
    if (! preg_match($pattern, $content, $match)) {
        return '';
    }

    return $match['body'];
}

echo "=== Governance Canonical Truth Checker ===\n\n";

if (! is_dir($howToDir)) {
    add_finding($findings, 'BLOCKER', 'Missing .agents/how-to directory.', '.agents/how-to', 'Agents cannot load governance.', 'Restore the governance directory.');
} else {
    $allFiles = markdown_files($root, $howToDir);
    echo "Files found: " . count($allFiles) . "\n\n";

    echo "--- Check 1: Root-Level Shadow Governance ---\n";
    $rootLevelFiles = array_values(array_filter($allFiles, static function (string $file): bool {
        return preg_match('#^\.agents/how-to/[^/]+\.md$#', $file) === 1
            && ! in_array($file, ['.agents/how-to/README.md', '.agents/how-to/00-how-to-reading-order.md'], true);
    }));

    if ($rootLevelFiles !== []) {
        foreach ($rootLevelFiles as $file) {
            add_finding(
                $findings,
                'BLOCKER',
                'Root-level shadow governance file exists.',
                $file,
                'Root how-to files bypass categorized ownership and create canonical ambiguity.',
                'Move or merge this file into the correct categorized subfolder.'
            );
        }
        echo "FAIL: Root-level shadow governance detected.\n\n";
    } else {
        echo "PASS: No root-level shadow governance.\n\n";
    }

    echo "--- Check 2: Duplicate Canonical Filenames ---\n";
    $basenames = [];
    foreach ($allFiles as $file) {
        $basenames[basename($file)][] = $file;
    }

    foreach ($basenames as $basename => $paths) {
        if (count($paths) <= 1) {
            continue;
        }

        add_finding(
            $findings,
            'BLOCKER',
            "Duplicate governance filename '{$basename}'.",
            implode(', ', $paths),
            'Duplicate governance filenames drift and confuse recursive loaders.',
            'Keep one canonical file and update references.'
        );
    }

    echo count(array_filter($basenames, static fn (array $paths): bool => count($paths) > 1)) === 0
        ? "PASS: No duplicate filenames.\n\n"
        : "FAIL: Duplicate filenames detected.\n\n";

    echo "--- Check 3: Reading Order Integrity ---\n";
    if (! file_exists($readingOrderFile)) {
        add_finding($findings, 'BLOCKER', 'Missing reading order file.', '.agents/how-to/00-how-to-reading-order.md', 'Agents cannot load governance deterministically.', 'Restore the reading order file.');
        echo "FAIL: Reading order missing.\n\n";
    } else {
        $content = (string) file_get_contents($readingOrderFile);
        preg_match_all('/^(\d+)\.\s+`([^`]+)`(?P<suffix>.*)$/m', $content, $matches, PREG_SET_ORDER);

        $listedFiles = [];
        $numbers = [];
        foreach ($matches as $match) {
            $number = (int) $match[1];
            $path = $match[2];
            $suffix = $match['suffix'] ?? '';
            $numbers[] = $number;
            $listedFiles[] = $path;

            if (str_contains($suffix, 'PLANNED')) {
                add_finding(
                    $findings,
                    'HIGH',
                    'Planned file is listed in mandatory reading order.',
                    $path,
                    'Planned missing files must not be treated as required canonical inputs.',
                    'Move planned files to the Planned, Not Required section.'
                );
            }

            if (! file_exists($root . '/' . $path)) {
                add_finding(
                    $findings,
                    'HIGH',
                    'Reading order references a missing required file.',
                    $path,
                    'Agents following the reading order would load a stale or missing path.',
                    'Restore the file or remove the required reference.'
                );
            }
        }

        $numberCounts = array_count_values($numbers);
        foreach ($numberCounts as $number => $count) {
            if ($count > 1) {
                add_finding(
                    $findings,
                    'BLOCKER',
                    "Duplicate reading-order number {$number}.",
                    $readingOrderFile,
                    'Duplicate numbering makes the loading sequence ambiguous.',
                    'Renumber the reading order so every number is unique.'
                );
            }
        }

        $listedCounts = array_count_values($listedFiles);
        foreach ($listedCounts as $path => $count) {
            if ($count > 1) {
                add_finding(
                    $findings,
                    'HIGH',
                    'Canonical file appears more than once in reading order.',
                    $path,
                    'Duplicate references create conflicting load order.',
                    'List each canonical governance file exactly once.'
                );
            }
        }

        $expected = array_values(array_filter($allFiles, static function (string $file): bool {
            return preg_match('#^\.agents/how-to/[^/]+/[^/]+\.md$#', $file) === 1;
        }));

        foreach (array_diff($expected, $listedFiles) as $missing) {
            add_finding(
                $findings,
                'HIGH',
                'Canonical governance file missing from reading order.',
                $missing,
                'Agents following the reading order would miss an active governance document.',
                'Add the file once to the correct reading-order section.'
            );
        }

        $plannedSection = extract_section($content, 'Planned, Not Required');
        if ($plannedSection === '') {
            add_finding(
                $findings,
                'MEDIUM',
                'Reading order lacks Planned, Not Required section.',
                $readingOrderFile,
                'Planned docs need a non-blocking home to avoid fake missing-file failures.',
                'Add a Planned, Not Required section.'
            );
        }

        $generatedEvidenceSection = extract_section($content, 'Generated Evidence');
        if ($generatedEvidenceSection === '') {
            add_finding(
                $findings,
                'MEDIUM',
                'Reading order lacks Generated Evidence section.',
                $readingOrderFile,
                'Evidence must be distinguished from canonical governance.',
                'Add a Generated Evidence section.'
            );
        }

        $canonicalSection = extract_section($content, 'Canonical vs Support Material');
        $canonicalMandatoryBlock = '';
        if (preg_match('/Canonical governance \(mandatory, must exist\):(?P<body>.*?)(?:\n\n[A-Z][^\n]+:|\z)/s', $canonicalSection, $match)) {
            $canonicalMandatoryBlock = $match['body'];
        }

        foreach (['.agents/management/evidence', 'EVIDENCE/', '_pack/'] as $evidencePath) {
            if (str_contains($canonicalMandatoryBlock, $evidencePath)) {
                add_finding(
                    $findings,
                    'BLOCKER',
                    'Evidence path is listed as mandatory canonical governance.',
                    $evidencePath,
                    'Evidence proves status; it is not canonical governance unless explicitly promoted.',
                    'Move evidence paths to Generated Evidence or support material.'
                );
            }
        }

        echo "PASS: Reading order parsed; findings, if any, are reported below.\n\n";
    }
}

echo "--- Check 4: GOVERNANCE_INDEX.md Reference Integrity ---\n";
if (! file_exists($indexFile)) {
    add_finding($findings, 'BLOCKER', 'Missing governance index.', '.agents/GOVERNANCE_INDEX.md', 'Agents lose canonical routing.', 'Restore .agents/GOVERNANCE_INDEX.md.');
    echo "FAIL: Governance index missing.\n\n";
} else {
    $indexContent = (string) file_get_contents($indexFile);
    preg_match_all('#`([^`]+how-to-[^`]+\.md)`#', $indexContent, $refMatches);
    $references = $refMatches[1] ?? [];

    foreach ($references as $reference) {
        if (str_contains($reference, '*')) {
            continue;
        }

        $candidatePaths = [
            $root . '/' . $reference,
            $howToDir . '/' . $reference,
            $howToDir . '/architecture/' . basename($reference),
            $howToDir . '/components/' . basename($reference),
            $howToDir . '/documentation/' . basename($reference),
            $howToDir . '/implementation/' . basename($reference),
            $howToDir . '/modeling/' . basename($reference),
            $howToDir . '/project/' . basename($reference),
            $howToDir . '/verification/' . basename($reference),
        ];

        $exists = false;
        foreach ($candidatePaths as $candidatePath) {
            if (file_exists($candidatePath)) {
                $exists = true;
                break;
            }
        }

        if (! $exists) {
            add_finding(
                $findings,
                'HIGH',
                'Governance index references a stale how-to path.',
                $reference,
                'Agents following the index would load a missing path.',
                'Update the reference to the canonical categorized path.'
            );
        }
    }

    echo "PASS: Governance index references parsed; findings, if any, are reported below.\n\n";
}

echo "=== Findings ===\n";
if ($findings === []) {
    echo "GREEN: Governance canonical truth is coherent.\n\n";
    echo "Why this matters:\n";
    echo "- No root shadow how-to files remain.\n";
    echo "- Every canonical governance file is listed exactly once.\n";
    echo "- Planned docs are not treated as required governance.\n";
    echo "- Evidence is not mandatory canonical governance.\n";
    exit(0);
}

foreach ($findings as $finding) {
    $blocksGreen = in_array($finding['severity'], ['BLOCKER', 'HIGH'], true) ? 'yes' : 'no';

    echo "severity: {$finding['severity']}\n";
    echo "finding: {$finding['finding']}\n";
    echo "governance_source: AGENTS.md §1B, §1E; .agents/how-to/README.md; .agents/how-to/00-how-to-reading-order.md\n";
    echo "where: {$finding['where']}\n";
    echo "why_it_matters: {$finding['why']}\n";
    echo "required_action: {$finding['action']}\n";
    echo "blocks_GREEN: {$blocksGreen}\n\n";
}

echo "YELLOW: Governance canonical truth has findings requiring correction.\n";
exit(1);
