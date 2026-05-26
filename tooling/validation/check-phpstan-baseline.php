<?php

declare(strict_types=1);

require_once __DIR__ . '/governance-gate-baseline-lib.php';

$root = dirname(__DIR__, 2);
$mode = avax_gate_mode($argv);
$baselinePath = $root . '/.agents/management/baselines/phpstan-baseline.json';
$writeBaselinePath = avax_gate_arg_value($argv, '--write-baseline');

function avax_phpstan_command(string $root, array $paths = []): string
{
    $command = [
        escapeshellarg($root . '/vendor/bin/phpstan'),
        'analyse',
        '--memory-limit=1G',
        '--error-format=raw',
        '--no-progress',
    ];

    foreach ($paths as $path) {
        $command[] = escapeshellarg($root . '/' . $path);
    }

    return implode(' ', $command);
}

function avax_phpstan_parse_output(string $output, string $root): array
{
    $findings = [];
    foreach (explode("\n", $output) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, 'Note: ')) {
            continue;
        }

        if (!preg_match('/^(.+?):(\d+):(.*)$/', $line, $matches)) {
            continue;
        }

        $file = avax_gate_normalize_path($root, $matches[1]);
        $message = trim($matches[3]);

        $findings[] = [
            'severity' => avax_phpstan_severity($file, $message),
            'path' => $file,
            'line' => (int) $matches[2],
            'finding_type' => avax_gate_type_from_message($message),
            'message' => $message,
        ];
    }

    return $findings;
}

function avax_phpstan_severity(string $file, string $message): string
{
    if (preg_match('#components/Identity/|tests/.*/Identity/|Auth|Token|Access|Security|Tenant|Credential|Session#', $file . ' ' . $message) === 1) {
        return 'HIGH';
    }

    if (str_contains($message, 'does not exist') || str_contains($message, 'undefined')) {
        return 'HIGH';
    }

    if (str_contains($message, 'will always evaluate')) {
        return 'HIGH';
    }

    return 'MEDIUM';
}

function avax_phpstan_run(string $root, array $paths = []): array
{
    $output = [];
    $exitCode = 0;
    exec(avax_phpstan_command($root, $paths) . ' 2>&1', $output, $exitCode);

    return [
        'exit_code' => $exitCode,
        'output' => implode("\n", $output),
    ];
}

function avax_phpstan_changed_analysis_paths(string $root): array
{
    $paths = [];
    foreach (avax_gate_changed_files($root) as $file) {
        if (!str_ends_with($file, '.php')) {
            continue;
        }

        if (preg_match('#^(framework|components|tests)/#', $file) !== 1) {
            continue;
        }

        if (file_exists($root . '/' . $file)) {
            $paths[] = $file;
        }
    }

    sort($paths);

    return $paths;
}

if ($mode === 'full') {
    $result = avax_phpstan_run($root);
    echo $result['output'];
    if ($result['output'] !== '') {
        echo "\n";
    }
    exit((int) $result['exit_code']);
}

if ($mode === 'changed') {
    $paths = avax_phpstan_changed_analysis_paths($root);
    echo "=== phpstan Changed Mode ===\n";
    echo "Changed PHP files in configured analysis scope: " . count($paths) . "\n";

    if ($paths === []) {
        echo "GREEN — no changed PHP files under framework/, components/, or tests/.\n";
        exit(0);
    }

    foreach ($paths as $path) {
        echo "  - {$path}\n";
    }

    $result = avax_phpstan_run($root, $paths);
    $findings = avax_phpstan_parse_output($result['output'], $root);

    if ($findings === []) {
        echo "GREEN — PHPStan found no changed-scope findings.\n";
        exit(0);
    }

    echo "RED — PHPStan found changed-scope findings: " . count($findings) . "\n";
    foreach (array_slice($findings, 0, 50) as $finding) {
        echo "[{$finding['severity']}] {$finding['path']}:{$finding['line']} {$finding['message']}\n";
    }
    exit(1);
}

$result = avax_phpstan_run($root);
$findings = avax_phpstan_parse_output($result['output'], $root);

if ($writeBaselinePath !== null) {
    $target = $writeBaselinePath === '1' ? $baselinePath : $root . '/' . ltrim($writeBaselinePath, '/');
    avax_gate_write_baseline($target, 'phpstan', $findings, $root);
    echo "BASELINE_WRITTEN {$target}\n";
    echo "Entries: " . count($findings) . "\n";
    exit(0);
}

$comparison = avax_gate_compare_with_baseline('phpstan', $findings, $baselinePath, $root);
avax_gate_print_baseline_result('phpstan', $comparison);
exit($comparison['valid'] ? 0 : 1);
