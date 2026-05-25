<?php
/**
 * Run serial tests via a fresh PHP process, bypassing composer's environment.
 */
$projectRoot = dirname(__DIR__, 2);
chdir($projectRoot);

$cmd = [
    PHP_BINARY,
    '-d', 'memory_limit=512M',
    'vendor/bin/phpunit',
    '--no-coverage',
    '--testsuite', 'Full',
    '--group', 'serial,process,slow',
    '--do-not-cache-result',
];

$process = proc_open(
    $cmd,
    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
    $pipes,
    $projectRoot,
    null, // No inherited env vars
);

if ($process === false) {
    echo "Failed to start process\n";
    exit(2);
}

fclose($pipes[0]);
echo stream_get_contents($pipes[1]);
echo stream_get_contents($pipes[2]);
$exitCode = proc_close($process);
exit($exitCode);
