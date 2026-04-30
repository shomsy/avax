<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\DistributedCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use RuntimeException;



/**
 * Value object representing a single compiled cache manifest entry.
 */
final readonly class CompiledCacheManifestEntry
{
    /**
     * @param string       $name             Unique name/identifier for this entry
     * @param string       $compiledPath     Path to the compiled file
     * @param list<string> $sourceFiles      List of source file paths
     * @param string       $fingerprint      Hash fingerprint of source files
     * @param Timestamp    $createdAt        When this entry was created
     * @param Timestamp    $updatedAt        When this entry was last updated
     * @param string|null  $type             Type of compiled cache (config, routes, views, etc.)
     * @param string|null  $phpVersion       PHP version used for compilation
     * @param string|null  $frameworkVersion Framework version used for compilation
     */
    public function __construct(
        public string      $name,
        public string      $compiledPath,
        public array       $sourceFiles,
        public string      $fingerprint,
        public Timestamp   $createdAt,
        public Timestamp   $updatedAt,
        public string|null $type = null,
        public string|null $phpVersion = null,
        public string|null $frameworkVersion = null,
    ) {}

    /**
     * Create from array representation.
     *
     * @param array{
     *     name: string,
     *     compiledPath: string,
     *     sourceFiles: list<string>,
     *     fingerprint: string,
     *     createdAt: int,
     *     updatedAt: int,
     *     type?: string|null,
     *     phpVersion?: string|null,
     *     frameworkVersion?: string|null
     * } $data
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            name            : $data['name'],
            compiledPath    : $data['compiledPath'],
            sourceFiles     : $data['sourceFiles'],
            fingerprint     : $data['fingerprint'],
            createdAt       : Timestamp::fromUnixTime($data['createdAt']),
            updatedAt       : Timestamp::fromUnixTime($data['updatedAt']),
            type            : $data['type'] ?? null,
            phpVersion      : $data['phpVersion'] ?? null,
            frameworkVersion: $data['frameworkVersion'] ?? null,
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array{
     *     name: string,
     *     compiledPath: string,
     *     sourceFiles: list<string>,
     *     fingerprint: string,
     *     createdAt: int,
     *     updatedAt: int,
     *     type: string|null,
     *     phpVersion: string|null,
     *     frameworkVersion: string|null
     * }
     */
    public function toArray() : array
    {
        return [
            'name'             => $this->name,
            'compiledPath'     => $this->compiledPath,
            'sourceFiles'      => $this->sourceFiles,
            'fingerprint'      => $this->fingerprint,
            'createdAt'        => $this->createdAt->seconds,
            'updatedAt'        => $this->updatedAt->seconds,
            'type'             => $this->type,
            'phpVersion'       => $this->phpVersion,
            'frameworkVersion' => $this->frameworkVersion,
        ];
    }

    /**
     * Create a copy with an updated timestamp.
     */
    public function withUpdatedAt(Timestamp $timestamp) : self
    {
        return new self(
            name            : $this->name,
            compiledPath    : $this->compiledPath,
            sourceFiles     : $this->sourceFiles,
            fingerprint     : $this->fingerprint,
            createdAt       : $this->createdAt,
            updatedAt       : $timestamp,
            type            : $this->type,
            phpVersion      : $this->phpVersion,
            frameworkVersion: $this->frameworkVersion,
        );
    }

    /**
     * Get the modification time of the compiled file.
     */
    public function getCompiledFileMtime() : int|false
    {
        if (! $this->compiledFileExists()) {
            return false;
        }

        return filemtime($this->compiledPath);
    }

    /**
     * Check if the compiled file exists.
     */
    public function compiledFileExists() : bool
    {
        return file_exists($this->compiledPath);
    }
}