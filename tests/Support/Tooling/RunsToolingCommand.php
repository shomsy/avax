<?php

declare(strict_types=1);

namespace Avax\Tests\Support\Tooling;

/**
 * Test helper for running SDLC/governance tooling commands as external PHP processes.
 *
 * Captures exit code, stdout, and stderr independently.
 * Does not hardcode PHP binary or git paths.
 */
final class RunsToolingCommand
{
    /**
     * @return array{exit_code: int, stdout: string, stderr: string}
     */
    public static function run(string $script, array $args = [], ?string $cwd = null, array $env = []): array
    {
        $php = PHP_BINARY;
        if ($script === $php) {
            $command = escapeshellarg($php);
        } else {
            $command = escapeshellarg($php) . ' ' . escapeshellarg($script);
        }

        foreach ($args as $arg) {
            $command .= ' ' . escapeshellarg($arg);
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        // Retrieve full environment and merge with overrides
        $envVars = array_merge(getenv(), $env);

        // Remove variables that would confuse child processes
        unset($envVars['argv'], $envVars['argc']);

        $process = proc_open($command, $descriptors, $pipes, $cwd, $envVars);
        if (! is_resource($process)) {
            return ['exit_code' => 127, 'stdout' => '', 'stderr' => 'proc_open failed'];
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return [
            'exit_code' => $exitCode,
            'stdout' => $stdout,
            'stderr' => $stderr,
        ];
    }
}
