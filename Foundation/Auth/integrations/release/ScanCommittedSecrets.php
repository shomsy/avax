<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final readonly class ScanCommittedSecrets
{
    /**
     * @param list<string> $ignoredDirectories
     *
     * @return list<array{path:string, pattern:string, line:int}>
     */
    public function execute(string $rootPath, array $ignoredDirectories = ['vendor', '.git', 'build']) : array
    {
        $findings = [];
        $iterator = new RecursiveIteratorIterator(iterator: new RecursiveDirectoryIterator(directory: $rootPath));

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $path = $file->getPathname();

            if ($this->isIgnored(path: $path, ignoredDirectories: $ignoredDirectories)) {
                continue;
            }

            $lineNumber = 0;

            $lines = file(filename: $path, flags: FILE_IGNORE_NEW_LINES);

            foreach ($lines !== false ? $lines : [] as $line) {
                $lineNumber++;
                $matchedPattern = $this->matchPattern(line: $line);

                if ($matchedPattern === null) {
                    continue;
                }

                $findings[] = [
                    'path'    => $path,
                    'pattern' => $matchedPattern,
                    'line'    => $lineNumber,
                ];
            }
        }

        return $findings;
    }

    /**
     * @param list<string> $ignoredDirectories
     */
    private function isIgnored(string $path, array $ignoredDirectories) : bool
    {
        foreach ($ignoredDirectories as $directory) {
            $needle = DIRECTORY_SEPARATOR . trim(string: $directory, characters: DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if (str_contains(haystack: $path, needle: $needle)) {
                return true;
            }
        }

        return false;
    }

    private function matchPattern(string $line) : string|null
    {
        $patterns = [
            'aws_access_key_id' => '/AKIA[0-9A-Z]{16}/',
            'github_pat'        => '/github_pat_[A-Za-z0-9_]{20,}/',
            'private_key'       => '/-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----/',
            'slack_token'       => '/xox[baprs]-[A-Za-z0-9-]{10,}/',
        ];

        foreach ($patterns as $name => $pattern) {
            if (preg_match(pattern: $pattern, subject: $line) === 1) {
                return $name;
            }
        }

        return null;
    }
}
