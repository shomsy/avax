<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

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

        if (! is_file($migrationGuide)) {
            $notes[] = 'Migration guide is missing.';
        }

        if (! is_file($upgradeTest)) {
            $notes[] = 'Automated upgrade boundary test is missing.';
        }

        if ($legacyReferences !== []) {
            $notes[] = 'Legacy namespace references still exist in tracked files.';
        }

        return [
            'package'                     => $this->detectPackageName(repositoryRoot: $repositoryRoot),
            'clean'                       => $legacyReferences === [] && is_file($migrationGuide) && is_file($upgradeTest),
            'current_namespace'           => 'Avax\\Auth\\System\\',
            'migration_documented'        => is_file($migrationGuide),
            'automated_upgrade_test'      => is_file($upgradeTest),
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
            iterator: new RecursiveDirectoryIterator(directory: $repositoryRoot, flags: RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $relativePath = ltrim(str_replace($repositoryRoot, '', $file->getPathname()), DIRECTORY_SEPARATOR);

            if ($this->shouldIgnore(path: $relativePath) || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname());

            if (! is_string($contents)) {
                continue;
            }

            if (
                str_contains($contents, 'System\\Configuration\\AuthServiceProvider')
                || str_contains($contents, 'Avax\\Container\\Auth')
            ) {
                $matches[] = $relativePath;
            }
        }

        sort($matches);

        return array_values(array_unique($matches));
    }

    private function shouldIgnore(string $path) : bool
    {
        foreach (['vendor/', '.git/', 'build/'] as $ignoredPrefix) {
            if (str_starts_with($path, $ignoredPrefix)) {
                return true;
            }
        }

        return false;
    }

    private function detectPackageName(string $repositoryRoot) : string
    {
        $composerJsonPath = rtrim($repositoryRoot, DIRECTORY_SEPARATOR) . '/composer.json';

        if (! is_file($composerJsonPath)) {
            return 'unknown';
        }

        $json    = file_get_contents($composerJsonPath);
        $decoded = is_string($json) ? json_decode($json, true) : null;

        return is_array($decoded) && is_string($decoded['name'] ?? null)
            ? $decoded['name']
            : 'unknown';
    }
}
