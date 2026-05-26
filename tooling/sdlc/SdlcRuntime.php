<?php

declare(strict_types=1);

final class SdlcRuntime
{
    public static function root(): string
    {
        $override = getenv('AVAX_SDLC_ROOT');
        if (is_string($override) && $override !== '') {
            return rtrim($override, '/');
        }

        return dirname(__DIR__, 2);
    }

    public static function phpBinary(): string
    {
        return PHP_BINARY;
    }

    public static function phpVersion(): string
    {
        return PHP_VERSION;
    }

    public static function findGit(): ?string
    {
        if (getenv('AVAX_SDLC_DISABLE_GIT') === '1') {
            return null;
        }

        $git = trim((string) shell_exec('command -v git 2>/dev/null'));

        return $git !== '' ? $git : null;
    }

    public static function requireGit(): string
    {
        $git = self::findGit();
        if ($git !== null) {
            return $git;
        }

        self::printRuntimeHeader('SDLC Runtime Error');
        echo "RED: This PHP runtime cannot access git. Use the canonical SDLC command documented in .agents/how-to/verification/how-to-sdlc-runners.md or fix the PHP runtime environment.\n";
        exit(1);
    }

    /**
     * @return array{exit_code:int, output:string}
     */
    public static function runCommand(string $command, ?string $cwd = null): array
    {
        $descriptor = [1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($command, $descriptor, $pipes, $cwd ?? self::root());
        if (! is_resource($process)) {
            return ['exit_code' => 1, 'output' => 'proc_open failed'];
        }

        $output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        return ['exit_code' => $exitCode, 'output' => trim($output)];
    }

    public static function printRuntimeHeader(string $title): void
    {
        echo $title."\n";
        echo 'PHP_BINARY='.self::phpBinary()."\n";
        echo 'PHP_VERSION='.self::phpVersion()."\n";
        echo 'git='.(self::findGit() ?? 'missing')."\n";
    }
}
