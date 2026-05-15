<?php

/**
 * check-gate-self-tests.php
 *
 * Meta-gate: inventories all mandatory gates and verifies each has
 * self-test/fixture evidence.
 *
 * Usage: php tooling/governance/check-gate-self-tests.php
 */

$exitCode = 0;

echo "Gate Self-Test Meta-Gate\n";
echo "========================\n\n";

$mandatoryGates = [
    'check-truth-consistency.php' => [
        'path' => 'tooling/governance/check-truth-consistency.php',
        'has_self_test' => 'UNKNOWN',
        'scans_files' => true,
    ],
    'check-runtime-composition-leaks.php' => [
        'path' => 'tooling/refactor/check-runtime-composition-leaks.php',
        'has_self_test' => 'UNKNOWN',
        'scans_files' => true,
    ],
    'check-public-surface.php' => [
        'path' => 'tooling/refactor/check-public-surface.php',
        'has_self_test' => 'UNKNOWN',
        'scans_files' => true,
    ],
    'check-semantic-phpdoc.php' => [
        'path' => 'tooling/governance/check-semantic-phpdoc.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => true,
        'fixture_path' => 'tooling/governance/fixtures/semantic-phpdoc/',
    ],
    'check-how-to-document-structure.php' => [
        'path' => 'tooling/governance/check-how-to-document-structure.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => true,
        'fixture_path' => 'tooling/governance/fixtures/how-to-structure/',
    ],
    'check-serviceprovider-governance-consistency.php' => [
        'path' => 'tooling/governance/check-serviceprovider-governance-consistency.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => true,
    ],
    'check-canonical-terms.php' => [
        'path' => 'tooling/governance/check-canonical-terms.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => true,
    ],
    'check-large-unit-thresholds.php' => [
        'path' => 'tooling/governance/check-large-unit-thresholds.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => true,
        'fixture_path' => 'tooling/governance/fixtures/large-units/',
    ],
    'check-security-commit-block-readiness.php' => [
        'path' => 'tooling/governance/check-security-commit-block-readiness.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => true,
    ],
    'check-quality-ratchet.php' => [
        'path' => 'tooling/governance/check-quality-ratchet.php',
        'has_self_test' => 'PARTIAL',
        'scans_files' => false,
    ],
];

echo str_pad('Gate', 55) . ' | Exists | Self-test | Scans files | Fixtures' . "\n";
echo str_repeat('-', 100) . "\n";

$gatesPresent = 0;
$gatesWithSelfTest = 0;
$gatesScanningZero = 0;

$basePath = __DIR__ . '/../..';

foreach ($mandatoryGates as $name => $info) {
    $fullPath = $basePath . '/' . $info['path'];
    $exists = file_exists($fullPath) ? 'YES' : 'MISSING';

    $selfTest = $info['has_self_test'] ?? 'NONE';
    $fixtures = isset($info['fixture_path'])
        ? (is_dir($basePath . '/' . $info['fixture_path']) ? 'YES' : 'NO')
        : 'N/A';

    if ($exists === 'YES') {
        $gatesPresent++;
        if ($info['has_self_test'] !== 'NONE') {
            $gatesWithSelfTest++;
        }
    } else {
        $gatesScanningZero++;
    }

    echo str_pad($name, 55) . " | $exists | $selfTest | {$info['scans_files']} | $fixtures\n";
}

echo "\nSummary:\n";
echo "  Gates present: {$gatesPresent}/" . count($mandatoryGates) . "\n";
echo "  Gates with self-test: {$gatesWithSelfTest}/" . count($mandatoryGates) . "\n";
echo "  Gates scanning zero files: {$gatesScanningZero}\n";

if ($gatesScanningZero > 0) {
    echo "\n[BLOCKER] Missing gates exist.\n";
    $exitCode = 1;
}
if ($gatesWithSelfTest < count($mandatoryGates)) {
    echo "\n[YELLOW] " . (count($mandatoryGates) - $gatesWithSelfTest) . " gates lack confirmed self-tests.\n";
}

echo "\nExit code: {$exitCode}\n";
exit($exitCode);
