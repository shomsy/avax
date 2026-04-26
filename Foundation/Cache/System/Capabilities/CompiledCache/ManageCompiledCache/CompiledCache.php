<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

interface CompiledCache
{
    /**
     * Read a compiled artifact.
     *
     * If the artifact is missing or stale, rebuild it using the provided builder.
     *
     * @template T
     *
     * @param callable(): T $build
     *
     * @return T
     */
    public function read(string $name, callable $build, CompiledCacheSources $sources) : mixed;

    /**
     * Compile and write an artifact immediately.
     */
    public function compile(string $name, callable $build, CompiledCacheSources $sources) : CompiledCacheArtifact;

    /**
     * Delete one compiled artifact.
     */
    public function clear(string $name) : void;

    /**
     * Delete all compiled artifacts.
     */
    public function clearAll() : void;
}