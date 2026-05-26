<?php

declare(strict_types=1);

require_once __DIR__.'/SdlcRuntime.php';

final class GitChangedFiles
{
    /**
     * @return list<string>
     */
    public static function all(?string $root = null): array
    {
        $root ??= SdlcRuntime::root();
        $git = escapeshellarg(SdlcRuntime::requireGit());
        $commands = [
            $git.' diff --name-only',
            $git.' diff --cached --name-only',
            $git.' ls-files --others --exclude-standard',
        ];

        $files = [];
        foreach ($commands as $command) {
            $result = SdlcRuntime::runCommand($command, $root);
            if ($result['exit_code'] !== 0) {
                SdlcRuntime::printRuntimeHeader('Git Changed Files Error');
                echo "RED: Unable to collect changed files.\n";
                echo "command: {$command}\n";
                echo "exit_code: {$result['exit_code']}\n";
                echo $result['output']."\n";
                exit(1);
            }

            foreach (preg_split('/\R/', $result['output']) ?: [] as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $files[$line] = true;
                }
            }
        }

        $sorted = array_keys($files);
        sort($sorted);

        return $sorted;
    }
}

