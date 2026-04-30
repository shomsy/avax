<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ComponentManifest;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Discovers and loads component manifests from the filesystem.
 */
final class ComponentDiscovery
{
    /**
     * @var array<string, ComponentManifest>
     */
    private array $manifests = [];

    /**
     * Discover manifests in component directories.
     *
     * @param list<string> $searchPaths
     */
    public function discover(array $searchPaths) : self
    {
        foreach ($searchPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $this->searchDirectory($path);
        }

        return $this;
    }

    private function searchDirectory(string $path) : void
    {
        $manifestFile = $path . '/component.php';

        if (file_exists($manifestFile)) {
            $manifest = require $manifestFile;

            if ($manifest instanceof ComponentManifest) {
                $this->manifests[$manifest->name] = $manifest;
            }
        }

        // Recurse into subdirectories
        $iterator = new RecursiveDirectoryIterator(
            $path,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_PATHNAME,
        );

        foreach (new RecursiveIteratorIterator($iterator) as $subPath) {
            if (str_ends_with($subPath, '/component.php')) {
                $manifest = require $subPath;

                if ($manifest instanceof ComponentManifest) {
                    $this->manifests[$manifest->name] = $manifest;
                }
            }
        }
    }

    /**
     * Register a manifest directly.
     */
    public function register(ComponentManifest $manifest) : self
    {
        $this->manifests[$manifest->name] = $manifest;

        return $this;
    }

    /**
     * @return array<string, ComponentManifest>
     */
    public function all() : array
    {
        return $this->manifests;
    }

    public function get(string $name) : ComponentManifest|null
    {
        return $this->manifests[$name] ?? null;
    }

    /**
     * Find components that depend on the given component.
     *
     * @return list<string>
     */
    public function whoDependsOn(string $name) : array
    {
        $dependents = [];

        foreach ($this->manifests as $manifestName => $manifest) {
            if (in_array($name, $manifest->dependsOn, true)) {
                $dependents[] = $manifestName;
            }
        }

        return $dependents;
    }

    /**
     * Detect missing dependencies (components that depend on non-existent components).
     *
     * @return list<string>
     */
    public function detectMissingDependencies() : array
    {
        $missing    = [];
        $knownNames = array_keys($this->manifests);

        foreach ($this->manifests as $name => $manifest) {
            foreach ($manifest->dependsOn as $dep) {
                if (! in_array($dep, $knownNames, true)) {
                    $missing[] = sprintf(
                        "Component '%s' depends on '%s' which is not registered",
                        $name,
                        $dep,
                    );
                }
            }
        }

        return $missing;
    }
}
