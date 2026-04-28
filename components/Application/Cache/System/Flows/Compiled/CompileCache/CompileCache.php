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

final class CompileCache
{
    public function __construct(
        private CompiledCacheDirectory $directory,
        private CompiledCacheManifest  $manifest,
        private Clock                  $clock = new SystemClock()
    ) {}

    public function compile(
        string               $name,
        callable             $build,
        CompiledCacheSources $sources
    ) : CompiledCacheArtifact
    {
        $nameObj = new CompiledCacheName(name: $name);

        $payload = $build();

        $payloadBuilder = new BuildCompiledPhpPayload();
        $phpPayload     = $payloadBuilder->build(payload: $payload);

        $writer   = new AtomicCompiledCacheWrite(directory: $this->directory, clock: $this->clock);
        $artifact = $writer->write(name: $nameObj, payload: $phpPayload);

        $fingerprint = $sources->fingerprint();

        $pathResolver = new ResolveCompiledCachePath(directory: $this->directory);
        $artifactPath = $pathResolver->resolveArtifactPath(name: $nameObj);
        $now          = $this->clock->now();

        $entry = CompiledCacheManifestEntry::create(
            name             : $nameObj->toString(),
            path             : $artifactPath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: $fingerprint
        );

        $this->manifest->set(entry: $entry);

        $manifestPath   = $pathResolver->resolveManifestPath();
        $manifestWriter = new WriteCompiledCacheManifest(clock: $this->clock);
        $manifestWriter->write(manifest: $this->manifest, manifestPath: $manifestPath);

        return $artifact;
    }
}