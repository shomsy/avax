<?php

/**
 * check-security-commit-block-readiness.php
 *
 * Verifies that the security commit block governance rules exist in all required documents.
 *
 * Usage: php tooling/governance/check-security-commit-block-readiness.php
 */

$exitCode = 0;
$howToDir = __DIR__ . '/../../.agents/how-to';

echo "Security Commit Block Readiness Gate\n";
echo "=====================================\n\n";

$checks = [
    'how-to-system-security.md' => [
        ['pattern' => '/Security Must Scream/i', 'label' => 'Security Must Scream Rule'],
        ['pattern' => '/Security Commit Block/i', 'label' => 'Security Commit Block Rule'],
    ],
    'how-to-git.md' => [
        ['pattern' => '/Security Commit Block/i', 'label' => 'Security Commit Block Rule'],
        ['pattern' => '/GREEN commit with unresolved security issue/i', 'label' => 'GREEN commit forbidden rule'],
    ],
    'how-to-code-review.md' => [
        ['pattern' => '/Security Review Trigger/i', 'label' => 'Security Review Trigger Rule'],
        ['pattern' => '/Security Commit Block/i', 'label' => 'Security Commit Block Rule'],
    ],
    'how-to-production-readiness.md' => [
        ['pattern' => '/Security Must Scream/i', 'label' => 'Security Must Scream Rule'],
        ['pattern' => '/GREEN commit with unresolved security issue/i', 'label' => 'GREEN commit forbidden rule'],
        ['pattern' => '/Security Commit Block/i', 'label' => 'Security Commit Block Rule'],
    ],
];

$allPass = true;
foreach ($checks as $file => $patterns) {
    $path = $howToDir . '/' . $file;
    if (!file_exists($path)) {
        echo "[BLOCKER] Missing document: $file\n";
        $allPass = false;
        continue;
    }
    $content = file_get_contents($path);
    foreach ($patterns as $check) {
        if (preg_match($check['pattern'], $content)) {
            echo "[PASS] $file — {$check['label']}\n";
        } else {
            echo "[FAIL] $file — {$check['label']} NOT FOUND\n";
            $allPass = false;
            $exitCode = 1;
        }
    }
}

echo "\nOverall: " . ($allPass ? 'PASS — all security commit block rules present' : 'FAIL') . "\n";
echo "Exit code: {$exitCode}\n";
exit($exitCode);
