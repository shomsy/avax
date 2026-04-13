<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use JsonException;
use RuntimeException;

final readonly class GenerateReleaseSbom
{
    /**
     * @return array{
     *     bomFormat:string,
     *     specVersion:string,
     *     metadata:array<string, mixed>,
     *     components:list<array<string, mixed>>
     * }
     */
    public function execute(string $composerJsonPath, string|null $composerLockPath = null) : array
    {
        $composerJson = $this->readJsonFile($composerJsonPath);
        $lockPath = $composerLockPath ?? dirname($composerJsonPath) . '/composer.lock';
        $components = [];

        foreach ($this->readLockPackages($lockPath) as $package) {
            $components[] = [
                'type' => 'library',
                'name' => (string) ($package['name'] ?? 'unknown'),
                'version' => (string) ($package['version'] ?? 'unknown'),
                'purl' => 'pkg:composer/' . rawurlencode((string) ($package['name'] ?? 'unknown')) . '@' . rawurlencode((string) ($package['version'] ?? 'unknown')),
            ];
        }

        usort(
            $components,
            static fn (array $left, array $right) : int => strcmp(
                $left['name'],
                $right['name']
            )
        );

        return [
            'bomFormat' => 'CycloneDX',
            'specVersion' => '1.5',
            'metadata' => [
                'component' => [
                    'type' => 'library',
                    'name' => is_string($composerJson['name'] ?? null) ? $composerJson['name'] : 'unknown',
                    'version' => is_string($composerJson['version'] ?? null) ? $composerJson['version'] : 'dev-main',
                ],
            ],
            'components' => $components,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readLockPackages(string $composerLockPath) : array
    {
        if (! is_file($composerLockPath)) {
            return [];
        }

        $lock = $this->readJsonFile($composerLockPath);
        $packages = [];

        foreach (['packages', 'packages-dev'] as $section) {
            foreach ($lock[$section] ?? [] as $package) {
                if (! is_array($package)) {
                    continue;
                }

                $packages[] = $package;
            }
        }

        return $packages;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonFile(string $path) : array
    {
        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException("Could not read JSON file: {$path}");
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Could not decode JSON file: {$path}", previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException("JSON file must decode to an object: {$path}");
        }

        return $decoded;
    }
}
