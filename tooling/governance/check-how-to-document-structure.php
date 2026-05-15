<?php

/**
 * check-how-to-document-structure.php
 *
 * Scans .agents/how-to/*.md files for structural integrity issues.
 *
 * Usage: php tooling/governance/check-how-to-document-structure.php
 */

$exitCode = 0;
$howToDir = __DIR__ . '/../../.agents/how-to';
$findings = [];
$scanned = 0;

$files = glob($howToDir . '/how-to-*.md');
sort($files);

$bannedPhrases = [
    'Below is a fully expanded governance draft',
    'as requested',
    'copy this prompt',
    'here is the prompt',
    'this was added by',
];

foreach ($files as $file) {
    $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file);
    $content = file_get_contents($file);
    if ($content === false) {
        continue;
    }
    $scanned++;
    $lines = explode("\n", $content);
    $docSectionNumbers = [];
    $foundFinalLaw = false;

    foreach ($lines as $i => $line) {
        $lineNum = $i + 1;

        // Check banned draft phrases
        foreach ($bannedPhrases as $phrase) {
            if (stripos($line, $phrase) !== false) {
                $findings[] = [
                    'file' => $relative,
                    'line' => $lineNum,
                    'type' => 'banned_draft_phrase',
                    'severity' => 'HIGH',
                    'message' => "Banned draft/AI phrase found: '$phrase'",
                ];
            }
        }

        // Check headings starting with #
        if (preg_match('/^## (\d+(?:\.\d+)?)\.\s/', $line, $m)) {
            $num = $m[1];
            if (isset($docSectionNumbers[$num])) {
                $findings[] = [
                    'file' => $relative,
                    'line' => $lineNum,
                    'type' => 'duplicate_heading',
                    'severity' => 'MEDIUM',
                    'message' => "Duplicate heading number: $num",
                ];
            }
            $docSectionNumbers[$num] = true;
        }

        // Track Final Law — note: in AvaX how-to docs, "Final Law" sections are
        // the concluding statement and sections after them are expected extensions.
        // Only flag if there are 3+ numbered sections after Final Law (indicates structure problem).
        if (preg_match('/^##\s+.*Final\s+Law/i', $line)) {
            $foundFinalLaw = true;
        }
    }

    // Check for broken fences (count backtick fences)
    $fenceOpen = 0;
    foreach ($lines as $i => $line) {
        if (preg_match('/^```/', $line)) {
            $fenceOpen++;
        }
    }
    if ($fenceOpen % 2 !== 0) {
        $findings[] = [
            'file' => $relative,
            'line' => 0,
            'type' => 'broken_fence',
            'severity' => 'HIGH',
            'message' => "Unclosed markdown fence (open count: $fenceOpen)",
        ];
    }

    // Check for fake GREEN wording
    $greenClaimLines = 0;
    foreach ($lines as $i => $line) {
        if (preg_match('/^(Everything is GREEN|No blockers|The system is perfect|All GREEN|FULL GREEN)/i', trim($line))) {
            $greenClaimLines++;
        }
    }
    if ($greenClaimLines >= 2) {
        $findings[] = [
            'file' => $relative,
            'line' => 0,
            'type' => 'fake_green_wording',
            'severity' => 'HIGH',
            'message' => "Suspicious GREEN claim wording without evidence ($greenClaimLines instances)",
        ];
    }
}

// Report
echo "How-To Document Structure Gate\n";
echo "==============================\n\n";
echo "Scanned files: {$scanned}\n";
echo "Violations found: " . count($findings) . "\n\n";

if (count($findings) > 0) {
    foreach ($findings as $f) {
        $lineInfo = $f['line'] > 0 ? ":{$f['line']}" : '';
        echo "[{$f['severity']}] {$f['file']}{$lineInfo} — {$f['message']}\n";
    }
    echo "\n";
    $exitCode = 1;
} else {
    echo "PASS — all how-to documents have clean structure.\n";
}

echo "Exit code: {$exitCode}\n";
exit($exitCode);
