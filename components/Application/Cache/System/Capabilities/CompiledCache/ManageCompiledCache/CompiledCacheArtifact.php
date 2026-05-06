<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;

final readonly class CompiledCacheArtifact
{
    public function __construct(
        public CompiledCacheName $name,
        public CompiledCachePath $path,
        public Timestamp         $createdAt,
        public string            $sourceFingerprint,
        public mixed             $payload = null
    ) {}

    public static function create(
        string $name,
        string $path,
        int    $createdAt,
        string $sourceFingerprint
    ) : self
    {
        return new self(
            name             : new CompiledCacheName($name),
            path             : new CompiledCachePath($path),
            createdAt        : Timestamp::fromUnixTime($createdAt),
            sourceFingerprint: $sourceFingerprint
        );
    }

    public function toString() : string
    {
        return $this->path->toString();
    }
}
