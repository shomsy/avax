<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use RuntimeException;

/**
 * Manages compiled/optimized cache manifest for distributed cache systems.
 *
 * Tracks which config/routes/views are compiled, providing methods to
 * load, save, check freshness, and manage manifest entries.
 * Uses JSON file-based storage for the manifest.
 */
final class CompiledCacheManifest
{
    /** @var array<string, CompiledCacheManifestEntry> */
    private array $entries = [];

    private string|null $manifestPath = null;

    public function __construct(
        private readonly Clock $clock = new SystemClock(),
    ) {}

    /**
     * Create a new empty manifest.
     */
    public static function empty(Clock $clock = null) : self
    {
        return new self(clock: $clock ?? new SystemClock());
    }

    /**
     * Load a manifest from a JSON file.
     *
     * @throws RuntimeException if the file exists but contains invalid JSON
     */
    public static function load(string $path, Clock $clock = null) : self
    {
        $manifest               = new self(clock: $clock ?? new SystemClock());
        $manifest->manifestPath = $path;

        if (! file_exists($path)) {
            return $manifest;
        }

        $content = file_get_contents($path);

        if ($content === false) {
            throw new RuntimeException(
                sprintf('Failed to read manifest file: %s', $path),
            );
        }

        $data = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new RuntimeException(
                sprintf('Invalid manifest file format: %s', $path),
            );
        }

        foreach ($data['entries'] ?? [] as $name => $entryData) {
            $entry                    = CompiledCacheManifestEntry::fromArray($entryData);
            $manifest->entries[$name] = $entry;
        }

        return $manifest;
    }

    /**
     * Save the manifest to a JSON file.
     *
     * @throws RuntimeException if the file cannot be written
     */
    public function save(string $path = null) : void
    {
        $savePath = $path ?? $this->manifestPath;

        if ($savePath === null) {
            throw new RuntimeException('No manifest path specified for saving');
        }

        $directory = dirname($savePath);

        if (! is_dir($directory) && ! mkdir($directory, 0o755, true)) {
            throw new RuntimeException(
                sprintf('Failed to create manifest directory: %s', $directory),
            );
        }

        $data = [
            'version'     => '1.0',
            'generatedAt' => $this->clock->now()->seconds,
            'entries'     => array_map(
                static fn (CompiledCacheManifestEntry $compiledCacheManifestEntry) : array => $compiledCacheManifestEntry->toArray(),
                $this->entries,
            ),
        ];

        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($content === false) {
            throw new RuntimeException('Failed to encode manifest data as JSON');
        }

        $result = file_put_contents($savePath, $content, LOCK_EX);

        if ($result === false) {
            throw new RuntimeException(
                sprintf('Failed to write manifest file: %s', $savePath),
            );
        }

        $this->manifestPath = $savePath;
    }

    /**
     * Add an entry to the manifest.
     */
    public function addEntry(
        string $name,
        string $compiledPath,
        array  $sourceFiles,
        string $type = null,
        string $phpVersion = null,
        string $frameworkVersion = null,
    ) : CompiledCacheManifestEntry
    {
        $now = $this->clock->now();

        // Calculate fingerprint from source files
        $fingerprint = $this->calculateFingerprint($sourceFiles);

        $compiledCacheManifestEntry = new CompiledCacheManifestEntry(
            name            : $name,
            compiledPath    : $compiledPath,
            sourceFiles     : $sourceFiles,
            fingerprint     : $fingerprint,
            createdAt       : $now,
            updatedAt       : $now,
            type            : $type,
            phpVersion      : $phpVersion ?? PHP_VERSION,
            frameworkVersion: $frameworkVersion,
        );

        $this->entries[$name] = $compiledCacheManifestEntry;

        return $compiledCacheManifestEntry;
    }

    /**
     * Calculate a fingerprint for a set of source files.
     *
     * @param list<string> $sourceFiles
     */
    private function calculateFingerprint(array $sourceFiles) : string
    {
        $hashParts = [];

        foreach ($sourceFiles as $sourceFile) {
            $hashParts[] = file_exists($sourceFile) ? $sourceFile . ':' . filemtime($sourceFile) : $sourceFile . ':missing';
        }

        return hash('sha256', implode('|', $hashParts));
    }

    /**
     * Set an entry directly.
     */
    public function setEntry(CompiledCacheManifestEntry $compiledCacheManifestEntry) : void
    {
        $this->entries[$compiledCacheManifestEntry->name] = $compiledCacheManifestEntry;
    }

    /**
     * Remove an entry from the manifest.
     */
    public function removeEntry(string $name) : bool
    {
        if (! isset($this->entries[$name])) {
            return false;
        }

        unset($this->entries[$name]);

        return true;
    }

    /**
     * Get an entry by name.
     */
    public function getEntry(string $name) : CompiledCacheManifestEntry|null
    {
        return $this->entries[$name] ?? null;
    }

    /**
     * Check if an entry exists.
     */
    public function hasEntry(string $name) : bool
    {
        return isset($this->entries[$name]);
    }

    /**
     * Get all entries.
     *
     * @return array<string, CompiledCacheManifestEntry>
     */
    public function getAllEntries() : array
    {
        return $this->entries;
    }

    /**
     * Get entries by type.
     *
     * @return list<CompiledCacheManifestEntry>
     */
    public function getEntriesByType(string $type) : array
    {
        $filtered = [];

        foreach ($this->entries as $entry) {
            if ($entry->type === $type) {
                $filtered[] = $entry;
            }
        }

        return $filtered;
    }

    /**
     * Get all stale entries (source files have changed).
     *
     * @return list<CompiledCacheManifestEntry>
     */
    public function getStaleEntries() : array
    {
        $stale = [];

        foreach ($this->entries as $name => $entry) {
            if (! $this->isFresh($name)) {
                $stale[] = $entry;
            }
        }

        return $stale;
    }

    /**
     * Check if an entry is fresh (source files haven't changed).
     */
    public function isFresh(string $name) : bool
    {
        $entry = $this->entries[$name] ?? null;

        if ($entry === null) {
            return false;
        }

        $currentFingerprint = $this->calculateFingerprint($entry->sourceFiles);

        return $entry->fingerprint === $currentFingerprint;
    }

    /**
     * Get the count of entries.
     */
    public function count() : int
    {
        return count($this->entries);
    }

    /**
     * Clear all entries.
     */
    public function clear() : void
    {
        $this->entries = [];
    }

    /**
     * Update the timestamp of an entry.
     */
    public function touchEntry(string $name) : bool
    {
        if (! isset($this->entries[$name])) {
            return false;
        }

        $entry                = $this->entries[$name];
        $this->entries[$name] = $entry->withUpdatedAt($this->clock->now());

        return true;
    }

    /**
     * Get the manifest path.
     */
    public function getManifestPath() : string|null
    {
        return $this->manifestPath;
    }
}
