<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

final readonly class CreateReleaseProvenance
{
    public function __construct(
        private ReadGitOutput $readGitOutput = new ReadGitOutput()
    ) {}

    /**
     * @param list<string> $validationCommands
     *
     * @return array<string, mixed>
     */
    public function execute(string $repositoryRoot, array $validationCommands = []) : array
    {
        return [
            'package'             => $this->detectPackageName(repositoryRoot: $repositoryRoot),
            'repository_root'     => realpath($repositoryRoot) !== false ? realpath($repositoryRoot) : $repositoryRoot,
            'git_commit'          => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', 'HEAD']),
            'git_branch'          => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', '--abbrev-ref', 'HEAD']),
            'git_dirty'           => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['status', '--short']) !== '',
            'generated_at'        => gmdate(DATE_ATOM),
            'php_version'         => PHP_VERSION,
            'validation_commands' => array_values($validationCommands),
        ];
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim($repositoryRoot, '/') . '/composer.json';

        if (! is_file($composerJsonPath)) {
            return 'unknown';
        }

        $contents = file_get_contents($composerJsonPath);

        if ($contents === false) {
            return 'unknown';
        }

        $decoded = json_decode($contents, true);

        return is_array($decoded) && is_string($decoded['name'] ?? null)
            ? $decoded['name']
            : 'unknown';
    }
}
