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
        private readonly Clock $clock = new SystemClock()
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
    public function check(CompiledCacheManifestEntry $entry) : FreshnessStatus
    {
        // Check cache first
        $cacheKey = $entry->name . ':' . $entry->fingerprint;

        if (isset($this->statusCache[$cacheKey])) {
            $cached = $this->statusCache[$cacheKey];

            // Cache is valid for 5 seconds
            if ($this->clock->now()->difference($cached->checkedAt)->seconds < 5) {
                return $cached;
            }
        }

        $status                       = $this->doCheck($entry);
        $this->statusCache[$cacheKey] = $status;

        return $status;
    }

    /**
     * Perform the actual freshness check.
     */
    private function doCheck(CompiledCacheManifestEntry $entry) : FreshnessStatus
    {
        // Check if compiled file exists
        if (! $entry->compiledFileExists()) {
            return new FreshnessStatus(
                entryName        : $entry->name,
                isFresh          : false,
                reason           : 'Compiled file does not exist',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($entry->sourceFiles),
                compiledFileMtime: 0
            );
        }

        // Check if any source file is missing
        $missingSources = $this->getMissingSourceFiles($entry->sourceFiles);

        if (! empty($missingSources)) {
            return new FreshnessStatus(
                entryName        : $entry->name,
                isFresh          : false,
                reason           : sprintf('Missing source files: %s', implode(', ', $missingSources)),
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($entry->sourceFiles),
                compiledFileMtime: $entry->getCompiledFileMtime()
            );
        }

        // Compare fingerprints
        $currentFingerprint = $this->calculateFingerprint($entry->sourceFiles);

        if ($entry->fingerprint !== $currentFingerprint) {
            return new FreshnessStatus(
                entryName        : $entry->name,
                isFresh          : false,
                reason           : 'Source files have changed (fingerprint mismatch)',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($entry->sourceFiles),
                compiledFileMtime: $entry->getCompiledFileMtime()
            );
        }

        // Compare modification times as a secondary check
        $compiledMtime = $entry->getCompiledFileMtime();

        if ($compiledMtime === false) {
            return new FreshnessStatus(
                entryName        : $entry->name,
                isFresh          : false,
                reason           : 'Cannot determine compiled file modification time',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $this->getSourceFilesMtime($entry->sourceFiles),
                compiledFileMtime: 0
            );
        }

        $sourceMtime = $this->getSourceFilesMtime($entry->sourceFiles);

        if ($sourceMtime > $compiledMtime) {
            return new FreshnessStatus(
                entryName        : $entry->name,
                isFresh          : false,
                reason           : 'Source files are newer than compiled file',
                checkedAt        : $this->clock->now(),
                sourceFilesMtime : $sourceMtime,
                compiledFileMtime: $compiledMtime
            );
        }

        return new FreshnessStatus(
            entryName        : $entry->name,
            isFresh          : true,
            reason           : 'All checks passed',
            checkedAt        : $this->clock->now(),
            sourceFilesMtime : $sourceMtime,
            compiledFileMtime: $compiledMtime
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

        foreach ($sourceFiles as $file) {
            if (file_exists($file)) {
                $mtime = filemtime($file);

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

        foreach ($sourceFiles as $file) {
            if (! file_exists($file)) {
                $missing[] = $file;
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

        foreach ($sourceFiles as $file) {
            if (file_exists($file)) {
                $hashParts[] = $file . ':' . filemtime($file);
            } else {
                $hashParts[] = $file . ':missing';
            }
        }

        return hash('sha256', implode('|', $hashParts));
    }

    /**
     * Mark an entry as dirty (needs recompilation).
     */
    public function markDirty(CompiledCacheManifestEntry $entry) : FreshnessStatus
    {
        $status = new FreshnessStatus(
            entryName        : $entry->name,
            isFresh          : false,
            reason           : 'Manually marked as dirty',
            checkedAt        : $this->clock->now(),
            sourceFilesMtime : $this->getSourceFilesMtime($entry->sourceFiles),
            compiledFileMtime: $entry->compiledFileExists() ? $entry->getCompiledFileMtime() : 0
        );

        $cacheKey                     = $entry->name . ':' . $entry->fingerprint;
        $this->statusCache[$cacheKey] = $status;

        return $status;
    }

    /**
     * Mark an entry as fresh (no recompilation needed).
     */
    public function markFresh(CompiledCacheManifestEntry $entry) : FreshnessStatus
    {
        $status = new FreshnessStatus(
            entryName        : $entry->name,
            isFresh          : true,
            reason           : 'Manually marked as fresh',
            checkedAt        : $this->clock->now(),
            sourceFilesMtime : $this->getSourceFilesMtime($entry->sourceFiles),
            compiledFileMtime: $entry->compiledFileExists() ? $entry->getCompiledFileMtime() : 0
        );

        $cacheKey                     = $entry->name . ':' . $entry->fingerprint;
        $this->statusCache[$cacheKey] = $status;

        return $status;
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
    public function needsRebuild(CompiledCacheManifestEntry $entry) : bool
    {
        return ! $this->check($entry)->isFresh;
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

        foreach ($this->statusCache as $key => $status) {
            if (str_starts_with($key, $entryName . ':')) {
                $keysToRemove[] = $key;
            }
        }

        foreach ($keysToRemove as $key) {
            unset($this->statusCache[$key]);
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
        public Timestamp $checkedAt,
        public int       $sourceFilesMtime,
        public int|false $compiledFileMtime
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
            'checkedAt'         => $this->checkedAt->seconds,
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

        return $this->checkedAt->seconds - $this->compiledFileMtime;
    }
}
