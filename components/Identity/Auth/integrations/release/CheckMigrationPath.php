<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Release;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final readonly class CheckMigrationPath
{
    /**
     * @return array{
     *     package:string,
     *     clean:bool,
     *     current_namespace:string,
     *     migration_documented:bool,
     *     automated_upgrade_test:bool,
     *     legacy_namespace_references:list<string>,
     *     notes:list<string>
     * }
     */
    public function execute(string $repositoryRoot) : array
    {
        $legacyReferences = $this->findLegacyReferences(repositoryRoot: $repositoryRoot);
        $migrationGuide   = $repositoryRoot . '/docs/upgrade-migration-guide.md';
        $upgradeTest      = $repositoryRoot . '/tests/System/ProductBoundaryTest.php';
        $notes            = [];

        if (! is_file(filename: $migrationGuide)) {
            $notes[] = 'Migration guide is missing.';
        }

        if (! is_file(filename: $upgradeTest)) {
            $notes[] = 'Automated upgrade boundary test is missing.';
        }

        if ($legacyReferences !== []) {
            $notes[] = 'Legacy namespace references still exist in tracked files.';
        }

        return [
            'package'                     => $this->detectPackageName(repositoryRoot: $repositoryRoot),
            'clean'                       => $legacyReferences === [] && is_file(filename: $migrationGuide) && is_file(filename: $upgradeTest),
            'current_namespace'           => 'Avax\\Auth\\System\\',
            'migration_documented'        => is_file(filename: $migrationGuide),
            'automated_upgrade_test'      => is_file(filename: $upgradeTest),
            'legacy_namespace_references' => $legacyReferences,
            'notes'                       => $notes,
        ];
    }

    /**
     * @return list<string>
     */
    private function findLegacyReferences(string $repositoryRoot) : array
    {
        $matches  = [];
        $iterator = new RecursiveIteratorIterator(
            iterator: new RecursiveDirectoryIterator(directory: $repositoryRoot, flags: FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $relativePath = ltrim(string: str_replace(search: $repositoryRoot, replace: '', subject: $file->getPathname()), characters: DIRECTORY_SEPARATOR);

            if ($this->shouldIgnore(path: $relativePath) || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents(filename: $file->getPathname());

            if (! is_string(value: $contents)) {
                continue;
            }

            if (
                str_contains(haystack: $contents, needle: 'System\\Configuration\\AuthServiceProvider')
                || str_contains(haystack: $contents, needle: 'Avax\\Container\\Auth')
            ) {
                $matches[] = $relativePath;
            }
        }

        sort(array: $matches);

        return array_values(array: array_unique(array: $matches));
    }

    private function shouldIgnore(string $path) : bool
    {
        return array_any(array: ['vendor/', '.git/', 'build/'], callback: static fn ($ignoredPrefix) => str_starts_with(haystack: $path, needle: $ignoredPrefix));
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim(string: $repositoryRoot, characters: DIRECTORY_SEPARATOR) . '/composer.json';

        if (! is_file(filename: $composerJsonPath)) {
            return 'unknown';
        }

        $json    = file_get_contents(filename: $composerJsonPath);
        $decoded = is_string(value: $json) ? json_decode(json: $json, associative: true) : null;

        return is_array(value: $decoded) && is_string(value: $decoded['name'] ?? null)
            ? $decoded['name']
            : 'unknown';
    }
}
