<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\CompileCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\AtomicCompiledCacheWrite;
use Avax\Cache\System\Capabilities\ManageCompiledCache\BuildCompiledPhpPayload;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheArtifact;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheManifest;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheManifestEntry;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheName;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Capabilities\ManageCompiledCache\ResolveCompiledCachePath;
use Avax\Cache\System\Capabilities\ManageCompiledCache\WriteCompiledCacheManifest;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

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
        $nameObj = new CompiledCacheName($name);

        $payload = $build();

        $payloadBuilder = new BuildCompiledPhpPayload();
        $phpPayload     = $payloadBuilder->build($payload);

        $writer         = new AtomicCompiledCacheWrite($this->directory, $this->clock);
        $artifact = $writer->write($nameObj, $phpPayload);

        $fingerprint = $sources->fingerprint();

        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $artifactPath = $pathResolver->resolveArtifactPath($nameObj);
        $now = $this->clock->now();

        $entry = CompiledCacheManifestEntry::create(
            name             : $nameObj->toString(),
            path             : $artifactPath->toString(),
            createdAt        : $now->seconds,
            sourceFingerprint: $fingerprint
        );

        $this->manifest->set($entry);

        $manifestPath = $pathResolver->resolveManifestPath();
        $manifestWriter = new WriteCompiledCacheManifest($this->clock);
        $manifestWriter->write($this->manifest, $manifestPath);

        return $artifact;
    }
}