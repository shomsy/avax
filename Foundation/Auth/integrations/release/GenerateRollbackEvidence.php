<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

final readonly class GenerateRollbackEvidence
{
    public function __construct(
        private ReadGitOutput $readGitOutput = new ReadGitOutput()
    ) {}

    /**
     * @param list<string> $artifacts
     * @param list<string> $validationCommands
     *
     * @return array<string, mixed>
     */
    public function execute(
        string      $repositoryRoot,
        string|null $rollbackTarget = null,
        array|null  $artifacts = null,
        array       $validationCommands = []
    ) : array
    {
        $artifacts      ??= [];
        $resolvedTarget = $this->resolveRollbackTarget(repositoryRoot: $repositoryRoot, rollbackTarget: $rollbackTarget);

        return [
            'package'             => $this->detectPackageName(repositoryRoot: $repositoryRoot),
            'repository_root'     => realpath($repositoryRoot) !== false ? realpath($repositoryRoot) : $repositoryRoot,
            'current_commit'      => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', 'HEAD']),
            'rollback_target'     => $resolvedTarget,
            'rollback_ready'      => $resolvedTarget !== null,
            'rollback_command'    => $resolvedTarget !== null ? 'git checkout ' . $resolvedTarget : null,
            'generated_at'        => gmdate(DATE_ATOM),
            'validation_commands' => array_values($validationCommands),
            'artifacts'           => $this->artifactEvidence(artifacts: $artifacts),
        ];
    }

    private function resolveRollbackTarget(string $repositoryRoot, string|null $rollbackTarget) : string|null
    {
        $candidate = $rollbackTarget ?? 'HEAD^';
        $resolved  = $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', '--verify', $candidate]);

        return $resolved !== '' ? $resolved : null;
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim($repositoryRoot, '/') . '/composer.json';

        if (! is_file($composerJsonPath)) {
            return 'unknown';
        }

        $contents = file_get_contents($composerJsonPath);
        $decoded  = is_string($contents) ? json_decode($contents, true) : null;
        $name     = is_array($decoded) ? ($decoded['name'] ?? null) : null;

        return is_string($name) ? $name : 'unknown';
    }

    /**
     * @param list<string> $artifacts
     *
     * @return list<array{path:string, sha256:string, bytes:int}>
     */
    private function artifactEvidence(array $artifacts) : array
    {
        $evidence = [];

        foreach ($artifacts as $artifact) {
            if (trim($artifact) === '' || ! is_file($artifact)) {
                continue;
            }

            $sha256 = hash_file('sha256', $artifact);
            $bytes  = filesize($artifact);

            if ($sha256 === false || $bytes === false) {
                continue;
            }

            $evidence[] = [
                'path'   => $artifact,
                'sha256' => $sha256,
                'bytes'  => $bytes,
            ];
        }

        return $evidence;
    }
}
