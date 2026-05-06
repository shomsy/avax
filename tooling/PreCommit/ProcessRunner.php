<?php

declare(strict_types=1);

namespace Avax\Tooling\PreCommit;

final class ProcessRunner
{
    /**
     * @param  list<string>  $command
     */
    public function run(array $command, ?string $workingDirectory = null): ProcessResult
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open(
            command: $command,
            descriptor_spec: $descriptorSpec,
            pipes: $pipes,
            cwd: $workingDirectory,
        );

        if (! is_resource($process)) {
            throw new HookInstallerException('Failed to start process: '.implode(' ', $command));
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        return new ProcessResult(
            exitCode: is_int($exitCode) ? $exitCode : ExitCode::FAILURE,
            stdout: trim($stdout === false ? '' : $stdout),
            stderr: trim($stderr === false ? '' : $stderr),
        );
    }
}
