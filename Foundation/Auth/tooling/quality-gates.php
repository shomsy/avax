<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Process\Process;

$root = dirname(__DIR__);
$quiet = in_array('--quiet', $argv) || in_array('-q', $argv);
$verbose = in_array('--verbose', $argv) || in_array('-v', $argv);
$json = in_array('--json', $argv) || in_array('-j', $argv);

$binDir = $root . '/vendor/bin';
$definitions = [
    1 => [
        'name' => 'Trust',
        'question' => 'Does the change fail closed when mandatory config, dependency, policy input, or evidence is missing?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/phpstan', 'analyse', '--memory-limit=1G', '--error-format=raw'], 'fails_on_error' => true],
            ['cmd' => 'php', 'args' => [$binDir . '/phpstan', 'analyse', '--memory-limit=1G', '-c', 'phpstan.strict.neon', '--error-format=raw'], 'fails_on_error' => false, 'optional' => true],
            ['cmd' => 'php', 'args' => [$binDir . '/phpunit', '--testdox', '--no-coverage'], 'fails_on_error' => true],
        ],
        'category' => 'static-analysis',
    ],
    2 => [
        'name' => 'Operator Clarity',
        'question' => 'Can another human or agent understand the failure, recovery path, and artifact location without tribal knowledge?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/phpstan', 'analyse', '--memory-limit=1G', '--error-format=table'], 'fails_on_error' => false],
        ],
        'category' => 'clarity',
    ],
    3 => [
        'name' => 'Rollback Posture',
        'question' => 'Is there an explicit path to reverse or contain the change when it mutates state, runtime policy, or public behavior?',
        'commands' => [
            ['cmd' => 'php', 'args' => ['tooling/create-rollback-evidence.php'], 'fails_on_error' => true],
        ],
        'category' => 'rollback',
    ],
    4 => [
        'name' => 'Contract Stability',
        'question' => 'Are public interfaces stable, or is the migration path documented and validated?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/phpstan', 'analyse', '--memory-limit=1G'], 'fails_on_error' => true],
            ['cmd' => 'php', 'args' => [$binDir . '/rector', 'process', '--dry-run'], 'fails_on_error' => false],
        ],
        'category' => 'bc',
    ],
    5 => [
        'name' => 'State Ownership',
        'question' => 'Is durable truth stored in an owned boundary instead of accidental local memory, cache drift, or UI residue?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/phpunit', '--testsuite=integration', '--testdox'], 'fails_on_error' => false],
        ],
        'category' => 'testing',
    ],
    6 => [
        'name' => 'Async Containment',
        'question' => 'If async or background work exists, are acknowledgement, retry, timeout, and quarantine rules explicit and observable?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/phpunit', '--testsuite=integration', '--testdox'], 'fails_on_error' => false],
        ],
        'category' => 'testing',
    ],
    7 => [
        'name' => 'Deterministic Automation',
        'question' => 'Can CI, deployment, or operational tooling consume the result without manual interpretation or fuzzy parsing?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/phpstan', 'analyse', '--memory-limit=1G', '--error-format=raw'], 'fails_on_error' => true],
            ['cmd' => 'php', 'args' => [$binDir . '/phpunit', '--testdox', '--no-coverage'], 'fails_on_error' => true],
        ],
        'category' => 'ci',
    ],
    8 => [
        'name' => 'Observability Logic',
        'question' => 'Do logs, traces, metrics, events, and exit codes make the behavior diagnosable through the real execution path?',
        'commands' => [],
        'category' => 'observability',
        'manual' => true,
    ],
    9 => [
        'name' => 'Runtime Hardening',
        'question' => 'Does the change preserve least privilege, secret hygiene, and runtime boundary policy?',
        'commands' => [
            ['cmd' => 'php', 'args' => ['tooling/scan-committed-secrets.php'], 'fails_on_error' => true],
        ],
        'category' => 'security',
    ],
    10 => [
        'name' => 'Performance Posture',
        'question' => 'Are new latency, scale, or throughput claims measured and recorded instead of asserted?',
        'commands' => [
            ['cmd' => 'php', 'args' => [$binDir . '/infection', '--configuration=infection.json.dist', '--skip-initial-tests'], 'fails_on_error' => false, 'optional' => true],
        ],
        'category' => 'performance',
    ],
    11 => [
        'name' => 'Source Truth',
        'question' => 'Do README, help text, and governance docs still describe the shipped system accurately?',
        'commands' => [],
        'category' => 'docs',
        'manual' => true,
    ],
    12 => [
        'name' => 'Evidence Integrity',
        'question' => 'Is validation proof present, machine-readable, and tied to this change and its claimed scope?',
        'commands' => [
            ['cmd' => 'php', 'args' => ['tooling/generate-sbom.php'], 'fails_on_error' => false],
        ],
        'category' => 'release',
    ],
    13 => [
        'name' => 'Self-Healing Loop',
        'question' => 'Were findings either fixed, revalidated, or turned into explicit tracked backlog items with evidence before closure?',
        'commands' => [],
        'category' => 'process',
        'manual' => true,
    ],
];

$results = [
    'timestamp' => date('c'),
    'version' => '1.0.0',
    'summary' => [
        'total' => count($definitions),
        'passed' => 0,
        'failed' => 0,
        'manual' => 0,
    ],
    'gates' => [],
];

$hasFailure = false;

foreach ($definitions as $gateId => $gate) {
    $gateResult = [
        'id' => $gateId,
        'name' => $gate['name'],
        'question' => $gate['question'],
        'category' => $gate['category'],
        'status' => 'pending',
        'commands' => [],
    ];

    if ($gate['manual'] ?? false) {
        $gateResult['status'] = 'manual';
        $gateResult['note'] = 'Requires manual review';
        $results['summary']['manual']++;
    } else {
        $gatePassed = true;
        foreach ($gate['commands'] as $cmdSpec) {
            $cmd = $cmdSpec['cmd'];
            $args = $cmdSpec['args'] ?? [];
            $fullCmd = array_merge([$cmd], $args);
            
            try {
                $process = new Process($fullCmd);
                $process->setWorkingDirectory($root);
                $process->setTimeout(300);
                $process->run();
                
                $exitCode = $process->getExitCode();
                $output = $process->getOutput();
                $errorOutput = $process->getErrorOutput();
                
                $optional = $cmdSpec['optional'] ?? false;
                $passed = $optional 
                    ? true 
                    : ($cmdSpec['fails_on_error'] 
                        ? ($exitCode === 0) 
                        : ($exitCode === 0 || $exitCode === null));
                
                if (!$passed && !($cmdSpec['optional'] ?? false)) {
                    $gatePassed = false;
                }
                
                $cmdResult = [
                    'command' => implode(' ', $fullCmd),
                    'exit_code' => $exitCode,
                    'passed' => $passed,
                    'output_length' => strlen($output),
                ];
                
                if ($verbose && !$quiet) {
                    $cmdResult['output'] = $output;
                    if ($errorOutput) {
                        $cmdResult['error_output'] = $errorOutput;
                    }
                }
                
                $gateResult['commands'][] = $cmdResult;
                
            } catch (\Throwable $e) {
                $gatePassed = false;
                $gateResult['commands'][] = [
                    'command' => implode(' ', $fullCmd),
                    'error' => $e->getMessage(),
                    'passed' => false,
                ];
            }
        }
        
        $gateResult['status'] = $gatePassed ? 'passed' : 'failed';
        $gateResult['overall'] = $gatePassed;
        
        if ($gatePassed) {
            $results['summary']['passed']++;
        } else {
            $results['summary']['failed']++;
            $hasFailure = true;
        }
    }
    
    $results['gates'][] = $gateResult;
}

$results['summary']['pass_rate'] = $results['summary']['total'] > 0
    ? round($results['summary']['passed'] / $results['summary']['total'] * 100, 1) . '%'
    : '0%';

$results['overall'] = $hasFailure ? 'FAILED' : 'PASSED';

if ($json) {
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit($hasFailure ? 1 : 0);
}

if (!$quiet) {
    echo "\n=== Quality Gates Report ===\n";
    echo "Timestamp: {$results['timestamp']}\n";
    echo "Overall: {$results['overall']}\n";
    echo "\n";
    echo "Summary:\n";
    echo "  Total:   {$results['summary']['total']}\n";
    echo "  Passed: {$results['summary']['passed']}\n";
    echo "  Failed: {$results['summary']['failed']}\n";
    echo "  Manual: {$results['summary']['manual']}\n";
    echo "  Rate:   {$results['summary']['pass_rate']}\n";
    echo "\n";
    
    foreach ($results['gates'] as $gate) {
        $icon = match($gate['status']) {
            'passed' => '✓',
            'failed' => '✗',
            'manual' => '•',
            default => '?',
        };
        $status = strtoupper($gate['status']);
        echo "{$icon} Gate {$gate['id']}: {$gate['name']} [{$status}]\n";
        
        if (!$quiet && $gate['status'] === 'failed') {
            foreach ($gate['commands'] as $cmd) {
                if (!($cmd['passed'] ?? true)) {
                    echo "    Failed: {$cmd['command']}\n";
                }
            }
        }
    }
    
    echo "\n";
    echo "Run with: php tooling/quality-gates.php --json for machine-readable output\n";
}

exit($hasFailure ? 1 : 0);