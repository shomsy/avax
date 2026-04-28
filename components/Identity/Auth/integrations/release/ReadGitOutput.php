<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Release;

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
            ['rev-parse', '--verify', 'HEAD^']                       => $this->readPreviousHeadCommit(gitDirectory: $gitDirectory),
            ['rev-parse', '--abbrev-ref', 'HEAD']                    => $this->readHeadReferenceName(gitDirectory: $gitDirectory),
            default                                                  => '',
        };
    }

    private function resolveGitDirectory(string $repositoryRoot) : string|null
    {
        $current = realpath(path: $repositoryRoot);

        while ( is_string(value: $current) && $current !== '' ) {
            $gitPath = $current . '/.git';

            if (is_dir(filename: $gitPath)) {
                return $gitPath;
            }

            if (is_file(filename: $gitPath)) {
                $contents = file_get_contents(filename: $gitPath);

                if (! is_string(value: $contents)) {
                    return null;
                }

                $gitDir = trim(string: str_replace(search: 'gitdir:', replace: '', subject: $contents));

                if ($gitDir === '') {
                    return null;
                }

                if ($gitDir[0] !== '/') {
                    $gitDir = $current . '/' . $gitDir;
                }

                $resolvedGitDirectory = realpath(path: $gitDir);

                return is_string(value: $resolvedGitDirectory) ? $resolvedGitDirectory : $gitDir;
            }

            $parent = dirname(path: $current);

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

        if (str_starts_with(haystack: $head, needle: 'ref: ')) {
            return $this->readReference(gitDirectory: $gitDirectory, reference: substr(string: $head, offset: 5));
        }

        return $this->isCommitHash(value: $head) ? $head : '';
    }

    private function readHead(string $gitDirectory) : string
    {
        $path = $gitDirectory . '/HEAD';

        if (! is_file(filename: $path)) {
            return '';
        }

        $contents = file_get_contents(filename: $path);

        return is_string(value: $contents) ? trim(string: $contents) : '';
    }

    private function readReference(string $gitDirectory, string $reference) : string
    {
        $referencePath = $gitDirectory . '/' . $reference;

        if (is_file(filename: $referencePath)) {
            $contents = file_get_contents(filename: $referencePath);

            return is_string(value: $contents) && $this->isCommitHash(value: trim(string: $contents))
                ? trim(string: $contents)
                : '';
        }

        $packedRefsPath = $gitDirectory . '/packed-refs';

        if (! is_file(filename: $packedRefsPath)) {
            return '';
        }

        $lines = file(filename: $packedRefsPath, flags: FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (! is_array(value: $lines)) {
            return '';
        }

        foreach ($lines as $line) {
            $trimmed = trim(string: $line);

            if ($trimmed === '' || str_starts_with(haystack: $trimmed, needle: '#') || str_starts_with(haystack: $trimmed, needle: '^')) {
                continue;
            }

            $parts = preg_split(pattern: '/\s+/', subject: $trimmed, limit: 2);

            if (! is_array(value: $parts)) {
                $parts = [];
            }

            [$hash, $packedReference] = array_pad(array: $parts, length: 2, value: '');

            if ($packedReference === $reference && $this->isCommitHash(value: $hash)) {
                return $hash;
            }
        }

        return '';
    }

    private function isCommitHash(string $value) : bool
    {
        return preg_match(pattern: '/^[0-9a-f]{40}$/i', subject: $value) === 1;
    }

    private function readPreviousHeadCommit(string $gitDirectory) : string
    {
        $logPath = $gitDirectory . '/logs/HEAD';

        if (! is_file(filename: $logPath)) {
            return '';
        }

        $lines = file(filename: $logPath, flags: FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (! is_array(value: $lines) || $lines === []) {
            return '';
        }

        $line = trim(string: end(array: $lines));

        if ($line === '') {
            return '';
        }

        $parts = preg_split(pattern: '/\s+/', subject: $line);
        $hash  = is_array(value: $parts) ? $parts[0] : '';

        $isAllZeroHash = preg_match(pattern: '/^0+$/', subject: $hash) === 1;

        return $this->isCommitHash(value: $hash) && ! $isAllZeroHash ? $hash : '';
    }

    private function readHeadReferenceName(string $gitDirectory) : string
    {
        $head = $this->readHead(gitDirectory: $gitDirectory);

        if (! str_starts_with(haystack: $head, needle: 'ref: ')) {
            return 'HEAD';
        }

        $reference = substr(string: $head, offset: 5);

        if (str_starts_with(haystack: $reference, needle: 'refs/heads/')) {
            return substr(string: $reference, offset: strlen(string: 'refs/heads/'));
        }

        return $reference;
    }
}
