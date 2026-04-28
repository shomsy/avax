<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Cache\System\Foundation\Time\Timestamp;

final readonly class CompiledCacheManifestEntry
{
    public function __construct(
        public CompiledCacheName $name,
        public CompiledCachePath $path,
        public Timestamp         $createdAt,
        public string            $sourceFingerprint,
        public string|null       $phpVersion = null,
        public string|null       $frameworkVersion = null
    ) {}

    public static function fromArray(array $data) : self
    {
        return self::create(
            name             : $data['name'],
            path             : $data['path'],
            createdAt        : $data['createdAt'],
            sourceFingerprint: $data['sourceFingerprint']
        );
    }

    public static function create(
        string $name,
        string $path,
        int    $createdAt,
        string $sourceFingerprint
    ) : self
    {
        return new self(
            name             : new CompiledCacheName(name: $name),
            path             : new CompiledCachePath(path: $path),
            createdAt        : Timestamp::fromUnixTime(timestamp: $createdAt),
            sourceFingerprint: $sourceFingerprint
        );
    }

    public function toArray() : array
    {
        return [
            'name'              => $this->name->toString(),
            'path'              => $this->path->toString(),
            'createdAt'         => $this->createdAt->seconds,
            'sourceFingerprint' => $this->sourceFingerprint,
            'phpVersion'        => $this->phpVersion,
            'frameworkVersion'  => $this->frameworkVersion,
        ];
    }
}