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

final readonly class CompileCache
{
    private CompiledCacheDirectory $compiledCacheDirectory;

    private CompiledCacheManifest $compiledCacheManifest;

    public function __construct(
        CompiledCacheDirectory $directory,
        CompiledCacheManifest  $manifest,
        private Clock                  $clock = new SystemClock(),
    )
    {
        $this->compiledCacheDirectory = $directory;
        $this->compiledCacheManifest  = $manifest;
    }

    public function compile(
        string               $name,
        callable             $build,
        CompiledCacheSources $sources,
    ) : CompiledCacheArtifact
    {
        $compiledCacheName = new CompiledCacheName(name: $name);

        $payload = $build();

        $buildCompiledPhpPayload = new BuildCompiledPhpPayload();
        $phpPayload              = $buildCompiledPhpPayload->build(payload: $payload);

        $atomicCompiledCacheWrite = new AtomicCompiledCacheWrite(directory: $this->compiledCacheDirectory, clock: $this->clock);
        $compiledCacheArtifact    = $atomicCompiledCacheWrite->write(name: $compiledCacheName, payload: $phpPayload);

        $fingerprint                = $sources->fingerprint();

        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath        = $resolveCompiledCachePath->resolveArtifactPath(name: $compiledCacheName);
        $now          = $this->clock->now();

        $compiledCacheManifestEntry = CompiledCacheManifestEntry::create(
            name             : $compiledCacheName->toString(),
            path             : $compiledCachePath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: $fingerprint,
        );

        $this->compiledCacheManifest->set(entry: $compiledCacheManifestEntry);

        $manifestPath               = $resolveCompiledCachePath->resolveManifestPath();
        $writeCompiledCacheManifest = new WriteCompiledCacheManifest();
        $writeCompiledCacheManifest->write(manifest: $this->compiledCacheManifest, manifestPath: $manifestPath);

        return $compiledCacheArtifact;
    }
}
