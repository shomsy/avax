<?php

declare(strict_types=1);

namespace Avax\Tooling\Refactor;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Component Gates Validator
 *
 * Validates components against TODO.md Section 5 requirements:
 * 1. public contract
 * 2. internal runtime behavior
 * 3. fake/local adapter
 * 4. production adapter boundary
 * 5. configuration schema
 * 6. health/doctor check
 * 7. failure model
 * 8. retry/timeout/circuit/backoff policy
 * 9. observability events
 * 10. contract tests
 * 11. failure tests
 * 12. runtime-safety rules
 * 13. example usage
 * 14. documentation
 * 15. operator diagnostics
 */
if ($argc < 2) {
    echo "Usage: php check-component-gates.php <component-path>\n";
    echo "Example: php check-component-gates.php components/API/Contracts\n";
    exit(1);
}

$componentPath = $argv[1];

if (! is_dir($componentPath)) {
    echo "Error: {$componentPath} is not a directory\n";
    exit(1);
}

echo "===========================================\n";
echo "Component Gates Validator\n";
echo "===========================================\n\n";
echo "Component: {$componentPath}\n\n";

$results = [
    'public_contract' => false,
    'internal_runtime' => false,
    'fake_adapter' => false,
    'local_adapter' => false,
    'production_adapter' => false,
    'configuration' => false,
    'health_check' => false,
    'failure_model' => false,
    'retry_policy' => false,
    'observability' => false,
    'contract_tests' => false,
    'failure_tests' => false,
    'runtime_safety' => false,
    'example_usage' => false,
    'documentation' => false,
    'diagnostics' => false,
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($componentPath)
);

$files = [];
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php') {
        $files[] = $file->getPathname();
    }
}

echo 'Scanning '.count($files)." PHP files...\n\n";

foreach ($files as $file) {
    $content = file_get_contents($file);
    $basename = basename((string) $file);

    // 1. Public contract (System/PublicSurface)
    if (stripos((string) $file, 'PublicSurface') !== false || $basename === 'PublicSurface.php') {
        $results['public_contract'] = true;
    }

    // 2. Internal runtime (Capabilities/*)
    if (stripos((string) $file, 'Capabilities') !== false) {
        $results['internal_runtime'] = true;
    }

    // 3. Fake adapter
    if (preg_match('/Fake[A-Z]/', $basename)) {
        $results['fake_adapter'] = true;
    }

    // 4. Local adapter
    if (preg_match('/Local[A-Z]/', $basename)) {
        $results['local_adapter'] = true;
    }

    // 5. Production adapter
    if (preg_match('/(S[3-9]|Production|Aws|Gcp|Azure)[A-Z]/', $basename)) {
        $results['production_adapter'] = true;
    }

    // 6. Configuration
    if (stripos((string) $file, 'Configuration') !== false || stripos($basename, 'Config') !== false) {
        $results['configuration'] = true;
    }

    // 7. Health check
    if (stripos((string) $file, 'Health') !== false || stripos($basename, 'Health') !== false) {
        $results['health_check'] = true;
    }

    // 8. Failure model (Foundation/Failure)
    if (stripos((string) $file, 'Failure') !== false || stripos($basename, 'Exception') !== false) {
        $results['failure_model'] = true;
    }

    // 9. Retry/timeout/circuit policy
    if (preg_match('/(Retry|Timeout|Circuit|Backoff)/i', $basename)) {
        $results['retry_policy'] = true;
    }

    // 10. Observability events
    if (preg_match('/(Event|Trace|Metric|Log)/i', $basename)) {
        $results['observability'] = true;
    }

    // 11. Contract tests
    if (preg_match('/(Contract|Test).*Test\.php/', $basename)) {
        $results['contract_tests'] = true;
    }

    // 12. Failure tests
    if (preg_match('/(Failure|Exception|Error).*Test\.php/', $basename)) {
        $results['failure_tests'] = true;
    }

    // 13. Example usage
    if (stripos((string) $file, 'examples') !== false || stripos($basename, 'Example') !== false) {
        $results['example_usage'] = true;
    }

    // 14. Documentation
    if (preg_match('/how-this-works\.md|README\.md/', $basename)) {
        $results['documentation'] = true;
    }

    // 15. Diagnostics
    if (preg_match('/(Diagnostics|Debug|Inspector)/i', $basename)) {
        $results['diagnostics'] = true;
    }
}

echo "GATES CHECK RESULTS:\n";
echo "-------------------------------------------\n";

$labels = [
    'public_contract' => '1. Public Contract',
    'internal_runtime' => '2. Internal Runtime',
    'fake_adapter' => '3. Fake Adapter',
    'local_adapter' => '3. Local Adapter',
    'production_adapter' => '4. Production Adapter',
    'configuration' => '5. Configuration Schema',
    'health_check' => '6. Health/Doctor Check',
    'failure_model' => '7. Failure Model',
    'retry_policy' => '8. Retry/Timeout/Circuit',
    'observability' => '9. Observability Events',
    'contract_tests' => '10. Contract Tests',
    'failure_tests' => '11. Failure Tests',
    'runtime_safety' => '12. Runtime-Safety Rules',
    'example_usage' => '13. Example Usage',
    'documentation' => '14. Documentation',
    'diagnostics' => '15. Operator Diagnostics',
];

$passed = 0;
$total = count($results);

foreach ($results as $key => $value) {
    $status = $value ? '✅ PASS' : '❌ FAIL';
    $label = $labels[$key] ?? $key;
    echo sprintf('%s: %s%s', $label, $status, PHP_EOL);
    if ($value) {
        $passed++;
    }
}

echo "-------------------------------------------\n";
echo sprintf('Score: %s/%d%s', $passed, $total, PHP_EOL);
echo "-------------------------------------------\n\n";

$critical = ['public_contract', 'internal_runtime', 'configuration', 'health_check', 'failure_model'];
$criticalPassed = 0;

foreach ($critical as $key) {
    if ($results[$key]) {
        $criticalPassed++;
    }
}

echo sprintf('CRITICAL GATES: %s/', $criticalPassed).count($critical)."\n";

if ($passed === $total) {
    echo "\n✅ ALL GATES PASSED\n";
    exit(0);
}

echo "\n⚠️  GATES INCOMPLETE\n";
exit(1);
