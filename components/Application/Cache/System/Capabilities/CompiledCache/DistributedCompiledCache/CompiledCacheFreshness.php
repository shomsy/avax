<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

/**
 * Checks freshness of compiled cache entries.
 *
 * Compares modification times of source files vs compiled files
 * to determine if recompilation is needed.
 */
final class CompiledCacheFreshness
{
    /** @var array<string, FreshnessStatus> */
    private array $statusCache = [];

    public function __construct(
        private readonly Clock $clock = new SystemClock(),
    ) {
    }

    /**
     * Create a new freshness checker.
     */
    public static function create(?Clock $clock = null): self
    {
        return new self(clock: $clock ?? new SystemClock());
    }

    /**
     * Check freshness for multiple entries.
     *
     * @param list<CompiledCacheManifestEntry> $entries
     *
     * @return array<string, FreshnessStatus> Keyed by entry name
     */
    public function checkAll(array $entries): array
    {
        $results = [];

        foreach ($entries as $entry) {
            $results[$entry->name] = $this->check($entry);
        }

        return $results;
    }

    /**
     * Check the freshness of a compiled cache entry.
     */
    public function check(CompiledCacheManifestEntry $compiledCacheManifestEntry): FreshnessStatus
    {
        // Check cache first
        $cacheKey = $compiledCacheManifestEntry->name . ':' . $compiledCacheManifestEntry->fingerprint;

        if (isset($this->statusCache[$cacheKey])) {
            $cached = $this->statusCache[$cacheKey];

            // Cache is valid for 5 seconds
            if ($this->clock->now()->difference($cached->checkedAt)->seconds < 5) {
                return $cached;
            }
        }

        $freshnessStatus              = $this->doCheck($compiledCacheManifestEntry);
        $this->statusCache[$cacheKey] = $freshnessStatus;

        return $freshnessStatus;
    }

    /**
     * Perform the actual freshness check.
     */
    private function doCheck(CompiledCacheManifestEntry $compiledCacheManifestEntry): FreshnessStatus
    {
        // Check if compiled file exists
        if (! $compiledCacheManifestEntry->compiledFileExists()) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Compiled file does not exist',
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: 0,
                checkedAt        : $this->clock->now(),
            );
        }

        // Check if any source file is missing
        $missingSources = $this->getMissingSourceFiles($compiledCacheManifestEntry->sourceFiles);

        if ($missingSources !== []) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : sprintf('Missing source files: %s', implode(', ', $missingSources)),
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: $compiledCacheManifestEntry->getCompiledFileMtime(),
                checkedAt        : $this->clock->now(),
            );
        }

        // Compare fingerprints
        $currentFingerprint = $this->calculateFingerprint($compiledCacheManifestEntry->sourceFiles);

        if ($compiledCacheManifestEntry->fingerprint !== $currentFingerprint) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Source files have changed (fingerprint mismatch)',
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: $compiledCacheManifestEntry->getCompiledFileMtime(),
                checkedAt        : $this->clock->now(),
            );
        }

        // Compare modification times as a secondary check
        $compiledMtime = $compiledCacheManifestEntry->getCompiledFileMtime();

        if ($compiledMtime === false) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Cannot determine compiled file modification time',
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: 0,
                checkedAt        : $this->clock->now(),
            );
        }

        $sourceMtime = $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles);

        if ($sourceMtime > $compiledMtime) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Source files are newer than compiled file',
                sourceFilesMtime : $sourceMtime,
                compiledFileMtime: $compiledMtime,
                checkedAt        : $this->clock->now(),
            );
        }

        return new FreshnessStatus(
            entryName        : $compiledCacheManifestEntry->name,
            isFresh          : true,
            reason           : 'All checks passed',
            sourceFilesMtime : $sourceMtime,
            compiledFileMtime: $compiledMtime,
            checkedAt        : $this->clock->now(),
        );
    }

    /**
     * Get the maximum modification time of source files.
     *
     * @param list<string> $sourceFiles
     */
    private function getSourceFilesMtime(array $sourceFiles): int
    {
        $maxMtime = 0;

        foreach ($sourceFiles as $sourceFile) {
            if (file_exists($sourceFile)) {
                $mtime = filemtime($sourceFile);

                if ($mtime !== false && $mtime > $maxMtime) {
                    $maxMtime = $mtime;
                }
            }
        }

        return $maxMtime;
    }

    /**
     * Get list of missing source files.
     *
     * @param list<string> $sourceFiles
     *
     * @return list<string>
     */
    private function getMissingSourceFiles(array $sourceFiles): array
    {
        $missing = [];

        foreach ($sourceFiles as $sourceFile) {
            if (! file_exists($sourceFile)) {
                $missing[] = $sourceFile;
            }
        }

        return $missing;
    }

    /**
     * Calculate a fingerprint for a set of source files.
     *
     * @param list<string> $sourceFiles
     */
    private function calculateFingerprint(array $sourceFiles): string
    {
        $hashParts = [];

        foreach ($sourceFiles as $sourceFile) {
            $hashParts[] = file_exists($sourceFile) ? $sourceFile . ':' . filemtime($sourceFile) : $sourceFile . ':missing';
        }

        return hash('sha256', implode('|', $hashParts));
    }

    /**
     * Mark an entry as dirty (needs recompilation).
     */
    public function markDirty(CompiledCacheManifestEntry $compiledCacheManifestEntry): FreshnessStatus
    {
        $freshnessStatus = new FreshnessStatus(
            entryName        : $compiledCacheManifestEntry->name,
            isFresh          : false,
            reason           : 'Manually marked as dirty',
            sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
            compiledFileMtime: $compiledCacheManifestEntry->compiledFileExists() ? $compiledCacheManifestEntry->getCompiledFileMtime() : 0,
            checkedAt        : $this->clock->now(),
        );

        $cacheKey                     = $compiledCacheManifestEntry->name . ':' . $compiledCacheManifestEntry->fingerprint;
        $this->statusCache[$cacheKey] = $freshnessStatus;

        return $freshnessStatus;
    }

    /**
     * Mark an entry as fresh (no recompilation needed).
     */
    public function markFresh(CompiledCacheManifestEntry $compiledCacheManifestEntry): FreshnessStatus
    {
        $freshnessStatus = new FreshnessStatus(
            entryName        : $compiledCacheManifestEntry->name,
            isFresh          : true,
            reason           : 'Manually marked as fresh',
            sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
            compiledFileMtime: $compiledCacheManifestEntry->compiledFileExists() ? $compiledCacheManifestEntry->getCompiledFileMtime() : 0,
            checkedAt        : $this->clock->now(),
        );

        $cacheKey                     = $compiledCacheManifestEntry->name . ':' . $compiledCacheManifestEntry->fingerprint;
        $this->statusCache[$cacheKey] = $freshnessStatus;

        return $freshnessStatus;
    }

    /**
     * Get all entries that need rebuilding.
     *
     * @param list<CompiledCacheManifestEntry> $entries
     *
     * @return list<CompiledCacheManifestEntry>
     */
    public function getEntriesNeedingRebuild(array $entries): array
    {
        $needsRebuild = [];

        foreach ($entries as $entry) {
            if ($this->needsRebuild($entry)) {
                $needsRebuild[] = $entry;
            }
        }

        return $needsRebuild;
    }

    /**
     * Check if an entry needs recompilation.
     */
    public function needsRebuild(CompiledCacheManifestEntry $compiledCacheManifestEntry): bool
    {
        return ! $this->check($compiledCacheManifestEntry)->isFresh;
    }

    /**
     * Clear the freshness status cache.
     */
    public function clearCache(): void
    {
        $this->statusCache = [];
    }

    /**
     * Clear the cache for a specific entry.
     */
    public function clearEntryCache(string $entryName): void
    {
        $keysToRemove = [];

        foreach (array_keys($this->statusCache) as $key) {
            if (str_starts_with($key, $entryName . ':')) {
                $keysToRemove[] = $key;
            }
        }

        foreach ($keysToRemove as $keyToRemove) {
            unset($this->statusCache[$keyToRemove]);
        }
    }
}
