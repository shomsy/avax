<?php

declare(strict_types=1);

namespace Avax\Auth\Integrations\Release;

use JsonException;
use RuntimeException;

final readonly class ReviewComposerDependencies
{
    /**
     * @return array{
     *     approved:bool,
     *     reviewed_packages:list<string>,
     *     unreviewed_packages:list<string>,
     *     unstable_packages:list<string>,
     *     unapproved_plugin_packages:list<string>,
     *     packages_from_unapproved_hosts:list<string>
     * }
     */
    public function execute(string $composerLockPath, string $policyPath) : array
    {
        $lock             = $this->readJsonFile(path: $composerLockPath);
        $policy           = $this->readJsonFile(path: $policyPath);
        $packages         = $this->readPackages(json: $lock);
        $reviewedPackages = $this->readStringList(json: $policy, key: 'reviewed_packages');
        $approvedPlugins  = array_fill_keys($this->readStringList(json: $policy, key: 'allowed_plugin_packages'), true);
        $approvedHosts    = array_fill_keys($this->readStringList(json: $policy, key: 'allowed_source_hosts'), true);

        $unreviewed        = [];
        $unstable          = [];
        $unapprovedPlugins = [];
        $unapprovedHosts   = [];

        foreach ($packages as $package) {
            $name    = (string) ($package['name'] ?? 'unknown');
            $version = (string) ($package['version'] ?? 'unknown');
            $type    = is_string($package['type'] ?? null) ? $package['type'] : null;

            if (! in_array($name, $reviewedPackages, true)) {
                $unreviewed[] = $name;
            }

            if ($this->isUnstableVersion(version: $version)) {
                $unstable[] = $name . '@' . $version;
            }

            if ($type === 'composer-plugin' && ! array_key_exists($name, $approvedPlugins)) {
                $unapprovedPlugins[] = $name;
            }

            $host = $this->readPackageHost(package: $package);

            if ($host !== null && $approvedHosts !== [] && ! array_key_exists($host, $approvedHosts)) {
                $unapprovedHosts[] = $name . '@' . $host;
            }
        }

        sort($reviewedPackages);
        sort($unreviewed);
        sort($unstable);
        sort($unapprovedPlugins);
        sort($unapprovedHosts);

        return [
            'approved'                       => $unreviewed === [] && $unstable === [] && $unapprovedPlugins === [] && $unapprovedHosts === [],
            'reviewed_packages'              => $reviewedPackages,
            'unreviewed_packages'            => array_values(array_unique($unreviewed)),
            'unstable_packages'              => array_values(array_unique($unstable)),
            'unapproved_plugin_packages'     => array_values(array_unique($unapprovedPlugins)),
            'packages_from_unapproved_hosts' => array_values(array_unique($unapprovedHosts)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function readJsonFile(string $path) : array
    {
        $json = file_get_contents($path);

        if ($json === false) {
            throw new RuntimeException(message: "Could not read JSON file: {$path}");
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(message: "Could not decode JSON file: {$path}", previous: $exception);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException(message: "JSON file must decode to an object: {$path}");
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $json
     *
     * @return list<array<string, mixed>>
     */
    private function readPackages(array $json) : array
    {
        $packages = [];

        foreach (['packages', 'packages-dev'] as $section) {
            foreach ($json[$section] ?? [] as $package) {
                if (is_array($package)) {
                    $packages[] = $package;
                }
            }
        }

        return $packages;
    }

    /**
     * @param array<string, mixed> $json
     *
     * @return list<string>
     */
    private function readStringList(array $json, string $key) : array
    {
        $values = $json[$key] ?? [];

        if (! is_array($values)) {
            return [];
        }

        $resolved = [];

        foreach ($values as $value) {
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $resolved[] = trim($value);
        }

        $resolved = array_values(array_unique($resolved));
        sort($resolved);

        return $resolved;
    }

    private function isUnstableVersion(string $version) : bool
    {
        $normalized = strtolower(trim($version));

        if ($normalized === '') {
            return false;
        }

        return str_starts_with($normalized, 'dev-')
            || str_contains($normalized, '-dev')
            || str_contains($normalized, 'alpha')
            || str_contains($normalized, 'beta')
            || str_contains($normalized, 'rc');
    }

    /**
     * @param array<string, mixed> $package
     */
    private function readPackageHost(array $package) : string|null
    {
        foreach (['dist', 'source'] as $section) {
            $url = $package[$section]['url'] ?? null;

            if (! is_string($url) || trim($url) === '') {
                continue;
            }

            $host = parse_url($url, PHP_URL_HOST);

            if (is_string($host) && trim($host) !== '') {
                return strtolower($host);
            }
        }

        return null;
    }
}
