<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\ReadCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CheckCompiledCacheIsFresh;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheCouldNotBeRead;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheFreshness;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheManifest;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheName;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Capabilities\ManageCompiledCache\ResolveCompiledCachePath;
use Avax\Cache\System\Flows\CompileCache\CompileCache;
use Avax\Cache\System\Foundation\Time\Clock;
use Avax\Cache\System\Foundation\Time\SystemClock;

final class ReadCompiledCache
{
    public function __construct(
        private CompiledCacheDirectory $directory,
        private CompiledCacheManifest  $manifest,
        private Clock                  $clock = new SystemClock()
    ) {}

    public function read(
        string               $name,
        callable             $build,
        CompiledCacheSources $sources
    ) : mixed
    {
        $nameObj = new CompiledCacheName($name);

        $freshnessChecker = new CheckCompiledCacheIsFresh($this->directory, $this->manifest);
        $freshness        = $freshnessChecker->check($nameObj, $sources);

        if ($freshness === CompiledCacheFreshness::MISSING || $freshness === CompiledCacheFreshness::STALE) {
            return $this->rebuild($nameObj, $build, $sources);
        }

        return $this->requireCompiledArtifact($nameObj);
    }

    private function rebuild(
        CompiledCacheName    $name,
        callable             $build,
        CompiledCacheSources $sources
    ) : mixed
    {
        $compileFlow = new CompileCache(
            $this->directory,
            $this->manifest,
            $this->clock
        );

        $compileFlow->compile($name->toString(), $build, $sources);

        return $this->requireCompiledArtifact($name);
    }

    private function requireCompiledArtifact(CompiledCacheName $name) : mixed
    {
        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $path         = $pathResolver->resolveArtifactPath($name);

        if (! file_exists($path->toString())) {
            throw new CompiledCacheCouldNotBeRead($name->toString());
        }

        $payload = require $path->toString();

        return $payload;
    }
}