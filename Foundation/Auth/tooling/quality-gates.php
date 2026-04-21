<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

$root           = dirname(__DIR__);
$quiet          = in_array('--quiet', $argv, true) || in_array('-q', $argv, true);
$verbose        = in_array('--verbose', $argv, true) || in_array('-v', $argv, true);
$json           = in_array('--json', $argv, true) || in_array('-j', $argv, true);
$buildDirectory = $root . '/build';

if (! is_dir($buildDirectory)) {
    mkdir($buildDirectory, 0777, true);
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
        'name'     => 'Evidence Bundle',
        'question' => 'Was the evidence bundle generated and linked to the release?',
        'commands' => [
            [$php, 'tooling/generate-evidence-bundle.php'],
        ],
        'category' => 'evidence',
    ],
    7  => [
        'name'     => 'Release Quality',
        'question' => 'Was the release quality gate passed with mutation testing and integration tests?',
        'commands' => [
            [$php, $binDir . '/phpunit', '--testsuite=mutation', '--no-coverage'],
            [$php, $binDir . '/infection', '--no-coverage'],
        ],
        'category' => 'quality',
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
    'timestamp' => gmdate(DATE_ATOM, time()),
    'version'   => '2.0.0',
    'summary'   => [
        'total'  => count($definitions),
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
            'command'   => implode(' ', $command),
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

$results['summary']['pass_rate'] = round(($results['summary']['passed'] / count($definitions)) * 100, 1) . '%';
$results['overall']              = $hasFailure ? 'FAILED' : 'PASSED';

file_put_contents(
    $buildDirectory . '/quality-gates-report.json',
    json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
);

if ($json) {
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
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

    if (! is_resource($process)) {
        return [
            'exit_code' => 1,
            'stdout'    => '',
            'stderr'    => 'Could not start command.',
        ];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    return [
        'exit_code' => is_int($exitCode) ? $exitCode : 1,
        'stdout'    => is_string($stdout) ? $stdout : '',
        'stderr'    => is_string($stderr) ? $stderr : '',
    ];
}
