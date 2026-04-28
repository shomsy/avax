<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Release;

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
            'timestamp' => gmdate(format: DATE_ATOM),
            'version'   => '1.0.0',
            'package'   => $this->detectPackageName(repositoryRoot: $repositoryRoot),
            'overall'   => 'PASSED',
            'summary'   => [
                'passed' => 0,
                'failed' => 0,
                'total'  => count(value: $resolvedChecks),
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
                'command'     => implode(separator: ' ', array: $check['command']),
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
        $bin = rtrim(string: $repositoryRoot, characters: DIRECTORY_SEPARATOR) . '/vendor/bin';

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
                'command'     => [$php, $bin . '/rector', 'process', '--dry-run', '--no-progress-bar', '--config', 'rector.php'],
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
        $composerJsonPath = rtrim(string: $repositoryRoot, characters: DIRECTORY_SEPARATOR) . '/composer.json';

        if (! is_file(filename: $composerJsonPath)) {
            return 'unknown';
        }

        $json    = file_get_contents(filename: $composerJsonPath);
        $decoded = is_string(value: $json) ? json_decode(json: $json, associative: true) : null;

        return is_array(value: $decoded) && is_string(value: $decoded['name'] ?? null)
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
            ['pipe', 'r'],
            ['pipe', 'w'],
            ['pipe', 'w'],
        ];

        $process = proc_open(command: $command, descriptor_spec: $descriptor, pipes: $pipes, cwd: $workingDirectory);

        if (! is_resource(value: $process)) {
            throw new RuntimeException(message: 'Conformance command could not start.');
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
}
