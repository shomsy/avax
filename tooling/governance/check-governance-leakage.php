<?php

declare(strict_types=1);

/**
 * Recursively scans generic governance docs for project-specific leakage.
 *
 * Generic docs are every markdown file under .agents/how-to/ except:
 * - .agents/how-to/project/**
 * - .agents/how-to/README.md
 * - .agents/how-to/00-how-to-reading-order.md
 *
 * Project-specific references are allowed only when the line or nearby context
 * is explicitly marked as an example or project overlay note.
 */

$root = dirname(__DIR__, 2);
$howToDir = $root . '/.agents/how-to';

$allowedMarkers = [
    'Example:',
    'AvaX example:',
    'Project-specific example:',
    'Project overlay note:',
];

$patterns = [
    [
        'severity' => 'BLOCKER',
        'pattern' => '/\b(?:AvaX|Avax|avax)\b/',
        'reason' => 'Project name appears in generic governance.',
        'suggested_fix' => 'Move the project-specific rule to .agents/how-to/project/ or mark it as an explicit example.',
        'exclude' => [
            '/\bavax-[a-z0-9-]+\b/i',
        ],
    ],
    [
        'severity' => 'HIGH',
        'pattern' => '#components/(?:Identity|API|Application|Operations|Security|HTTP|Container)\b[^`\s)]*#',
        'reason' => 'Project component path appears in generic governance.',
        'suggested_fix' => 'Use a generic component placeholder or move the rule to project overlay governance.',
    ],
    [
        'severity' => 'HIGH',
        'pattern' => '/\bRuntimeCompilation\b/',
        'reason' => 'Project-specific runtime term appears in generic governance.',
        'suggested_fix' => 'Use generic runtime compilation wording or move the rule to project overlay governance.',
    ],
    [
        'severity' => 'HIGH',
        'pattern' => '#(?:/home/[^`\s)]+/avax-auth-rewrite-v2|avax-auth-rewrite-v2)#',
        'reason' => 'Hardcoded project path appears in generic governance.',
        'suggested_fix' => 'Replace with a repository-root placeholder or move the rule to project overlay governance.',
    ],
    [
        'severity' => 'MEDIUM',
        'pattern' => '#tooling/(?:governance|refactor|security|performance|testing)/[^`\s)]*#',
        'reason' => 'Repository-specific tooling path appears in generic governance.',
        'suggested_fix' => 'Refer to the configured checker by role, or mark the line as a project-specific example.',
    ],
    [
        'severity' => 'MEDIUM',
        'pattern' => '#EVIDENCE/[^`\s)]*#',
        'reason' => 'Repository-specific evidence path appears in generic governance.',
        'suggested_fix' => 'Refer to the configured evidence location, or move the rule to project overlay governance.',
    ],
    [
        'severity' => 'MEDIUM',
        'pattern' => '/\bPublicSurface\b.*\b(?:AvaX|Avax|avax)\b|\b(?:AvaX|Avax|avax)\b.*\bPublicSurface\b/',
        'reason' => 'PublicSurface is tied to project-specific taxonomy in a generic document.',
        'suggested_fix' => 'Keep PublicSurface as a generic boundary term, or move project-specific taxonomy to project overlay governance.',
    ],
];

function relative_path(string $root, string $path): string
{
    return str_replace($root . '/', '', $path);
}

function is_allowed_by_marker(array $lines, int $lineNumber, array $markers): bool
{
    $start = max(0, $lineNumber - 3);
    for ($i = $start; $i <= $lineNumber; $i++) {
        $candidate = trim($lines[$i] ?? '');
        foreach ($markers as $marker) {
            if (str_starts_with($candidate, $marker)) {
                return true;
            }
        }
    }

    return false;
}

function code_block_lines(array $lines): array
{
    $inCodeBlock = false;
    $codeBlockLines = [];

    foreach ($lines as $lineNumber => $line) {
        if (preg_match('/^```/', trim($line))) {
            $codeBlockLines[$lineNumber] = true;
            $inCodeBlock = ! $inCodeBlock;
            continue;
        }

        if ($inCodeBlock) {
            $codeBlockLines[$lineNumber] = true;
        }
    }

    return $codeBlockLines;
}

function excluded_by_pattern(string $line, array $excludes): bool
{
    foreach ($excludes as $exclude) {
        if (preg_match($exclude, $line)) {
            return true;
        }
    }

    return false;
}

$files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($howToDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'md') {
        continue;
    }

    $relative = relative_path($root, $file->getPathname());

    if (str_starts_with($relative, '.agents/how-to/project/')) {
        continue;
    }

    if (in_array(basename($relative), ['README.md', '00-how-to-reading-order.md'], true)) {
        continue;
    }

    if (str_contains($relative, '/generated/') || str_contains($relative, '/evidence/')) {
        continue;
    }

    $files[] = $relative;
}

sort($files);

$findings = [];

echo "================================================================\n";
echo "GOVERNANCE LEAKAGE DETECTION (RECURSIVE)\n";
echo "Scanning all generic .agents/how-to/ markdown files\n";
echo "================================================================\n\n";
echo "Files to scan: " . count($files) . "\n\n";

foreach ($files as $relative) {
    $path = $root . '/' . $relative;
    $lines = explode("\n", (string) file_get_contents($path));
    $codeBlockLines = code_block_lines($lines);
    $fileFindingCount = 0;

    foreach ($lines as $lineNumber => $line) {
        if (isset($codeBlockLines[$lineNumber])) {
            continue;
        }

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern['pattern'], $line, $match)) {
                continue;
            }

            if (excluded_by_pattern($line, $pattern['exclude'] ?? [])) {
                continue;
            }

            if (is_allowed_by_marker($lines, $lineNumber, $allowedMarkers)) {
                continue;
            }

            $fileFindingCount++;
            $findings[] = [
                'file' => $relative,
                'line' => $lineNumber + 1,
                'severity' => $pattern['severity'],
                'match' => $match[0],
                'reason' => $pattern['reason'],
                'suggested_fix' => $pattern['suggested_fix'],
                'content' => trim($line),
            ];
        }
    }

    echo ($fileFindingCount === 0 ? 'CLEAN' : "LEAKS ({$fileFindingCount})") . ": {$relative}\n";
}

echo "\n================================================================\n";
echo "CHECK COMPLETE\n";
echo "Files checked: " . count($files) . "\n";
echo "Files with leaks: " . count(array_unique(array_column($findings, 'file'))) . "\n";
echo "Total findings: " . count($findings) . "\n";

if ($findings === []) {
    echo "GREEN: No unapproved project leakage found in generic governance.\n";
    echo "================================================================\n";
    exit(0);
}

echo "\nFINDINGS\n";
foreach ($findings as $finding) {
    echo "{$finding['severity']}: {$finding['file']}:{$finding['line']}\n";
    echo "  matched_phrase: {$finding['match']}\n";
    echo "  reason: {$finding['reason']}\n";
    echo "  suggested_fix: {$finding['suggested_fix']}\n";
    echo "  line: {$finding['content']}\n";
}

echo "================================================================\n";
exit(1);
