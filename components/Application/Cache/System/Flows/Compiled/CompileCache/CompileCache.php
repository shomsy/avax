<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Compiled\CompileCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\AtomicCompiledCacheWrite;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\BuildCompiledPhpPayload;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifestEntry;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheName;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\ResolveCompiledCachePath;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\WriteCompiledCacheManifest;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class CompileCache
{
    public function __construct(
        private CompiledCacheDirectory $compiledCacheDirectory,
        private CompiledCacheManifest  $compiledCacheManifest,
        private Filesystem             $filesystem,
        private Clock                  $clock = new SystemClock(),
    ) {
    }

    public function compile(
        string $name,
        callable $build,
        CompiledCacheSources $compiledCacheSources,
    ): CompiledCacheArtifact {
        $compiledCacheName = new CompiledCacheName(name: $name);

        $payload = $build();

        $buildCompiledPhpPayload = new BuildCompiledPhpPayload();
        $phpPayload = $buildCompiledPhpPayload->build(payload: $payload);

        $atomicCompiledCacheWrite   = new AtomicCompiledCacheWrite(
            compiledCacheDirectory: $this->compiledCacheDirectory,
            filesystem            : $this->filesystem,
            clock                 : $this->clock,
        );
        $compiledCacheArtifact = $atomicCompiledCacheWrite->write($compiledCacheName, $phpPayload);

        $fingerprint = $compiledCacheSources->fingerprint();

        $resolveCompiledCachePath = new ResolveCompiledCachePath($this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath($compiledCacheName);
        $now = $this->clock->now();

        $compiledCacheManifestEntry = CompiledCacheManifestEntry::create(
            name             : $compiledCacheName->toString(),
            path             : $compiledCachePath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: $fingerprint,
        );

        $this->compiledCacheManifest->set($compiledCacheManifestEntry);

        $manifestPath = $resolveCompiledCachePath->resolveManifestPath();
        $writeCompiledCacheManifest = new WriteCompiledCacheManifest($this->filesystem);
        $writeCompiledCacheManifest->write($this->compiledCacheManifest, $manifestPath);

        return $compiledCacheArtifact;
    }
}
