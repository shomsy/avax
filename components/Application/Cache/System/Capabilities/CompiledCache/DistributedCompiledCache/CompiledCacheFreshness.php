<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

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
    ) {}

    /**
     * Create a new freshness checker.
     */
    public static function create(Clock|null $clock = null) : self
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
    public function checkAll(array $entries) : array
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
    public function check(CompiledCacheManifestEntry $compiledCacheManifestEntry) : FreshnessStatus
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
    private function doCheck(CompiledCacheManifestEntry $compiledCacheManifestEntry) : FreshnessStatus
    {
        // Check if compiled file exists
        if (! $compiledCacheManifestEntry->compiledFileExists()) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Compiled file does not exist',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: 0,
            );
        }

        // Check if any source file is missing
        $missingSources = $this->getMissingSourceFiles($compiledCacheManifestEntry->sourceFiles);

        if ($missingSources !== []) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : sprintf('Missing source files: %s', implode(', ', $missingSources)),
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: $compiledCacheManifestEntry->getCompiledFileMtime(),
            );
        }

        // Compare fingerprints
        $currentFingerprint = $this->calculateFingerprint($compiledCacheManifestEntry->sourceFiles);

        if ($compiledCacheManifestEntry->fingerprint !== $currentFingerprint) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Source files have changed (fingerprint mismatch)',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: $compiledCacheManifestEntry->getCompiledFileMtime(),
            );
        }

        // Compare modification times as a secondary check
        $compiledMtime = $compiledCacheManifestEntry->getCompiledFileMtime();

        if ($compiledMtime === false) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Cannot determine compiled file modification time',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
                compiledFileMtime: 0,
            );
        }

        $sourceMtime = $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles);

        if ($sourceMtime > $compiledMtime) {
            return new FreshnessStatus(
                entryName        : $compiledCacheManifestEntry->name,
                isFresh          : false,
                reason           : 'Source files are newer than compiled file',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $sourceMtime,
                compiledFileMtime: $compiledMtime,
            );
        }

        return new FreshnessStatus(
            entryName        : $compiledCacheManifestEntry->name,
            isFresh          : true,
            reason           : 'All checks passed',
            checkedAt        : $this->clock->now(),
            sourceFilesMtime : $sourceMtime,
            compiledFileMtime: $compiledMtime,
        );
    }

    /**
     * Get the maximum modification time of source files.
     *
     * @param list<string> $sourceFiles
     */
    private function getSourceFilesMtime(array $sourceFiles) : int
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
    private function getMissingSourceFiles(array $sourceFiles) : array
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
    private function calculateFingerprint(array $sourceFiles) : string
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
    public function markDirty(CompiledCacheManifestEntry $compiledCacheManifestEntry) : FreshnessStatus
    {
        $freshnessStatus = new FreshnessStatus(
            entryName        : $compiledCacheManifestEntry->name,
            isFresh          : false,
            reason           : 'Manually marked as dirty',
            checkedAt        : $this->clock->now(),
            sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
            compiledFileMtime: $compiledCacheManifestEntry->compiledFileExists() ? $compiledCacheManifestEntry->getCompiledFileMtime() : 0,
        );

        $cacheKey                     = $compiledCacheManifestEntry->name . ':' . $compiledCacheManifestEntry->fingerprint;
        $this->statusCache[$cacheKey] = $freshnessStatus;

        return $freshnessStatus;
    }

    /**
     * Mark an entry as fresh (no recompilation needed).
     */
    public function markFresh(CompiledCacheManifestEntry $compiledCacheManifestEntry) : FreshnessStatus
    {
        $freshnessStatus = new FreshnessStatus(
            entryName        : $compiledCacheManifestEntry->name,
            isFresh          : true,
            reason           : 'Manually marked as fresh',
            checkedAt        : $this->clock->now(),
            sourceFilesMtime : $this->getSourceFilesMtime($compiledCacheManifestEntry->sourceFiles),
            compiledFileMtime: $compiledCacheManifestEntry->compiledFileExists() ? $compiledCacheManifestEntry->getCompiledFileMtime() : 0,
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
    public function getEntriesNeedingRebuild(array $entries) : array
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
    public function needsRebuild(CompiledCacheManifestEntry $compiledCacheManifestEntry) : bool
    {
        return ! $this->check($compiledCacheManifestEntry)->isFresh;
    }

    /**
     * Clear the freshness status cache.
     */
    public function clearCache() : void
    {
        $this->statusCache = [];
    }

    /**
     * Clear the cache for a specific entry.
     */
    public function clearEntryCache(string $entryName) : void
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

/**
 * Value object representing the freshness status of a compiled cache entry.
 */
final readonly class FreshnessStatus
{
    public function __construct(
        public string    $entryName,
        public bool      $isFresh,
        public string    $reason,
        public Timestamp $timestamp,
        public int       $sourceFilesMtime,
        public int|false $compiledFileMtime,
    ) {}

    /**
     * Convert to array representation.
     *
     * @return array{
     *     entryName: string,
     *     isFresh: bool,
     *     reason: string,
     *     checkedAt: int,
     *     sourceFilesMtime: int,
     *     compiledFileMtime: int|false,
     *     isStale: bool,
     *     isMissing: bool,
     *     compiledFileAge: int
     * }
     */
    public function toArray() : array
    {
        return [
            'entryName'         => $this->entryName,
            'isFresh'           => $this->isFresh,
            'reason'            => $this->reason,
            'checkedAt' => $this->timestamp->seconds,
            'sourceFilesMtime'  => $this->sourceFilesMtime,
            'compiledFileMtime' => $this->compiledFileMtime,
            'isStale'           => $this->isStale(),
            'isMissing'         => $this->isMissing(),
            'compiledFileAge'   => $this->getCompiledFileAge(),
        ];
    }

    /**
     * Check if the entry is stale (not fresh).
     */
    public function isStale() : bool
    {
        return ! $this->isFresh;
    }

    /**
     * Check if the entry is missing.
     */
    public function isMissing() : bool
    {
        return $this->compiledFileMtime === 0 || $this->compiledFileMtime === false;
    }

    /**
     * Get the age of the compiled file in seconds.
     */
    public function getCompiledFileAge() : int
    {
        if ($this->compiledFileMtime === false || $this->compiledFileMtime === 0) {
            return 0;
        }

        return $this->timestamp->seconds - $this->compiledFileMtime;
    }
}
