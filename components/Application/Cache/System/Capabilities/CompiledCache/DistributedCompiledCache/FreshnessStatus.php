<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

/**
 * Value object representing the freshness status of a compiled cache entry.
 */
final readonly class FreshnessStatus
{
    public Timestamp $timestamp;

    public function __construct(
        public string $entryName,
        public bool $isFresh,
        public string $reason,
        public Timestamp $timestamp,
        public int  $sourceFilesMtime,
        public int|false $compiledFileMtime,
    )
    {
        $this->timestamp = $timestamp;
    }

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
            'entryName'        => $this->entryName,
            'isFresh'          => $this->isFresh,
            'reason'           => $this->reason,
            'checkedAt' => $this->timestamp->seconds,
            'sourceFilesMtime' => $this->sourceFilesMtime,
            'compiledFileMtime' => $this->compiledFileMtime,
            'isStale'          => $this->isStale(),
            'isMissing'        => $this->isMissing(),
            'compiledFileAge'  => $this->getCompiledFileAge(),
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
