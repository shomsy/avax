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
    public function __construct(private CompiledCacheDirectory $compiledCacheDirectory, private CompiledCacheManifest $compiledCacheManifest, private Clock $clock = new SystemClock()) {}

    public function read(
        string $name,
        callable $build,
        CompiledCacheSources $compiledCacheSources,
    ) : mixed
    {
        $compiledCacheName = new CompiledCacheName(name: $name);

        $checkCompiledCacheIsFresh = new CheckCompiledCacheIsFresh($this->compiledCacheDirectory, $this->compiledCacheManifest);
        $compiledCacheFreshness = $checkCompiledCacheIsFresh->check($compiledCacheName, $compiledCacheSources);

        if ($compiledCacheFreshness === CompiledCacheFreshness::MISSING || $compiledCacheFreshness === CompiledCacheFreshness::STALE) {
            return $this->rebuild($compiledCacheName, $build, $compiledCacheSources);
        }

        return $this->requireCompiledArtifact($compiledCacheName);
    }

    private function rebuild(
        CompiledCacheName $compiledCacheName,
        callable $build,
        CompiledCacheSources $compiledCacheSources,
    ) : mixed
    {
        $compileCache = new CompileCache(
            compiledCacheDirectory: $this->compiledCacheDirectory,
            compiledCacheManifest : $this->compiledCacheManifest,
            clock                 : $this->clock,
        );

        $compileCache->compile(name: $compiledCacheName->toString(), build: $build, compiledCacheSources: $compiledCacheSources);

        return $this->requireCompiledArtifact($compiledCacheName);
    }

    private function requireCompiledArtifact(CompiledCacheName $compiledCacheName) : mixed
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(compiledCacheDirectory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(compiledCacheName: $compiledCacheName);

        if (! file_exists($compiledCachePath->toString())) {
            throw new CompiledCacheCouldNotBeRead(name: $compiledCacheName->toString());
        }

        $payload = require $compiledCachePath->toString();

        $validator = new ValidateCompiledCachePayload();
        $validator->validate(name: $compiledCacheName->toString(), payload: $payload);

        return $payload;
    }
}
