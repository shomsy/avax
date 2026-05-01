<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class CompiledCacheArtifact
{
    public CompiledCacheName $compiledCacheName;

    public CompiledCachePath $compiledCachePath;

    public Timestamp $timestamp;

    public function __construct(
        public CompiledCacheName $compiledCacheName,
        public CompiledCachePath $compiledCachePath,
        public Timestamp         $timestamp,
        public string            $sourceFingerprint,
        public mixed             $payload = null,
    ) {
        $this->compiledCacheName = $compiledCacheName;
        $this->compiledCachePath = $compiledCachePath;
        $this->timestamp         = $timestamp;
    }

    public static function create(
        string $name,
        string $path,
        int    $createdAt,
        string $sourceFingerprint,
    ) : self
    {
        return new self(
            name             : new CompiledCacheName(name: $name),
            path             : new CompiledCachePath(path: $path),
            createdAt        : Timestamp::fromUnixTime(timestamp: $createdAt),
            sourceFingerprint: $sourceFingerprint,
        );
    }

    public function toString() : string
    {
        return sprintf(
            'CompiledCacheArtifact(%s, %s)',
            $this->compiledCacheName->toString(),
            $this->compiledCachePath->toString(),
        );
    }
}
