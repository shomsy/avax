<?php

declare(strict_types=1);

require dirname(path: __DIR__) . '/vendor/autoload.php';

$root           = dirname(path: __DIR__);
$quiet          = in_array(needle: '--quiet', haystack: $argv, strict: true) || in_array(needle: '-q', haystack: $argv, strict: true);
$verbose        = in_array(needle: '--verbose', haystack: $argv, strict: true) || in_array(needle: '-v', haystack: $argv, strict: true);
$json           = in_array(needle: '--json', haystack: $argv, strict: true) || in_array(needle: '-j', haystack: $argv, strict: true);
$buildDirectory = $root . '/build';

if (! is_dir(filename: $buildDirectory)) {
    mkdir(directory: $buildDirectory, permissions: 0777, recursive: true);
}

$php         = PHP_BINARY;
$binDir      = $root . '/vendor/bin';
$definitions = [
    1  => [
        'name'     => 'Trust',
        'question' => 'Does the change fail closed when mandatory config, dependency, policy input, or evidence is missing?',
        'commands' => [
            [$php, $binDir . '/phpstan', 'analyse', '--memory-limit=1G', '--error-format=raw'],
            [$php, $binDir . '/phpunit', '--testdox', '--no-coverage'],
        ],
        'category' => 'static-analysis',
    ],
    2  => [
        'name'     => 'Operator Clarity',
        'question' => 'Can another human or agent understand the failure, recovery path, and artifact location without tribal knowledge?',
        'commands' => [
            [$php, $binDir . '/phpstan', 'analyse', '--memory-limit=1G', '--error-format=table'],
        ],
        'category' => 'clarity',
    ],
    3  => [
        'name'     => 'Rollback Posture',
        'question' => 'Is there an explicit path to reverse or contain the change when it mutates state, runtime policy, or public behavior?',
        'commands' => [
            [$php, 'tooling/create-rollback-evidence.php'],
        ],
        'category' => 'rollback',
    ],
    4  => [
        'name'     => 'Contract Stability',
        'question' => 'Are public interfaces stable, or is the migration path documented and validated?',
        'commands' => [
            [$php, 'tooling/check-migration-path.php', '--json'],
            [$php, $binDir . '/phpstan', 'analyse', '--memory-limit=1G'],
            [$php, $binDir . '/rector', 'process', '--dry-run', '--no-progress-bar', '--config', 'rector.php'],
        ],
        'category' => 'bc',
    ],
    5  => [
        'name'     => 'State Ownership',
        'question' => 'Is durable truth stored in an owned boundary instead of accidental local memory, cache drift, or UI residue?',
        'commands' => [
            [$php, $binDir . '/phpunit', '--testsuite=integration', '--testdox'],
        ],
        'category' => 'testing',
    ],
    6  => [
        'name'     => 'Async Containment',
        'question' => 'If async or background work exists, are acknowledgement, retry, timeout, and quarantine rules explicit and observable?',
        'commands' => [
            [$php, $binDir . '/phpunit', '--testsuite=integration', '--testdox'],
        ],
        'category' => 'testing',
    ],
    7  => [
        'name'     => 'Deterministic Automation',
        'question' => 'Can CI, deployment, or operational tooling consume the result without manual interpretation or fuzzy parsing?',
        'commands' => [
            [$php, 'tooling/run-conformance-harness.php'],
        ],
        'category' => 'ci',
    ],
    8  => [
        'name'     => 'Observability Logic',
        'question' => 'Do logs, traces, metrics, events, and exit codes make the behavior diagnosable through the real execution path?',
        'commands' => [],
        'category' => 'observability',
        'manual'   => true,
    ],
    9  => [
        'name'     => 'Runtime Hardening',
        'question' => 'Does the change preserve least privilege, secret hygiene, and runtime boundary policy?',
        'commands' => [
            [$php, 'tooling/scan-committed-secrets.php'],
        ],
        'category' => 'security',
    ],
    10 => [
        'name'     => 'Performance Posture',
        'question' => 'Are new latency, scale, or throughput claims measured and recorded instead of asserted?',
        'commands' => [
            [$php, 'tooling/run-with-coverage-driver', 'vendor/bin/infection', '--configuration=infection.json.dist', '--skip-initial-tests'],
        ],
        'category' => 'performance',
        'optional' => true,
    ],
    11 => [
        'name'     => 'Source Truth',
        'question' => 'Do README, help text, and governance docs still describe the shipped system accurately?',
        'commands' => [
            [$php, 'tooling/check-source-truth.php', '--json'],
            [$php, 'tooling/check-system-shape.php', '--json'],
        ],
        'category' => 'docs',
    ],
    12 => [
        'name'     => 'Evidence Integrity',
        'question' => 'Is validation proof present, machine-readable, and tied to this change and its claimed scope?',
        'commands' => [
            [$php, 'tooling/generate-sbom.php'],
            [$php, 'tooling/generate-evidence-bundle.php'],
        ],
        'category' => 'release',
    ],
    13 => [
        'name'     => 'Self-Healing Loop',
        'question' => 'Were findings either fixed, revalidated, or turned into explicit tracked backlog items with evidence before closure?',
        'commands' => [],
        'category' => 'process',
        'manual'   => true,
    ],
];

$results = [
    'timestamp' => gmdate(format: DATE_ATOM, timestamp: time()),
    'version'   => '2.0.0',
    'summary'   => [
        'total'  => count(value: $definitions),
        'passed' => 0,
        'failed' => 0,
        'manual' => 0,
    ],
    'gates'     => [],
];

$hasFailure = false;

foreach ($definitions as $gateId => $gate) {
    $gateResult = [
        'id'       => $gateId,
        'name'     => $gate['name'],
        'question' => $gate['question'],
        'category' => $gate['category'],
        'status'   => 'pending',
        'commands' => [],
    ];

    if ($gate['manual'] ?? false) {
        $gateResult['status'] = 'manual';
        $gateResult['note']   = 'Requires manual review';
        $results['summary']['manual']++;
        $results['gates'][] = $gateResult;
        continue;
    }

    $gatePassed = true;

    foreach ($gate['commands'] as $command) {
        $execution = runCommand(command: $command, workingDirectory: $root);
        $optional  = $gate['optional'] ?? false;
        $passed    = $execution['exit_code'] === 0 || $optional;

        if (! $passed) {
            $gatePassed = false;
        }

        $commandResult = [
            'command'   => implode(separator: ' ', array: $command),
            'exit_code' => $execution['exit_code'],
            'passed'    => $passed,
        ];

        if ($verbose && ! $quiet) {
            $commandResult['stdout'] = $execution['stdout'];
            $commandResult['stderr'] = $execution['stderr'];
        }

        $gateResult['commands'][] = $commandResult;
    }

    $gateResult['status'] = $gatePassed ? 'passed' : 'failed';

    if ($gatePassed) {
        $results['summary']['passed']++;
    } else {
        $results['summary']['failed']++;
        $hasFailure = true;
    }

    $results['gates'][] = $gateResult;
}

$results['summary']['pass_rate'] = round(num: ($results['summary']['passed'] / count(value: $definitions)) * 100, precision: 1) . '%';
$results['overall']              = $hasFailure ? 'FAILED' : 'PASSED';

file_put_contents(
    filename: $buildDirectory . '/quality-gates-report.json',
    data    : json_encode(value: $results, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

if ($json) {
    echo json_encode(value: $results, flags: JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($hasFailure ? 1 : 0);
}

if (! $quiet) {
    echo "=== Quality Gates Report ===\n";
    echo 'Timestamp: ' . $results['timestamp'] . "\n";
    echo 'Overall: ' . $results['overall'] . "\n\n";
    echo "Summary:\n";
    echo '  Total: ' . $results['summary']['total'] . "\n";
    echo '  Passed: ' . $results['summary']['passed'] . "\n";
    echo '  Failed: ' . $results['summary']['failed'] . "\n";
    echo '  Manual: ' . $results['summary']['manual'] . "\n";
    echo '  Rate: ' . $results['summary']['pass_rate'] . "\n\n";

    foreach ($results['gates'] as $gate) {
        $icon = match ($gate['status']) {
            'passed' => '[PASS]',
            'failed' => '[FAIL]',
            'manual' => '[MANUAL]',
            default  => '[PENDING]',
        };

        echo sprintf("%s Gate %d: %s\n", $icon, $gate['id'], $gate['name']);

        if ($gate['status'] === 'failed') {
            foreach ($gate['commands'] as $command) {
                if (($command['passed'] ?? true) === false) {
                    echo '  - failed: ' . $command['command'] . "\n";
                }
            }
        }
    }
}

exit($hasFailure ? 1 : 0);

/**
 * @param list<string> $command
 *
 * @return array{exit_code:int, stdout:string, stderr:string}
 */
function runCommand(array $command, string $workingDirectory) : array
{
    $descriptor = [
        ['pipe', 'r'],
        ['pipe', 'w'],
        ['pipe', 'w'],
    ];

    $process = proc_open(command: $command, descriptor_spec: $descriptor, pipes: $pipes, cwd: $workingDirectory);

    if (! is_resource(value: $process)) {
        return [
            'exit_code' => 1,
            'stdout'    => '',
            'stderr'    => 'Could not start command.',
        ];
    }

    fclose(stream: $pipes[0]);
    stream_set_blocking(stream: $pipes[1], enable: false);
    stream_set_blocking(stream: $pipes[2], enable: false);

    $stdout = '';
    $stderr = '';
    $status = ['exitcode' => 1, 'running' => true];

    do {
        $status  = proc_get_status(process: $process);
        $running = $status['running'];
        $read    = [];

        if (! feof(stream: $pipes[1])) {
            $read[] = $pipes[1];
        }

        if (! feof(stream: $pipes[2])) {
            $read[] = $pipes[2];
        }

        if ($read === []) {
            if (! $running) {
                break;
            }

            usleep(microseconds: 10_000);
            continue;
        }

        $write  = null;
        $except = null;
        $ready  = @stream_select(read: $read, write: $write, except: $except, seconds: 0, microseconds: 200_000);

        if ($ready === false) {
            break;
        }

        foreach ($read as $stream) {
            $chunk = stream_get_contents(stream: $stream);

            if ($chunk === false || $chunk === '') {
                continue;
            }

            if ($stream === $pipes[1]) {
                $stdout .= $chunk;
                continue;
            }

            $stderr .= $chunk;
        }
    } while ( $running || ! feof(stream: $pipes[1]) || ! feof(stream: $pipes[2]) );

    fclose(stream: $pipes[1]);
    fclose(stream: $pipes[2]);

    $exitCode = proc_close(process: $process);

    if ($exitCode < 0) {
        $exitCode = $status['exitcode'];

        if ($exitCode < 0) {
            $exitCode = 1;
        }
    }

    return [
        'exit_code' => $exitCode,
        'stdout'    => $stdout,
        'stderr'    => $stderr,
    ];
}
