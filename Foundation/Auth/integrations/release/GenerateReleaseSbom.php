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
        $composerJson = $this->readJsonFile(path: $composerJsonPath);
        $lockPath     = $composerLockPath ?? dirname(path: $composerJsonPath) . '/composer.lock';
        $components   = [];

        foreach ($this->readLockPackages(composerLockPath: $lockPath) as $package) {
            $components[] = [
                'type'    => 'library',
                'name'    => (string) ($package['name'] ?? 'unknown'),
                'version' => (string) ($package['version'] ?? 'unknown'),
                'purl'    => 'pkg:composer/' . rawurlencode(string: (string) ($package['name'] ?? 'unknown')) . '@' . rawurlencode(string: (string) ($package['version'] ?? 'unknown')),
            ];
        }

        usort(
            array   : $components,
            callback: static fn (array $left, array $right) : int => strcmp(
                string1: $left['name'],
                string2: $right['name']
            )
        );

        return [
            'bomFormat'   => 'CycloneDX',
            'specVersion' => '1.5',
            'metadata'    => [
                'component' => [
                    'type'    => 'library',
                    'name'    => is_string(value: $composerJson['name'] ?? null) ? $composerJson['name'] : 'unknown',
                    'version' => is_string(value: $composerJson['version'] ?? null) ? $composerJson['version'] : 'dev-main',
                ],
            ],
            'components'  => $components,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonFile(string $path) : array
    {
        $json = file_get_contents(filename: $path);

        if ($json === false) {
            throw new RuntimeException(message: "Could not read JSON file: {$path}");
        }

        try {
            $decoded = json_decode(json: $json, associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: "Could not decode JSON file: {$path}", previous: $exception);
        }

        if (! is_array(value: $decoded)) {
            throw new RuntimeException(message: "JSON file must decode to an object: {$path}");
        }

        return $decoded;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readLockPackages(string $composerLockPath) : array
    {
        if (! is_file(filename: $composerLockPath)) {
            return [];
        }

        $lock     = $this->readJsonFile(path: $composerLockPath);
        $packages = [];

        foreach (['packages', 'packages-dev'] as $section) {
            foreach ($lock[$section] ?? [] as $package) {
                if (! is_array(value: $package)) {
                    continue;
                }

                $packages[] = $package;
            }
        }

        return $packages;
    }
}
