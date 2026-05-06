<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class CompiledCacheManifestEntry
{
    public function __construct(
        public CompiledCacheName $compiledCacheName,
        public CompiledCachePath $compiledCachePath,
        public Timestamp $timestamp,
        public string $sourceFingerprint,
        public ?string $phpVersion = null,
        public ?string $frameworkVersion = null,
    ) {
    }

    /**
     * @param array{name: string, path: string, createdAt: int, sourceFingerprint: string} $data
     */
    public static function fromArray(array $data): self
    {
        return self::create(
            name             : $data['name'],
            path             : $data['path'],
            createdAt        : $data['createdAt'],
            sourceFingerprint: $data['sourceFingerprint'],
        );
    }

    public static function create(
        string $name,
        string $path,
        int $createdAt,
        string $sourceFingerprint,
    ): self {
        return new self(
            compiledCacheName: new CompiledCacheName(name: $name),
            compiledCachePath: new CompiledCachePath(path: $path),
            timestamp        : Timestamp::fromUnixTime(timestamp: $createdAt),
            sourceFingerprint: $sourceFingerprint,
        );
    }

    /**
     * @return array{
     *     name: string,
     *     path: string,
     *     createdAt: int,
     *     sourceFingerprint: string,
     *     phpVersion: string|null,
     *     frameworkVersion: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'name'              => $this->compiledCacheName->toString(),
            'path'              => $this->compiledCachePath->toString(),
            'createdAt'         => $this->timestamp->seconds,
            'sourceFingerprint' => $this->sourceFingerprint,
            'phpVersion'        => $this->phpVersion,
            'frameworkVersion'  => $this->frameworkVersion,
        ];
    }
}
