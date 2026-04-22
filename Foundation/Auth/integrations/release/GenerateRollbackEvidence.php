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
            'repository_root'     => realpath(path: $repositoryRoot) !== false ? realpath(path: $repositoryRoot) : $repositoryRoot,
            'current_commit'      => $this->readGitOutput->execute(repositoryRoot: $repositoryRoot, arguments: ['rev-parse', 'HEAD']),
            'rollback_target'     => $resolvedTarget,
            'rollback_ready'      => $resolvedTarget !== null,
            'rollback_command'    => $resolvedTarget !== null ? 'git checkout ' . $resolvedTarget : null,
            'generated_at'        => gmdate(format: DATE_ATOM),
            'validation_commands' => $validationCommands,
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
        $composerJsonPath = rtrim(string: $repositoryRoot, characters: '/') . '/composer.json';

        if (! is_file(filename: $composerJsonPath)) {
            return 'unknown';
        }

        $contents = file_get_contents(filename: $composerJsonPath);
        $decoded  = is_string(value: $contents) ? json_decode(json: $contents, associative: true) : null;
        $name     = is_array(value: $decoded) ? ($decoded['name'] ?? null) : null;

        return is_string(value: $name) ? $name : 'unknown';
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
            if (trim(string: $artifact) === '' || ! is_file(filename: $artifact)) {
                continue;
            }

            $sha256 = hash_file(algo: 'sha256', filename: $artifact);
            $bytes  = filesize(filename: $artifact);

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
