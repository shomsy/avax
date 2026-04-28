<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Integrations\Release;

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
        $approvedPlugins  = array_fill_keys(keys: $this->readStringList(json: $policy, key: 'allowed_plugin_packages'), value: true);
        $approvedHosts    = array_fill_keys(keys: $this->readStringList(json: $policy, key: 'allowed_source_hosts'), value: true);

        $unreviewed        = [];
        $unstable          = [];
        $unapprovedPlugins = [];
        $unapprovedHosts   = [];

        foreach ($packages as $package) {
            $name    = (string) ($package['name'] ?? 'unknown');
            $version = (string) ($package['version'] ?? 'unknown');
            $type    = is_string(value: $package['type'] ?? null) ? $package['type'] : null;

            if (! in_array(needle: $name, haystack: $reviewedPackages, strict: true)) {
                $unreviewed[] = $name;
            }

            if ($this->isUnstableVersion(version: $version)) {
                $unstable[] = $name . '@' . $version;
            }

            if ($type === 'composer-plugin' && ! array_key_exists(key: $name, array: $approvedPlugins)) {
                $unapprovedPlugins[] = $name;
            }

            $host = $this->readPackageHost(package: $package);

            if ($host !== null && $approvedHosts !== [] && ! array_key_exists(key: $host, array: $approvedHosts)) {
                $unapprovedHosts[] = $name . '@' . $host;
            }
        }

        sort(array: $reviewedPackages);
        sort(array: $unreviewed);
        sort(array: $unstable);
        sort(array: $unapprovedPlugins);
        sort(array: $unapprovedHosts);

        return [
            'approved'                       => $unreviewed === [] && $unstable === [] && $unapprovedPlugins === [] && $unapprovedHosts === [],
            'reviewed_packages'              => $reviewedPackages,
            'unreviewed_packages'            => array_values(array: array_unique(array: $unreviewed)),
            'unstable_packages'              => array_values(array: array_unique(array: $unstable)),
            'unapproved_plugin_packages'     => array_values(array: array_unique(array: $unapprovedPlugins)),
            'packages_from_unapproved_hosts' => array_values(array: array_unique(array: $unapprovedHosts)),
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
     * @param array<string, mixed> $json
     *
     * @return list<array<string, mixed>>
     */
    private function readPackages(array $json) : array
    {
        $packages = [];

        foreach (['packages', 'packages-dev'] as $section) {
            foreach ($json[$section] ?? [] as $package) {
                if (is_array(value: $package)) {
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

        if (! is_array(value: $values)) {
            return [];
        }

        $resolved = [];

        foreach ($values as $value) {
            if (! is_string(value: $value) || trim(string: $value) === '') {
                continue;
            }

            $resolved[] = trim(string: $value);
        }

        $resolved = array_values(array: array_unique(array: $resolved));
        sort(array: $resolved);

        return $resolved;
    }

    private function isUnstableVersion(string $version) : bool
    {
        $normalized = strtolower(string: trim(string: $version));

        if ($normalized === '') {
            return false;
        }

        return str_starts_with(haystack: $normalized, needle: 'dev-')
            || str_contains(haystack: $normalized, needle: '-dev')
            || str_contains(haystack: $normalized, needle: 'alpha')
            || str_contains(haystack: $normalized, needle: 'beta')
            || str_contains(haystack: $normalized, needle: 'rc');
    }

    /**
     * @param array<string, mixed> $package
     */
    private function readPackageHost(array $package) : string|null
    {
        foreach (['dist', 'source'] as $section) {
            $url = $package[$section]['url'] ?? null;

            if (! is_string(value: $url) || trim(string: $url) === '') {
                continue;
            }

            $host = parse_url(url: $url, component: PHP_URL_HOST);

            if (is_string(value: $host) && trim(string: $host) !== '') {
                return strtolower(string: $host);
            }
        }

        return null;
    }
}
