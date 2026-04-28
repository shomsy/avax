<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Release;

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
            'repository_root'     => realpath(path: $repositoryRoot) !== false ? realpath(path: $repositoryRoot) : $repositoryRoot,
            'git_commit'          => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', 'HEAD']),
            'git_branch'          => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', '--abbrev-ref', 'HEAD']),
            'git_dirty'           => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['status', '--short']) !== '',
            'generated_at'        => gmdate(format: DATE_ATOM),
            'php_version'         => PHP_VERSION,
            'validation_commands' => $validationCommands,
        ];
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim(string: $repositoryRoot, characters: '/') . '/composer.json';

        if (! is_file(filename: $composerJsonPath)) {
            return 'unknown';
        }

        $contents = file_get_contents(filename: $composerJsonPath);

        if ($contents === false) {
            return 'unknown';
        }

        $decoded = json_decode(json: $contents, associative: true);

        return is_array(value: $decoded) && is_string(value: $decoded['name'] ?? null)
            ? $decoded['name']
            : 'unknown';
    }
}
