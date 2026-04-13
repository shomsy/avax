<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

final readonly class CreateReleaseProvenance
{
    /**
     * @param list<string> $validationCommands
     * @return array<string, mixed>
     */
    public function execute(string $repositoryRoot, array $validationCommands = []) : array
    {
        return [
            'package' => $this->detectPackageName($repositoryRoot),
            'repository_root' => realpath($repositoryRoot) !== false ? realpath($repositoryRoot) : $repositoryRoot,
            'git_commit' => $this->runGit($repositoryRoot, 'rev-parse HEAD'),
            'git_branch' => $this->runGit($repositoryRoot, 'rev-parse --abbrev-ref HEAD'),
            'git_dirty' => $this->runGit($repositoryRoot, 'status --short') !== '',
            'generated_at' => gmdate(DATE_ATOM),
            'php_version' => PHP_VERSION,
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

    private function runGit(string $repositoryRoot, string $arguments) : string
    {
        $command = sprintf(
            'git -C %s %s 2>/dev/null',
            escapeshellarg($repositoryRoot),
            $arguments
        );
        $output = shell_exec($command);

        return trim((string) $output);
    }
}
