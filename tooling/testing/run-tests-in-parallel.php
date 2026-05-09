<?php

declare(strict_types=1);

/**
 * Run PHPUnit tests in parallel using Symfony Process.
 *
 * Groups test files and runs each group as a separate process.
 * Collects results from process output.
 *
 * Usage: php tooling/testing/run-tests-in-parallel.php [--processes N]
 */

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use Symfony\Component\Process\Process;

$options = getopt('', ['processes:']);
$processCount = isset($options['processes']) ? (int) $options['processes'] : 0;

if ($processCount <= 0) {
    $processCount = (int) shell_exec('nproc 2>/dev/null || echo 4') ?: 4;
}

$projectRoot = dirname(__DIR__, 2);

// Collect all test files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator("{$projectRoot}/tests", RecursiveDirectoryIterator::SKIP_DOTS)
);

$testFiles = [];
foreach ($iterator as $file) {
    if ($file->getExtension() === 'php' && str_ends_with($file->getFilename(), 'Test.php')) {
        $testFiles[] = $file->getPathname();
    }
}

sort($testFiles);
$totalFiles = count($testFiles);

echo "Running {$totalFiles} test files in {$processCount} parallel processes...\n\n";

$groups = array_chunk($testFiles, (int) ceil($totalFiles / $processCount));

$processes = [];
foreach ($groups as $i => $files) {
    $phpunit = "{$projectRoot}/vendor/bin/phpunit";
    $cmd = array_merge(
        ['php', '-d', 'memory_limit=512M', $phpunit, '--no-coverage'],
        $files
    );

    $process = new Process($cmd, $projectRoot);
    $process->setTimeout(300);
    $process->start();
    $processes[$i] = $process;
    echo "  Started group " . ($i + 1) . " (" . count($files) . " files)\n";
}

$startTime = microtime(true);

// Wait for all processes
foreach ($processes as $i => $process) {
    $process->wait();
}

$totalTime = microtime(true) - $startTime;

// Collect results
$totalTests = 0;
$totalAssertions = 0;
$hasFailure = false;
$combinedOutput = '';

foreach ($processes as $i => $process) {
    $output = $process->getOutput() . $process->getErrorOutput();
    $combinedOutput .= $output;

    if (preg_match('/\((\d+)\s+tests?,\s*(\d+)\s+assertions?\)/', $output, $m)) {
        $totalTests += (int) $m[1];
        $totalAssertions += (int) $m[2];
    }
    if ($process->getExitCode() !== 0) {
        $hasFailure = true;
        echo "\n--- Group " . ($i + 1) . " (exit code: " . $process->getExitCode() . ") ---\n";
        echo $output . "\n";
    }
}

$sequentialBaseline = 5.6;
$speedup = $sequentialBaseline > 0 ? round((1 - $totalTime / $sequentialBaseline) * 100) : 0;

printf("\nCompleted in %.3fs (%d tests, %d assertions, ~%d%% speedup vs %.1fs sequential)\n",
    $totalTime, $totalTests, $totalAssertions, max(0, $speedup), $sequentialBaseline);

exit($hasFailure ? 1 : 0);
