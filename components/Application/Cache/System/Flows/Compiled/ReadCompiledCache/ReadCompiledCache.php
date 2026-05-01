<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Compiled\ReadCompiledCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CheckCompiledCacheIsFresh;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheCouldNotBeRead;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheFreshness;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheName;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\ResolveCompiledCachePath;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\ValidateCompiledCachePayload;
use Avax\Components\Application\Cache\System\Flows\Compiled\CompileCache\CompileCache;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;

final readonly class ReadCompiledCache
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

    public function read(
        string               $name,
        callable             $build,
        CompiledCacheSources $sources,
    ) : mixed
    {
        $compiledCacheName = new CompiledCacheName(name: $name);

        $checkCompiledCacheIsFresh = new CheckCompiledCacheIsFresh(directory: $this->compiledCacheDirectory, manifest: $this->compiledCacheManifest);
        $compiledCacheFreshness = $checkCompiledCacheIsFresh->check(name: $compiledCacheName, sources: $sources);

        if ($compiledCacheFreshness === CompiledCacheFreshness::MISSING || $compiledCacheFreshness === CompiledCacheFreshness::STALE) {
            return $this->rebuild(name: $compiledCacheName, build: $build, sources: $sources);
        }

        return $this->requireCompiledArtifact(name: $compiledCacheName);
    }

    private function rebuild(
        CompiledCacheName    $name,
        callable             $build,
        CompiledCacheSources $sources,
    ) : mixed
    {
        $compileCache = new CompileCache(
            directory: $this->compiledCacheDirectory,
            manifest : $this->compiledCacheManifest,
            clock    : $this->clock,
        );

        $compileCache->compile(name: $name->toString(), build: $build, sources: $sources);

        return $this->requireCompiledArtifact(name: $name);
    }

    private function requireCompiledArtifact(CompiledCacheName $name) : mixed
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $name);

        if (! file_exists($compiledCachePath->toString())) {
            throw new CompiledCacheCouldNotBeRead(name: $name->toString());
        }

        $payload = require $compiledCachePath->toString();

        $validator = new ValidateCompiledCachePayload();
        $validator->validate(name: $name->toString(), payload: $payload);

        return $payload;
    }
}
