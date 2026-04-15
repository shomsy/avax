<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use RuntimeException;

final readonly class RunConformanceHarness
{
    /**
     * @param list<array{name:string, description:string, command:list<string>}>|null $checks
     *
     * @return array{
     *     timestamp:string,
     *     version:string,
     *     package:string,
     *     overall:string,
     *     summary:array{passed:int, failed:int, total:int},
     *     checks:array<string, array{
     *         description:string,
     *         status:string,
     *         exit_code:int,
     *         command:string
     *     }>
     * }
     */
    public function execute(string $repositoryRoot, array|null $checks = null) : array
    {
        $resolvedChecks = $checks ?? $this->defaultChecks(repositoryRoot: $repositoryRoot);
        $results        = [
            'timestamp' => gmdate(DATE_ATOM),
            'version'   => '1.0.0',
            'package'   => $this->detectPackageName(repositoryRoot: $repositoryRoot),
            'overall'   => 'PASSED',
            'summary'   => [
                'passed' => 0,
                'failed' => 0,
                'total'  => count($resolvedChecks),
            ],
            'checks'    => [],
        ];

        foreach ($resolvedChecks as $check) {
            $execution = $this->runCommand(
                command         : $check['command'],
                workingDirectory: $repositoryRoot
            );

            $status = $execution['exit_code'] === 0 ? 'PASSED' : 'FAILED';

            $results['checks'][$check['name']] = [
                'description' => $check['description'],
                'status'      => $status,
                'exit_code'   => $execution['exit_code'],
                'command'     => implode(' ', $check['command']),
            ];

            if ($status === 'PASSED') {
                $results['summary']['passed']++;
                continue;
            }

            $results['summary']['failed']++;
            $results['overall'] = 'FAILED';
        }

        return $results;
    }

    /**
     * @return list<array{name:string, description:string, command:list<string>}>
     */
    private function defaultChecks(string $repositoryRoot) : array
    {
        $php = PHP_BINARY;
        $bin = rtrim($repositoryRoot, DIRECTORY_SEPARATOR) . '/vendor/bin';

        return [
            [
                'name'        => 'phpstan',
                'description' => 'Static analysis',
                'command'     => [$php, $bin . '/phpstan', 'analyse', '--memory-limit=1G'],
            ],
            [
                'name'        => 'phpstan-strict',
                'description' => 'Strict static analysis',
                'command'     => [$php, $bin . '/phpstan', 'analyse', '--memory-limit=1G', '-c', 'phpstan.strict.neon'],
            ],
            [
                'name'        => 'phpunit',
                'description' => 'Test suite',
                'command'     => [$php, $bin . '/phpunit'],
            ],
            [
                'name'        => 'rector',
                'description' => 'Code style compliance',
                'command'     => [$php, $bin . '/rector', 'process', '--dry-run'],
            ],
            [
                'name'        => 'secret-scan',
                'description' => 'Secret scanning',
                'command'     => [$php, 'tooling/scan-committed-secrets.php'],
            ],
            [
                'name'        => 'migration-check',
                'description' => 'Compatibility migration boundary',
                'command'     => [$php, 'tooling/check-migration-path.php', '--json'],
            ],
            [
                'name'        => 'source-truth',
                'description' => 'Canonical status consistency',
                'command'     => [$php, 'tooling/check-source-truth.php', '--json'],
            ],
            [
                'name'        => 'system-shape',
                'description' => 'Canonical screaming architecture shape',
                'command'     => [$php, 'tooling/check-system-shape.php', '--json'],
            ],
        ];
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim($repositoryRoot, DIRECTORY_SEPARATOR) . '/composer.json';

        if (! is_file($composerJsonPath)) {
            return 'unknown';
        }

        $json    = file_get_contents($composerJsonPath);
        $decoded = is_string($json) ? json_decode($json, true) : null;

        return is_array($decoded) && is_string($decoded['name'] ?? null)
            ? $decoded['name']
            : 'unknown';
    }

    /**
     * @param list<string> $command
     *
     * @return array{exit_code:int, stdout:string, stderr:string}
     */
    private function runCommand(array $command, string $workingDirectory) : array
    {
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(command: $command, descriptor_spec: $descriptor, pipes: $pipes, cwd: $workingDirectory);

        if (! is_resource($process)) {
            throw new RuntimeException(message: 'Conformance command could not start.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return [
            'exit_code' => $exitCode,
            'stdout'    => $stdout === false ? '' : $stdout,
            'stderr'    => $stderr === false ? '' : $stderr,
        ];
    }
}
