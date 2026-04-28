<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\Compiled\ReadCompiledCache;

use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CheckCompiledCacheIsFresh;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheCouldNotBeRead;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheFreshness;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheName;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\ResolveCompiledCachePath;
use Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\ValidateCompiledCachePayload;
use Avax\Cache\System\Flows\Compiled\CompileCache\CompileCache;
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
        $nameObj = new CompiledCacheName(name: $name);

        $freshnessChecker = new CheckCompiledCacheIsFresh(directory: $this->directory, manifest: $this->manifest);
        $freshness        = $freshnessChecker->check(name: $nameObj, sources: $sources);

        if ($freshness === CompiledCacheFreshness::MISSING || $freshness === CompiledCacheFreshness::STALE) {
            return $this->rebuild(name: $nameObj, build: $build, sources: $sources);
        }

        return $this->requireCompiledArtifact(name: $nameObj);
    }

    private function rebuild(
        CompiledCacheName    $name,
        callable             $build,
        CompiledCacheSources $sources
    ) : mixed
    {
        $compileFlow = new CompileCache(
            directory: $this->directory,
            manifest : $this->manifest,
            clock    : $this->clock
        );

        $compileFlow->compile(name: $name->toString(), build: $build, sources: $sources);

        return $this->requireCompiledArtifact(name: $name);
    }

    private function requireCompiledArtifact(CompiledCacheName $name) : mixed
    {
        $pathResolver = new ResolveCompiledCachePath(directory: $this->directory);
        $path         = $pathResolver->resolveArtifactPath(name: $name);

        if (! file_exists($path->toString())) {
            throw new CompiledCacheCouldNotBeRead(name: $name->toString());
        }

        $payload = require $path->toString();

        $validator = new ValidateCompiledCachePayload();
        $validator->validate(name: $name->toString(), payload: $payload);

        return $payload;
    }
}