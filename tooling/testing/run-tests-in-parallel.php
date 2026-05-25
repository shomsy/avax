<?php

declare(strict_types=1);

/**
 * Run PHPUnit tests in parallel using Symfony Process.
 *
 * Groups test files and runs each group as a separate PHPUnit invocation.
 * Collects and aggregates results from all parallel processes.
 *
 * Usage:
 *   php tooling/testing/run-tests-in-parallel.php
 *   php tooling/testing/run-tests-in-parallel.php --processes 8
 *   php tooling/testing/run-tests-in-parallel.php --exclude-group serial,process,slow
 *
 * Exit codes:
 *   0 — All tests passed
 *   1 — One or more tests failed
 */

require dirname(__DIR__, 2).'/vendor/autoload.php';

use Symfony\Component\Process\Process;

// ─── Parse options ─────────────────────────────────────────────────────────

$options = getopt('', ['processes:', 'exclude-group:', 'testsuite:']);
$processCount = isset($options['processes']) ? (int) $options['processes'] : 0;
$excludeGroup = is_string($options['exclude-group'] ?? null) ? $options['exclude-group'] : '';
$testsuite = is_string($options['testsuite'] ?? null) ? $options['testsuite'] : '';

if ($processCount <= 0) {
    $processCount = (int) shell_exec('nproc 2>/dev/null || echo 4') ?: 4;
}

$projectRoot = dirname(__DIR__, 2);

// ─── Collect test files ────────────────────────────────────────────────────

if ($testsuite !== '') {
    // Use PHPUnit --list-tests --testsuite to discover files, then chunk them
    $testFiles = listTestsuiteFiles($projectRoot, $testsuite, $excludeGroup);
    $useTestsuite = false;  // We'll pass files directly, not --testsuite
} else {
    $testFiles = collectTestFiles("{$projectRoot}/tests");
    $useTestsuite = false;

    // Apply exclude-group filter by scanning test file contents for #[Group] attributes
    if ($excludeGroup !== '') {
        $excludedGroups = array_map('trim', explode(',', $excludeGroup));
        $testFiles = filterExcludedGroups($testFiles, $excludedGroups);
    }
}

$totalFiles = count($testFiles);

if ($totalFiles === 0) {
    echo "No test files found.\n";
    exit(1);
}

// ─── Split into groups ─────────────────────────────────────────────────────

$groups = array_chunk($testFiles, max(1, (int) ceil($totalFiles / $processCount)));

$groupCount = count($groups);

$label = "{$totalFiles} test files";
if ($testsuite !== '') {
    $label = "testsuite '{$testsuite}' ({$totalFiles} files)";
}
echo "Running {$label} in {$groupCount} parallel processes";
if ($excludeGroup !== '') {
    echo " (excluded groups: {$excludeGroup})";
}
echo "...\n\n";

// ─── Start parallel processes ──────────────────────────────────────────────

$phpunit = "{$projectRoot}/vendor/bin/phpunit";
$processes = [];

foreach ($groups as $i => $files) {
    $cmd = ['php', '-d', 'memory_limit=512M', $phpunit, '--no-coverage', '--do-not-cache-result', ...$files];

    $process = new Process($cmd, $projectRoot);
    $process->setTimeout(300);
    $process->start();
    $processes[$i] = $process;
    echo "  Started group ".($i + 1)." (".count($files)." files)\n";
}

$startTime = microtime(true);

// Wait for all processes to complete
foreach ($processes as $i => $process) {
    $process->wait();
}

$totalTime = microtime(true) - $startTime;

// ─── Aggregate results ─────────────────────────────────────────────────────

$totalTests = 0;
$totalAssertions = 0;
$totalFailures = 0;
$totalErrors = 0;
$hasFailure = false;
$failureOutput = '';

foreach ($processes as $i => $process) {
    $output = $process->getOutput();
    $errorOutput = $process->getErrorOutput();
    $combined = $output.$errorOutput;

    // Extract summary line: "Tests: N, Assertions: M, Failures: F, Errors: E, ..."
    // PHPUnit 10.x format: "Tests: 9451, Assertions: 27244, Failures: 12, Risky: 1."
    // Also matches: "(9451 tests, 27244 assertions)"
    if (preg_match('/Tests:\s*(\d+)/', $combined, $m)) {
        $totalTests += (int) $m[1];
    } elseif (preg_match('/\((\d+)\s+tests?,\s*(\d+)\s+assertions?\)/', $combined, $m)) {
        $totalTests += (int) $m[1];
        $totalAssertions += (int) $m[2];
    }

    if (preg_match('/Assertions:\s*(\d+)/', $combined, $m)) {
        $totalAssertions += (int) $m[1];
    }

    if (preg_match('/Failures:\s*(\d+)/', $combined, $m)) {
        $totalFailures += (int) $m[1];
    }
    if (preg_match('/Errors:\s*(\d+)/', $combined, $m)) {
        $totalErrors += (int) $m[1];
    }

    if ($process->getExitCode() !== 0) {
        $hasFailure = true;
        $failureOutput .= "\n--- Group ".($i + 1)." (exit code: ".$process->getExitCode().") ---\n".$output.$errorOutput."\n";
    }
}

// ─── Output results ────────────────────────────────────────────────────────

echo "\n";

if ($failureOutput !== '') {
    echo $failureOutput;
}

$status = $hasFailure ? 'FAIL' : 'PASS';
$riskyNote = '';
if (preg_match_all('/Risky:\s*(\d+)/', implode('', array_map(
    static fn ($p) => $p->getOutput().$p->getErrorOutput(),
    $processes
)), $riskyMatches)) {
    $totalRisky = array_sum($riskyMatches[1]);
    if ($totalRisky > 0) {
        $riskyNote = ", Risky: {$totalRisky}";
    }
}

printf(
    "%s — %d tests, %d assertions, %d failures, %d errors%s in %.3fs (%d processes)\n",
    $status,
    $totalTests,
    $totalAssertions,
    $totalFailures,
    $totalErrors,
    $riskyNote,
    $totalTime,
    $processCount,
);

exit($hasFailure ? 1 : 0);

// ─── Helpers ───────────────────────────────────────────────────────────────

/**
 * @return list<string>
 */
function collectTestFiles(string $testsDir): array
{
    if (!is_dir($testsDir)) {
        return [];
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($testsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    $files = [];
    /** @var SplFileInfo $file */
    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Test.php')) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

/**
 * Filter out test files that contain #[Group] attributes matching excluded groups.
 *
 * @param list<string> $testFiles
 * @param list<string> $excludedGroups
 * @return list<string>
 */
function filterExcludedGroups(array $testFiles, array $excludedGroups): array
{
    $filtered = [];
    $groupPattern = '/#\[Group\(\s*[\'"]('.implode('|', array_map('preg_quote', $excludedGroups)).')[\'"]\s*\)\]/i';

    foreach ($testFiles as $file) {
        $content = file_get_contents($file);
        if ($content === false) {
            $filtered[] = $file;
            continue;
        }

        // If file has an excluded group attribute, skip it
        if (preg_match($groupPattern, $content)) {
            continue;
        }

        $filtered[] = $file;
    }

    return $filtered;
}

/**
 * Use PHPUnit --list-tests --testsuite to discover files for a testsuite.
 *
 * Returns absolute paths to test files, suitable for chunking.
 *
 * @param string $projectRoot
 * @param string $testsuite
 * @param string $excludeGroup
 * @return list<string>
 */
function listTestsuiteFiles(string $projectRoot, string $testsuite, string $excludeGroup): array
{
    $phpunit = "{$projectRoot}/vendor/bin/phpunit";
    $cmd = ['php', $phpunit, '--no-coverage', '--testsuite', $testsuite, '--list-tests'];

    $process = new Process($cmd, $projectRoot);
    $process->setTimeout(60);
    $process->run();

    $output = $process->getOutput();
    $files = [];

    // PHPUnit --list-tests output format:
    //  - Avax\Tests\Unit\SomeTest::testMethod
    // Extract class names, then resolve to file paths
    $classes = [];
    if (preg_match_all('/^\s*-\s*([A-Za-z0-9_\\\\]+)::/m', $output, $matches)) {
        $classes = array_unique($matches[1]);
    }

    // Map class names to file paths
    foreach ($classes as $class) {
        // Convert namespace to path: Avax\Tests\Unit\Foo\BarTest -> tests/Unit/Foo/BarTest.php
        $relativeClass = str_replace('Avax\\Tests\\', '', $class);
        $path = str_replace('\\', '/', $relativeClass);
        $file = "{$projectRoot}/tests/{$path}.php";

        if (is_file($file)) {
            $files[] = $file;
        }
    }

    // Also scan for component tests outside tests/
    if (preg_match_all('/^\s*-\s*Avax\\\\Components\\\\([A-Za-z0-9_\\\\]+)::/m', $output, $compMatches)) {
        foreach (array_unique($compMatches[1]) as $relative) {
            $path = str_replace('\\', '/', $relative);
            $file = "{$projectRoot}/components/{$path}.php";
            if (is_file($file)) {
                $files[] = $file;
            }
        }
    }

    $files = array_values(array_unique($files));
    sort($files);

    // Apply exclude-group filter
    if ($excludeGroup !== '') {
        $excludedGroups = array_map('trim', explode(',', $excludeGroup));
        $files = filterExcludedGroups($files, $excludedGroups);
    }

    return $files;
}
