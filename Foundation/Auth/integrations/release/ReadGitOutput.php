<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

final readonly class ReadGitOutput
{
    /**
     * @param list<string> $arguments
     */
    public function execute(string $repositoryRoot, array $arguments) : string
    {
        $gitDirectory = $this->resolveGitDirectory(repositoryRoot: $repositoryRoot);

        if ($gitDirectory === null) {
            return '';
        }

        return match ($arguments) {
            ['rev-parse', 'HEAD'], ['rev-parse', '--verify', 'HEAD'] => $this->readHeadCommit(gitDirectory: $gitDirectory),
            ['rev-parse', '--verify', 'HEAD^'] => $this->readPreviousHeadCommit(gitDirectory: $gitDirectory),
            ['rev-parse', '--abbrev-ref', 'HEAD'] => $this->readHeadReferenceName(gitDirectory: $gitDirectory),
            default => '',
        };
    }

    private function resolveGitDirectory(string $repositoryRoot) : string|null
    {
        $current = realpath($repositoryRoot);

        while (is_string($current) && $current !== '') {
            $gitPath = $current . '/.git';

            if (is_dir($gitPath)) {
                return $gitPath;
            }

            if (is_file($gitPath)) {
                $contents = file_get_contents($gitPath);

                if (! is_string($contents)) {
                    return null;
                }

                $gitDir = trim(str_replace('gitdir:', '', $contents));

                if ($gitDir === '') {
                    return null;
                }

                if ($gitDir[0] !== '/') {
                    $gitDir = $current . '/' . $gitDir;
                }

                $resolvedGitDirectory = realpath($gitDir);

                return is_string($resolvedGitDirectory) ? $resolvedGitDirectory : $gitDir;
            }

            $parent = dirname($current);

            if ($parent === $current) {
                break;
            }

            $current = $parent;
        }

        return null;
    }

    private function readHeadCommit(string $gitDirectory) : string
    {
        $head = $this->readHead(gitDirectory: $gitDirectory);

        if ($head === '') {
            return '';
        }

        if (str_starts_with($head, 'ref: ')) {
            return $this->readReference(gitDirectory: $gitDirectory, reference: substr($head, 5));
        }

        return $this->isCommitHash(value: $head) ? $head : '';
    }

    private function readPreviousHeadCommit(string $gitDirectory) : string
    {
        $logPath = $gitDirectory . '/logs/HEAD';

        if (! is_file($logPath)) {
            return '';
        }

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (! is_array($lines) || $lines === []) {
            return '';
        }

        $line = trim(end($lines));

        if ($line === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $line);
        $hash = is_array($parts) ? $parts[0] : '';

        $isAllZeroHash = preg_match('/^0+$/', $hash) === 1;

        return $this->isCommitHash(value: $hash) && ! $isAllZeroHash ? $hash : '';
    }

    private function readHeadReferenceName(string $gitDirectory) : string
    {
        $head = $this->readHead(gitDirectory: $gitDirectory);

        if (! str_starts_with($head, 'ref: ')) {
            return 'HEAD';
        }

        $reference = substr($head, 5);

        if (str_starts_with($reference, 'refs/heads/')) {
            return substr($reference, strlen('refs/heads/'));
        }

        return $reference;
    }

    private function readHead(string $gitDirectory) : string
    {
        $path = $gitDirectory . '/HEAD';

        if (! is_file($path)) {
            return '';
        }

        $contents = file_get_contents($path);

        return is_string($contents) ? trim($contents) : '';
    }

    private function readReference(string $gitDirectory, string $reference) : string
    {
        $referencePath = $gitDirectory . '/' . $reference;

        if (is_file($referencePath)) {
            $contents = file_get_contents($referencePath);

            return is_string($contents) && $this->isCommitHash(value: trim($contents))
                ? trim($contents)
                : '';
        }

        $packedRefsPath = $gitDirectory . '/packed-refs';

        if (! is_file($packedRefsPath)) {
            return '';
        }

        $lines = file($packedRefsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (! is_array($lines)) {
            return '';
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '^')) {
                continue;
            }

            $parts = preg_split('/\s+/', $trimmed, 2);

            if (! is_array($parts)) {
                $parts = [];
            }

            [$hash, $packedReference] = array_pad($parts, 2, '');

            if ($packedReference === $reference && $this->isCommitHash(value: $hash)) {
                return $hash;
            }
        }

        return '';
    }

    private function isCommitHash(string $value) : bool
    {
        return preg_match('/^[0-9a-f]{40}$/i', $value) === 1;
    }
}
